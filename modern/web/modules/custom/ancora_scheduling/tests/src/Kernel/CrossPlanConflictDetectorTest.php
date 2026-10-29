<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_academic\PeriodInputs;
use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Timetable\CrossPlanConflictDetector;
use Drupal\ancora_scheduling\Timetable\TimetableReader;
use Drupal\Core\Site\Settings;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Read-only cross-plan conflict warnings (#133, phase 2).
 *
 * The fixture of docs/modernization/temporal-semantics.md: plan A from
 * Monday 2026-09-07, weekly on plan days 1 and 8, 08:00, 1.5-hour slots, a
 * 0.5-hour break after slot 1; plan B, the week of 2026-09-14, 08:30, 1-hour
 * slots; plan C in November; plan U without dates. One owner's teachers and
 * rooms are shared by the plans (D1).
 */
#[CoversClass(CrossPlanConflictDetector::class)]
#[Group('ancora_scheduling')]
final class CrossPlanConflictDetectorTest extends AcademicKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ancora_academic',
    'ancora_engine',
    'ancora_scheduling',
    'datetime',
    'system',
    'user',
    'views',
  ];

  /**
   * Records by code: plans, periods, teachers, rooms, placements.
   */
  private array $r = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->set('ancora_engine.settings', new EngineSettings(new Settings(['ancora_engine' => ['mode' => 'workspace']])));
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    $this->installEntitySchema('ancora_placement');
    $this->catalogue($this->owner, '');
    $this->plan('A', '2026-09-07', '2026-09-20', ['start_hour' => 8, 'slot_duration' => 1.5, 'plan_days' => [1, 8]], [1 => 0.5]);
    $this->plan('B', '2026-09-14', '2026-09-18', ['start_hour' => 8.5, 'slot_duration' => 1, 'plan_days' => [1]]);
    $this->plan('C', '2026-11-02', '2026-11-06', ['start_hour' => 8.5, 'slot_duration' => 1, 'plan_days' => [1]]);
    $this->plan('U', NULL, NULL, ['start_hour' => 8, 'slot_duration' => 1, 'plan_days' => [1]]);
    // A1 TA/L1 Tue slot 2 [10:00, 11:30); B1 TA/L2 Tue slot 2 [09:30, 10:30):
    // the teacher overlaps on 2026-09-15 10:00-10:30.
    $this->place('A1', 'A', 'TA', 'L1', 2, 2);
    $this->place('B1', 'B', 'TA', 'L2', 2, 2);
    // A2 TB/L2 Tue slot 1 [08:00, 09:30) touches B1's room L2 at 09:30.
    $this->place('A2', 'A', 'TB', 'L2', 2, 1);
    // A3 TD/L1, 2 slots from Wed slot 1: [08:00, 09:30) and, after the
    // break, [10:00, 11:30); B2 TC/L1 Wed slot 1 [08:30, 09:30): the room
    // overlaps on 2026-09-16 08:30-09:30.
    $this->place('A3', 'A', 'TD', 'L1', 3, 1, 2);
    $this->place('B2', 'B', 'TC', 'L1', 3, 1);
    // Disjoint dates, and an unknown calendar.
    $this->place('C1', 'C', 'TA', 'L1', 2, 2);
    $this->place('U1', 'U', 'TA', 'L1', 2, 2);
    foreach (['A', 'B', 'C', 'U'] as $plan) {
      $this->finishRun($plan);
    }
  }

  /**
   * The owner's catalogue: teachers, rooms, activity types, specialty.
   */
  private function catalogue(UserInterface $owner, string $suffix): void {
    $this->setCurrentUser($owner);
    $create = function (string $type, array $values): mixed {
      $record = $this->newRecord($type, $values);
      $record->save();
      return $record;
    };
    foreach (['TA', 'TB', 'TC', 'TD'] as $code) {
      $this->r[$code . $suffix] = $create('ancora_teacher', ['label' => "Teacher $code", 'code' => $code]);
    }
    foreach (['L1', 'L2'] as $code) {
      $this->r[$code . $suffix] = $create('ancora_room', ['label' => "Room $code", 'code' => $code, 'capacity' => 0]);
    }
    $this->r['C1' . $suffix] = $create('ancora_activity_type', [
      'label' => 'Lecture',
      'code' => 'C1',
      'duration_slots' => 1,
      'priority' => 1,
      'cost' => 1,
    ]);
    $this->r['C2' . $suffix] = $create('ancora_activity_type', [
      'label' => 'Block',
      'code' => 'C2',
      'duration_slots' => 2,
      'priority' => 2,
      'cost' => 1,
    ]);
    $this->r['E' . $suffix] = $create('ancora_specialty', ['label' => 'Program', 'code' => 'E']);
    $this->r['N' . $suffix] = $create('ancora_level', ['label' => 'Year 1', 'code' => 'N']);
    $this->r['S' . $suffix] = $create('ancora_subject', [
      'label' => 'Subject',
      'code' => 'S',
      'specialty' => $this->r['E' . $suffix]->id(),
      'level' => $this->r['N' . $suffix]->id(),
    ]);
    $this->r['B' . $suffix] = $create('ancora_cohort', [
      'label' => 'Cohort',
      'code' => 'B',
      'specialty' => $this->r['E' . $suffix]->id(),
      'level' => $this->r['N' . $suffix]->id(),
      'enrollment' => 20,
    ]);
    $this->setCurrentUser($this->owner);
  }

  /**
   * A plan with one period of 5 days and 6 slots.
   */
  private function plan(string $code, ?string $starts, ?string $ends, array $period, array $breaks = [], string $suffix = ''): void {
    $plan = $this->newRecord('ancora_plan', [
      'label' => "Plan $code",
      'code' => $code,
      'starts_on' => $starts,
      'ends_on' => $ends,
    ]);
    $plan->save();
    $p = $this->newRecord('ancora_period', $period + [
      'plan' => $plan->id(),
      'label' => "Week $code",
      'code' => "P$code",
      'days' => 5,
      'slots_per_day' => 6,
    ]);
    $p->save();
    foreach ($breaks as $after => $length) {
      $this->newRecord('ancora_break', [
        'plan' => $plan->id(),
        'period' => $p->id(),
        'after_slot' => $after,
        'length' => $length,
      ])->save();
    }
    $this->r["plan:$code$suffix"] = $plan;
    $this->r["period:$code$suffix"] = $p;
  }

  /**
   * A placement of its own template and group.
   */
  private function place(string $code, string $plan, string $teacher, string $room, int $day, int $slot, int $slots = 1, string $suffix = '', ?int $job = NULL): void {
    $p = $this->r["plan:$plan$suffix"];
    $period = $this->r["period:$plan$suffix"];
    $template = $this->newRecord('ancora_activity_template', [
      'plan' => $p->id(),
      'subject' => $this->r['S' . $suffix]->id(),
      'period' => $period->id(),
      'activity_type' => $this->r[($slots === 2 ? 'C2' : 'C1') . $suffix]->id(),
    ]);
    $template->save();
    $group = $this->newRecord('ancora_student_group', [
      'plan' => $p->id(),
      'label' => "Group $code",
      'code' => "G$code",
      'specialty' => $this->r['E' . $suffix]->id(),
      'level' => $this->r['N' . $suffix]->id(),
      'activity_type' => $this->r[($slots === 2 ? 'C2' : 'C1') . $suffix]->id(),
      'cohorts' => [$this->r['B' . $suffix]->id()],
    ]);
    $group->save();
    $placement = $this->container->get('entity_type.manager')->getStorage('ancora_placement')->create([
      'plan' => $p->id(),
      'period' => $period->id(),
      'template' => $template->id(),
      'student_group' => $group->id(),
      'teacher' => $this->r[$teacher . $suffix]->id(),
      'room' => $this->r[$room . $suffix]->id(),
      'day' => $day,
      'slot' => $slot,
      'engine_id' => "e-$code$suffix",
      'job' => $job ?? 1,
    ]);
    $placement->save();
    $this->r[$code . $suffix] = $placement;
  }

  /**
   * A succeeded run of a plan's scope, so its timetable shows placements.
   */
  private function finishRun(string $plan, string $suffix = ''): int {
    $jobs = $this->container->get(JobRepository::class);
    $records = [
      $this->r["plan:$plan$suffix"],
      $this->r["period:$plan$suffix"],
      $this->r['E' . $suffix],
      $this->r['N' . $suffix],
    ];
    $id = $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $this->r["plan:$plan$suffix"]->getCreatorId(), NULL, ScopeMapper::source(...$records));
    $jobs->finish($jobs->claim('test'), JobState::Succeeded, ['kept' => 'placements']);
    return $id;
  }

  /**
   * A fresh detector (it keeps its results for one request).
   */
  private function detector(): CrossPlanConflictDetector {
    return new CrossPlanConflictDetector($this->container->get('entity_type.manager'), $this->container->get(TimetableReader::class), $this->container->get(PeriodInputs::class));
  }

  /**
   * Conflicts as "resource label: mine / other: dates" lines.
   */
  private static function lines(array $result): array {
    $out = array_map(static fn (array $c): string => sprintf(
      '%s %s: %s/%s %s',
      $c['resource'],
      $c['resource_label'],
      $c['mine']['group'],
      $c['other']['group'] ?? '?',
      implode(',', array_map(static fn (array $o): string => $o['date'] . ' ' . $o['start'] . '-' . $o['end'] . ($o['after_end'] ? ' after' : ''), $c['occurrences'])),
    ), $result['conflicts']);
    sort($out);
    return $out;
  }

  /**
   * True positives, adjacency, breaks, slot lengths, disjoint dates, unknown.
   */
  public function testConflictsOfPlans(): void {
    $a = $this->detector()->forPlan($this->r['plan:A'], $this->owner);
    $this->assertTrue($a['evaluated']);
    $this->assertSame([
      'room Room L1: Group A3/Group B2 2026-09-16 08:30-09:30',
      'teacher Teacher TA: Group A1/Group B1 2026-09-15 10:00-10:30',
    ], self::lines($a), 'the teacher on one date (different slot lengths), the room on the first slot of a 2-slot activity; A2/B1 only touch; C is in November; U is not evaluated');
    $this->assertSame([['plan' => 'Plan U', 'period' => 'Week U', 'reason' => 'no_first_day']], $a['unknown']);
    $this->assertSame('Plan B', $a['conflicts'][0]['other']['plan']);

    $b = $this->detector()->forPlan($this->r['plan:B'], $this->owner);
    $this->assertSame([
      'room Room L1: Group B2/Group A3 2026-09-16 08:30-09:30',
      'teacher Teacher TA: Group B1/Group A1 2026-09-15 10:00-10:30',
    ], self::lines($b), 'the same pairs from the other side, once each');
    $this->assertSame([], self::lines($this->detector()->forPlan($this->r['plan:C'], $this->owner)));
    $this->assertTrue($this->detector()->forPlan($this->r['plan:C'], $this->owner)['evaluated'], 'C is evaluated: no overlap is a real answer for it');
    $u = $this->detector()->forPlan($this->r['plan:U'], $this->owner);
    $this->assertFalse($u['evaluated'], 'no first day: not evaluated, never "no conflict"');
    $this->assertSame([], $u['conflicts']);

    // One period of the plan only: the same two conflicts (B has one period).
    $this->assertCount(2, $this->detector()->forPlan($this->r['plan:B'], $this->owner, $this->r['period:B'])['conflicts']);
  }

  /**
   * Prospective destinations of a move, without committing it.
   */
  public function testCandidates(): void {
    $result = $this->detector()->forCandidates($this->r['A1'], [[2, 1], [2, 2], [3, 1], [3, 3], [4, 2]], $this->owner);
    $this->assertTrue($result['evaluated']);
    $summary = array_map(static fn (array $cs): array => array_map(static fn (array $c): string => $c['resource'] . ' ' . $c['occurrences'][0]['date'] . ' ' . $c['occurrences'][0]['start'] . '-' . $c['occurrences'][0]['end'], $cs), $result['cells']);
    $this->assertSame([
      '2.1' => [],
      '2.2' => ['teacher 2026-09-15 10:00-10:30'],
      '3.1' => ['room 2026-09-16 08:30-09:30'],
      '3.3' => [],
      '4.2' => [],
    ], $summary, 'Tue slot 1 [08:00, 09:30) touches B1 at 09:30; Wed slot 1 meets B2 in room L1; its own plan is not counted');
    $unknown = $this->detector()->forCandidates($this->r['U1'], [[2, 2]], $this->owner);
    $this->assertSame([FALSE, 'no_first_day', []], [$unknown['evaluated'], $unknown['reason'], $unknown['cells']]);
  }

  /**
   * After the plan's last day, overlapping repetitions, a running job.
   */
  public function testCalendarEdgesAndRuns(): void {
    // D: one day (2026-09-14), a 5-day period repeated on plan days 1 and 2
    // (closer than its length). Its Monday placement happens on 2026-09-14
    // and 2026-09-15 (after the plan's last day) at [09:30, 10:30): it meets
    // B1 on 2026-09-15.
    $this->plan('D', '2026-09-14', '2026-09-14', ['start_hour' => 8.5, 'slot_duration' => 1, 'plan_days' => [1, 2]]);
    $this->place('D1', 'D', 'TA', 'L2', 1, 2);
    $this->finishRun('D');
    $this->assertSame([
      'room Room L2: Group D1/Group B1 2026-09-15 09:30-10:30 after',
      'teacher Teacher TA: Group D1/Group A1 2026-09-15 10:00-10:30 after',
      'teacher Teacher TA: Group D1/Group B1 2026-09-15 09:30-10:30 after',
    ], self::lines($this->detector()->forPlan($this->r['plan:D'], $this->owner)), 'both repetitions dated; the second is after the plan\'s last day and marked; A2 only touches room L2 at 09:30');

    // A placement of a running job is not shown by the timetable, so it is not
    // compared.
    $jobs = $this->container->get(JobRepository::class);
    $records = [$this->r['plan:B'], $this->r['period:B'], $this->r['E'], $this->r['N']];
    $running = $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $this->owner->id(), NULL, ScopeMapper::source(...$records));
    $jobs->claim('test');
    $this->place('B9', 'B', 'TB', 'L2', 2, 1, 1, '', $running);
    $lines = self::lines($this->detector()->forPlan($this->r['plan:A'], $this->owner));
    $this->assertSame([], array_filter($lines, static fn (string $l): bool => str_contains($l, 'B9')), 'the running job\'s placement is not compared');
  }

  /**
   * Owner scope, access and isolation; read-only.
   */
  public function testAccessIsolationAndReadOnly(): void {
    // Another planner with the same codes and an overlapping dated plan.
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->catalogue($other, ':o');
    $this->setCurrentUser($other);
    $this->plan('O', '2026-09-14', '2026-09-18', ['start_hour' => 8.5, 'slot_duration' => 1, 'plan_days' => [1]], [], ':o');
    $this->place('O1', 'O', 'TA', 'L1', 2, 2, 1, ':o');
    $this->finishRun('O', ':o');
    $this->setCurrentUser($this->owner);
    $tables = $this->counts();

    $this->assertSame([], self::lines($this->detector()->forPlan($this->r['plan:O:o'], $other)), 'another owner\'s "Teacher TA" is another entity: no conflict with the owner\'s plans');
    $denied = $this->detector()->forPlan($this->r['plan:A'], $other);
    $this->assertSame(['evaluated' => FALSE, 'unknown' => [], 'conflicts' => []], $denied, 'another planner learns nothing about the owner\'s plans');
    $this->assertSame('no_access', $this->detector()->forCandidates($this->r['A1'], [[2, 2]], $other)['reason']);
    $admin = $this->createUser(['view academic data', 'administer academic data']);
    $this->assertCount(2, $this->detector()->forPlan($this->r['plan:A'], $admin)['conflicts'], 'an administrator sees the owner\'s warnings');
    $this->assertSame($tables, $this->counts(), 'read only: nothing written');
    $tags = $this->detector()->cacheability()->getCacheTags();
    foreach ([
      'ancora_placement_list',
      'ancora_plan_list',
      'ancora_period_list',
      'ancora_break_list',
      JobRepository::CACHE_TAG,
    ] as $tag) {
      $this->assertContains($tag, $tags);
    }
  }

  /**
   * A bound: 600 placements of two overlapping plans, 10 teachers, 10 rooms.
   */
  public function testPerformanceBound(): void {
    $etm = $this->container->get('entity_type.manager');
    $teachers = $rooms = [];
    for ($i = 0; $i < 10; $i++) {
      $teachers[] = $this->newRecord('ancora_teacher', ['label' => "T$i", 'code' => "PT$i"]);
      $rooms[] = $this->newRecord('ancora_room', ['label' => "R$i", 'code' => "PR$i", 'capacity' => 0]);
      end($teachers)->save();
      end($rooms)->save();
    }
    $n = 0;
    foreach (['X' => '2026-09-14', 'Y' => '2026-09-14'] as $code => $starts) {
      $this->plan($code, $starts, '2026-09-25', ['start_hour' => 8, 'slot_duration' => 1, 'plan_days' => [1, 8]]);
      $templates = $groups = [];
      for ($i = 0; $i < 20; $i++) {
        $templates[] = $etm->getStorage('ancora_activity_template')->create([
          'plan' => $this->r["plan:$code"]->id(),
          'subject' => $this->r['S']->id(),
          'period' => $this->r["period:$code"]->id(),
          'activity_type' => $this->r['C1']->id(),
        ]);
        end($templates)->save();
      }
      for ($i = 0; $i < 15; $i++) {
        $groups[] = $etm->getStorage('ancora_student_group')->create([
          'plan' => $this->r["plan:$code"]->id(),
          'label' => "G$code$i",
          'code' => "G$code$i",
          'specialty' => $this->r['E']->id(),
          'level' => $this->r['N']->id(),
          'activity_type' => $this->r['C1']->id(),
          'cohorts' => [$this->r['B']->id()],
        ]);
        end($groups)->save();
      }
      foreach ($templates as $t) {
        foreach ($groups as $g) {
          $n++;
          $etm->getStorage('ancora_placement')->create([
            'plan' => $this->r["plan:$code"]->id(),
            'period' => $this->r["period:$code"]->id(),
            'template' => $t->id(),
            'student_group' => $g->id(),
            'teacher' => $teachers[$n % 10]->id(),
            'room' => $rooms[($n * 7) % 10]->id(),
            'day' => 1 + $n % 5,
            'slot' => 1 + intdiv($n, 5) % 6,
            'engine_id' => "p$n",
            'job' => 1,
          ])->save();
        }
      }
      $this->finishRun($code);
    }
    $start = hrtime(TRUE);
    $result = $this->detector()->forPlan($this->r['plan:X'], $this->owner);
    $seconds = (hrtime(TRUE) - $start) / 1e9;
    $this->assertSame(600, $n);
    $this->assertNotEmpty($result['conflicts']);
    $this->assertLessThan(15.0, $seconds, sprintf('600 placements (2 repetitions each): %.2f s', $seconds));
  }

  /**
   * Row counts of the tables the detector might touch.
   */
  private function counts(): array {
    $db = $this->container->get('database');
    return array_map(static fn (string $t): int => (int) $db->query("SELECT count(*) FROM {{$t}}")->fetchField(), [
      'ancora_placement' => 'ancora_placement',
      'ancora_engine_job' => 'ancora_engine_job',
      'ancora_plan' => 'ancora_plan',
    ]);
  }

}
