<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Sync;

use Drupal\ancora_academic\Entity\Level;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Specialty;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\Engine\JobInputInterface;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use PgSql\Connection as PgConnection;

/**
 * Syncs the site's planning data into the engine database, inside the run.
 *
 * Registered as "ancora_engine.job_input", the optional job input service of
 * the engine worker. For a job with a source it:
 * 1. loads the plan, period, specialty and level the job was queued for, and
 *    refuses the job when one is gone, or when the period moved to another
 *    plan or the specialty or level to another user;
 * 2. records the input snapshot: the sha256 of the canonical export of the
 *    job's plan (its records and its creator's catalogues);
 * 3. builds the legacy rows of the whole site (LegacyRows, numbered ids):
 *    every plan with its creator's catalogues, and the catalogues of users
 *    without a plan, each owned by the legacy user of its creator
 *    (ScopeMapper::creatorOf()), and writes them with replace semantics
 *    (LegacyTableSync).
 *
 * The whole site, because the legacy database the engine was written for
 * holds every user's rows at all times: a user's catalogues serve all the
 * user's plans, the breakdown triggers pair a subject with the periods of
 * every plan of its creator, and generar() reads data across plans (for
 * example the groups of every plan, quirk Q-02, and the activity type order
 * of every user, Q-03). The diff keeps unchanged rows untouched.
 *
 * It runs in the worker's engine transaction, so a failure here, or any
 * failure of the run afterwards, rolls the sync back too and leaves the
 * previous timetable as it was.
 */
final class EngineSync implements JobInputInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AcademicExport $export,
    private readonly MemoryCacheInterface $entityMemoryCache,
    private readonly SiteRows $rows,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function prepare(Job $job, PgConnection $connection, EngineClient $engine): array {
    $source = $job->source;
    if ($source === NULL) {
      throw new \LogicException('Only a job with a source is synced.');
    }
    // A looping worker must see the records as they are now, not as an
    // earlier job loaded them.
    $this->entityMemoryCache->deleteAll();
    $plan = $this->load('ancora_plan', $source->planId);
    $period = $this->load('ancora_period', $source->periodId);
    $specialty = $this->load('ancora_specialty', $source->specialtyId);
    $level = $this->load('ancora_level', $source->levelId);
    if (!$plan instanceof Plan || !$period instanceof Period || !$specialty instanceof Specialty || !$level instanceof Level) {
      throw new \RuntimeException('The plan, period, specialty or level of this job no longer exists.');
    }
    try {
      $scope = ScopeMapper::scope($plan, $period, $specialty, $level);
    }
    catch (\InvalidArgumentException $e) {
      throw new \RuntimeException($e->getMessage(), 0, $e);
    }
    if ($scope->toParameters() !== $job->scope->toParameters() || $source->idPrefix !== ScopeMapper::ID_MARKER) {
      throw new \RuntimeException('The engine ids of the plan, period, specialty or level changed after the job was queued; queue it again.');
    }

    $hash = ScopeMapper::hash($this->export->export($plan));
    [$rows, $creators, $issues] = $this->siteRows();
    $changes = (new LegacyTableSync($engine, $connection))->sync($rows, $creators);
    return [
      'hash' => $hash,
      'schema' => AcademicExport::SCHEMA,
      'issues' => $issues,
      'rows' => array_sum(array_map('count', $rows)),
    ] + $changes;
  }

  /**
   * The legacy rows of the whole site (SiteRows::site()).
   *
   * @return array{0: array<string, list<array<string, scalar|null>>>, 1: list<string>, 2: int}
   *   The rows by table (LegacyRows order), the legacy users who own them,
   *   and the number of export issues of every plan.
   */
  private function siteRows(): array {
    return $this->rows->site();
  }

  /**
   * Loads one academic record.
   */
  private function load(string $entityTypeId, int $id): ?object {
    return $this->entityTypeManager->getStorage($entityTypeId)->load($id);
  }

}
