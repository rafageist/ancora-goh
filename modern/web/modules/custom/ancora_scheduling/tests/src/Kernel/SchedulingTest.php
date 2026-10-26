<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\LegacyTableSync;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Scope mapping, input snapshot hash, synced jobs and the timetable rules.
 *
 * The engine database itself is exercised by scripts/r1-journey.sh.
 */
#[Group('ancora_scheduling')]
final class SchedulingTest extends AcademicKernelTestBase {

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
  }

  /**
   * The scope is numbered engine ids; other plans and users are refused.
   */
  public function testScopeMapping(): void {
    $plan = $this->loadFixture();
    $period = $this->byCode('ancora_period', 'PER', $plan);
    $specialty = $this->byCode('ancora_specialty', 'E');
    $level = $this->byCode('ancora_level', 'N');
    $this->assertSame('u' . $this->owner->id(), ScopeMapper::creator($plan));
    $this->assertSame(ScopeMapper::creatorOf((int) $this->owner->id()), ScopeMapper::creator($plan));
    $scope = ScopeMapper::scope($plan, $period, $specialty, $level);
    $this->assertSame([
      sprintf('%010d:E', $specialty->id()),
      sprintf('%010d:N', $level->id()),
      sprintf('%010d:PER', $period->id()),
    ], $scope->toParameters());
    $source = ScopeMapper::source($plan, $period, $specialty, $level);
    $this->assertSame([(int) $plan->id(), (int) $period->id(), (int) $specialty->id(), (int) $level->id(), ':'], [
      $source->planId,
      $source->periodId,
      $source->specialtyId,
      $source->levelId,
      $source->idPrefix,
    ]);

    // A specialty of another user does not make a scope with this plan.
    $other_user = $this->createUser(['view academic data', 'edit academic data']);
    $foreign = $this->newRecord('ancora_specialty', ['uid' => $other_user->id(), 'label' => 'Program', 'code' => 'E']);
    $foreign->save();
    try {
      ScopeMapper::scope($plan, $period, $foreign, $level);
      $this->fail('A specialty of another user was accepted.');
    }
    catch (\InvalidArgumentException $e) {
      $this->assertStringContainsString('does not belong to the creator', $e->getMessage());
    }

    $other = $this->container->get('entity_type.manager')->getStorage('ancora_plan')->create([
      'label' => 'Other',
      'code' => 'Q',
    ]);
    $other->save();
    $this->expectException(\InvalidArgumentException::class);
    ScopeMapper::scope($other, $period, $specialty, $level);
  }

  /**
   * Every synced row is selected by its numbered id.
   *
   * LegacyTableSync reads and deletes the synced rows by the owner column of
   * each table; a row with another id would never be replaced.
   */
  public function testEveryRowIsNumbered(): void {
    $plan = $this->loadFixture();
    $export = $this->container->get(AcademicExport::class)->export($plan);
    $rows = $this->container->get(LegacyRows::class)->build($export, ScopeMapper::creator($plan), TRUE);
    $this->assertSame([], array_diff_key(LegacyTableSync::TABLES, $rows));
    foreach (LegacyTableSync::TABLES as $table => [$key, $owner]) {
      $this->assertNotEmpty($rows[$table], $table);
      foreach ($rows[$table] as $row) {
        $this->assertMatchesRegularExpression('/' . LegacyTableSync::SYNCED_ID . '/', (string) $row[$owner], "$table.$owner");
        foreach ($key as $column) {
          $this->assertArrayHasKey($column, $row, "$table.$column");
        }
        if (isset($row['creador'])) {
          $this->assertSame(ScopeMapper::creatorOf((int) $this->owner->id()), $row['creador'], "$table.creador");
        }
      }
    }
  }

  /**
   * The input snapshot hash is stable and follows every change of the plan.
   */
  public function testInputHash(): void {
    $plan = $this->loadFixture();
    $export = $this->container->get(AcademicExport::class);
    $hash = ScopeMapper::hash($export->export($plan));
    $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $hash);
    $this->assertSame($hash, ScopeMapper::hash($export->export($plan)));
    $room = $this->byCode('ancora_room', 'L1');
    $room->set('capacity', 60)->save();
    $this->assertNotSame($hash, ScopeMapper::hash($export->export($plan)));
  }

  /**
   * Synced jobs keep their source; only some outcomes change placements.
   */
  public function testSyncedJobsAndTimetableRules(): void {
    $plan = $this->loadFixture();
    $period = $this->byCode('ancora_period', 'PER', $plan);
    $specialty = $this->byCode('ancora_specialty', 'E');
    $level = $this->byCode('ancora_level', 'N');
    $jobs = $this->container->get('ancora_engine.job_repository');
    $scope = ScopeMapper::scope($plan, $period, $specialty, $level);
    $source = ScopeMapper::source($plan, $period, $specialty, $level);
    $run = function (JobState $state, array $result) use ($jobs, $scope, $source): int {
      $id = $jobs->enqueue($scope, new JobOptions(), 60, 1, NULL, $source);
      $this->assertSame($id, $jobs->activeForScope($scope)->id);
      $jobs->finish($jobs->claim('test'), $state, $result);
      $this->assertNull($jobs->activeForScope($scope));
      return $id;
    };

    $reader = $this->container->get('ancora_scheduling.timetable');
    $this->assertSame([], $reader->read($plan, $period)['scopes'], 'no job: no timetable');

    $succeeded = $run(JobState::Succeeded, ['kept' => 'placements', 'input_hash' => 'x']);
    $job = $jobs->load($succeeded);
    $this->assertSame($source->idPrefix, $job->idPrefix());
    $this->assertSame((int) $period->id(), $job->source->periodId);
    $this->assertTrue(JobRepository::changedPlacements($job));
    $read = $reader->read($plan, $period);
    $this->assertCount(1, $read['scopes']);
    $this->assertSame($succeeded, $read['scopes'][0]['job']->id);
    $this->assertTrue($read['scopes'][0]['final']);
    $this->assertTrue($read['scopes'][0]['stale'], 'the input hash differs from the current export');
    // No engine database is configured in this test.
    $this->assertSame('unreachable', $read['scopes'][0]['error']);
    $this->assertSame('Program', $read['scopes'][0]['specialty']);

    $draft = $run(JobState::Cancelled, ['kept' => 'partial']);
    $read = $reader->read($plan, $period);
    $this->assertSame($draft, $read['scopes'][0]['job']->id, 'a soft-cancelled run is what the engine now holds');
    $this->assertFalse($read['scopes'][0]['final'], 'and it is never a final timetable');

    $failed = $run(JobState::Failed, ['kept' => 'none']);
    $this->assertFalse(JobRepository::changedPlacements($jobs->load($failed)));
    $hard = $run(JobState::Cancelled, ['kept' => 'none']);
    $this->assertFalse(JobRepository::changedPlacements($jobs->load($hard)));
    $read = $reader->read($plan, $period);
    $this->assertSame($draft, $read['scopes'][0]['job']->id, 'rolled-back runs do not change what is shown');

    $final = $run(JobState::Succeeded, ['kept' => 'placements']);
    $read = $reader->read($plan, $period);
    $this->assertSame($final, $read['scopes'][0]['job']->id);
    $this->assertTrue($read['scopes'][0]['final']);
    $this->assertSame([$final, $hard, $failed, $draft, $succeeded], array_keys($jobs->forSource((int) $plan->id(), (int) $period->id())));
  }

}
