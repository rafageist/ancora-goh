<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Engine;

use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobScope;
use PgSql\Connection as PgConnection;

/**
 * Explicit SQL on the site's engine database (legacy MPI/Prioriza schema).
 *
 * The engine is independent of Drupal's ORM: this class uses the PHP pgsql
 * extension directly, with bound parameters only, and never creates any
 * object in the engine database. The run connection is asynchronous so the
 * worker can keep its heartbeat and forward cancellations while generar()
 * runs.
 *
 * Transaction model of a run (proved by
 * dev/sql/tests/concurrency/cancel_midflight.sh):
 * - READ COMMITTED, so the generador.cancelar row committed by another
 *   connection is seen by generar()'s next check (REPEATABLE READ would
 *   never see it);
 * - one transaction: the scope's previous placements are deleted, generar()
 *   runs, the metrics are read, then COMMIT; any error rolls everything back;
 * - statement_timeout = the job's time limit, the hard stop.
 */
final class EngineClient {

  /**
   * Placements of the scope (specialty, level, period).
   */
  private const SCOPE_PLACEMENTS = "SELECT ac.id FROM actividad ac
    JOIN plantilla_de_actividad pa ON pa.id = ac.plantilla
    JOIN desglose d ON d.id = pa.desglose
    JOIN asignatura s ON s.id = d.asignatura
    WHERE s.especialidad = $1 AND s.nivel = $2 AND d.periodo = $3";

