<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Engine;

use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\Job;

/**
 * Runs a job in a job-scoped workspace of the site database (#93, #95).
 *
 * The workspace mode of the worker (EngineSettings::isWorkspace()). The
 * engine connection settings name the SITE's database, where the unchanged
 * engine is installed (dev/workspace/install.sh --worker <role>), and the
 * site's worker role. For each attempt:
 *
 * 1. a control connection opens the attempt's workspace
 *    (scheduler_control.open_job_workspace("<job>_<attempt>")): a schema
 *    with the legacy shapes, owned by a new NOLOGIN job role of which the
 *    worker is a member WITH INHERIT FALSE;
 * 2. the job connection (application_name EngineClient::applicationName())
 *    does SET ROLE to the job role and SET search_path to
 *    "<workspace>, scheduler, pg_temp"; from then on every legacy name
 *    resolves to the workspace;
 * 3. the job input service (WorkspaceSync) loads the input and commits it;
 * 4. generar() runs as one autocommit statement (READ COMMITTED), with the
 *    worker's heartbeat, cancellation forwarding and statement_timeout, as
 *    in EngineClient::run();
 * 5. when it returned, the metrics, the E2E digest and the new placements
 *    (rows of "actividad" that were not input, in insertion order) are read.
 *
 * Nothing is deleted before the run (no D7: legacy continuation). The worker
 * publishes the output (JobOutputInterface) and then closes the workspace.
 * A soft cancellation is a row of scheduler_control.cancel_request for the
 * job role: only this attempt sees it.
 */
final class WorkspaceClient {

  private const POLL_US = 100000;

  public function __construct(
    private readonly EngineClient $engine,
    private readonly EngineSettings $settings,
  ) {}

  /**
   * The workspace key of a job attempt.
   */
  public static function key(Job $job): string {
    return $job->id . '_' . $job->attempts;
  }

