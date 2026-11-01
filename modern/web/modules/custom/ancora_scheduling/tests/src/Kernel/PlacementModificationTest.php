<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\Entity\PlacementChange;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\WorkspaceSync;
use Drupal\ancora_scheduling\Timetable\PlacementCheckRunner;
use Drupal\ancora_scheduling\Timetable\PlacementModification;
use Drupal\ancora_scheduling\Timetable\PlacementModificationException;
use Drupal\ancora_scheduling\Timetable\TimetableReader;
use Drupal\Core\Site\Settings;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Moving one saved placement (#130): the command, its guards and its audit.
 *
 * The checks themselves run in a workspace (PlacementCheckRunner) and are
 * exercised against the real engine by scripts/workspace-journey.sh; here a
 * done check is written as the runner writes it, so that every guard of
 * the commit can be tested without an engine.
 */
#[CoversClass(PlacementModification::class)]
#[CoversClass(PlacementCheckRunner::class)]
#[Group('ancora_scheduling')]
final class PlacementModificationTest extends AcademicKernelTestBase {

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
   * The placements: G1 on day 1 slot 1, G2 on day 2 slot 3.
   *
   * @var array<string, \Drupal\ancora_scheduling\Entity\Placement>
   */
  private array $placements = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->workspaceMode(TRUE);
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    $this->installSchema('ancora_scheduling', ['ancora_placement_check']);
    $this->installEntitySchema('ancora_placement');
    $this->installEntitySchema('ancora_placement_change');
    $plan = $this->loadFixture();
    $etm = $this->container->get('entity_type.manager');
    $templates = $etm->getStorage('ancora_activity_template')->loadByProperties(['plan' => $plan->id()]);
    ksort($templates);
    $template = reset($templates);
    $resource = $this->newRecord('ancora_other_resource', ['label' => 'Projector', 'code' => 'R1']);
    $resource->save();
    foreach ([
      'G1' => ['TA', 'L1', 1, 1, [$resource->id()]],
      'G2' => ['TA', 'L1', 2, 3, []],
    ] as $group => [$teacher, $room, $day, $slot, $resources]) {
      $placement = $etm->getStorage('ancora_placement')->create([
        'plan' => $plan->id(),
        'period' => $this->byCode('ancora_period', 'PER', $plan)->id(),
        'template' => $template->id(),
        'student_group' => $this->byCode('ancora_student_group', $group, $plan)->id(),
        'teacher' => $this->byCode('ancora_teacher', $teacher)->id(),
        'room' => $this->byCode('ancora_room', $room)->id(),
        'day' => $day,
        'slot' => $slot,
        'other_resources' => $resources,
        'engine_id' => "e-$group",
        'job' => 7,
      ]);
      $placement->save();
      $this->placements[$group] = $placement;
    }
  }

  /**
   * Sets the engine mode of the site.
   */
  private function workspaceMode(bool $on): void {
    $this->container->set('ancora_engine.settings', new EngineSettings(new Settings(['ancora_engine' => ['mode' => $on ? 'workspace' : 'two_database']])));
    $this->container->set('ancora_scheduling.placement_modification', NULL);
  }

  /**
   * The command service (rebuilt after a mode change).
   */
  private function modification(): PlacementModification {
    return $this->container->get(PlacementModification::class);
  }

  /**
   * A done check of a placement, as PlacementCheckRunner writes it.
   *
   * @param \Drupal\ancora_scheduling\Entity\Placement $placement
   *   The placement.
   * @param array<string, list<string>> $refused
   *   Reasons of the refused cells by "day.slot"; the others are valid.
   * @param array $overrides
   *   Column values to override (version, signature, changed, state).
   */
  private function doneCheck(Placement $placement, array $refused = [], array $overrides = []): int {
    $cells = [];
    for ($day = 1; $day <= 5; $day++) {
      for ($slot = 1; $slot <= 4; $slot++) {
        $reasons = $refused["$day.$slot"] ?? [];
        $cells[] = ['day' => $day, 'slot' => $slot, 'ok' => $reasons === [], 'reasons' => $reasons, 'warnings' => []];
      }
    }
    $now = $this->container->get('datetime.time')->getCurrentTime();
    return (int) $this->container->get('database')->insert('ancora_placement_check')->fields($overrides + [
      'placement_id' => $placement->id(),
      'requested_by' => $this->owner->id(),
      'state' => 'done',
      'version' => $this->container->get(WorkspaceSync::class)->currentVersion(),
      'signature' => PlacementModification::signature($placement),
      'result' => json_encode([
        'from' => ['day' => (int) $placement->get('day')->value, 'slot' => (int) $placement->get('slot')->value],
        'cells' => $cells,
      ]),
      'created' => $now,
      'changed' => $now,
    ])->execute();
  }

  /**
   * A fresh placement.
   */
  private function reload(string $group): Placement {
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_placement');
    $storage->resetCache();
    return $storage->load($this->placements[$group]->id());
  }

  /**
   * The refusal reason of a commit, after checking nothing was written.
   */
  private function refused(callable $commit, string $group = 'G1'): string {
    $before = [PlacementModification::signature($this->reload($group)), $this->recordCount('ancora_placement_change')];
    try {
      $commit();
    }
    catch (PlacementModificationException $e) {
      $this->assertSame($before, [
        PlacementModification::signature($this->reload($group)),
        $this->recordCount('ancora_placement_change'),
      ], 'nothing written: ' . $e->reason);
      return $e->reason;
    }
    $this->fail('The commit was not refused.');
  }

  /**
   * A move keeps everything but the day and slot, and is audited.
   */
  public function testMoveIsTimeOnlyAndAudited(): void {
    $g1 = $this->placements['G1'];
    $keep = static fn (Placement $p): array => [
      $p->get('template')->target_id, $p->get('student_group')->target_id, $p->get('teacher')->target_id,
      $p->get('room')->target_id, $p->get('other_resources')->getValue(), $p->getEngineId(), (int) $p->get('job')->value,
    ];
    $kept = $keep($g1);
    $check = $this->doneCheck($g1, ['2.3' => ['teacher']]);
    $change = $this->modification()->commit($g1, $check, 2, 1, $this->owner);

    $moved = $this->reload('G1');
    $this->assertSame([2, 1], [(int) $moved->get('day')->value, (int) $moved->get('slot')->value]);
    $this->assertSame($kept, $keep($moved), 'group, teacher, room, resources, engine id and job are kept');
    $this->assertInstanceOf(PlacementChange::class, $change);
    $values = array_map(static fn (string $f) => $change->get($f)->value ?? $change->get($f)->target_id, [
      'from_day',
      'from_slot',
      'to_day',
      'to_slot',
      'reason',
      'uid',
      'placement',
      'plan',
    ]);
    $this->assertEquals([1, 1, 2, 1, 'move', $this->owner->id(), $g1->id(), $g1->get('plan')->target_id], $values);
    $this->assertSame((int) $check, (int) $change->get('check_id')->value);
    $this->assertSame([(int) $change->id()], array_map(static fn ($c) => (int) $c->id(), $this->modification()->history($moved)));
    $this->assertSame([(int) $g1->id() => TRUE], $this->modification()->moved([
      (int) $g1->id(),
      (int) $this->placements['G2']->id(),
    ]));

    // The audit has the plan's access rules.
    $this->assertTrue($change->access('view', $this->owner));
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->assertFalse($change->access('view', $other));
    $this->assertFalse($moved->access('update', $other));

    // The read model marks it (the explorer shows "Moved by hand").
    $plan = $g1->get('plan')->entity;
    $records = [
      $plan,
      $this->byCode('ancora_period', 'PER', $plan),
      $this->byCode('ancora_specialty', 'E'),
      $this->byCode('ancora_level', 'N'),
    ];
    $jobs = $this->container->get(JobRepository::class);
    $id = $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $this->owner->id(), NULL, ScopeMapper::source(...$records));
    $jobs->finish($jobs->claim('test'), JobState::Succeeded, ['kept' => 'placements']);
    $read = $this->container->get(TimetableReader::class)->read($plan, $records[1]);
    $flags = [];
    foreach ($read['scopes'][0]['placements'] as $p) {
      $flags[$p['placement_id']] = $p['moved'];
    }
    $this->assertSame([(int) $g1->id() => TRUE, (int) $this->placements['G2']->id() => FALSE], $flags, "job $id");

    // Deleting the placement deletes its moves.
    $moved->delete();
    $this->assertSame(0, $this->recordCount('ancora_placement_change'));
  }

  /**
   * Every refusal of the commit, with nothing written.
   */
  public function testRefusals(): void {
    $g1 = $this->placements['G1'];
    $m = fn () => $this->modification();
    $check = $this->doneCheck($g1, ['2.3' => ['teacher', 'room']]);

    $this->assertSame('cell', $this->refused(fn () => $m()->commit($g1, $check, 2, 3, $this->owner)), 'a refused cell');
    $this->assertSame('cell', $this->refused(fn () => $m()->commit($g1, $check, 9, 9, $this->owner)), 'a cell outside the check');
    $this->assertSame('cell', $this->refused(fn () => $m()->commit($g1, $check, 'x', '1 OR 1=1', $this->owner)), 'malformed');
    $this->assertSame('same', $this->refused(fn () => $m()->commit($g1, $check, 1, 1, $this->owner)), 'where it is');
    $this->assertSame('check', $this->refused(fn () => $m()->commit($g1, 999999, 2, 1, $this->owner)), 'no such check');
    $this->assertSame('check', $this->refused(fn () => $m()->commit($g1, 'x', 2, 1, $this->owner)), 'malformed check id');
    $other_check = $this->doneCheck($this->placements['G2']);
    $this->assertSame('check', $this->refused(fn () => $m()->commit($g1, $other_check, 2, 1, $this->owner)), "another placement's check");
    $queued = $this->doneCheck($g1, [], ['state' => 'queued', 'version' => NULL, 'signature' => NULL, 'result' => NULL]);
    $this->assertSame('check', $this->refused(fn () => $m()->commit($g1, $queued, 2, 1, $this->owner)), 'a queued check');
    $old = $this->doneCheck($g1, [], ['changed' => $this->container->get('datetime.time')->getCurrentTime() - PlacementModification::CHECK_TTL - 1]);
    $this->assertSame('expired', $this->refused(fn () => $m()->commit($g1, $old, 2, 1, $this->owner)), 'an expired check');
    $forged = $this->doneCheck($g1, [], ['signature' => str_repeat('0', 64)]);
    $this->assertSame('placement', $this->refused(fn () => $m()->commit($g1, $forged, 2, 1, $this->owner)), 'the placement changed');
    $stale = $this->doneCheck($g1, [], ['version' => str_repeat('0', 64)]);
    $this->assertSame('version', $this->refused(fn () => $m()->commit($g1, $stale, 2, 1, $this->owner)), 'the input changed');

    // Another planner, and a viewer without the edit permission.
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->assertSame('access', $this->refused(fn () => $m()->commit($g1, $check, 2, 1, $other)));
    $this->assertSame('access', $this->refused(fn () => $m()->requestCheck($g1, $other)));
    $viewer = $this->createUser(['view academic data']);
    $this->assertSame('access', $this->refused(fn () => $m()->commit($g1, $check, 2, 1, $viewer)));

    // The two-database mode offers no move.
    $this->workspaceMode(FALSE);
    $this->assertFalse($m()->isAvailable());
    $this->assertSame('mode', $this->refused(fn () => $m()->commit($g1, $check, 2, 1, $this->owner)));
    $this->assertSame('mode', $this->refused(fn () => $m()->requestCheck($g1, $this->owner)));
    $this->workspaceMode(TRUE);
    $this->assertInstanceOf(PlacementChange::class, $m()->commit($g1, $check, 2, 1, $this->owner), 'the same check still commits');
  }

  /**
   * Checks are not generation jobs; a generation job refuses a commit.
   */
  public function testChecksAndGenerationJobs(): void {
    $g1 = $this->placements['G1'];
    $m = $this->modification();
    $jobs = $this->container->get(JobRepository::class);

    // Pending checks are not jobs: nothing to claim, no active job, and a
    // pending check of the same placement does not block a commit.
    $pending = $m->requestCheck($g1, $this->owner);
    $this->assertSame($pending, $m->requestCheck($g1, $this->owner), 'a pending check is reused');
    $this->container->get('database')->update('ancora_placement_check')->fields(['state' => 'running'])->condition('id', $pending)->execute();
    $this->assertNull($jobs->activeForSite());
    $this->assertNull($jobs->claim('test'));
    $check = $this->doneCheck($g1);
    $this->assertInstanceOf(PlacementChange::class, $m->commit($this->reload('G1'), $check, 3, 2, $this->owner), 'not blocked by a running check');

    // A generation job queued after the check: refused while it is queued or
    // running; a check request is still accepted.
    $check = $this->doneCheck($this->reload('G1'));
    $plan = $g1->get('plan')->entity;
    $records = [
      $plan,
      $this->byCode('ancora_period', 'PER', $plan),
      $this->byCode('ancora_specialty', 'E'),
      $this->byCode('ancora_level', 'N'),
    ];
    $job = $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $this->owner->id(), NULL, ScopeMapper::source(...$records));
    $this->assertSame('job', $this->refused(fn () => $m->commit($this->reload('G1'), $check, 4, 4, $this->owner)), 'queued');
    $this->container->get('database')->update('ancora_placement_check')->fields(['state' => 'failed'])->condition('id', $pending)->execute();
    $this->assertGreaterThan($pending, $m->requestCheck($this->reload('G1'), $this->owner), 'checks are not blocked by the queued job');
    $claimed = $jobs->claim('test');
    $this->assertSame($job, $claimed->id);
    $this->assertSame('job', $this->refused(fn () => $m->commit($this->reload('G1'), $check, 4, 4, $this->owner)), 'running');

    // The run changed a placement (as a publication does): the check is stale.
    $this->placements['G2']->set('day', 4)->save();
    $jobs->finish($claimed, JobState::Succeeded, ['kept' => 'placements']);
    $this->assertSame('version', $this->refused(fn () => $m->commit($this->reload('G1'), $check, 4, 4, $this->owner)), 'stale after the run');
  }

  /**
   * Undo goes back to where the last move came from, with a new check.
   */
  public function testUndo(): void {
    $m = $this->modification();
    $first = $m->commit($this->placements['G1'], $this->doneCheck($this->placements['G1']), 2, 1, $this->owner);
    $second = $m->commit($this->reload('G1'), $this->doneCheck($this->reload('G1')), 3, 1, $this->owner);

    $this->assertSame('undo', $this->refused(fn () => $m->undo($first, $this->doneCheck($this->reload('G1')), $this->owner)), 'only the last move');
    $this->assertSame('undo', $this->refused(fn () => $m->commit($this->reload('G1'), $this->doneCheck($this->reload('G1')), 4, 4, $this->owner, $second)), 'only back to where it came from');
    $this->assertSame('cell', $this->refused(fn () => $m->undo($second, $this->doneCheck($this->reload('G1'), ['2.1' => ['room']]), $this->owner)), 'the old cell is no longer valid');

    $undo = $m->undo($second, $this->doneCheck($this->reload('G1')), $this->owner);
    $this->assertSame([2, 1], [
      (int) $this->reload('G1')->get('day')->value,
      (int) $this->reload('G1')->get('slot')->value,
    ]);
    $this->assertSame(['undo', (int) $second->id()], [
      $undo->get('reason')->value,
      (int) $undo->get('reverts')->target_id,
    ]);
    $this->assertCount(3, $m->history($this->reload('G1')));
  }

  /**
   * The runner: lost checks fail; a check that cannot run fails cleanly.
   */
  public function testRunnerLifecycle(): void {
    $db = $this->container->get('database');
    $now = $this->container->get('datetime.time')->getCurrentTime();
    $lost = $this->doneCheck($this->placements['G1'], [], [
      'state' => 'running',
      'changed' => $now - PlacementCheckRunner::LOST_AFTER - 1,
      'result' => NULL,
    ]);
    $queued = $this->modification()->requestCheck($this->placements['G2'], $this->owner);
    // No engine is configured in a Kernel test: the check fails, with its
    // reason, and nothing else is written.
    $runner = $this->container->get(PlacementCheckRunner::class);
    $this->assertSame(1, $runner->runPending(5));
    $states = $db->query('SELECT id, state FROM {ancora_placement_check} ORDER BY id')->fetchAllKeyed();
    $this->assertSame('failed', $states[$lost]);
    $this->assertSame('failed', $states[$queued]);
    $this->assertNotEmpty($db->query('SELECT error FROM {ancora_placement_check} WHERE id = :id', [':id' => $queued])->fetchField());
    $this->assertSame(0, $this->recordCount('ancora_placement_change'));
    $this->assertSame(0, (int) $db->query('SELECT count(*) FROM {ancora_engine_job}')->fetchField(), 'no generation job was created');
  }

}
