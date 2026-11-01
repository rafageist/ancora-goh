<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Job;

/**
 * Where the input of a job comes from: Drupal academic records.
 *
 * A job with a source is synced before it runs: in the run's own engine
 * transaction, the worker asks the job input service
 * (JobInputInterface) to load the plan's records into the engine database.
 * A job without a source runs on data already in the engine database.
 *
 * The ids are entity ids of the site's Drupal database (ancora_plan,
 * ancora_period, ancora_specialty, ancora_level). The id prefix ("id
 * marker") tells how the job's engine ids are built: the placement digest
 * lists each id from after the marker's first occurrence
 * (EngineClient::PLACEMENTS). Jobs of the academic sync record ":", the
 * separator of their numbered ids ("0000000012:G1"); jobs queued before
 * #70 recorded their plan's prefix ("p3:").
 */
final class JobSource {

  /**
   * Largest prefix length (the job table column).
   */
  public const MAX_PREFIX_LENGTH = 64;

  public function __construct(
    public readonly int $planId,
    public readonly int $periodId,
    public readonly int $specialtyId,
    public readonly int $levelId,
    public readonly string $idPrefix,
  ) {
    foreach ([$planId, $periodId, $specialtyId, $levelId] as $id) {
      if ($id < 1) {
        throw new \InvalidArgumentException('The source ids must be positive entity ids.');
      }
    }
    if (strlen($idPrefix) > self::MAX_PREFIX_LENGTH || !preg_match('/^[A-Za-z0-9._:-]*$/', $idPrefix)) {
      throw new \InvalidArgumentException('The engine id prefix may contain only letters, digits, ".", "_", ":" and "-".');
    }
  }

  /**
   * Builds the source of a job row, or NULL for a job without a source.
   */
  public static function fromRow(object $row): ?self {
    if (!isset($row->plan_id)) {
      return NULL;
    }
    return new self((int) $row->plan_id, (int) $row->period_id, (int) $row->specialty_id, (int) $row->level_id, (string) $row->id_prefix);
  }

}
