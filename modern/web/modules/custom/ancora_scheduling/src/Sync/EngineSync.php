<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Sync;

use Drupal\ancora_academic\Entity\Level;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Specialty;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\Engine\JobInputInterface;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use PgSql\Connection as PgConnection;

/**
 * Syncs a job's plan from Drupal into the engine database, inside the run.
 *
 * Registered as "ancora_engine.job_input", the optional job input service of
 * the engine worker. For a job with a source it:
 * 1. loads the plan, period, specialty and level the job was queued for, and
 *    refuses the job when one is gone, moved to another plan, or when its
 *    code changed since the job was queued (the queued scope would no longer
 *    match the data);
 * 2. exports the whole plan (AcademicExport, every period), which keeps the
 *    legacy ids of all the plan's scopes stable whichever period runs;
 * 3. records the input snapshot: the sha256 of the canonical export;
 * 4. builds the legacy rows with the plan's prefix and owner (LegacyRows)
 *    and writes them with replace semantics (LegacyTableSync).
 *
 * It runs in the worker's engine transaction, so a failure here, or any
 * failure of the run afterwards, rolls the sync back too and leaves the
 * previous timetable as it was.
 */
final class EngineSync implements JobInputInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AcademicExport $export,
    private readonly LegacyRows $legacyRows,
    private readonly MemoryCacheInterface $entityMemoryCache,
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
    if ($scope->toParameters() !== $job->scope->toParameters() || ScopeMapper::prefix($plan) !== $source->idPrefix) {
      throw new \RuntimeException('The codes of the plan, period, specialty or level changed after the job was queued; queue it again.');
    }

    $export = $this->export->export($plan);
    $hash = ScopeMapper::hash($export);
    $rows = $this->legacyRows->build($export, ScopeMapper::creator($plan), $source->idPrefix);
    $changes = (new LegacyTableSync($engine, $connection))->sync($rows, $source->idPrefix, ScopeMapper::creator($plan));
    return [
      'hash' => $hash,
      'schema' => $export['schema'],
      'issues' => count($export['issues']),
      'rows' => array_sum(array_map('count', $rows)),
    ] + $changes;
  }

  /**
   * Loads one academic record.
   */
  private function load(string $entityTypeId, int $id): ?object {
    return $this->entityTypeManager->getStorage($entityTypeId)->load($id);
  }

}
