<?php

/**
 * @file
 * Legacy oracle of the workspace path (issue #93).
 *
 * Measures the scenario's runs on a FULL legacy-schema database, as the #92
 * characterization cases do: inside one transaction that is rolled back, the
 * placements the runs made are removed (their occupancy rows cascade), then
 * every run is executed again in scenario order with wsproof.run()
 * (modern/scripts/lib/workspace/measure.sql): placements, inserted rows and
 * DML counters per run. For the legacy application's database the final
 * placements are also compared with the ones the HTTP runs left, so the
 * measured rerun is shown to reproduce the HTTP oracle.
 *
 * Usage:
 *
 * @code
 * php workspace-oracle.php <scenario.json> <database> <users.json> <legacy|engine> <workspace.json> > oracle.json
 * @endcode
 *
 * legacy: the legacy application's database; the scope ids are found by the
 * scenario labels and owners. engine: a site's engine database (two-database
 * layout, the same rows the sync wrote); the scope ids are the jobs' (from
 * workspace.json).
 */

declare(strict_types=1);

use Ancora\Parity\Scenario;

require __DIR__ . '/src/Scenario.php';

[, $file, $database, $users_file, $kind, $ws_file] = $argv + array_fill(0, 6, NULL);
if ($ws_file === NULL) {
  fwrite(STDERR, "usage: workspace-oracle.php <scenario.json> <database> <users.json> <legacy|engine> <workspace.json>\n");
  exit(2);
}
$scenario = Scenario::load($file);
$db = pg_connect('dbname=' . $database . " options='-c client_min_messages=warning'");
$users = array_flip(json_decode((string) file_get_contents($users_file), TRUE, 8, JSON_THROW_ON_ERROR));
$ws = json_decode((string) file_get_contents($ws_file), TRUE, 64, JSON_THROW_ON_ERROR);
$q = static function (string $sql, array $params = []) use ($db): ?string {
  $r = pg_query_params($db, $sql, $params);
  if ($r === FALSE) {
    throw new \RuntimeException(pg_last_error($db));
  }
  return pg_num_rows($r) > 0 ? pg_fetch_result($r, 0, 0) : NULL;
};
$record = static fn (string $key): array => $scenario->records[$key];
$owner = static fn (string $key): string => $users[$record($key)['as']];
$scope = static function (array $run) use ($q, $record, $owner): array {
  $s = $record($run['specialty']);
  $l = $record($run['level']);
  $p = $record($run['period']);
  $plan = $record($p['plan']);
  return [
    $q('SELECT id FROM especialidad WHERE descripcion = $1 AND creador = $2', [$s['label'], $owner($run['specialty'])]),
    $q('SELECT id FROM nivel WHERE descripcion = $1 AND creador = $2', [$l['label'], $owner($run['level'])]),
    $q('SELECT pe.id FROM periodo pe JOIN planificacion pl ON pl.id = pe.planificacion WHERE pe.descripcion = $1 AND pl.descripcion = $2 AND pl.creador = $3',
      [$p['label'], $plan['label'], $owner($p['plan'])]),
  ];
};
$all = static fn (): ?string => $q("SELECT md5(coalesce(string_agg(wsproof.k_act(id) || '|' || wsproof.k_teacher(profesor) || '|' || wsproof.k_room(lugar) || '@' || dia || '.' || turno, ',' ORDER BY 1), '')) FROM actividad");

$q('BEGIN');
$http = $all();
$q('DELETE FROM actividad');
$runs = [];
foreach ($scenario->runs() as $i => $run) {
  $o = $run['options'];
  $params = $kind === 'engine' ? $ws['runs'][$i]['scope'] : $scope($run);
  $options = [
    (string) $o['forma'],
    $o['parche'] ? 't' : 'f',
    (string) $o['compactar'],
    $o['parche1'] ? 't' : 'f',
    $o['buscalugar'] ? 't' : 'f',
  ];
  $out = $q('SELECT wsproof.run($1, $2, $3, $4::bigint, $5::boolean, $6::bigint, $7::boolean, $8::boolean)', array_merge($params, $options));
  $runs[$run['key']] = json_decode((string) $out, TRUE);
}
$rerun = $all();
$q('ROLLBACK');
echo json_encode(['runs' => $runs, 'http_placements' => $http, 'rerun_placements' => $rerun], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
