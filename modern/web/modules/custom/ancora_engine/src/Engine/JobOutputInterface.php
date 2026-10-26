<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Engine;

use Drupal\ancora_engine\Job\Job;

/**
 * Publishes the output of a workspace run as Drupal records (#95).
 *
 * The worker calls it after a workspace run returned normally (complete,
 * partial or soft-cancelled: what was placed is the timetable, as in
 * legacy), before it records the job's outcome. The implementation
 * (ancora_scheduling, "ancora_engine.job_output") writes through the Entity
 * API in one transaction of Drupal's connection: all of the run's new
 * placements or none of them.
 */
interface JobOutputInterface {

  /**
   * Publishes the run's new placements.
   *
   * @param \Drupal\ancora_engine\Job\Job $job
   *   The running job.
   * @param \Drupal\ancora_engine\Engine\RunResult $run
   *   The returned run: $run->output holds the new placements, $run->input
   *   the input summary (with the snapshot version).
   *
   * @return array
   *   A JSON-serializable summary for the job result: "published" (records
   *   created) and "already_published" (records that existed, a replay).
   *
   * @throws \Drupal\ancora_engine\Engine\PublicationException
   *   When nothing was published: the input changed after the snapshot
   *   ("stale"), the output is invalid ("invalid"), the run is no longer
   *   this attempt's ("fenced"), or saving failed ("failed").
   */
  public function publish(Job $job, RunResult $run): array;

}
