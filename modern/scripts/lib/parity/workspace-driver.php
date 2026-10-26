<?php

/**
 * @file
 * Workspace path of the differential parity harness (issue #93).
 *
 * Runs every timetable request of the scenario (the jobs drupal-driver.php
 * queued) through a job-scoped SQL workspace inside the SITE database, with
 * no engine database: docs/modernization/single-db-workspace.md.
 *
 * For each job, in id order:
 * 1. the orchestrator (the cluster superuser, standing in for a provisioning
 *    role) creates the job's login role and its workspace schema
 *    (scheduler_control.create_workspace);
 * 2. the site's legacy rows are built from the Drupal entities inside ONE
 *    read-only REPEATABLE READ transaction of Drupal's own connection
 *    (AcademicExport and LegacyRows, as EngineSync does);
 * 3. the job role, on its own connection with
 *    search_path = <workspace>, scheduler, pg_temp, writes them into its
 *    workspace with the unchanged LegacyTableSync (legacy triggers enabled),
 *    adds the placements kept by the earlier jobs (scheduler_control
 *    placement store, the stand-in for #95's promotion), reduces the
 *    workspace to the #92 closure of the request and analyzes it;
 * 4. generar() runs as one statement (wsproof.run: placements, inserted
 *    rows, DML counters);
 * 5. the orchestrator keeps the job's new placements in the store.
 *
 * The Drupal tables are checksummed before and after, and no job role may
 * hold any privilege on them.
 *
 * Usage (drush php:script; the PG* environment of the disposable cluster):
 *
 * @code
 * drush php:script scripts/lib/parity/workspace-driver.php -- <scenario.json> <out dir>
 * @endcode
 *
 * Writes <out dir>/workspace.json.
 */

declare(strict_types=1);

use PgSql\Connection;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\LegacyTableSync;

// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
[$ws_scenario_file, $ws_out] = $extra + [NULL, NULL];
$ws_site_db = (string) \Drupal::database()->getConnectionOptions()['database'];
$ws_now = static fn (): float => microtime(TRUE);

$ws_connect = static function (string $user, string $options = '') use ($ws_site_db): Connection {
  $dsn = sprintf("host='%s' port='%s' dbname='%s' user='%s' application_name='ancora_ws_%s'", getenv('PGHOST'), getenv('PGPORT'), $ws_site_db, $user, $user);
  if ($options !== '') {
    $dsn .= " options='$options'";
  }
  $conn = pg_connect($dsn, PGSQL_CONNECT_FORCE_NEW);
  if (!$conn) {
    throw new \RuntimeException("cannot connect as $user");
  }
  return $conn;
};
$ws_q = static function (Connection $c, string $sql, array $params = []): array {
  $r = @pg_query_params($c, $sql, $params);
  if ($r === FALSE) {
    throw new \RuntimeException(pg_last_error($c) . " in: " . substr($sql, 0, 200));
  }
  return pg_fetch_all($r, PGSQL_NUM) ?: [];
};
$ws_v = static fn (Connection $c, string $sql, array $params = []): ?string => $ws_q($c, $sql, $params)[0][0] ?? NULL;

// The site's legacy rows (EngineSync::siteRows(), which is private).
$ws_site_rows = static function (): array {
  $etm = \Drupal::entityTypeManager();
  $export = \Drupal::service('Drupal\ancora_academic\Export\AcademicExport');
  $legacy = \Drupal::service('Drupal\ancora_academic\Export\LegacyRows');
  \Drupal::service('entity.memory_cache')->deleteAll();
  $owners = [];
  foreach (AcademicEntityTypes::OWNED as $type) {
    foreach ($etm->getStorage($type)->getAggregateQuery()->accessCheck(FALSE)->groupBy('uid')->execute() as $row) {
      $owners[(int) $row['uid']] = TRUE;
    }
  }
  ksort($owners);
  $plans = $etm->getStorage('ancora_plan')->getQuery()->accessCheck(FALSE)->sort('id')->execute();
  $by_owner = [];
  foreach ($etm->getStorage('ancora_plan')->loadMultiple($plans) as $plan) {
    $by_owner[(int) $plan->getCreatorId()][] = $plan;
  }
  $rows = array_fill_keys(LegacyRows::TABLES, []);
  $seen = [];
  $creators = [];
  foreach (array_keys($owners) as $owner) {
    $creator = ScopeMapper::creatorOf($owner);
    $creators[] = $creator;
    $exports = [];
    foreach ($by_owner[$owner] ?? [] as $plan) {
      $exports[] = $export->export($plan);
    }
    $exports = $exports ?: [$export->catalogue($owner)];
    foreach ($exports as $e) {
      foreach ($legacy->build($e, $creator, TRUE) as $table => $list) {
        foreach ($list as $row) {
          $key = $table . "\x1F" . LegacyTableSync::keyOf($row, LegacyTableSync::TABLES[$table][0]);
          if (!isset($seen[$key])) {
            $seen[$key] = TRUE;
            $rows[$table][] = $row;
          }
        }
      }
    }
  }
  return [$rows, $creators];
};

