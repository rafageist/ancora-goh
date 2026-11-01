<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Job;

/**
 * States of a generation job and the transitions between them.
 *
 * - queued: waiting for a worker.
 * - running: claimed by one worker (at most one per site).
 * - succeeded: generar() returned normally, no cancellation was requested,
 *   every demand is placed. The only state whose placements are a final
 *   timetable.
 * - partial: generar() returned normally without cancellation, but some
 *   demands could not be placed (over-constrained input). The placements are
 *   kept as a draft for review, never as a final timetable.
 * - cancelled: a cancellation was requested. A soft cancellation keeps the
 *   placements made so far (result "kept": "partial"); a hard cancellation
 *   rolls the run back (result "kept": "none").
 * - timed_out: statement_timeout (or the worker's wall-clock guard) stopped
 *   the run; it was rolled back, nothing of it is kept.
 * - failed: any other error, or a lost worker after its last attempt; the run
 *   was rolled back.
 *
 * A stale running job (heartbeat too old) goes back to queued once.
 */
enum JobState: string {

  case Queued = 'queued';
  case Running = 'running';
  case Succeeded = 'succeeded';
  case Partial = 'partial';
  case Cancelled = 'cancelled';
  case TimedOut = 'timed_out';
  case Failed = 'failed';

  /**
   * Whether the job will not change any more.
   */
  public function isTerminal(): bool {
    return !in_array($this, [self::Queued, self::Running], TRUE);
  }

  /**
   * Whether the placements of the run are a final timetable.
   */
  public function isFinalTimetable(): bool {
    return $this === self::Succeeded;
  }

  /**
   * Whether the state machine allows going from this state to another.
   */
  public function canTransitionTo(JobState $to): bool {
    return match ($this) {
      self::Queued => in_array($to, [self::Running, self::Cancelled], TRUE),
      self::Running => in_array($to, [
        self::Succeeded,
        self::Partial,
        self::Cancelled,
        self::TimedOut,
        self::Failed,
        self::Queued,
      ], TRUE),
      default => FALSE,
    };
  }

}
