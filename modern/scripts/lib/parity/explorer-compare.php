<?php

/**
 * @file
 * Compares the legacy timetable explorer with the Drupal one (#119, D14).
 *
 * For each "explorer_grid" step: the start cells of the legacy grid
 * (legacy-explorer.json, LegacyDriver::explorerGrid()) must equal the
 * Drupal explorer's (drupal-explorer-<label>.json, explorer-dump.php). A
 * step marked "drupal": "refused" records a legacy request Drupal must not
 * answer (a forged period or record, like Q-12): the legacy cells are
 * reported, and Drupal must refuse it.
 *
 * Usage:
 *
 * @code
 * php explorer-compare.php <scenario.json> <out dir> <label>
 * @endcode
 */

declare(strict_types=1);

[, $file, $out, $label] = $argv + array_fill(0, 4, NULL);
if ($label === NULL) {
  fwrite(STDERR, "usage: explorer-compare.php <scenario.json> <out dir> <label>\n");
  exit(2);
}
$scenario = json_decode((string) file_get_contents($file), TRUE, 64, JSON_THROW_ON_ERROR);
$legacy = json_decode((string) @file_get_contents("$out/legacy-explorer.json") ?: '{}', TRUE, 64, JSON_THROW_ON_ERROR);
$drupal = json_decode((string) @file_get_contents("$out/drupal-explorer-$label.json") ?: '{}', TRUE, 64, JSON_THROW_ON_ERROR);
$fail = 0;
$pass = 0;
foreach ($scenario['characterize'] ?? [] as $step) {
  if ($step['kind'] !== 'explorer_grid') {
    continue;
  }
  $key = $step['key'];
  $l = $legacy[$key] ?? NULL;
  $d = $drupal[$key] ?? NULL;
  if ($l === NULL || $d === NULL) {
    printf("FAIL   explorer %s (%s): no %s result\n", $key, $label, $l === NULL ? 'legacy' : 'Drupal');
    $fail++;
    continue;
  }
  if (($step['drupal'] ?? 'same') === 'refused') {
    $refused = count($d) === 1 && str_starts_with((string) $d[0], 'refused:');
    printf("%-6s explorer %s (%s): legacy answers with %d start cells, Drupal %s (%s)\n", $refused ? 'PASS' : 'FAIL', $key, $label, count($l), $refused ? 'refuses: ' . $d[0] : 'ANSWERS', $step['ref'] ?? '');
    $refused ? $pass++ : $fail++;
    continue;
  }
  $same = $l === $d;
  printf("%-6s explorer %s (%s): %d start cells%s\n", $same ? 'PASS' : 'FAIL', $key, $label, count($l), $same ? '' : '');
  if (!$same) {
    foreach (array_diff($l, $d) as $line) {
      echo "         - legacy $line\n";
    }
    foreach (array_diff($d, $l) as $line) {
      echo "         + Drupal $line\n";
    }
    $fail++;
    continue;
  }
  $pass++;
}
printf("# %s explorer (%s): %d PASS, %d FAIL\n", $scenario['id'], $label, $pass, $fail);
exit($fail === 0 ? 0 : 1);
