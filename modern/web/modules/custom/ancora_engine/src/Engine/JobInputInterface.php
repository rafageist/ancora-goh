<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Engine;

use Drupal\ancora_engine\Job\Job;
use PgSql\Connection as PgConnection;

/**
 * Loads the input of a job with a source into the engine database.
 *
 * The worker calls it inside the run's engine transaction, after BEGIN and
 * before the scope's placements are replaced and generar() runs. Whatever it
 * writes is therefore committed or rolled back together with the run: a
 * failed input load, a failed or cancelled run or a timeout leave the engine
 * database (and the previous timetable) as they were.
 *
 * The service is optional: the module that provides it (ancora_scheduling)
 * registers it as "ancora_engine.job_input". Without it, a job with a source
 * fails instead of running on stale data.
 */
interface JobInputInterface {

  /**
   * Loads the job's input.
   *
   * @param \Drupal\ancora_engine\Job\Job $job
   *   The running job; $job->source is set.
   * @param \PgSql\Connection $connection
   *   The run connection, inside its open transaction. Use only bound
   *   parameters (EngineClient::rows() and EngineClient::affected()).
   * @param \Drupal\ancora_engine\Engine\EngineClient $engine
   *   The engine client, for its query helpers.
   *
   * @return array
   *   A JSON-serializable summary, stored in the job result under "input".
   *   It must contain "hash", the input snapshot hash.
   *
   * @throws \RuntimeException
   *   When the input cannot be loaded; the run is rolled back and the job
   *   fails with the message (which must hold no secret).
   */
  public function prepare(Job $job, PgConnection $connection, EngineClient $engine): array;

}
