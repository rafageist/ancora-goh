<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Migration;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\Engine\PublicationException;
use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\Timetable\PlacementMapper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\user\UserInterface;

/**
 * Imports a two-database site's placements as placement entities (#96).
 *
 * The migration of a site to the single-database layout
 * (scripts/ancora-site.sh migrate-single-db) runs this while the site is
 * still configured for its engine database tt_<slug>_engine. The placements
 * are the rows of "actividad" there: the only engine data that is not a
 * projection of Drupal's records (the sync rewrites everything else).
 *
 * - Read: one read-only connection, the rows in physical order (ctid), the
 *   order in which legacy reads them; the placement entities are created in
 *   that order, so the entity id order a workspace run loads them in is the
 *   same (SiteRows::placements()).
 * - Identity: engine_id is the row's id, unchanged; the other columns are
 *   mapped back to the records and checked as a publication is
 *   (PlacementMapper).
 * - Job: the two-database layout never recorded which job placed a row. Each
 *   placement is linked to the job whose placements the two-database
 *   timetable showed for its scope (plan, period, specialty and level of the
 *   template): the latest job of the scope that changed its placements
 *   (JobRepository::changedPlacements(), as TimetableReader reads them). The
 *   placing moment (momento_de_colocacion) is kept as placed_at.
 * - Refusals (nothing is imported): a row that does not map to the records
 *   or fails a check, a scope without such a job, a row of
 *   otros_recursos_usados (other resources do not exist in Drupal, D9), or
 *   an existing placement entity that is not one of these rows.
 * - Idempotent: a placement entity with the row's engine id is kept as it is.
 *   One transaction of Drupal's connection: a failure imports nothing, and
 *   running it again is the recovery.
 */
final class TwoDatabaseImport {

