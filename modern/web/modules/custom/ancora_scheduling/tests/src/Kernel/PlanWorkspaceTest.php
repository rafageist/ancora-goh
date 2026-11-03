<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_scheduling\Workspace\CanvasLayout;
use Drupal\ancora_scheduling\Workspace\PlanWorkspace;
use Drupal\ancora_scheduling\Workspace\ResumeStore;
use Drupal\ancora_scheduling\Workspace\WorkspaceVarySubscriber;
use Drupal\Core\Site\Settings;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use Drupal\Tests\ancora_scheduling\Traits\WorkspaceFixtureTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The planning workspace (#141, S1a): layout, state and "Resume".
 *
 * The workspace fixture (WorkspaceFixtureTrait) for the planner, and a copy
 * for a second owner whose ids must never be accepted.
 */
#[CoversClass(CanvasLayout::class)]
#[CoversClass(PlanWorkspace::class)]
#[CoversClass(ResumeStore::class)]
#[CoversClass(WorkspaceVarySubscriber::class)]
#[Group('ancora_scheduling')]
final class PlanWorkspaceTest extends AcademicKernelTestBase {

  use WorkspaceFixtureTrait;

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
   * The planner's records.
   */
  private array $r;

  /**
   * The second owner's records.
   */
  private array $o;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->set('ancora_engine.settings', new EngineSettings(new Settings(['ancora_engine' => ['mode' => 'workspace']])));
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    $this->installEntitySchema('ancora_placement');
    $this->installEntitySchema('ancora_placement_change');
    $this->r = $this->workspaceFixture((int) $this->owner->id());
    $other = $this->createUser(['view academic data', 'edit academic data'], 'other');
    $this->o = $this->workspaceFixture((int) $other->id(), 'O');
  }

  /**
   * One block per placement, across its slots and the breaks between them.
   */
  public function testCanvasLayout(): void {
    // 08:00, 1.5 h slots, a 0.5 h break after slot 1 (plan A's period).
    $start = static fn (int $slot): float => 8 + ($slot - 1) * 1.5 + ($slot > 1 ? 0.5 : 0);
    $layout = CanvasLayout::build(5, 6, 1.5, [1 => 0.5, 6 => 0.25], $start, [
      ['placement_id' => 1, 'day' => 1, 'slot' => 1, 'slots' => 2, 'activity' => 'Lab'],
      ['placement_id' => 2, 'day' => 3, 'slot' => 3, 'slots' => 3, 'activity' => 'Workshop'],
      ['placement_id' => 3, 'day' => 3, 'slot' => 4, 'slots' => 1, 'activity' => 'Overlap'],
      ['placement_id' => 4, 'day' => 5, 'slot' => 6, 'slots' => 3, 'activity' => 'Late'],
      ['placement_id' => 5, 'day' => 9, 'slot' => 1, 'slots' => 1, 'activity' => 'No such day'],
    ]);
    // Line 1 is the day header; a break row after slot 1, none after the
    // last slot of the day.
    $rows = array_map(static fn (array $r): string => $r['type'] . ':' . $r['line'], $layout['rows']);
    $this->assertSame(['slot:2', 'break:3', 'slot:4', 'slot:5', 'slot:6', 'slot:7', 'slot:8'], $rows);
    $this->assertSame(['09:30', '10:00'], [$layout['rows'][1]['start'], $layout['rows'][1]['end']]);
    $this->assertCount(5, $layout['days']);

    $lab = $layout['days'][0]['blocks'][0];
    $this->assertSame([2, 5, 1, 2], [$lab['row_start'], $lab['row_end'], $lab['first_slot'], $lab['last_slot']]);
    $this->assertSame(['08:00', '11:30', FALSE], [$lab['start'], $lab['end'], $lab['overflow']]);
    $this->assertSame([['start' => '09:30', 'end' => '10:00', 'row' => 2]], $lab['breaks']);

    // Two blocks that overlap get two lanes.
    $day3 = $layout['days'][2];
    $this->assertSame(2, $day3['lanes']);
    $lanes = array_map(
      static fn (array $b): string => "{$b['placement_id']},{$b['row_start']},{$b['row_end']},{$b['lane']}",
      $day3['blocks'],
    );
    $this->assertSame(['2,5,8,1', '3,6,7,2'], $lanes);
    $this->assertSame([], $day3['blocks'][0]['breaks']);
    $this->assertSame('11:30', $day3['blocks'][0]['start']);
    $this->assertSame('16:00', $day3['blocks'][0]['end']);

    // Past the last slot: cut at the end of the day and marked.
    $late = $layout['days'][4]['blocks'][0];
    $this->assertSame([6, 6, TRUE], [$late['first_slot'], $late['last_slot'], $late['overflow']]);
    // A placement outside the period's days is not drawn.
    $this->assertSame(4, array_sum(array_map(static fn (array $d): int => count($d['blocks']), $layout['days'])));
  }

  /**
   * The URL state: what the plan offers, and a 404 for anything else.
   */
  public function testState(): void {
    $workspace = $this->container->get(PlanWorkspace::class);
    $plan = $this->r['plan:A'];
    $state = $workspace->state($plan, $this->owner, new InputBag([]));
    $this->assertSame((int) $this->r['period:A']->id(), (int) $state['period']->id());
    $this->assertSame(['all', NULL, NULL], [$state['lens'], $state['obj'], $state['sel']]);
    $this->assertCount(4, $state['placements']);
    // Teachers: the owner's; groups: the plan's.
    $this->assertSame(['Teacher TA', 'Teacher TB', 'Teacher TC'], array_values($state['options']['teacher']));
    $this->assertSame(['Group GA1', 'Group GA2', 'Group GA3', 'Group GA4'], array_values($state['options']['group']));

    $state = $workspace->state($plan, $this->owner, new InputBag([
      'period' => (string) $this->r['period:A']->id(),
      'lens' => 'teacher',
      'obj' => (string) $this->r['TA']->id(),
      'sel' => (string) $this->r['A2']->id(),
    ]));
    $this->assertSame('teacher', $state['lens']);
    $this->assertSame([(int) $this->r['TA']->id(), (int) $this->r['A2']->id()], [$state['obj'], $state['sel']]);
    $this->assertSame(
      '/ancora/planning/' . $plan->id() . '/workspace?period=' . $this->r['period:A']->id() . '&lens=room&obj=' . $this->r['L1']->id() . '&sel=' . $this->r['A2']->id(),
      $workspace->url($state, ['obj' => (int) $this->r['L1']->id(), 'lens' => 'room'])->toString(),
    );
    // A move (S1b) belongs to its selection and period.
    $moving = ['check' => 7, 'undo' => 3] + $state;
    $this->assertStringEndsWith('&sel=' . $this->r['A2']->id() . '&check=7&undo=3', $workspace->url($moving, ['lens' => 'room'])->toString());
    $this->assertStringNotContainsString('check=', $workspace->url($moving, ['sel' => (int) $this->r['A1']->id()])->toString());
    $this->assertStringNotContainsString('check=', $workspace->url($moving, ['period' => (int) $this->r['period:A2']->id()])->toString());
    $query = new InputBag(['sel' => (string) $this->r['A2']->id(), 'check' => '7']);
    $state = $workspace->state($plan, $this->owner, $query);
    $this->assertSame([7, NULL, TRUE], [$state['check'], $state['undo'], $state['movable']]);
    // "All": no lens and no record in the URL.
    $this->assertStringNotContainsString('lens', $workspace->url($state, ['lens' => 'all'])->toString());

    foreach ([
      'a period of another plan' => ['period' => (string) $this->r['period:B']->id()],
      "another owner's period" => ['period' => (string) $this->o['period:A']->id()],
      'not a number' => ['period' => '1 OR 1=1'],
      'an unknown perspective' => ['lens' => 'resource'],
      "another owner's teacher" => ['lens' => 'teacher', 'obj' => (string) $this->o['TA']->id()],
      'a room as a teacher' => ['lens' => 'teacher', 'obj' => '999999'],
      'a record without a perspective' => ['obj' => (string) $this->r['TA']->id()],
      'a placement of another period' => ['sel' => (string) $this->r['B1']->id()],
      "another owner's placement" => ['sel' => (string) $this->o['A1']->id()],
      'an array' => ['sel' => ['1']],
      'a check without a selection' => ['check' => '7'],
      'a check that is not an id' => ['sel' => (string) $this->r['A2']->id(), 'check' => '7a'],
      'an undo that is not an id' => ['sel' => (string) $this->r['A2']->id(), 'undo' => '-1'],
    ] as $case => $query) {
      try {
        $workspace->state($plan, $this->owner, new InputBag($query));
        $this->fail("No 404 for $case");
      }
      catch (NotFoundHttpException) {
        $this->addToAssertionCount(1);
      }
    }
  }

  /**
   * The regions: one block per placement, cross-plan state per block.
   */
  public function testRegions(): void {
    $workspace = $this->container->get(PlanWorkspace::class);
    $state = $workspace->state($this->r['plan:A'], $this->owner, new InputBag([
      'lens' => 'teacher',
      'obj' => (string) $this->r['TA']->id(),
      'sel' => (string) $this->r['A1']->id(),
    ]));
    $regions = $workspace->regions($state);
    $blocks = [];
    foreach ($regions['canvas']['#days'] as $day) {
      foreach ($day['blocks'] as $block) {
        $blocks[$block['placement_id']] = $block;
      }
    }
    $a1 = (int) $this->r['A1']->id();
    $a2 = (int) $this->r['A2']->id();
    $this->assertEqualsCanonicalizing([$a1, $a2], array_keys($blocks));
    $this->assertSame(1, $blocks[$a1]['warnings']);
    $this->assertFalse($blocks[$a1]['not_evaluated']);
    $this->assertTrue($blocks[$a1]['selected']);
    $this->assertSame(0, $blocks[$a2]['warnings']);
    $this->assertSame([['start' => '09:30', 'end' => '10:00', 'row' => 2]], $blocks[$a2]['breaks']);
    $this->assertSame('Teacher TA: 2 activities', (string) $regions['canvas']['#heading']);
    $this->assertCount(1, $regions['inspector']['#conflicts']);
    $this->assertStringContainsString('in plan Intensive B, period Week B, day 2, slot 2, on 2026-09-15 10:00–10:30', (string) $regions['inspector']['#conflicts'][0]);
    $this->assertNull($regions['inspector']['#cross_text']);

    // The undated plan: every block "not evaluated", never "no overlap".
    $state = $workspace->state($this->r['plan:U'], $this->owner, new InputBag(['sel' => (string) $this->r['U1']->id()]));
    $regions = $workspace->regions($state);
    foreach ($regions['canvas']['#days'] as $day) {
      foreach ($day['blocks'] as $block) {
        $this->assertTrue($block['not_evaluated']);
        $this->assertSame(0, $block['warnings']);
      }
    }
    $this->assertFalse($regions['bar']['#cross']['evaluated']);
    $this->assertStringContainsString('not evaluated (the plan has no first day)', (string) $regions['inspector']['#cross_text']);
  }

  /**
   * Vary: X-Ancora-Regions is appended, an existing Vary is kept.
   */
  public function testVary(): void {
    $response = new Response('', 200, ['Vary' => 'Cookie, Accept-Encoding']);
    WorkspaceVarySubscriber::addVary($response);
    WorkspaceVarySubscriber::addVary($response);
    $this->assertSame(['Cookie', 'Accept-Encoding', 'X-Ancora-Regions'], $response->getVary());
    $plain = new Response();
    WorkspaceVarySubscriber::addVary($plain);
    $this->assertSame(['X-Ancora-Regions'], $plain->getVary());
  }

  /**
   * The "Resume" record: user.data, revalidated on every read.
   */
  public function testResume(): void {
    $store = $this->container->get(ResumeStore::class);
    $user_data = $this->container->get('user.data');
    $this->assertNull($store->resume($this->owner));

    $store->record($this->owner, $this->r['plan:A'], $this->r['period:A']);
    $this->assertSame(['plan' => (int) $this->r['plan:A']->id(), 'period' => (int) $this->r['period:A']->id()], $user_data->get('ancora_scheduling', (int) $this->owner->id(), ResumeStore::NAME));
    $resume = $store->resume($this->owner);
    $this->assertSame((int) $this->r['plan:A']->id(), (int) $resume['plan']->id());
    $this->assertSame((int) $this->r['period:A']->id(), (int) $resume['period']->id());

    // A period that no longer belongs to the plan is ignored.
    $uid = (int) $this->owner->id();
    $user_data->set('ancora_scheduling', $uid, ResumeStore::NAME, [
      'plan' => (int) $this->r['plan:A']->id(),
      'period' => (int) $this->r['period:B']->id(),
    ]);
    $this->assertNull($store->resume($this->owner)['period']);

    // A plan the account may not view: not offered, and the record dropped.
    $foreign = ['plan' => (int) $this->o['plan:A']->id(), 'period' => NULL];
    $user_data->set('ancora_scheduling', $uid, ResumeStore::NAME, $foreign);
    $this->assertNull($store->resume($this->owner));
    $this->assertNull($user_data->get('ancora_scheduling', (int) $this->owner->id(), ResumeStore::NAME));

    // A deleted plan: the same.
    $store->record($this->owner, $this->r['plan:U'], NULL);
    $this->r['plan:U']->delete();
    $this->assertNull($store->resume($this->owner));

    // Each account has its own record.
    $store->record($this->owner, $this->r['plan:A'], NULL);
    $this->assertNull($store->resume($this->createUser(['view academic data'])));
  }

}