  /**
   * The metrics of dev/sql/tests/fixtures/helpers.sql (tst.metrics).
   *
   * Same definitions, restricted to the job's scope: demands of the scope,
   * distinct (template, group) placed in the scope, unplaced demands, overflow
   * of the scope's placements beyond the period's slots, and collisions (a
   * shared teacher, place or brigade in overlapping slots of the period)
   * involving at least one placement of the scope. On a database that holds
   * a single scope they equal tst.metrics.
   */
  private const METRICS = "WITH demand AS (
      SELECT pa.id AS plantilla, g.id AS grupo
      FROM plantilla_de_actividad pa
      JOIN desglose d ON d.id = pa.desglose
      JOIN asignatura a ON a.id = d.asignatura
      JOIN periodo pe ON pe.id = d.periodo
      JOIN grupo g ON g.clasificacion = pa.clasificacion AND g.especialidad = a.especialidad
                  AND g.nivel = a.nivel AND g.planificacion = pe.planificacion
      WHERE a.especialidad = $1 AND a.nivel = $2 AND d.periodo = $3
    ), placed AS (
      SELECT ac.id, ac.plantilla, ac.grupo, ac.profesor, ac.lugar, ac.dia, ac.turno,
             ac.turno + c.duracion - 1 AS turno_fin, d.periodo,
             (s.especialidad = $1 AND s.nivel = $2) AS in_scope
      FROM actividad ac
      JOIN plantilla_de_actividad pa ON pa.id = ac.plantilla
      JOIN clasificacion_de_actividad c ON c.id = pa.clasificacion
      JOIN desglose d ON d.id = pa.desglose
      JOIN asignatura s ON s.id = d.asignatura
      WHERE d.periodo = $3
    )
    SELECT
      (SELECT count(*) FROM demand) AS demands,
      (SELECT count(DISTINCT (p.plantilla, p.grupo)) FROM placed p WHERE p.in_scope) AS placed,
      (SELECT count(*) FROM demand dm
         WHERE NOT EXISTS (SELECT 1 FROM actividad a WHERE a.plantilla = dm.plantilla AND a.grupo = dm.grupo)) AS unplaced,
      (SELECT count(*) FROM placed x JOIN placed y
         ON x.id < y.id AND x.dia = y.dia AND x.turno <= y.turno_fin AND y.turno <= x.turno_fin
         WHERE (x.in_scope OR y.in_scope)
           AND (x.profesor = y.profesor OR x.lugar = y.lugar
                OR EXISTS (SELECT 1 FROM brigada_de_grupo bx JOIN brigada_de_grupo by ON bx.brigada = by.brigada
                           WHERE bx.grupo = x.grupo AND by.grupo = y.grupo))) AS collisions,
      (SELECT count(*) FROM placed p JOIN periodo pe ON pe.id = p.periodo
         WHERE p.in_scope AND p.turno_fin > pe.cant_turnos) AS overflow";

  /**
   * The placement list of dev/e2e/lib.sh (e2e_placements), for the scope.
   *
   * Template/group/teacher name/place/day/slot, comma-joined in template and
   * group order. Its md5 is the placement digest
   * (dev/e2e/expected-placements.md5 for the E2E organization).
   */
  private const PLACEMENTS = "SELECT coalesce(string_agg(a.plantilla || '/' || a.grupo || '/' || p.nombre || '/' || a.lugar || '/' || a.dia || '/' || a.turno, ',' ORDER BY a.plantilla, a.grupo), '')
    FROM actividad a
    JOIN profesor p ON p.id = a.profesor
    JOIN plantilla_de_actividad pa ON pa.id = a.plantilla
    JOIN desglose d ON d.id = pa.desglose
    JOIN asignatura s ON s.id = d.asignatura
    WHERE s.especialidad = $1 AND s.nivel = $2 AND d.periodo = $3";

  /**
   * Polling interval of the asynchronous run, in microseconds.
   */
  private const POLL_US = 100000;

  public function __construct(
    private readonly EngineSettings $settings,
  ) {}

  /**
   * The application_name of a run: identifies its backend for cancellation.
   */
  public static function applicationName(int $jobId, int $attempt): string {
    return 'ancora_engine_job_' . $jobId . '_' . $attempt;
  }

  /**
   * Runs generar() for a claimed job.
   *
   * @param \Drupal\ancora_engine\Job\Job $job
   *   The job, in state running.
   * @param callable $tick
   *   Called about every 100 ms while generar() runs, with the elapsed
   *   seconds. It returns NULL to continue, or one of the
   *   RunResult::STOP_* constants to stop the run (pg_cancel_backend).
   */
  public function run(Job $job, callable $tick): RunResult {
    $app = self::applicationName($job->id, $job->attempts);
    $conn = $this->connect($app);
    try {
      $pid = (int) $this->value($conn, 'SELECT pg_backend_pid()');
      $this->value($conn, "SELECT set_config('statement_timeout', $1, false)", [($job->timeLimit * 1000) . 'ms']);
      // A backend whose worker disappeared stops at the next check.
      $this->value($conn, "SELECT set_config('client_connection_check_interval', '5s', false)");
      $params = $job->scope->toParameters();
      if (!$this->scopeExists($conn, $job->scope)) {
        return new RunResult(RunResult::ERROR, message: 'The scope does not exist in the engine database.');
      }
      // A request left over from an earlier run must not stop this one; it
      // is deleted in its own transaction, before the run holds any lock.
      $this->value($conn, 'DELETE FROM generador.cancelar WHERE especialidad = $1 AND nivel = $2 AND periodo = $3', $params);

      $this->value($conn, 'BEGIN ISOLATION LEVEL READ COMMITTED');
      $replaced = (int) $this->value($conn, 'WITH gone AS (DELETE FROM actividad WHERE id IN (' . self::SCOPE_PLACEMENTS . ') RETURNING 1) SELECT count(*) FROM gone', $params);

      $start = hrtime(TRUE);
      if (!pg_send_query_params($conn, 'SELECT generar($1, $2, $3, $4::bigint, $5::boolean, $6::bigint, $7::boolean, $8::boolean)', array_merge($params, $job->options->toParameters()))) {
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
          $this->cancelBackend($pid, $app);
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
        @pg_query($conn, 'ROLLBACK');
        return new RunResult(RunResult::ERROR, durationMs: $duration, sqlstate: $sqlstate ?: NULL, message: self::summary((string) $message), stoppedBy: $stopped, replaced: $replaced);
      }

      // generar() returned. A cancellation row still present was not
      // consumed: the run finished before seeing it.
      $left = (int) $this->value($conn, 'WITH gone AS (DELETE FROM generador.cancelar WHERE especialidad = $1 AND nivel = $2 AND periodo = $3 RETURNING 1) SELECT count(*) FROM gone', $params);
      $metrics = $this->metrics($conn, $job->scope);
      if ($metrics['collisions'] > 0 || $metrics['overflow'] > 0) {
        $this->value($conn, 'ROLLBACK');
        return new RunResult(RunResult::INVALID, $metrics, $duration, message: 'The placements broke an engine invariant (collision or overflow); the run was rolled back.', stoppedBy: $stopped, replaced: $replaced);
      }
      $this->value($conn, 'COMMIT');
      return new RunResult(RunResult::RETURNED, $metrics, $duration, stoppedBy: $stopped, cancelRowLeft: $left > 0, replaced: $replaced);
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Forwards a soft cancellation: a generador.cancelar row, committed.
   *
   * It uses its own connection. lock_timeout keeps it from waiting on a row
   * that the run has just consumed but not committed yet (the run would then
   * stop anyway).
   */
  public function signalSoftCancel(JobScope $scope): bool {
    $conn = $this->connect('ancora_engine_control');
    try {
      $this->value($conn, "SELECT set_config('lock_timeout', '1s', false)");
      $this->value($conn, 'INSERT INTO generador.cancelar (especialidad, nivel, periodo) VALUES ($1, $2, $3) ON CONFLICT DO NOTHING', $scope->toParameters());
      return TRUE;
    }
    catch (EngineException $e) {
      if ($e->sqlstate === '55P03') {
        return TRUE;
      }
      throw $e;
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Deletes a pending soft cancellation of the scope.
   */
  public function clearCancelRequest(JobScope $scope): void {
    $conn = $this->connect('ancora_engine_control');
    try {
      $this->value($conn, 'DELETE FROM generador.cancelar WHERE especialidad = $1 AND nivel = $2 AND periodo = $3', $scope->toParameters());
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Cancels the current statement of a run (hard cancellation).
   *
   * The backend is matched by pid AND application_name, so a reused pid of
   * another session is never cancelled.
   */
  public function cancelBackend(int $pid, string $applicationName): bool {
    $conn = $this->connect('ancora_engine_control');
    try {
      $sql = 'SELECT coalesce(bool_or(pg_cancel_backend(pid)), false) FROM pg_stat_activity WHERE pid = $1 AND application_name = $2 AND datname = current_database()';
      return $this->value($conn, $sql, [$pid, $applicationName]) === 't';
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Hard-cancels the running statement of a job attempt, by name.
   */
  public function cancelJob(Job $job): bool {
    $conn = $this->connect('ancora_engine_control');
    try {
      return $this->value($conn, 'SELECT coalesce(bool_or(pg_cancel_backend(pid)), false) FROM pg_stat_activity WHERE application_name = $1 AND datname = current_database()', [self::applicationName($job->id, $job->attempts)]) === 't';
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Terminates every backend of a job attempt and waits until none is left.
   *
   * @return bool
   *   TRUE when no backend of the attempt remains.
   */
  public function terminateJob(Job $job, int $waitSeconds = 10): bool {
    $conn = $this->connect('ancora_engine_control');
    $app = self::applicationName($job->id, $job->attempts);
    try {
      $this->value($conn, 'SELECT count(pg_terminate_backend(pid)) FROM pg_stat_activity WHERE application_name = $1 AND datname = current_database()', [$app]);
      $deadline = microtime(TRUE) + $waitSeconds;
      do {
        if ($this->value($conn, 'SELECT count(*) FROM pg_stat_activity WHERE application_name = $1 AND datname = current_database()', [$app]) === '0') {
          return TRUE;
        }
        usleep(200000);
      } while (microtime(TRUE) < $deadline);
      return FALSE;
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * The metrics and the placement digest of a scope, outside any run.
   */
  public function scopeMetrics(JobScope $scope): array {
    $conn = $this->connect('ancora_engine_read');
    try {
      return $this->metrics($conn, $scope);
    }
    finally {
      pg_close($conn);
    }
  }

  /**
   * Reads the metrics and the placement digest of a scope.
   */
  private function metrics(PgConnection $conn, JobScope $scope): array {
    $params = $scope->toParameters();
    $row = $this->row($conn, self::METRICS, $params);
    $list = (string) $this->value($conn, self::PLACEMENTS, $params);
    return [
      'demands' => (int) $row['demands'],
      'placed' => (int) $row['placed'],
      'unplaced' => (int) $row['unplaced'],
      'collisions' => (int) $row['collisions'],
      'overflow' => (int) $row['overflow'],
      'digest' => md5($list),
    ];
  }

  /**
   * Whether the specialty, level and period exist in the engine database.
   */
  private function scopeExists(PgConnection $conn, JobScope $scope): bool {
    return $this->value($conn, 'SELECT EXISTS (SELECT 1 FROM especialidad WHERE id = $1) AND EXISTS (SELECT 1 FROM nivel WHERE id = $2) AND EXISTS (SELECT 1 FROM periodo WHERE id = $3)', $scope->toParameters()) === 't';
  }

  /**
   * Opens a new connection to the site's engine database.
   *
   * @throws \Drupal\ancora_engine\Engine\EngineException
   *   When the connection fails. The message never includes the password.
   */
  public function connect(string $applicationName): PgConnection {
    $db = $this->settings->database();
    $parts = [
      'host' => (string) $db['host'],
      'port' => (string) $db['port'],
      'dbname' => (string) $db['database'],
      'user' => (string) $db['username'],
      'password' => (string) $db['password'],
      'sslmode' => (string) $db['sslmode'],
      'connect_timeout' => '10',
      'application_name' => $applicationName,
      'options' => '-c client_min_messages=warning',
    ];
    $dsn = [];
    foreach ($parts as $key => $value) {
      if ($value !== '') {
        $dsn[] = $key . "='" . strtr($value, ['\\' => '\\\\', "'" => "\\'"]) . "'";
      }
    }
    $error = '';
    set_error_handler(static function (int $severity, string $message) use (&$error): bool {
      $error = $message;
      return TRUE;
    });
    try {
      $conn = pg_connect(implode(' ', $dsn), PGSQL_CONNECT_FORCE_NEW);
    }
    finally {
      restore_error_handler();
    }
    if (!$conn instanceof PgConnection) {
      // Keep the server's reason only: no host, socket path or password.
      $reason = str_contains($error, 'FATAL:') ? substr($error, strrpos($error, 'FATAL:') + 6) : 'connection refused or timed out';
      if ($db['password'] !== '') {
        $reason = str_replace((string) $db['password'], '***', $reason);
      }
      throw new EngineException('Cannot connect to the engine database: ' . self::summary(preg_replace('/\s+/', ' ', $reason)));
    }
    return $conn;
  }

  /**
   * Runs one statement with bound parameters and returns the first value.
   *
   * @throws \Drupal\ancora_engine\Engine\EngineException
   */
  private function value(PgConnection $conn, string $sql, array $params = []): ?string {
    $row = $this->row($conn, $sql, $params);
    return $row === NULL ? NULL : (($value = reset($row)) === FALSE ? NULL : $value);
  }

  /**
   * Runs one statement with bound parameters and returns the first row.
   *
   * @throws \Drupal\ancora_engine\Engine\EngineException
   */
  private function row(PgConnection $conn, string $sql, array $params = []): ?array {
    if (!pg_send_query_params($conn, $sql, $params)) {
      throw new EngineException('The engine connection is not usable.');
    }
    $result = pg_get_result($conn);
    while (pg_get_result($conn) !== FALSE) {
      // Drain.
    }
    if ($result === FALSE) {
      throw new EngineException('The engine database returned no result.');
    }
    $status = pg_result_status($result);
    if ($status !== PGSQL_TUPLES_OK && $status !== PGSQL_COMMAND_OK) {
      throw new EngineException(
        self::summary((string) pg_result_error_field($result, PGSQL_DIAG_MESSAGE_PRIMARY)),
        (string) pg_result_error_field($result, PGSQL_DIAG_SQLSTATE),
      );
    }
    $row = pg_fetch_assoc($result);
    return $row === FALSE ? NULL : $row;
  }

  /**
   * One line, bounded: safe to store in a job and show to its users.
   */
  public static function summary(string $message): string {
    $line = trim(strtok(trim($message), "\n") ?: '');
    return mb_substr($line, 0, 500);
  }

}
