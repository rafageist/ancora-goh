<?php

/**
 * @file
 * The Drupal side of the timetable explorer comparison (#119, D14).
 *
 * For every "explorer_grid" characterization step of a scenario, runs
 * TimetableExplorer::explore() as the step's user with the same selectors
 * (scenario keys mapped to the Drupal records by owner and code) and writes
 * the start cells in the form LegacyDriver::explorerGrid() reads them from
 * the legacy grid: "day.slot|classification|subject|teacher|room". A
 * refused request is written as ["refused:<status>"].
 *
 * Usage (drush php:script; ANCORA_ENGINE_MODE selects the engine mode):
 *
 * @code
 * drush php:script scripts/lib/parity/explorer-dump.php -- <scenario.json> <out dir> <label>
 * @endcode
 *
 * Writes <out dir>/drupal-explorer-<label>.json.
 */

declare(strict_types=1);

use Ancora\Parity\Scenario;
use Drupal\ancora_scheduling\Timetable\TimetableExplorer;
use Drupal\user\Entity\User;

require __DIR__ . '/src/Scenario.php';

// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
[$ex_file, $ex_out, $ex_label] = $extra + [NULL, NULL, 'drupal'];
$ex_scenario = Scenario::load($ex_file);
$etm = \Drupal::entityTypeManager();
$ex_types = [
  'cohort' => 'ancora_cohort',
  'teacher' => 'ancora_teacher',
  'room' => 'ancora_room',
  'other_resource' => 'ancora_other_resource',
  'plan' => 'ancora_plan',
];
$ex_account = static function (string $key) use ($ex_scenario) {
  return ($ex_scenario->users[$key]['legacy'] ?? '') === 'seeded-admin' ? User::load(1) : user_load_by_name($key);
};
// The Drupal id of a scenario record: an owned record by owner and code, a
// period by its plan and code.
$ex_id = static function (string $key) use ($ex_scenario, $etm, $ex_types, $ex_account, &$ex_id): ?int {
  $r = $ex_scenario->records[$key] ?? NULL;
  if ($r === NULL) {
    return NULL;
  }
  if ($r['type'] === 'period') {
    $plan = $ex_id($r['plan']);
    $ids = $etm->getStorage('ancora_period')->getQuery()->accessCheck(FALSE)
      ->condition('plan', $plan)->condition('code', $ex_scenario->code($key))->execute();
    return $ids === [] ? NULL : (int) reset($ids);
  }
  $owner = $ex_account($r['as']);
  $ids = $etm->getStorage($ex_types[$r['type']])->getQuery()->accessCheck(FALSE)
    ->condition('uid', $owner->id())->condition('code', $ex_scenario->code($key))->execute();
  return $ids === [] ? NULL : (int) reset($ids);
};
$ex_fields = ['idbrg' => 'brigade', 'idprofe' => 'teacher', 'idlug' => 'room', 'idres' => 'resource'];

$out = [];
foreach ($ex_scenario->data['characterize'] ?? [] as $step) {
  if ($step['kind'] !== 'explorer_grid') {
    continue;
  }
  $selectors = [];
  $period = NULL;
  foreach ($step['fields'] as $name => $ref) {
    $value = isset($ex_scenario->records[$ref]) ? $ex_id($ref) : $ref;
    if ($name === 'idperiodo') {
      $period = $value;
    }
    elseif (isset($ex_fields[$name])) {
      $selectors[$ex_fields[$name]] = $value;
    }
  }
  \Drupal::service('entity.memory_cache')->deleteAll();
  $result = \Drupal::service(TimetableExplorer::class)->explore($ex_account($step['as']), $period, $selectors);
  if ($result['status'] !== 'ok') {
    $out[$step['key']] = ['refused:' . $result['status']];
    continue;
  }
  $lines = array_map(static fn (array $p): string => implode('|', [
    $p['day'] . '.' . $p['slot'],
    $p['activity_type'],
    $p['subject'],
    $p['teacher'],
    $p['room'],
  ]), $result['placements']);
  sort($lines, SORT_STRING);
  $out[$step['key']] = $lines;
  echo "ok    explorer {$step['key']}: " . count($lines) . " activities\n";
}
file_put_contents("$ex_out/drupal-explorer-$ex_label.json", json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
