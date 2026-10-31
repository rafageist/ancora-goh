<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Traits;

use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\ScopeMapper;

/**
 * The planning workspace fixture (#141): synthetic, privacy-safe.
 *
 * One owner's catalogue and three plans, as in the #133 fixture:
 * - plan A, "Autumn A", from Monday 2026-09-07 (plan days 1 and 8), 08:00,
 *   1.5 h slots and a 0.5 h break after slot 1;
 * - plan B, "Intensive B", the week of 2026-09-14 (08:30, 1 h slots);
 * - plan U, "Undated U", without a first day (cross-plan: not evaluated).
 *
 * Plan A's placements:
 * - A1: Lecture (1 slot), Group GA1, teacher TA, room L1, day 2 slot 2: TA
 *   is also in B1 (plan B, day 2 slot 2) on 2026-09-15 10:00-10:30;
 * - A2: Lab (2 slots), Group GA2, TA, L2, day 1 slots 1-2, across the break
 *   (08:00-09:30, break 09:30-10:00, 10:00-11:30);
 * - A3: Workshop (3 slots), Group GA3, TB, L1, day 3 slots 3-5;
 * - A4: Lecture, Group GA1, TC, L2, day 4 slot 1.
 * Plan U: U1, Lecture, Group GU1, TA, L1, day 2 slot 2; U2, Lab, Group GU2,
 * TB, L2, day 1 slots 1-2. Every scope has a succeeded run.
 *
 * The same code builds the browser CI site (scripts/lib/browser/fixture.php)
 * and the Kernel and Functional tests.
 */
trait WorkspaceFixtureTrait {

  /**
   * Builds the fixture for an owner.
   *
   * @return array<string, \Drupal\Core\Entity\EntityInterface>
   *   Records by key: TA, TB, TC, L1, L2, C1, C2, C3, E, N, S, B, plan:A,
   *   period:A (and B, U), the groups and the placements by code.
   */
  protected function workspaceFixture(int $uid, string $prefix = ''): array {
    $etm = \Drupal::entityTypeManager();
    $make = static function (string $type, array $values) use ($etm) {
      $record = $etm->getStorage($type)->create($values);
      $record->save();
      return $record;
    };
    $own = ['uid' => $uid];
    $r = [];
    foreach (['TA', 'TB', 'TC'] as $code) {
      $r[$code] = $make('ancora_teacher', $own + ['label' => "Teacher $prefix$code", 'code' => "$prefix$code"]);
    }
    foreach (['L1', 'L2'] as $code) {
      $r[$code] = $make('ancora_room', $own + [
        'label' => "Room $prefix$code",
        'code' => "$prefix$code",
        'capacity' => 0,
      ]);
    }
    foreach (['C1' => ['Lecture', 1], 'C2' => ['Lab', 2], 'C3' => ['Workshop', 3]] as $code => [$label, $slots]) {
      $r[$code] = $make('ancora_activity_type', $own + [
        'label' => $label,
        'code' => "$prefix$code",
        'duration_slots' => $slots,
        'priority' => 1,
        'cost' => 1,
      ]);
    }
    $r['E'] = $make('ancora_specialty', $own + ['label' => 'Program', 'code' => "{$prefix}E"]);
    $r['N'] = $make('ancora_level', $own + ['label' => 'Year 1', 'code' => "{$prefix}N"]);
    $r['S'] = $make('ancora_subject', $own + [
      'label' => 'Mathematics',
      'code' => "{$prefix}S",
      'specialty' => $r['E']->id(),
      'level' => $r['N']->id(),
    ]);
    $r['B'] = $make('ancora_cohort', $own + [
      'label' => 'Cohort',
      'code' => "{$prefix}B",
      'specialty' => $r['E']->id(),
      'level' => $r['N']->id(),
      'enrollment' => 20,
    ]);
    $plans = [
      'A' => ['Autumn A', '2026-09-07', '2026-09-20', 8, 1.5, [1, 8], [1 => 0.5]],
      'B' => ['Intensive B', '2026-09-14', '2026-09-18', 8.5, 1, [1], []],
      'U' => ['Undated U', NULL, NULL, 8, 1, [1], []],
    ];
    foreach ($plans as $code => [$label, $starts, $ends, $hour, $length, $days, $breaks]) {
      $plan = $make('ancora_plan', $own + [
        'label' => "$prefix$label",
        'code' => "$prefix$code",
        'starts_on' => $starts,
        'ends_on' => $ends,
      ]);
      $period = $make('ancora_period', [
        'plan' => $plan->id(),
        'label' => "Week $code",
        'code' => "{$prefix}P$code",
        'days' => 5,
        'slots_per_day' => 6,
        'start_hour' => $hour,
        'slot_duration' => $length,
        'plan_days' => $days,
      ]);
      foreach ($breaks as $after => $len) {
        $make('ancora_break', [
          'plan' => $plan->id(),
          'period' => $period->id(),
          'after_slot' => $after,
          'length' => $len,
        ]);
      }
      $r["plan:$code"] = $plan;
      $r["period:$code"] = $period;
    }
    $placements = [
      'A1' => ['A', 'C1', 'GA1', 'TA', 'L1', 2, 2],
      'A2' => ['A', 'C2', 'GA2', 'TA', 'L2', 1, 1],
      'A3' => ['A', 'C3', 'GA3', 'TB', 'L1', 3, 3],
      'A4' => ['A', 'C1', 'GA1', 'TC', 'L2', 4, 1],
      'B1' => ['B', 'C1', 'GB1', 'TA', 'L2', 2, 2],
      'U1' => ['U', 'C1', 'GU1', 'TA', 'L1', 2, 2],
      'U2' => ['U', 'C2', 'GU2', 'TB', 'L2', 1, 1],
    ];
    foreach ($placements as $code => [$plan, $type, $group, $teacher, $room, $day, $slot]) {
      if (!isset($r[$group])) {
        $r[$group] = $make('ancora_student_group', [
          'plan' => $r["plan:$plan"]->id(),
          'label' => "Group $group",
          'code' => "$prefix$group",
          'specialty' => $r['E']->id(),
          'level' => $r['N']->id(),
          'activity_type' => $r[$type]->id(),
          'cohorts' => [$r['B']->id()],
        ]);
      }
      $template = $make('ancora_activity_template', [
        'plan' => $r["plan:$plan"]->id(),
        'subject' => $r['S']->id(),
        'period' => $r["period:$plan"]->id(),
        'activity_type' => $r[$type]->id(),
      ]);
      $r[$code] = $make('ancora_placement', [
        'plan' => $r["plan:$plan"]->id(),
        'period' => $r["period:$plan"]->id(),
        'template' => $template->id(),
        'student_group' => $r[$group]->id(),
        'teacher' => $r[$teacher]->id(),
        'room' => $r[$room]->id(),
        'day' => $day,
        'slot' => $slot,
        'engine_id' => "e-$prefix$code",
        'job' => 1,
      ]);
    }
    $jobs = \Drupal::service('ancora_engine.job_repository');
    $export = \Drupal::service(AcademicExport::class);
    foreach (['A', 'B', 'U'] as $code) {
      $records = [$r["plan:$code"], $r["period:$code"], $r['E'], $r['N']];
      $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, $uid, NULL, ScopeMapper::source(...$records));
      // The run's input is the plan as it is now: a final, current timetable.
      $jobs->finish($jobs->claim('fixture'), JobState::Succeeded, [
        'kept' => 'placements',
        'input_hash' => ScopeMapper::hash($export->export($r["plan:$code"])),
      ]);
    }
    return $r;
  }

}