  /**
   * The advisory lock of placement writes (PlacementPublisher::LOCK).
   */
  private const LOCK = 2026095;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
    private readonly JobRepository $jobs,
    private readonly EngineClient $engine,
    private readonly EngineSettings $settings,
    private readonly AccountSwitcherInterface $accountSwitcher,
    private readonly PlacementMapper $mapper,
  ) {}

  /**
   * Reads the engine database: the placement rows and what is not imported.
   *
   * @return array{rows: list<array<string, string|null>>, resources: int, cancel_requests: int}
   *   The rows of "actividad" in physical order, the number of rows of
   *   otros_recursos_usados and of leftover soft cancellation requests
   *   (generador.cancelar, D12: they are not carried over; a workspace run
   *   only sees the requests of its own job).
   */
  public function read(): array {
    if ($this->settings->isWorkspace() || !$this->settings->isConfigured()) {
      throw new \RuntimeException('The site is not configured for its engine database (two-database layout).');
    }
    $conn = $this->engine->connectReadOnly();
    try {
      $rows = $this->engine->rows($conn, 'SELECT id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador FROM actividad ORDER BY ctid');
      $resources = (int) $this->engine->rows($conn, 'SELECT count(*) AS n FROM otros_recursos_usados')[0]['n'];
      $cancel = (int) $this->engine->rows($conn, 'SELECT count(*) AS n FROM generador.cancelar')[0]['n'];
    }
    finally {
      pg_close($conn);
    }
    return ['rows' => $rows, 'resources' => $resources, 'cancel_requests' => $cancel];
  }

  /**
   * Maps the rows to placement values, with every check, without writing.
   *
   * @return array{placements: list<array>, problems: list<string>}
   *   The values (with "job") in row order, and every problem found.
   */
  public function plan(array $read): array {
    $problems = [];
    if ($read['resources'] > 0) {
      $problems[] = sprintf('otros_recursos_usados holds %d rows; other resources do not exist in Drupal (D9), they cannot be imported.', $read['resources']);
    }
    $placements = [];
    $jobs = [];
    foreach ($read['rows'] as $i => $row) {
      $what = sprintf('Row %d (id %s)', $i + 1, $row['id']);
      $template = $this->mapper->template($row['plantilla']);
      $period = $template?->get('period')->entity;
      $subject = $template?->getSubject();
      if (!$period instanceof Period || $subject === NULL || $subject->getSpecialty() === NULL || $subject->getLevel() === NULL) {
        $problems[] = "$what: the template, its period or its subject no longer exists.";
        continue;
      }
      $creator = (int) $period->getCreatorId();
      try {
        $values = $this->mapper->values($row, $period, $creator, $what);
      }
      catch (PublicationException $e) {
        $problems[] = $e->getMessage();
        continue;
      }
      $ids = [
        (int) $period->getPlanId(),
        (int) $period->id(),
        (int) $subject->getSpecialty()->id(),
        (int) $subject->getLevel()->id(),
      ];
      $scope = implode(':', $ids);
      if (!array_key_exists($scope, $jobs)) {
        $jobs[$scope] = $this->scopeJob(...$ids);
      }
      if ($jobs[$scope] === NULL) {
        $problems[] = "$what: no job of its scope (plan, period, specialty, level) changed placements; the placement has no job to belong to.";
        continue;
      }
      $placements[] = $values + ['job' => $jobs[$scope]->id, 'creator' => $creator];
    }
    $seen = [];
    foreach ($placements as $p) {
      $key = $p['template'] . ':' . $p['student_group'];
      if (isset($seen[$key])) {
        $problems[] = sprintf('Rows %s and %s: the same template is placed twice for one group.', $seen[$key], $p['engine_id']);
      }
      $seen[$key] = $p['engine_id'];
    }
    $others = $this->foreign($read);
    if ($others > 0) {
      $problems[] = sprintf('%d placement entities exist that are not rows of the engine database; the site was not a two-database site, or it ran in the workspace mode.', $others);
    }
    return ['placements' => $placements, 'problems' => $problems];
  }

  /**
   * Imports the planned placements in one transaction.
   *
   * @param array $plan
   *   The result of plan(); it must have no problems.
   * @param int|null $failAfter
   *   Test hook: throw after this many new placements (the transaction is
   *   rolled back). NULL in production.
   *
   * @return array{imported: int, kept: int}
   *   New placement entities, and existing ones (an earlier run).
   */
  public function import(array $plan, ?int $failAfter = NULL): array {
    if ($plan['problems'] !== []) {
      throw new \RuntimeException("The placements cannot be imported:\n" . implode("\n", $plan['problems']));
    }
    $storage = $this->entityTypeManager->getStorage('ancora_placement');
    $users = $this->entityTypeManager->getStorage('user');
    // The caches the validation needs, before the transaction
    // (PlacementMapper::warmAccess()).
    foreach (array_unique(array_column($plan['placements'], 'creator')) as $creator) {
      $owner = $users->load($creator);
      if ($owner instanceof UserInterface) {
        $this->mapper->warmAccess($owner);
      }
    }
    $transaction = $this->database->startTransaction();
    $imported = 0;
    $kept = 0;
    try {
      $this->database->query('SELECT pg_advisory_xact_lock(:key)', [':key' => self::LOCK]);
      foreach ($plan['placements'] as $values) {
        if ($storage->getQuery()->accessCheck(FALSE)->condition('engine_id', $values['engine_id'])->count()->execute() > 0) {
          $kept++;
          continue;
        }
        $creator = $values['creator'];
        unset($values['creator']);
        $owner = $users->load($creator);
        if (!$owner instanceof UserInterface) {
          throw new \RuntimeException(sprintf('Placement %s: the creator of its plan no longer exists.', $values['engine_id']));
        }
        // Validated as the plan's creator: the reference checks then apply
        // that user's access, as a publication does.
        $this->accountSwitcher->switchTo($owner);
        try {
          $placement = $storage->create($values);
          assert($placement instanceof Placement);
          $violations = $placement->validate();
          if ($violations->count() > 0) {
            throw new \RuntimeException(sprintf('Placement %s is invalid: %s', $values['engine_id'], $violations->get(0)->getMessage()));
          }
          $placement->save();
        }
        finally {
          $this->accountSwitcher->switchBack();
        }
        $imported++;
        if ($failAfter !== NULL && $imported >= $failAfter) {
          throw new \RuntimeException(sprintf('test hook: stopping the import after %d placements', $imported));
        }
      }
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }
    unset($transaction);
    return ['imported' => $imported, 'kept' => $kept];
  }

  /**
   * The number of placement entities that are not rows of the engine database.
   */
  public function foreign(array $read): int {
    $ids = array_column($read['rows'], 'id');
    $query = $this->entityTypeManager->getStorage('ancora_placement')->getQuery()->accessCheck(FALSE);
    if ($ids !== []) {
      $query->condition('engine_id', $ids, 'NOT IN');
    }
    return (int) $query->count()->execute();
  }

  /**
   * The latest job of a scope that changed its placements, or NULL.
   */
  private function scopeJob(int $planId, int $periodId, int $specialtyId, int $levelId): ?Job {
    foreach ($this->jobs->forSource($planId, $periodId, PHP_INT_MAX) as $job) {
      if ($job->source !== NULL && $job->source->specialtyId === $specialtyId && $job->source->levelId === $levelId
        && JobRepository::changedPlacements($job)) {
        return $job;
      }
    }
    return NULL;
  }

}
