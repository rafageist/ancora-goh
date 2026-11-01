<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\WorkspaceSync;
use Drupal\ancora_scheduling\Timetable\PlacementModification;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Cross-plan conflict warnings over HTTP (#133): read-only, owner scope.
 *
 * Plan A from Monday 2026-09-07 (weekly on plan days 1 and 8, 08:00, 1.5 h
 * slots, a 0.5 h break after slot 1); plan B the week of 2026-09-14 (08:30,
 * 1 h slots); plan U without dates. A1 and B1 share teacher TA on Tuesday
 * 2026-09-15, 10:00-10:30.
 */
#[Group('ancora_scheduling')]
final class CrossPlanConflictUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_scheduling', 'block'];

  /**
   * The planner's permissions.
   */
  private const PLANNER = [
    'view academic data',
    'edit academic data',
    'view ancora engine jobs',
    'request ancora engine jobs',
    'view timetable viewer',
  ];

  /**
   * Records by key.
   */
  private array $r = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $settings['settings']['ancora_engine'] = (object) ['value' => ['mode' => 'workspace'], 'required' => TRUE];
    $this->writeSettings($settings);
  }

  /**
   * Creates and saves a record.
   */
  private function make(string $type, array $values): mixed {
    $record = \Drupal::entityTypeManager()->getStorage($type)->create($values);
    $record->save();
    return $record;
  }

  /**
   * The owner's catalogue, plans, placements and runs.
   */
  private function fixture(int $uid): void {
    $own = ['uid' => $uid];
    foreach (['TA', 'TB'] as $code) {
      $this->r[$code] = $this->make('ancora_teacher', $own + ['label' => "Teacher $code", 'code' => $code]);
    }
    foreach (['L1', 'L2'] as $code) {
      $this->r[$code] = $this->make('ancora_room', $own + ['label' => "Room $code", 'code' => $code, 'capacity' => 0]);
    }
    $this->r['C1'] = $this->make('ancora_activity_type', $own + [
      'label' => 'Lecture',
      'code' => 'C1',
      'duration_slots' => 1,
      'priority' => 1,
      'cost' => 1,
    ]);
    $this->r['E'] = $this->make('ancora_specialty', $own + ['label' => 'Program', 'code' => 'E']);
    $this->r['N'] = $this->make('ancora_level', $own + ['label' => 'Year 1', 'code' => 'N']);
    $this->r['S'] = $this->make('ancora_subject', $own + [
      'label' => 'Subject',
      'code' => 'S',
      'specialty' => $this->r['E']->id(),
      'level' => $this->r['N']->id(),
    ]);
    $this->r['B'] = $this->make('ancora_cohort', $own + [
      'label' => 'Cohort',
      'code' => 'B',
      'specialty' => $this->r['E']->id(),
      'level' => $this->r['N']->id(),
      'enrollment' => 20,
    ]);
    $plans = [
      'A' => ['2026-09-07', '2026-09-20', 8, 1.5, [1, 8], [1 => 0.5]],
      'B' => ['2026-09-14', '2026-09-18', 8.5, 1, [1], []],
      'U' => [NULL, NULL, 8, 1, [1], []],
    ];
    foreach ($plans as $code => [$starts, $ends, $hour, $length, $days, $breaks]) {
      $plan = $this->make('ancora_plan', $own + [
        'label' => "Plan $code",
        'code' => $code,
        'starts_on' => $starts,
        'ends_on' => $ends,
      ]);
      $period = $this->make('ancora_period', [
        'plan' => $plan->id(),
        'label' => "Week $code",
        'code' => "P$code",
        'days' => 5,
        'slots_per_day' => 6,
        'start_hour' => $hour,
        'slot_duration' => $length,
        'plan_days' => $days,
      ]);
      foreach ($breaks as $after => $len) {
        $this->make('ancora_break', [
          'plan' => $plan->id(),
          'period' => $period->id(),
          'after_slot' => $after,
          'length' => $len,
        ]);
      }
      $this->r["plan:$code"] = $plan;
      $this->r["period:$code"] = $period;
    }
    foreach ([
      'A1' => ['A', 'TA', 'L1', 2, 2],
      'B1' => ['B', 'TA', 'L2', 2, 2],
      'U1' => ['U', 'TA', 'L1', 2, 2],
    ] as $code => [$plan, $teacher, $room, $day, $slot]) {
      $template = $this->make('ancora_activity_template', [
        'plan' => $this->r["plan:$plan"]->id(),
        'subject' => $this->r['S']->id(),
        'period' => $this->r["period:$plan"]->id(),
        'activity_type' => $this->r['C1']->id(),
      ]);
      $group = $this->make('ancora_student_group', [
        'plan' => $this->r["plan:$plan"]->id(),
        'label' => "Group $code",
        'code' => "G$code",
        'specialty' => $this->r['E']->id(),
        'level' => $this->r['N']->id(),
        'activity_type' => $this->r['C1']->id(),
        'cohorts' => [$this->r['B']->id()],
      ]);
      $this->r[$code] = $this->make('ancora_placement', [
        'plan' => $this->r["plan:$plan"]->id(),
        'period' => $this->r["period:$plan"]->id(),
        'template' => $template->id(),
        'student_group' => $group->id(),
        'teacher' => $this->r[$teacher]->id(),
        'room' => $this->r[$room]->id(),
        'day' => $day,
        'slot' => $slot,
        'engine_id' => "e-$code",
        'job' => 1,
      ]);
    }
    $jobs = \Drupal::service('ancora_engine.job_repository');
    foreach (['A', 'B', 'U'] as $code) {
      $records = [$this->r["plan:$code"], $this->r["period:$code"], $this->r['E'], $this->r['N']];
      $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, $uid, NULL, ScopeMapper::source(...$records));
      $jobs->finish($jobs->claim('test'), JobState::Succeeded, ['kept' => 'placements']);
    }
  }

  /**
   * The timetables, the explorer, the viewer, access and invalidation.
   */
  public function testSections(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $this->fixture((int) $planner->id());
    $this->drupalLogin($planner);
    $a = $this->r['plan:A']->id();

    $this->drupalGet("/ancora/planning/$a/timetable");
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'Cross-plan conflicts');
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'Teacher Teacher TA: Subject');
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'in plan Plan B, period Week B, day 2, slot 2, on 2026-09-15 10:00–10:30.');
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'Not evaluated (dates unknown): Plan U, Week U (the plan has no first day).');
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'nothing is blocked');

    // No first day: "not evaluated", never "no conflicts".
    $this->drupalGet('/ancora/planning/' . $this->r['plan:U']->id() . '/timetable');
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'Dates unknown / cross-plan conflicts not evaluated.');
    $this->assertSession()->elementTextNotContains('css', '#ancora-cross-plan', 'No overlap found');

    // The explorer shows it; the viewer's read-only scope does not grow.
    $query = ['period' => $this->r['period:A']->id(), 'teacher' => $this->r['TA']->id()];
    $this->drupalGet('/ancora/timetables/explorer', ['query' => $query]);
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'on 2026-09-15 10:00–10:30');
    $this->drupalGet('/ancora/timetables/viewer', ['query' => $query]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementNotExists('css', '#ancora-cross-plan');

    // Invalidation: plan B moves to November; the warning goes at once.
    $this->r['plan:B']->set('starts_on', '2026-11-02')->set('ends_on', '2026-11-06')->save();
    $this->drupalGet("/ancora/planning/$a/timetable");
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'No overlap found with the dated periods of your plans.');
    // A break added to B's period changes its slot times: still no overlap
    // in November, and the page is rebuilt (no stale text).
    $this->r['plan:B']->set('starts_on', '2026-09-14')->set('ends_on', '2026-09-18')->save();
    $this->drupalGet("/ancora/planning/$a/timetable");
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'on 2026-09-15 10:00–10:30');
    \Drupal::entityTypeManager()->getStorage('ancora_break')->create([
      'plan' => $this->r['plan:B']->id(),
      'period' => $this->r['period:B']->id(),
      'after_slot' => 1,
      'length' => 1,
    ])->save();
    $this->drupalGet("/ancora/planning/$a/timetable");
    $this->assertSession()->elementTextContains('css', '#ancora-cross-plan', 'on 2026-09-15 10:30–11:30', 'B1 now runs 10:30-11:30');

    // Another planner sees nothing of it.
    $this->drupalLogin($this->drupalCreateUser(self::PLANNER));
    $this->drupalGet("/ancora/planning/$a/timetable");
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->pageTextNotContains('Teacher TA');
  }

  /**
   * The move grid and preview warn; the warned cell can still be chosen.
   */
  public function testMoveWarnings(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $this->fixture((int) $planner->id());
    $this->drupalLogin($planner);
    // Move B1 from Tuesday slot 1 elsewhere first, so that its slot 2 is a
    // prospective destination (it is where it is now).
    $b1 = $this->r['B1'];
    $b1->set('slot', 1)->save();
    $this->drupalGet("/ancora/timetables/placements/{$b1->id()}/move");
    $this->getSession()->getPage()->pressButton('Find the valid destinations');
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_placement');
    $storage->resetCache();
    $b1 = $storage->load($b1->id());
    $cells = [];
    for ($day = 1; $day <= 5; $day++) {
      for ($slot = 1; $slot <= 6; $slot++) {
        $cells[] = ['day' => $day, 'slot' => $slot, 'ok' => TRUE, 'reasons' => [], 'warnings' => []];
      }
    }
    \Drupal::database()->update('ancora_placement_check')->fields([
      'state' => 'done',
      'version' => \Drupal::service(WorkspaceSync::class)->currentVersion(),
      'signature' => PlacementModification::signature($b1),
      'result' => json_encode(['from' => ['day' => 2, 'slot' => 1], 'cells' => $cells]),
    ])->execute();
    $this->clickLink('Refresh');
    $this->assertSession()->pageTextContains('Cross-plan conflicts are evaluated');
    $this->assertSession()->elementTextContains('css', '#ancora-move-cell-2-2-why', 'Cross-plan warning: teacher Teacher TA busy in plan Plan A, period Week A on 2026-09-15 10:00–10:30.');
    $this->assertSession()->elementTextContains('css', '#ancora-move-cell-2-2-why', 'Valid.');
    $this->assertSession()->elementNotExists('css', 'input[name="op_preview_2_2"][disabled]');
    $this->assertSession()->elementTextNotContains('css', '#ancora-move-cell-2-4-why', 'Cross-plan');
    $this->getSession()->getPage()->pressButton('op_preview_2_2');
    $this->assertSession()->pageTextContains('Cross-plan warnings (the move is still allowed)');
    $this->assertSession()->pageTextContains('in plan Plan A, period Week A, day 2, slot 2, on 2026-09-15 10:00–10:30.');
    $this->getSession()->getPage()->pressButton('Move here');
    $this->assertSession()->elementTextContains('css', '#ancora-move-feedback[role="status"]', 'Moved it to day 2, slot 2.');

    // A placement of a plan without dates: not evaluated.
    $u1 = $this->r['U1'];
    $this->drupalGet("/ancora/timetables/placements/{$u1->id()}/move");
    $this->getSession()->getPage()->pressButton('Find the valid destinations');
    $storage->resetCache();
    \Drupal::database()->update('ancora_placement_check')->fields([
      'state' => 'done',
      'version' => \Drupal::service(WorkspaceSync::class)->currentVersion(),
      'signature' => PlacementModification::signature($storage->load($u1->id())),
      'result' => json_encode(['from' => ['day' => 2, 'slot' => 2], 'cells' => $cells]),
    ])->condition('placement_id', $u1->id())->execute();
    $this->clickLink('Refresh');
    $this->assertSession()->pageTextContains('Dates unknown / cross-plan conflicts not evaluated (the plan has no first day).');
  }

}