// Scenario key of a record by its type and Drupal code (default: the key).
$ws_codes = [];
foreach (json_decode((string) file_get_contents($ws_scenario_file), TRUE, 64, JSON_THROW_ON_ERROR)['records'] as $r) {
  $ws_codes[$r['type']][$r['code'] ?? $r['key']] ??= $r['key'];
}
$ws_key = static fn (string $type, string $code): string => $ws_codes[$type][$code] ?? "?$code";

$admin = $ws_connect((string) (getenv('PGUSER') ?: 'postgres'));
$canonical = $ws_q($admin, "SELECT string_agg(format('%I.%I', schemaname, tablename), ',' ORDER BY tablename) FROM pg_tables
  WHERE schemaname = 'public' AND (tablename LIKE 'ancora\\_%' AND tablename NOT LIKE 'ancora\\_engine%' OR tablename LIKE 'users%')")[0][0];
$checksum = static fn (): string => $ws_v($admin, 'SELECT md5(string_agg(t, E\'\\n\' ORDER BY t)) FROM (' . implode(' UNION ALL ', array_map(
  static fn (string $t): string => "SELECT '$t:' || x::text AS t FROM $t x", explode(',', $canonical))) . ') s');
$before = $checksum();

$engine = new EngineClient(\Drupal::service('ancora_engine.settings'));
$jobs = $ws_q($admin, 'SELECT id FROM ancora_engine_job ORDER BY id');
$repo = \Drupal::service('ancora_engine.job_repository');
$ws_v($admin, 'TRUNCATE scheduler_control.placement_store, scheduler_control.resource_store');
$result = ['runs' => [], 'drupal' => []];
foreach ($jobs as [$jid]) {
  $job = $repo->load((int) $jid);
  $n = 'p' . $jid;
  // Roles are cluster-wide: the name carries the tenant database.
  $role = "agoh_{$ws_site_db}_j$jid";
  $schema = "scheduler_job_$n";
  $m = ['job' => (int) $jid, 'scope' => $job->scope->toParameters(), 'options' => $job->options->toParameters()];
  $t = $ws_now();
  $ws_v($admin, sprintf('CREATE ROLE %s LOGIN IN ROLE agoh_engine', $role));
  $ws_v($admin, 'SELECT scheduler_control.create_workspace($1, $2, $3)', [$n, $schema, $role]);
  $m['ms_create'] = (int) round(($ws_now() - $t) * 1000);

  // One consistent snapshot of the Drupal entities.
  $t = $ws_now();
  $db = \Drupal::database();
  $db->query('SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL REPEATABLE READ READ ONLY');
  $tx = $db->startTransaction();
  $m['snapshot'] = [
    'isolation' => $db->query('SHOW transaction_isolation')->fetchField(),
    'read_only' => $db->query('SHOW transaction_read_only')->fetchField(),
    'start' => $db->query('SELECT pg_current_snapshot()::text')->fetchField(),
  ];
  [$rows, $creators] = $ws_site_rows();
  $m['snapshot']['end'] = $db->query('SELECT pg_current_snapshot()::text')->fetchField();
  unset($tx);
  $db->query('SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL READ COMMITTED READ WRITE');
  $m['ms_snapshot'] = (int) round(($ws_now() - $t) * 1000);
  $m['site_rows'] = array_sum(array_map('count', $rows));

  // The job's own connection and search_path.
  $conn = $ws_connect($role, "-c search_path=$schema,scheduler,pg_temp -c client_min_messages=warning");
  $m['search_path'] = $ws_v($conn, 'SHOW search_path');
  $t = $ws_now();
  $ws_v($conn, 'BEGIN');
  $changes = (new LegacyTableSync($engine, $conn))->sync($rows, $creators);
  $m['loaded'] = $changes['inserted'];
  $m['stored_placements'] = (int) $ws_v($conn, 'SELECT scheduler_control.load_stored_placements()');
  $m['input_rows'] = (int) $ws_v($conn, 'SELECT scheduler_control.ws_rows()');
  [$e, $l, $p] = $job->scope->toParameters();
  $buscalugar = $job->options->buscalugar ? 't' : 'f';
  $closure = [$e, $l, $p, $buscalugar];
  $m['closure_rows'] = (int) $ws_v($conn, 'SELECT scheduler_control.closure_keep($1, $2, $3, $4)', $closure);
  $m['pruned'] = (int) $ws_v($conn, 'SELECT scheduler_control.prune()');
  $ws_v($conn, 'COMMIT');
  $ws_v($conn, 'SELECT scheduler_control.ws_analyze()');
  $m['ms_input'] = (int) round(($ws_now() - $t) * 1000);
  $t = $ws_now();
  $out = $ws_v($conn, 'SELECT wsproof.run($1, $2, $3, $4::bigint, $5::boolean, $6::bigint, $7::boolean, $8::boolean)', array_merge($job->scope->toParameters(), $job->options->toParameters()));
  $m['ms_run'] = (int) round(($ws_now() - $t) * 1000);
  $m['outcome'] = json_decode((string) $out, TRUE);
  // The E2E digest as the harness computes it (Normalizer::runs()): scenario
  // keys of template, group and room, the teacher's label, day, slot.
  $e2e = [];
  foreach ($ws_q($conn, "SELECT split_part(a.plantilla, \$4, 2), split_part(a.grupo, \$4, 2), p.nombre, split_part(a.lugar, \$4, 2), a.dia, a.turno
      FROM actividad a JOIN profesor p ON p.id = a.profesor JOIN plantilla_de_actividad pa ON pa.id = a.plantilla
      JOIN desglose d ON d.id = pa.desglose JOIN asignatura s ON s.id = d.asignatura
      WHERE s.especialidad = \$1 AND s.nivel = \$2 AND d.periodo = \$3", [
        $e,
        $l,
        $p,
        ScopeMapper::ID_MARKER,
      ]) as $row) {
    [$tc, $gc, $teacher, $rc, $day, $slot] = $row;
    [$tk, $gk, $rk] = [$ws_key('activity_template', $tc), $ws_key('student_group', $gc), $ws_key('room', $rc)];
    $e2e[$tk . "\0" . $gk] = implode('/', [$tk, $gk, $teacher, $rk, $day, $slot]);
  }
  ksort($e2e, SORT_STRING);
  $m['e2e_digest'] = md5(implode(',', $e2e));
  $m['template_reads'] = (int) $ws_v($admin, "SELECT coalesce(sum(n_tup_ins + n_tup_upd + n_tup_del), 0) FROM pg_stat_user_tables WHERE schemaname = 'scheduler'");
  pg_close($conn);
  $m['stored'] = (int) $ws_v($admin, 'SELECT scheduler_control.store_output($1)', [$n]);
  $m['workspace_bytes'] = (int) $ws_v($admin, "SELECT coalesce(sum(pg_total_relation_size(c.oid)), 0) FROM pg_class c WHERE c.relnamespace = \$1::regnamespace AND c.relkind = 'r'", [$schema]);
  $m['privileges_on_drupal_tables'] = (int) $ws_v($admin, "SELECT count(*) FROM pg_tables t WHERE t.schemaname = 'public'
    AND (has_table_privilege(\$1, t.schemaname || '.' || t.tablename, 'SELECT') OR has_table_privilege(\$1, t.schemaname || '.' || t.tablename, 'INSERT,UPDATE,DELETE,TRUNCATE'))", [$role]);
  $result['runs'][] = $m;
  printf("ok    job %d (%s): %s rows from the snapshot, %d in the closure, %s\n", $jid, implode('/', $m['scope']), $m['site_rows'], $m['closure_rows'], $m['outcome']['metrics'] ?? '?');
}
$result['drupal'] = ['checksum_before' => $before, 'checksum_after' => $checksum(), 'tables' => $canonical];
file_put_contents("$ws_out/workspace.json", json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
