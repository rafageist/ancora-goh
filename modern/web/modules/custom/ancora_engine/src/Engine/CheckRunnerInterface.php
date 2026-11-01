<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Engine;

/**
 * Runs read-only check requests in workspaces (#130).
 *
 * Registered as "ancora_engine.check_runner" by the module that owns the
 * checks (ancora_scheduling). A check is NOT a generation job: it has its
 * own table and lifecycle, it never appears in the job queue, it is never
 * counted by the one-running-job rule, and it writes nothing that the
 * generation input reads, so it cannot make a job's publication stale. The
 * worker runs the pending checks at the start of each cycle (workspace mode
 * only), before it claims a generation job.
 */
interface CheckRunnerInterface {

  /**
   * Runs pending checks.
   *
   * @param int $max
   *   The most checks to run in this cycle.
   *
   * @return int
   *   The number of checks run.
   */
  public function runPending(int $max): int;

}
