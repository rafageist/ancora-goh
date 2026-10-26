<?php

/**
 * @file
 * Drupal driver of the differential parity harness (scripts/parity-diff.sh).
 *
 * Enters a scenario through the Drupal Entity API, as the scenario's users:
 * each record is created while the acting account is switched to its user
 * ("as"), validated with every entity and field constraint, then saved. The
 * visibility checks run as their user through access-checked entity queries
 * and entity access. Each run is queued as a timetable request (the job
 * source of the request form); scripts/parity-diff.sh then runs the worker,
 * which syncs the site into its engine database (EngineSync) and calls
 * generar().
 *
 * Usage (run with drush php:script; never alone):
 *
 * @code
 * drush php:script scripts/lib/parity/drupal-driver.php -- <scenario.json> <out dir>
 * @endcode
 *
 * Writes <out dir>/drupal-users.json (engine user id => scenario user key)
 * and prints one line per step: "ok", "FAIL" or "VIS".
 */

declare(strict_types=1);

use Ancora\Parity\Scenario;
use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

require __DIR__ . '/src/Scenario.php';

// Set by drush php:script.
// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
[$ancora_scenario_file, $ancora_out] = $extra + [NULL, NULL];
$ancora_scenario = Scenario::load($ancora_scenario_file);
$ancora_types = [
  'plan' => 'ancora_plan',
  'period' => 'ancora_period',
  'activity_type' => 'ancora_activity_type',
  'specialty' => 'ancora_specialty',
  'level' => 'ancora_level',
  'cohort' => 'ancora_cohort',
  'student_group' => 'ancora_student_group',
  'subject' => 'ancora_subject',
  'activity_template' => 'ancora_activity_template',
  'teacher' => 'ancora_teacher',
  'teacher_assignment' => 'ancora_teacher_assignment',
  'room' => 'ancora_room',
  'room_assignment' => 'ancora_room_assignment',
];
$ancora_failures = 0;
$ancora_fail = static function (string $what) use (&$ancora_failures): void {
  $ancora_failures++;
  echo "FAIL  $what\n";
};
$etm = \Drupal::entityTypeManager();
$switcher = \Drupal::service('account_switcher');

// Users: the scenario's administrator is the site's first account; every
// other user is a planner (the role of scripts/r1-journey.sh).
if (!Role::load('planner')) {
  $role = Role::create(['id' => 'planner', 'label' => 'Planner']);
  foreach (['view academic data', 'edit academic data', 'view ancora engine jobs', 'request ancora engine jobs'] as $permission) {
    $role->grantPermission($permission);
  }
  $role->save();
}
$ancora_accounts = [];
foreach ($ancora_scenario->users as $key => $user) {
  if (($user['legacy'] ?? '') === 'seeded-admin') {
    $account = User::load(1);
  }
  else {
    $account = user_load_by_name($key) ?: User::create(['name' => $key, 'mail' => "$key@example.invalid", 'status' => 1]);
    $account->addRole('planner');
    $account->save();
  }
  $ancora_accounts[$key] = $account;
}

// Records, in scenario order.
$ancora_entities = [];
foreach ($ancora_scenario->records as $key => $r) {
  foreach (Scenario::TYPES[$r['type']] as $field => $target) {
    foreach ((array) ($r[$field] ?? []) as $reference) {
      if (!isset($ancora_entities[$reference])) {
        $ancora_fail("create {$r['type']} $key: skipped, $field $reference was not created");
        continue 3;
      }
    }
  }
  $values = ['label' => $r['label'] ?? NULL, 'code' => $ancora_scenario->code($key)];
  $plan = $ancora_scenario->planOf($key);
  $refs = static function (array $fields) use ($r, &$ancora_entities): array {
    $out = [];
    foreach ($fields as $field) {
      if (!isset($r[$field])) {
        continue;
      }
      $out[$field] = is_array($r[$field])
        ? array_map(static fn (string $k): int|string|null => $ancora_entities[$k]->id(), $r[$field])
        : $ancora_entities[$r[$field]]->id();
    }
    return $out;
  };
  $values += match ($r['type']) {
    'plan' => ['starts_on' => $r['starts_on'] ?? NULL, 'duration_days' => $r['duration_days'], 'active' => TRUE],
    'period' => [
      'days' => $r['days'],
      'slots_per_day' => $r['slots_per_day'],
      'start_hour' => $r['start_hour'],
      'slot_duration' => $r['slot_duration'],
      'plan_days' => $r['plan_days'],
    ],
    'activity_type' => ['duration_slots' => $r['duration_slots'], 'priority' => $r['priority'], 'cost' => $r['cost']],
    'cohort' => ['enrollment' => $r['enrollment']],
    'activity_template' => [
      'sequence' => $r['sequence'],
      'requires_teacher' => $r['requires_teacher'] ?? TRUE,
      'requires_room' => $r['requires_room'] ?? TRUE,
    ],
    'teacher_assignment', 'room_assignment' => ['priority' => $r['priority'] ?? 1],
    'room' => ['capacity' => $r['capacity']],
    default => [],
  };
  $values += $refs([
    'plan', 'specialty', 'level', 'activity_type', 'cohorts', 'subject',
    'period', 'template', 'teacher', 'groups', 'room',
  ]);
  if (in_array($r['type'], ['activity_template', 'teacher_assignment', 'room_assignment'], TRUE)) {
    if (!isset($ancora_entities[$plan])) {
      $ancora_fail("create {$r['type']} $key: skipped, its plan was not created");
      continue;
    }
    $values['plan'] = $ancora_entities[$plan]->id();
    unset($values['label'], $values['code']);
  }
  $storage = $etm->getStorage($ancora_types[$r['type']]);
  $definitions = \Drupal::service('entity_field.manager')->getBaseFieldDefinitions($ancora_types[$r['type']]);
  foreach (array_keys($values) as $field) {
    if (!isset($definitions[$field])) {
      unset($values[$field]);
    }
  }
  $switcher->switchTo($ancora_accounts[$r['as']]);
  try {
    $entity = $storage->create($values);
    assert($entity instanceof AcademicEntityInterface);
    $violations = $entity->validate();
    if ($violations->count() > 0) {
      $messages = [];
      foreach ($violations as $violation) {
        $messages[] = $violation->getPropertyPath() . ': ' . strip_tags((string) $violation->getMessage());
      }
      $ancora_fail("create {$r['type']} $key: " . implode('; ', $messages));
      continue;
    }
    $entity->save();
    $ancora_entities[$key] = $entity;
    echo "ok    create {$r['type']} $key\n";
  }
  finally {
    $switcher->switchBack();
  }
}

