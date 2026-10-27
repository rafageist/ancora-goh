<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Timetable\TimetableExplorer;
use Drupal\Core\Site\Settings;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * The legacy timetable explorer (#119, D14, PAR-091; SQL case 147, s14).
 *
 * Two users and two plans of the first one; workspace mode (placement
 * entities). The two-database read is covered by s14 and r1-journey.
 */
#[CoversClass(TimetableExplorer::class)]
#[Group('ancora_scheduling')]
final class TimetableExplorerTest extends AcademicKernelTestBase {

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
   * The second planner.
   */
  private UserInterface $bob;

  /**
   * The period of the first plan.
   */
  private Period $per;

  /**
   * The period of the second plan.
   */
  private Period $per2;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->set('ancora_engine.settings', new EngineSettings(new Settings(['ancora_engine' => ['mode' => 'workspace']])));
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    $this->installEntitySchema('ancora_placement');
    $plan = $this->loadFixture();
    $etm = $this->container->get('entity_type.manager');
    $this->per = $this->byCode('ancora_period', 'PER', $plan);
    $b1 = $this->byCode('ancora_cohort', 'B1');
    // A lab type and group: B1 is in G1 (lecture) and GL (lab).
    $lab = $this->newRecord('ancora_activity_type', [
      'label' => 'Lab',
      'code' => 'C2',
      'duration_slots' => 1,
      'cost' => 1,
    ]);
    $lab->save();
    $of = [
      'specialty' => $this->byCode('ancora_specialty', 'E')->id(),
      'level' => $this->byCode('ancora_level', 'N')->id(),
    ];
    $gl = $this->newRecord('ancora_student_group', [
      'plan' => $plan->id(),
      'label' => 'Lab group',
      'code' => 'GL',
      'activity_type' => $lab->id(),
      'cohorts' => [$b1->id()],
    ] + $of);
    $gl->save();
    $subject = $this->byCode('ancora_subject', 'A');
    $al = $this->newRecord('ancora_activity_template', [
      'plan' => $plan->id(),
      'subject' => $subject->id(),
      'period' => $this->per->id(),
      'activity_type' => $lab->id(),
    ]);
    $al->save();
    // A second plan of the same owner: B1 is in its seminar group G3.
    $plan2 = $this->newRecord('ancora_plan', ['label' => 'Plan 2', 'code' => 'P2', 'duration_days' => 30]);
    $plan2->save();
    $this->per2 = $this->newRecord('ancora_period', [
      'plan' => $plan2->id(),
      'label' => 'Week 2',
      'code' => 'PER2',
      'days' => 2,
      'slots_per_day' => 3,
      'start_hour' => 8,
    ]);
    $this->per2->save();
    $seminar = $this->newRecord('ancora_activity_type', [
      'label' => 'Seminar',
      'code' => 'C3',
      'duration_slots' => 1,
      'cost' => 1,
    ]);
    $seminar->save();
    $g3 = $this->newRecord('ancora_student_group', [
      'plan' => $plan2->id(),
      'label' => 'Group 3',
      'code' => 'G3',
      'activity_type' => $seminar->id(),
      'cohorts' => [$b1->id()],
    ] + $of);
    $g3->save();
    $t9 = $this->newRecord('ancora_activity_template', [
      'plan' => $plan2->id(),
      'subject' => $subject->id(),
      'period' => $this->per2->id(),
      'activity_type' => $seminar->id(),
    ]);
    $t9->save();
    $resource = $this->newRecord('ancora_other_resource', ['label' => 'Projector', 'code' => 'R1']);
    $resource->save();

    // Placements as the publication makes them; P1's run succeeded, P2's
    // was partial (a draft).
    $templates = $etm->getStorage('ancora_activity_template')->loadByProperties(['plan' => $plan->id()]);
    ksort($templates);
    $a1 = reset($templates);
    $id = fn (string $type, string $code, ?Plan $in = NULL) => (int) $this->byCode($type, $code, $in)->id();
    $rows = [
      [$this->per, $a1, $id('ancora_student_group', 'G1', $plan), 'TA', 'L1', 1, 1, []],
      [$this->per, $a1, $id('ancora_student_group', 'G2', $plan), 'TA', 'L1', 1, 3, []],
      [$this->per, $al, (int) $gl->id(), 'TB', 'L2', 2, 1, [(int) $resource->id()]],
      [$this->per2, $t9, (int) $g3->id(), 'TA', 'L1', 1, 2, []],
    ];
    foreach ($rows as $i => [$period, $template, $group, $teacher, $room, $day, $slot, $resources]) {
      $etm->getStorage('ancora_placement')->create([
        'plan' => $period->getPlanId(),
        'period' => $period->id(),
        'template' => $template->id(),
        'student_group' => $group,
        'teacher' => $id('ancora_teacher', $teacher),
        'room' => $id('ancora_room', $room),
        'day' => $day,
        'slot' => $slot,
        'other_resources' => $resources,
        'engine_id' => "e-$i",
        'job' => 1,
      ])->save();
    }
    $jobs = $this->container->get(JobRepository::class);
    $specialty = $this->byCode('ancora_specialty', 'E');
    $level = $this->byCode('ancora_level', 'N');
    foreach ([
      [$plan, $this->per, JobState::Succeeded],
      [$plan2, $this->per2, JobState::Partial],
    ] as [$p, $period, $state]) {
      $jobs->enqueue(ScopeMapper::scope($p, $period, $specialty, $level), new JobOptions(), 60, (int) $this->owner->id(), NULL, ScopeMapper::source($p, $period, $specialty, $level));
      $jobs->finish($jobs->claim('test'), $state, ['kept' => 'placements']);
    }

    // The second planner, with a teacher and a resource of their own.
    $this->bob = $this->createUser(['view academic data', 'edit academic data'], 'bob');
    $this->setCurrentUser($this->bob);
    foreach ([
      ['ancora_teacher', ['label' => 'Teacher Z', 'code' => 'TZ']],
      ['ancora_other_resource', ['label' => 'Bob projector', 'code' => 'RZ']],
    ] as [$type, $values]) {
      $this->newRecord($type, $values)->save();
    }
    $this->setCurrentUser($this->owner);
  }

  /**
   * Cells of a result: "day.slot group".
   */
  private function cells(array $result): array {
    $out = array_map(static fn (array $p): string => $p['day'] . '.' . $p['slot'] . ' ' . $p['group_code'] . ($p['draft'] ? ' draft' : ''), $result['placements']);
    sort($out);
    return $out;
  }

  /**
   * By brigade (two groups, two plans), teacher, room, resource; precedence.
   */
  public function testExplore(): void {
    $explorer = $this->container->get(TimetableExplorer::class);
    $b1 = $this->byCode('ancora_cohort', 'B1')->id();
    $result = $explorer->explore($this->owner, $this->per->id(), ['brigade' => $b1]);
    $this->assertSame('ok', $result['status']);
    $this->assertSame(['1.1 G1', '2.1 GL'], $this->cells($result), 'every group of the brigade, this period only');
    $this->assertSame(['1.2 G3 draft'], $this->cells($explorer->explore($this->owner, $this->per2->id(), ['brigade' => $b1])), 'the other plan: its group, a draft');
    $ta = $this->byCode('ancora_teacher', 'TA')->id();
    $this->assertSame(['1.1 G1', '1.3 G2'], $this->cells($explorer->explore($this->owner, $this->per->id(), ['teacher' => $ta])));
    $this->assertSame(['2.1 GL'], $this->cells($explorer->explore($this->owner, $this->per->id(), ['room' => $this->byCode('ancora_room', 'L2')->id()])));
    $this->assertSame(['2.1 GL'], $this->cells($explorer->explore($this->owner, $this->per->id(), ['resource' => $this->byCode('ancora_other_resource', 'R1')->id()])));
    $last = $explorer->explore($this->owner, $this->per->id(), [
      'brigade' => $b1,
      'teacher' => $this->byCode('ancora_teacher', 'TB')->id(),
    ]);
    $this->assertSame(['teacher', ['2.1 GL']], [$last['selector'], $this->cells($last)], 'the last posted selector of the legacy order wins (Q-38)');
    $cell = $result['placements'][0];
    $this->assertSame(['Lecture', 'Subject Alpha', 'Teacher Alpha', 'Room 1'], [
      $cell['activity_type'],
      $cell['subject'],
      $cell['teacher'],
      $cell['room'],
    ]);
  }

  /**
   * The lists and the refusals: non-owners, forged and wrong selectors.
   */
  public function testAccessAndSelectors(): void {
    $explorer = $this->container->get(TimetableExplorer::class);
    $tz = $this->byCode('ancora_teacher', 'TZ', NULL, $this->bob)->id();
    $rz = $this->byCode('ancora_other_resource', 'RZ', NULL, $this->bob)->id();
    $options = $explorer->explore($this->owner, $this->per->id(), [])['options'];
    $this->assertArrayHasKey((int) $tz, $options['teacher'], 'every user\'s teachers are listed (Q-23)');
    $this->assertArrayNotHasKey((int) $rz, $options['resource'], 'other resources are the owner\'s');
    $this->assertSame(['ok', []], (static fn ($r) => [$r['status'], $r['placements']])($explorer->explore($this->owner, $this->per->id(), ['teacher' => $tz])), 'another user\'s teacher: nothing in this period');

    // The second planner: none of the owner's periods.
    $this->assertSame([], $explorer->periods($this->bob));
    $this->assertSame('period_refused', $explorer->explore($this->bob, $this->per->id(), ['teacher' => $tz])['status']);
    // Wrong and forged selectors.
    $this->assertSame('no_period', $explorer->explore($this->owner, NULL, [])['status']);
    $this->assertSame('period_refused', $explorer->explore($this->owner, 'abc', [])['status']);
    $this->assertSame('period_refused', $explorer->explore($this->owner, 999999, [])['status']);
    $this->assertSame('no_selector', $explorer->explore($this->owner, $this->per->id(), ['teacher' => ''])['status']);
    $this->assertSame('selector_refused', $explorer->explore($this->owner, $this->per->id(), ['teacher' => '999999'])['status']);
    $this->assertSame('selector_refused', $explorer->explore($this->owner, $this->per->id(), ['teacher' => '1 OR 1=1'])['status']);
    $this->assertSame('selector_refused', $explorer->explore($this->owner, $this->per->id(), ['resource' => $rz])['status'], 'another user\'s resource');
    $this->assertSame('selector_refused', $explorer->explore($this->owner, $this->per->id(), ['brigade' => $this->byCode('ancora_teacher', 'TA')->id() + 1000])['status']);
    // An administrator explores every plan.
    $admin = $this->createUser(['administer academic data']);
    $this->assertCount(2, $explorer->periods($admin));
    $this->assertSame('ok', $explorer->explore($admin, $this->per->id(), ['teacher' => $this->byCode('ancora_teacher', 'TA')->id()])['status']);
  }

}
