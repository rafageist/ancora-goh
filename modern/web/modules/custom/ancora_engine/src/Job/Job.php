<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Job;

/**
 * A generation job, as read from the ancora_engine_job table.
 */
final class Job {

  public function __construct(
    public readonly int $id,
    public readonly string $uuid,
    public readonly JobScope $scope,
    public readonly JobOptions $options,
    public readonly int $timeLimit,
    public readonly JobState $state,
    public readonly bool $cancelRequested,
    public readonly ?string $cancelMode,
    public readonly int $requestedBy,
    public readonly ?int $cancelledBy,
    public readonly ?int $retryOf,
    public readonly int $created,
    public readonly int $changed,
    public readonly ?int $started,
    public readonly ?int $finished,
    public readonly ?int $heartbeat,
    public readonly ?string $workerId,
    public readonly int $attempts,
    public readonly ?array $result,
    public readonly ?string $error,
    public readonly ?JobSource $source = NULL,
  ) {}

  /**
   * The prefix of the engine ids of the job's plan ('' without a source).
   */
  public function idPrefix(): string {
    return $this->source?->idPrefix ?? '';
  }

  /**
   * Builds a job from a database row.
   */
  public static function fromRow(object $row): self {
    $int = static fn ($value): ?int => $value === NULL ? NULL : (int) $value;
    $result = $row->result === NULL ? NULL : json_decode($row->result, TRUE, 16, JSON_THROW_ON_ERROR);
    return new self(
      (int) $row->id,
      $row->uuid,
      new JobScope($row->scope_specialty, $row->scope_level, $row->scope_period),
      JobOptions::fromArray(json_decode($row->options, TRUE, 4, JSON_THROW_ON_ERROR)),
      (int) $row->time_limit,
      JobState::from($row->state),
      (bool) $row->cancel_requested,
      $row->cancel_mode,
      (int) $row->requested_by,
      $int($row->cancelled_by),
      $int($row->retry_of),
      (int) $row->created,
      (int) $row->changed,
      $int($row->started),
      $int($row->finished),
      $int($row->heartbeat),
      $row->worker_id,
      (int) $row->attempts,
      is_array($result) ? $result : NULL,
      $row->error,
      JobSource::fromRow($row),
    );
  }

}
