<?php

/**
 * @file
 * Comparer of the differential parity harness (scripts/parity-diff.sh).
 *
 * Compares the canonical form of the legacy database with that of the
 * site's engine database (normalize.php) and prints one result per table,
 * per other table and per scheduling run:
 * - PASS: identical;
 * - KNOWN <ids>: the rows differ only in columns that the register of known
 *   deviations (modern/tests/parity/known-deviations.json) lists for this
 *   table and scenario; the difference is shown, and does not fail;
 * - FAIL: any other difference, with a readable diff ("- legacy", "+ Drupal").
 *
 * A run passes when both sides have the same metrics and placement digest,
 * and each side meets the scenario's expectations.
 *
 * Usage:
 *
 * @code
 * php compare.php <scenario.json> <legacy.json> <drupal.json> <known-deviations.json>
 * @endcode
 *
 * Exit status 0 when nothing FAILs.
 */

declare(strict_types=1);

use Ancora\Parity\Scenario;

require __DIR__ . '/src/Scenario.php';

[, $scenario_file, $legacy_file, $drupal_file, $known_file] = $argv + array_fill(0, 5, NULL);
if ($known_file === NULL) {
  fwrite(STDERR, "usage: compare.php <scenario.json> <legacy.json> <drupal.json> <known-deviations.json>\n");
  exit(2);
}
$scenario = Scenario::load($scenario_file);
$scenario_id = $scenario->id();
$expect = array_column($scenario->runs(), 'expect', 'key');
$read = static fn (string $f): array => json_decode((string) file_get_contents($f), TRUE, 64, JSON_THROW_ON_ERROR);
$legacy = $read($legacy_file);
$drupal = $read($drupal_file);
$known = array_values(array_filter(
  $read($known_file)['deviations'],
  static fn (array $d): bool => in_array('*', $d['scenarios'], TRUE) || in_array($scenario_id, $d['scenarios'], TRUE),
));

$fail = 0;
$summary = ['PASS' => 0, 'KNOWN' => 0, 'FAIL' => 0];
$report = static function (string $status, string $what, array $diff = []) use (&$fail, &$summary): void {
  $summary[preg_replace('/ .*/', '', $status)]++;
  if (str_starts_with($status, 'FAIL')) {
    $fail++;
  }
  printf("%-14s %s\n", $status, $what);
  foreach (array_slice($diff, 0, 40) as $line) {
    echo "      $line\n";
  }
  if (count($diff) > 40) {
    printf("      ... %d more lines\n", count($diff) - 40);
  }
};
$line = static fn (array $row): string => json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

/**
 * The rows only on one side, as diff lines.
 */
$diff = static function (array $a, array $b) use ($line): array {
  $a = array_map($line, $a);
  $b = array_map($line, $b);
  $lines = [];
  foreach (array_diff($a, $b) as $row) {
    $lines[] = "- legacy  $row";
  }
  foreach (array_diff($b, $a) as $row) {
    $lines[] = "+ Drupal  $row";
  }
  return $lines;
};
$blank = static function (array $rows, array $columns): array {
  $out = array_map(static function (array $row) use ($columns): array {
    foreach ($columns as $c) {
      if (array_key_exists($c, $row)) {
        $row[$c] = '(known)';
      }
    }
    return $row;
  }, $rows);
  usort($out, static fn (array $x, array $y): int => strcmp(json_encode($x), json_encode($y)));
  return $out;
};

echo "# tables (normalized: generated ids replaced by scenario keys)\n";
foreach ($legacy['tables'] as $table => $rows) {
  $other = $drupal['tables'][$table] ?? [];
  if ($rows === $other) {
    $report('PASS', sprintf('%s (%d rows)', $table, count($rows)));
    continue;
  }
  $entries = array_filter($known, static fn (array $d): bool => $d['table'] === $table);
  $columns = array_merge([], ...array_map(static fn (array $d): array => $d['columns'], $entries));
  if ($entries && count($rows) === count($other) && $blank($rows, $columns) === $blank($other, $columns)) {
    $report('KNOWN ' . implode(',', array_unique(array_column($entries, 'id'))), sprintf('%s (%d rows; differs only in %s)', $table, count($rows), implode(', ', array_unique($columns))), $diff($rows, $other));
    continue;
  }
  $report('FAIL', sprintf('%s (legacy %d rows, Drupal %d rows)', $table, count($rows), count($other)), $diff($rows, $other));
}

echo "# owners (distinct legacy users owning each person's rows)\n";
$report($legacy['owners'] === $drupal['owners'] ? 'PASS' : 'FAIL', 'owners: legacy ' . json_encode($legacy['owners']) . ', Drupal ' . json_encode($drupal['owners']));

echo "# other tables (row counts)\n";
$names = array_unique(array_merge(array_keys($legacy['other_tables']), array_keys($drupal['other_tables'])));
sort($names);
$other_diff = [];
foreach ($names as $name) {
  $l = $legacy['other_tables'][$name] ?? NULL;
  $d = $drupal['other_tables'][$name] ?? NULL;
  if ($l !== $d) {
    $other_diff[$name] = [$l, $d];
  }
}
foreach ($other_diff as $name => [$l, $d]) {
  $entries = array_filter($known, static fn (array $k): bool => $k['table'] === "other:$name");
  $text = sprintf('%s: legacy %s, Drupal %s rows', $name, $l ?? 'no table', $d ?? 'no table');
  $report($entries ? 'KNOWN ' . implode(',', array_column($entries, 'id')) : 'FAIL', $text);
}
if ($other_diff === []) {
  $report('PASS', sprintf('%d other tables have the same row counts', count($names)));
}

echo "# scheduling outcome (generar)\n";
foreach ($legacy['runs'] as $key => $run) {
  $other = $drupal['runs'][$key] ?? NULL;
  $problems = [];
  if ($other === NULL) {
    $problems[] = 'no Drupal run';
  }
  else {
    foreach (['metrics', 'digest'] as $field) {
      if ($run[$field] !== $other[$field]) {
        $problems[] = "$field: legacy {$run[$field]}, Drupal {$other[$field]}";
      }
    }
  }
  $report($problems ? 'FAIL' : 'PASS', sprintf('run %s: %s, digest %s', $key, $run['metrics'], $run['digest']), array_merge($problems, $problems ? $diff(array_map(static fn ($p) => [$p], $run['placements']), array_map(static fn ($p) => [$p], $other['placements'] ?? [])) : []));
  foreach (['legacy' => $run, 'Drupal' => $other] as $side => $result) {
    foreach ($expect[$key] ?? [] as $field => $expected) {
      $actual = $result[$field] ?? NULL;
      $report($actual === $expected ? 'PASS' : 'FAIL', sprintf('run %s on %s: %s = %s', $key, $side, $field, $actual ?? '(none)'), $actual === $expected ? [] : ["expected $expected"]);
    }
  }
}

printf("# %s: %d PASS, %d KNOWN, %d FAIL\n", $scenario_id, $summary['PASS'], $summary['KNOWN'], $summary['FAIL']);
exit($fail === 0 ? 0 : 1);