// Visibility: access-checked entity queries and entity access, as the user.
foreach ($ancora_scenario->visibility() as $check) {
  $switcher->switchTo($ancora_accounts[$check['as']]);
  try {
    $entity_type_id = $ancora_types[$check['type']];
    $ids = $etm->getStorage($entity_type_id)->getQuery()->accessCheck(TRUE)->execute();
    foreach (['visible' => TRUE, 'hidden' => FALSE] as $list => $expected) {
      foreach ($check[$list] ?? [] as $record_key) {
        $entity = $ancora_entities[$record_key] ?? NULL;
        if ($entity === NULL) {
          $ancora_fail("visibility: $record_key was not created");
          continue;
        }
        $listed = in_array((string) $entity->id(), array_map('strval', $ids), TRUE);
        $view = $entity->access('view');
        $update = $entity->access('update');
        // Every user may view any teacher or room (the legacy timetable
        // explorer lists them all, quirk Q-23); the lists and editing are
        // the owner's.
        $view_expected = $expected || in_array($entity_type_id, AcademicAccessControlHandler::VISIBLE_TO_ALL, TRUE);
        $ok = $listed === $expected && $view === $view_expected && $update === $expected;
        printf("VIS   %s %s %s %s, view %s, update %s: %s\n", $check['as'], $check['type'], $record_key, $listed ? 'listed' : 'not listed', $view ? 'allowed' : 'denied', $update ? 'allowed' : 'denied', $ok ? 'as expected' : 'UNEXPECTED');
        if (!$ok) {
          $ancora_failures++;
        }
      }
    }
  }
  finally {
    $switcher->switchBack();
  }
}

// Runs: queue a timetable request per run, as its user.
$jobs = \Drupal::service('ancora_engine.job_repository');
foreach ($ancora_scenario->runs() as $run) {
  $o = $run['options'];
  foreach (['plan', 'period', 'specialty', 'level'] as $field) {
    if (!isset($ancora_entities[$run[$field]])) {
      $ancora_fail("run {$run['key']}: skipped, $field {$run[$field]} was not created");
      continue 2;
    }
  }
  $records = array_map(static fn (string $k) => $ancora_entities[$k], [
    $run['plan'],
    $run['period'],
    $run['specialty'],
    $run['level'],
  ]);
  $id = $jobs->enqueue(
    ScopeMapper::scope(...$records),
    new JobOptions($o['forma'], $o['parche'], $o['compactar'], $o['parche1'], $o['buscalugar']),
    \Drupal::service('ancora_engine.settings')->timeLimit(),
    (int) $ancora_accounts[$run['as']]->id(),
    NULL,
    ScopeMapper::source(...$records),
  );
  echo "ok    queued run {$run['key']} as job $id\n";
}

// Engine user id => scenario user key.
$map = [];
foreach ($ancora_accounts as $key => $account) {
  $map[ScopeMapper::creatorOf((int) $account->id())] = $key;
}
file_put_contents("$ancora_out/drupal-users.json", json_encode($map, JSON_PRETTY_PRINT));
if ($ancora_failures > 0) {
  throw new \RuntimeException("$ancora_failures driver steps failed");
}
