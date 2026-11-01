<?php

/**
 * @file
 * Moving one saved placement (#130), for scripts/workspace-journey.sh.
 *
 * Usage: drush php:script scripts/lib/workspace-journey/move.php -- <command>.
 *
 * Every command runs as the account "planner" (the owner of the fixture).
 * - setup: the fixture of characterization case 150 on top of the E2E
 *   placements: a manual restriction of teacher TA and a period restriction
 *   on free cells, and a 2-slot activity (its own activity type and
 *   template) placed on a free cell; prints JSON.
 * - placement <template code> <group code>: the id of a placement.
 * - request <placement id>: queues a destination check; prints its id.
 * - state <check id>: the check's state and grid, JSON.
 * - commit <placement id> <check id> <day> <slot>: moves it; prints the
 *   change id, or "refused:<reason>".
 * - undo <change id> <check id>: undoes it; same output.
 * - cell <placement id>: "day.slot".
 */

declare(strict_types=1);

use Drupal\ancora_scheduling\Timetable\PlacementModification;
use Drupal\ancora_scheduling\Timetable\PlacementModificationException;

// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
[$command, $a1, $a2, $a3, $a4] = $extra + array_fill(0, 5, NULL);
$etm = \Drupal::entityTypeManager();
$planner = current($etm->getStorage('user')->loadByProperties(['name' => 'planner']));
\Drupal::service('account_switcher')->switchTo($planner);
$moves = \Drupal::service(PlacementModification::class);
$one = static function (string $type, array $properties) use ($etm) {
  $found = $etm->getStorage($type)->loadByProperties($properties);
  if (count($found) !== 1) {
    throw new \RuntimeException("no single $type for " . json_encode($properties));
  }
  return current($found);
};
$placements = $etm->getStorage('ancora_placement');
$plan = $one('ancora_plan', ['code' => 'P', 'uid' => $planner->id()]);
$period = $one('ancora_period', ['code' => 'PER', 'plan' => $plan->id()]);

switch ($command) {
  case 'setup':
    // The cells used by any placement, by teacher, room and group.
    $used = [];
    foreach ($placements->loadMultiple() as $p) {
      $used[$p->get('day')->value . '.' . $p->get('slot')->value] = TRUE;
    }
    $free = [];
    for ($d = 1; $d <= 5; $d++) {
      for ($s = 1; $s <= 4; $s++) {
        if (!isset($used["$d.$s"])) {
          $free[] = [$d, $s];
        }
      }
    }
    // A 2-slot activity of G1 with teacher TB in room L2, on two free slots
    // of one day (not the cells the restrictions take).
    $block = NULL;
    foreach ($free as [$d, $s]) {
      if ($s < 4 && in_array([$d, $s + 1], $free, TRUE)) {
        $block = [$d, $s];
        break;
      }
    }
    $rest = array_values(array_filter($free, static fn (array $c): bool => $block === NULL || ($c[0] !== $block[0] || ($c[1] !== $block[1] && $c[1] !== $block[1] + 1))));
    [$teacher_cell, $period_cell] = [$rest[0], $rest[1]];
    $ta = $one('ancora_teacher', ['code' => 'TA', 'uid' => $planner->id()]);
    $etm->getStorage('ancora_time_restriction')->create([
      'plan' => $plan->id(),
      'period' => $period->id(),
      'kind' => 'teacher',
      'teacher' => $ta->id(),
      'day' => $teacher_cell[0],
      'slot' => $teacher_cell[1],
    ])->save();
    $etm->getStorage('ancora_time_restriction')->create([
      'plan' => $plan->id(),
      'period' => $period->id(),
      'kind' => 'period',
      'day' => $period_cell[0],
      'slot' => $period_cell[1],
    ])->save();
    $type = $etm->getStorage('ancora_activity_type')->create([
      'label' => 'Block',
      'code' => 'C9',
      'duration_slots' => 2,
      'priority' => 9,
      'cost' => 1,
    ]);
    $type->save();
    // Its own specialty and subject: the generation scope (E, N) never
    // processes it (a template without teacher assignments in a scope makes
    // generar() run until its time limit).
    $specialty = $etm->getStorage('ancora_specialty')->create(['label' => 'Blocks', 'code' => 'F9']);
    $specialty->save();
    $level = $one('ancora_level', ['code' => 'N', 'uid' => $planner->id()]);
    $subject = $etm->getStorage('ancora_subject')->create([
      'label' => 'Block subject',
      'code' => 'Z9',
      'specialty' => $specialty->id(),
      'level' => $level->id(),
    ]);
    $subject->save();
    $template = $etm->getStorage('ancora_activity_template')->create([
      'plan' => $plan->id(),
      'subject' => $subject->id(),
      'period' => $period->id(),
      'activity_type' => $type->id(),
    ]);
    $template->save();
    $block_placement = $placements->create([
      'plan' => $plan->id(),
      'period' => $period->id(),
      'template' => $template->id(),
      'student_group' => $one('ancora_student_group', ['code' => 'G1', 'plan' => $plan->id()])->id(),
      'teacher' => $one('ancora_teacher', ['code' => 'TB', 'uid' => $planner->id()])->id(),
      'room' => $one('ancora_room', ['code' => 'L2', 'uid' => $planner->id()])->id(),
      'day' => $block[0],
      'slot' => $block[1],
      'engine_id' => 'journey-block',
      'job' => 1,
    ]);
    $block_placement->save();
    echo json_encode([
      'teacher_cell' => implode('.', $teacher_cell),
      'period_cell' => implode('.', $period_cell),
      'block' => (int) $block_placement->id(),
      'block_cell' => implode('.', $block),
    ]), "\n";
    break;

  case 'placement':
    $template = NULL;
    foreach ($etm->getStorage('ancora_activity_template')->loadByProperties(['plan' => $plan->id()]) as $t) {
      if ($t->getSubject()?->getCode() . $t->getSequence() === $a1) {
        $template = $t;
      }
    }
    $group = $one('ancora_student_group', ['code' => $a2, 'plan' => $plan->id()]);
    echo (int) array_key_first($placements->loadByProperties([
      'template' => $template?->id(),
      'student_group' => $group->id(),
    ])), "\n";
    break;

  case 'request':
    echo $moves->requestCheck($placements->load($a1), $planner), "\n";
    break;

  case 'state':
    $row = \Drupal::database()->select('ancora_placement_check', 'c')->fields('c')->condition('id', $a1)->execute()->fetchAssoc();
    $row['result'] = $row['result'] === NULL ? NULL : json_decode($row['result'], TRUE);
    $cells = [];
    foreach ($row['result']['cells'] ?? [] as $cell) {
      $cells[$cell['day'] . '.' . $cell['slot']] = $cell['ok'] ? 'ok' : implode(',', $cell['reasons']);
    }
    echo json_encode([
      'state' => $row['state'],
      'error' => $row['error'],
      'from' => $row['result']['from'] ?? NULL,
      'cells' => $cells,
    ]), "\n";
    break;

  case 'commit':
  case 'undo':
    try {
      $change = $command === 'commit'
        ? $moves->commit($placements->load($a1), $a2, $a3, $a4, $planner)
        : $moves->undo($etm->getStorage('ancora_placement_change')->load($a1), $a2, $planner);
      echo $change->id(), "\n";
    }
    catch (PlacementModificationException $e) {
      echo 'refused:', $e->reason, "\n";
    }
    break;

  case 'cell':
    $placements->resetCache();
    $p = $placements->load($a1);
    echo $p->get('day')->value, '.', $p->get('slot')->value, "\n";
    break;
}
\Drupal::service('account_switcher')->switchBack();
