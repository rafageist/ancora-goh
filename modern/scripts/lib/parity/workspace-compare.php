<?php

/**
 * @file
 * Comparison of the workspace path with the legacy oracles (issue #93).
 *
 * Per run of the scenario, the outcome of the job-scoped workspace in the
 * site database (workspace-driver.php) is compared with:
 * - the ENGINE oracle: the full engine database of the two-database layout,
 *   loaded by the same sync from the same Drupal data, measured as the #92
 *   cases measure (workspace-oracle.php). Everything must be identical:
 *   placements, inserted rows, DML counters per table, metrics.
 * - the LEGACY oracle: the legacy application's database (records entered over
 *   HTTP). Placements, DML counters and metrics must be identical. The
 *   inserted rows must be identical too, except the hours of the real-date
 *   rows, which differ only where the inputs already differ (the registered
 *   deviation of periodo.hora_inicial and duracion_del_turno, Q-20/A-1):
 *   such a difference is reported KNOWN with its diff, anything else FAILs.
 * Also checked: the HTTP oracle is reproduced by the measured rerun, the
 * scenario's expectations (metrics, E2E digest), the REPEATABLE READ
 * snapshot, the search_path, no privilege of any job role on a Drupal table,
 * no write to the template tables, and the Drupal checksum before = after.
 *
 * Usage:
 *
 * @code
 * php workspace-compare.php <scenario.json> <out dir>
 * @endcode
 *
 * Reads workspace.json, legacy-oracle.json, engine-oracle.json,
 * legacy-users.json and drupal-users.json from <out dir>.
 */

declare(strict_types=1);

[, $file, $out] = $argv + array_fill(0, 3, NULL);
if ($out === NULL) {
  fwrite(STDERR, "usage: workspace-compare.php <scenario.json> <out dir>\n");
  exit(2);
}
$load = static fn (string $name): array => json_decode((string) file_get_contents("$out/$name"), TRUE, 64, JSON_THROW_ON_ERROR);
$scenario = json_decode((string) file_get_contents($file), TRUE, 64, JSON_THROW_ON_ERROR);
$ws = $load('workspace.json');
$legacy = $load('legacy-oracle.json');
$engine = $load('engine-oracle.json');
$users = ['legacy' => $load('legacy-users.json'), 'drupal' => $load('drupal-users.json')];

$summary = ['PASS' => 0, 'KNOWN' => 0, 'FAIL' => 0];
$report = static function (string $status, string $what, array $details = []) use (&$summary): void {
  $summary[$status]++;
  printf("%-6s %s\n", $status, $what);
  foreach (array_slice($details, 0, 12) as $d) {
    echo "         $d\n";
  }
};
// {U:<id>} -> scenario user key.
$norm = static function (mixed $value, string $side) use ($users): mixed {
  $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  $json = preg_replace_callback('/\{U:([^}]*)\}/', static fn (array $m): string => '{' . ($users[$side][$m[1]] ?? '?' . $m[1]) . '}', $json);
  return json_decode($json, TRUE);
};
$diff = static function (array $a, array $b): array {
  return array_merge(
    array_map(static fn ($l) => "- oracle    $l", array_values(array_diff($a, $b))),
    array_map(static fn ($l) => "+ workspace $l", array_values(array_diff($b, $a))),
  );
};
$hours = static fn (array $lines): array => array_map(static fn (string $l): string => preg_replace('/\|h[^|]*/', '|h*', $l), $lines);

