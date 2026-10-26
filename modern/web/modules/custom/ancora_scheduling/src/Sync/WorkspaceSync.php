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
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use PgSql\Connection as PgConnection;

/**
 * Loads a job's input into its workspace (workspace mode, #93, #95).
 *
 * Registered as "ancora_engine.workspace_input". The worker calls it on the
 * job's own connection, with the job role and the workspace search_path set
 * (WorkspaceClient). Steps:
 *
 * 1. Checks the job's plan, period, specialty and level, as EngineSync does.
 * 2. Reads, in ONE REPEATABLE READ transaction of Drupal's own
 *    connection, the legacy rows of the whole site (SiteRows::site(), the
 *    rows EngineSync writes) and the stored placements of the site
 *    (SiteRows::placements(), entity id order = legacy entry order).
 *    Their version (SiteRows::version()) is recorded: the publication
 *    refuses a result whose input changed meanwhile.
 * 3. In the workspace, in one transaction: writes the rows with the
 *    unchanged LegacyTableSync (legacy triggers on), inserts the stored
 *    placements with their engine ids (the legacy RULEs rebuild their
 *    occupancy rows, as #93 proved), keeps the #92 closure of the request
 *    (scheduler_control.closure_keep, prune) and commits. Then ANALYZE.
 *
 * Existing placements are never deleted or recomputed (legacy continuation,
 * PAR-085; no D7). A stored placement whose template, group, teacher or
 * room no longer exists cannot be loaded (its row would be NULL). The input
 * then fails and nothing runs; deleting a record deletes its placements
 * (PlacementCascade), as the legacy foreign keys do.
 *
 * The whole site is written and then reduced to the closure (the #93
 * prototype's approach), not extracted first. The legacy breakdown triggers
 * create a breakdown for every subject and period of a creator as the rows
 * are written, so rows outside the closure appear in the workspace anyway.
 * The closure exists in one implementation (dev/workspace/workspace.sql).
 * See docs/modernization/placement-promotion.md.
 */
final class WorkspaceSync implements JobInputInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AcademicExport $export,
    private readonly MemoryCacheInterface $entityMemoryCache,
    private readonly SiteRows $rows,
    private readonly Connection $database,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function prepare(Job $job, PgConnection $connection, EngineClient $engine): array {
    $source = $job->source;
    if ($source === NULL) {
      throw new \LogicException('Only a job with a source is synced.');
    }
    $this->entityMemoryCache->deleteAll();
    [$plan, $period, $specialty, $level] = [
      $this->entityTypeManager->getStorage('ancora_plan')->load($source->planId),
      $this->entityTypeManager->getStorage('ancora_period')->load($source->periodId),
      $this->entityTypeManager->getStorage('ancora_specialty')->load($source->specialtyId),
      $this->entityTypeManager->getStorage('ancora_level')->load($source->levelId),
    ];
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

    [$rows, $creators, $placements, $hash, $snapshot] = $this->snapshot($plan);
    $version = SiteRows::version($rows, $placements);

    $engine->affected($connection, 'BEGIN');
    try {
      $changes = (new LegacyTableSync($engine, $connection))->sync($rows, $creators);
      foreach ($placements as $p) {
        $engine->affected($connection, 'INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)', [
          $p['id'], $p['plantilla'], $p['grupo'], $p['profesor'], $p['lugar'], $p['dia'], $p['turno'],
          $p['momento_de_colocacion'] === NULL ? NULL : var_export($p['momento_de_colocacion'], TRUE), $p['creador'],
        ]);
      }
      $input_rows = (int) $engine->rows($connection, 'SELECT scheduler_control.ws_rows() AS n')[0]['n'];
      [$e, $l, $p] = $job->scope->toParameters();
      $closure = [$e, $l, $p, $job->options->buscalugar ? 't' : 'f'];
      $kept = (int) $engine->rows($connection, 'SELECT scheduler_control.closure_keep($1, $2, $3, $4) AS n', $closure)[0]['n'];
      $pruned = (int) $engine->rows($connection, 'SELECT scheduler_control.prune() AS n')[0]['n'];
      $engine->affected($connection, 'COMMIT');
    }
    catch (\Throwable $e) {
      @pg_query($connection, 'ROLLBACK');
      throw $e instanceof \RuntimeException ? $e : new \RuntimeException($e->getMessage(), 0, $e);
    }
    $engine->rows($connection, 'SELECT scheduler_control.ws_analyze()');

    return [
      'hash' => $hash,
      'schema' => AcademicExport::SCHEMA,
      'site_version' => $version,
      'snapshot' => $snapshot,
      'rows' => array_sum(array_map('count', $rows)),
      'placements_loaded' => count($placements),
      'placement_ids' => array_column($placements, 'id'),
      'input_rows' => $input_rows,
      'closure_rows' => $kept,
      'pruned' => $pruned,
    ] + $changes;
  }

  /**
   * The current version of the input: site rows and stored placements.
   *
   * Read by the publication, in its own transaction, to refuse a result
   * whose input changed after the snapshot.
   */
  public function currentVersion(): string {
    $this->entityMemoryCache->deleteAll();
    [$rows] = $this->rows->site();
    return SiteRows::version($rows, $this->rows->placements($rows));
  }

  /**
   * Reads the input in one REPEATABLE READ transaction.
   *
   * @return array
   *   The site rows, their creators, the stored placements, the plan's
   *   input hash (ScopeMapper::hash(), as EngineSync) and the snapshot id.
   */
  private function snapshot(Plan $plan): array {
    // Not READ ONLY: loading entities writes Drupal's entity cache.
    $this->database->query('SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    try {
      $transaction = $this->database->startTransaction();
      $start = (string) $this->database->query('SELECT pg_current_snapshot()::text')->fetchField();
      [$rows, $creators] = $this->rows->site();
      $placements = $this->rows->placements($rows);
      $hash = ScopeMapper::hash($this->export->export($plan));
      $end = (string) $this->database->query('SELECT pg_current_snapshot()::text')->fetchField();
      $isolation = (string) $this->database->query('SHOW transaction_isolation')->fetchField();
      unset($transaction);
    }
    finally {
      $this->database->query('SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL READ COMMITTED');
    }
    if ($start !== $end || $isolation !== 'repeatable read') {
      throw new \RuntimeException('The input was not read from one consistent snapshot.');
    }
    return [$rows, $creators, $placements, $hash, ['isolation' => $isolation, 'snapshot' => $start]];
  }

}