  /**
   * Runs a claimed job in a new workspace.
   */
  public function run(Job $job, callable $tick, JobInputInterface $input): RunResult {
    $key = self::key($job);
    $control = $this->engine->connect('ancora_engine_ws_control');
    try {
      $ws = $this->engine->rows($control, 'SELECT schema_name, job_role FROM scheduler_control.open_job_workspace($1)', [$key])[0];
    }
    finally {
      pg_close($control);
    }
    $app = EngineClient::applicationName($job->id, $job->attempts);
    $conn = $this->engine->connect($app);
    try {
      $pid = (int) $this->engine->rows($conn, 'SELECT pg_backend_pid() AS pid')[0]['pid'];
      $this->engine->affected($conn, 'SET ROLE ' . pg_escape_identifier($conn, $ws['job_role']));
      $this->engine->affected($conn, 'SET search_path TO ' . pg_escape_identifier($conn, $ws['schema_name']) . ', scheduler, pg_temp');
      $this->engine->rows($conn, "SELECT set_config('statement_timeout', $1, false)", [($job->timeLimit * 1000) . 'ms']);
      $this->engine->rows($conn, "SELECT set_config('client_connection_check_interval', '5s', false)");
      try {
        $loaded = $input->prepare($job, $conn, $this->engine);
      }
      catch (\RuntimeException $e) {
        return new RunResult(RunResult::ERROR, sqlstate: $e instanceof EngineException ? $e->sqlstate : NULL, message: EngineClient::summary('The input sync failed and nothing ran: ' . $e->getMessage()), workspace: $key);
      }
      $input_ids = $loaded['placement_ids'] ?? [];
      unset($loaded['placement_ids']);
      $this->mark($key, 'running');

      $start = hrtime(TRUE);
      if (!pg_send_query_params($conn, 'SELECT generar($1, $2, $3, $4::bigint, $5::boolean, $6::bigint, $7::boolean, $8::boolean)', array_merge($job->scope->toParameters(), $job->options->toParameters()))) {
        throw new \RuntimeException('generar() could not be sent to the engine database.');
      }
      $stopped = NULL;
      while (TRUE) {
        if (!pg_consume_input($conn) || !pg_connection_busy($conn)) {
          break;
        }
        $action = $tick((hrtime(TRUE) - $start) / 1e9);
        if ($action !== NULL && $stopped === NULL) {
          $stopped = $action;
          $this->engine->cancelBackend($pid, $app);
        }
        usleep(self::POLL_US);
      }
      $result = pg_get_result($conn);
      while (pg_get_result($conn) !== FALSE) {
        // Drain: only one statement was sent.
      }
      $duration = (int) round((hrtime(TRUE) - $start) / 1e6);
      if ($result === FALSE || pg_result_status($result) !== PGSQL_TUPLES_OK) {
        $sqlstate = $result === FALSE ? NULL : pg_result_error_field($result, PGSQL_DIAG_SQLSTATE);
        $message = $result === FALSE ? pg_last_error($conn) : pg_result_error_field($result, PGSQL_DIAG_MESSAGE_PRIMARY);
        $this->mark($key, 'failed');
        return new RunResult(RunResult::ERROR, durationMs: $duration, sqlstate: $sqlstate ?: NULL, message: EngineClient::summary((string) $message), stoppedBy: $stopped, input: $loaded, workspace: $key);
      }
      $this->mark($key, 'ran');
      $left = (int) $this->engine->rows($conn, 'SELECT count(*) AS n FROM generador.cancelar')[0]['n'];
      $metrics = $this->engine->metricsOn($conn, $job->scope, $job->idPrefix());
      $output = $this->engine->rows($conn, 'SELECT id, plantilla, grupo, profesor, lugar, dia, turno, momento_de_colocacion, creador
        FROM actividad WHERE NOT (id = ANY (ARRAY(SELECT jsonb_array_elements_text($1::jsonb)))) ORDER BY ctid', [json_encode(array_values($input_ids), JSON_THROW_ON_ERROR)]);
      return new RunResult(RunResult::RETURNED, $metrics, $duration, stoppedBy: $stopped, cancelRowLeft: $left > 0, input: $loaded, output: $output, workspace: $key);
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Forwards a soft cancellation to the running attempt of a job.
   */
  public function signalSoftCancel(Job $job): bool {
    $conn = $this->engine->connect('ancora_engine_control');
    try {
      [$e, $l, $p] = $job->scope->toParameters();
      $this->engine->affected($conn, "INSERT INTO scheduler_control.cancel_request (job_role, especialidad, nivel, periodo)
        VALUES (left('agoh_' || current_database() || '_j' || \$1, 63), \$2, \$3, \$4) ON CONFLICT DO NOTHING", [
          self::key($job),
          $e,
          $l,
          $p,
        ]);
      return TRUE;
    }
    catch (EngineException) {
      return FALSE;
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Closes the workspace of a job attempt (dropped, or kept for diagnosis).
   */
  public function close(string $key): string {
    $conn = $this->engine->connect('ancora_engine_ws_control');
    try {
      $keep = (string) $this->settings->workspaceRetention();
      $params = [$key, $keep];
      return (string) $this->engine->rows($conn, 'SELECT scheduler_control.close_job_workspace($1, $2) AS state', $params)[0]['state'];
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Drops the workspaces of attempts that have no backend, and expired ones.
   *
   * @return list<string>
   *   The keys of the workspaces dropped.
   */
  public function cleanup(): array {
    $conn = $this->engine->connect('ancora_engine_ws_control');
    try {
      return array_column($this->engine->rows($conn, 'SELECT scheduler_control.worker_cleanup() AS job'), 'job');
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Records the state of a workspace.
   */
  private function mark(string $key, string $state): void {
    $conn = $this->engine->connect('ancora_engine_ws_control');
    try {
      $this->engine->rows($conn, 'SELECT scheduler_control.mark_workspace($1, $2)', [$key, $state]);
    }
    finally {
      pg_close($conn);
    }
  }

}