echo "# workspace path (single database, job-scoped schema) against the legacy oracles\n";
foreach ($scenario['runs'] as $i => $run) {
  $key = $run['key'];
  $w = $ws['runs'][$i] ?? NULL;
  if ($w === NULL || !isset($w['outcome'])) {
    $report('FAIL', "run $key: no workspace outcome");
    continue;
  }
  $wo = $norm($w['outcome'], 'drupal');
  $eo = $norm($engine['runs'][$key] ?? [], 'drupal');
  $lo = $norm($legacy['runs'][$key] ?? [], 'legacy');
  $report('PASS', sprintf('run %s: job %d, %d snapshot rows, closure %d rows (%d pruned), %d stored placements in, %s', $key, $w['job'], $w['site_rows'], $w['closure_rows'], $w['pruned'], $w['stored_placements'], $wo['metrics'] ?? '?'));
  foreach (['placements', 'inserted', 'dml', 'metrics'] as $field) {
    $same = ($wo[$field] ?? NULL) == ($eo[$field] ?? NULL);
    $list = is_array($wo[$field] ?? NULL) && array_is_list($wo[$field]);
    $details = $list ? $diff($eo[$field] ?? [], $wo[$field]) : [
      json_encode($eo[$field] ?? NULL),
      json_encode($wo[$field] ?? NULL),
    ];
    $count = $list ? ' (' . count($wo[$field]) . ')' : '';
    $report($same ? 'PASS' : 'FAIL', sprintf('run %s = engine oracle (full two-database engine): %s%s', $key, $field, $count), $same ? [] : $details);
  }
  foreach (['placements', 'dml', 'metrics'] as $field) {
    $same = ($wo[$field] ?? NULL) == ($lo[$field] ?? NULL);
    $list = is_array($wo[$field] ?? NULL) && array_is_list($wo[$field]);
    $details = $list ? $diff($lo[$field] ?? [], $wo[$field]) : [
      json_encode($lo[$field] ?? NULL),
      json_encode($wo[$field] ?? NULL),
    ];
    $report($same ? 'PASS' : 'FAIL', sprintf('run %s = legacy oracle (application over HTTP): %s', $key, $field), $same ? [] : $details);
  }
  if (($wo['inserted'] ?? NULL) === ($lo['inserted'] ?? NULL)) {
    $report('PASS', sprintf('run %s = legacy oracle: inserted rows (%d)', $key, count($wo['inserted'])));
  }
  elseif ($hours($wo['inserted'] ?? []) === $hours($lo['inserted'] ?? [])) {
    $report('KNOWN', sprintf('run %s = legacy oracle: inserted rows (%d) differ only in the hours of real-date rows (periodo.hora_inicial / duracion_del_turno, Q-20, A-1)', $key, count($wo['inserted'])),
      array_slice($diff($lo['inserted'], $wo['inserted']), 0, 2));
  }
  else {
    $report('FAIL', sprintf('run %s = legacy oracle: inserted rows', $key), $diff($lo['inserted'] ?? [], $wo['inserted'] ?? []));
  }
  foreach ($run['expect'] ?? [] as $field => $expected) {
    $actual = $field === 'e2e_digest' ? $w['e2e_digest'] : ($wo[$field] ?? NULL);
    $report($actual === $expected ? 'PASS' : 'FAIL', sprintf('run %s workspace: %s = %s', $key, $field, $actual ?? '(none)'));
  }
  $snap = $w['snapshot'];
  $report($snap['isolation'] === 'repeatable read' && $snap['read_only'] === 'on' && $snap['start'] === $snap['end'] ? 'PASS' : 'FAIL',
    sprintf('run %s: input read in one %s, read-only snapshot (%s)', $key, $snap['isolation'], $snap['start']));
  $report(str_replace(' ', '', $w['search_path']) === sprintf('scheduler_job_p%d,scheduler,pg_temp', $w['job']) ? 'PASS' : 'FAIL', sprintf('run %s: job search_path %s', $key, $w['search_path']));
  $report($w['privileges_on_drupal_tables'] === 0 && $w['template_reads'] === 0 ? 'PASS' : 'FAIL',
    sprintf('run %s: the job role has no privilege on a Drupal table (%d) and nothing was written to the template tables (%d)', $key, $w['privileges_on_drupal_tables'], $w['template_reads']));
  echo sprintf("METRIC run %s: snapshot %d ms, workspace %d ms, input+closure %d ms, run %d ms, workspace tables %d bytes\n",
    $key, $w['ms_snapshot'], $w['ms_create'], $w['ms_input'], $w['ms_run'], $w['workspace_bytes']);
}
$report($legacy['http_placements'] === $legacy['rerun_placements'] ? 'PASS' : 'FAIL', 'legacy oracle: the measured rerun reproduces the placements of the HTTP runs');

// The real worker in workspace mode, read back from the placement entities.
if (is_file("$out/roundtrip.json")) {
  echo "# real worker in workspace mode: placement entities read back from Drupal against the legacy oracle\n";
  $roundtrip = $load('roundtrip.json');
  $expected_total = 0;
  foreach ($scenario['runs'] as $i => $run) {
    $key = $run['key'];
    $r = $roundtrip['runs'][$i] ?? NULL;
    if ($r === NULL) {
      $report('FAIL', "run $key: no round trip");
      continue;
    }
    $mine = $norm($r['placements'], 'drupal');
    $theirs = $norm($legacy['runs'][$key]['placements'] ?? [], 'legacy');
    $report(in_array($r['state'], ['succeeded', 'partial'], TRUE) ? 'PASS' : 'FAIL', sprintf('run %s: job %d %s, %s stored placements and %s resource uses loaded, published %s', $key, $r['job'], $r['state'], $r['placements_loaded'] ?? '?', $r['resource_uses_loaded'] ?? '?', json_encode($r['publication'])), $r['error'] === NULL ? [] : [$r['error']]);
    $report($mine === $theirs ? 'PASS' : 'FAIL', sprintf('run %s: Drupal placement entities = legacy oracle, placement by placement (%d)', $key, count($mine)), $mine === $theirs ? [] : $diff($theirs, $mine));
    $expected_total = max($expected_total, 0) + (int) ($r['publication']['published'] ?? 0);
    if (isset($run['expect']['e2e_digest'])) {
      $report($r['digest'] === $run['expect']['e2e_digest'] ? 'PASS' : 'FAIL', sprintf('run %s: E2E digest read back from the placement entities = %s', $key, $r['digest'] ?? '(none)'));
    }
  }
  $report($roundtrip['total_placements'] === $expected_total ? 'PASS' : 'FAIL', sprintf('every published placement is one entity, none duplicated (%d)', $roundtrip['total_placements']));
}
$report($ws['drupal']['checksum_before'] === $ws['drupal']['checksum_after'] ? 'PASS' : 'FAIL',
  sprintf('Drupal canonical tables unchanged by the workspace path (%d tables, md5 %s)', count(explode(',', $ws['drupal']['tables'])), $ws['drupal']['checksum_after']));
printf("# %s workspace: %d PASS, %d KNOWN, %d FAIL\n", $scenario['id'], $summary['PASS'], $summary['KNOWN'], $summary['FAIL']);
exit($summary['FAIL'] === 0 ? 0 : 1);
