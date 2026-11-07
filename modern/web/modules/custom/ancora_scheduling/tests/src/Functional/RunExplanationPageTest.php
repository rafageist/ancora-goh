<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\ancora_engine\Job\JobSource;
use Drupal\ancora_engine\Job\JobState;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Terminal run explanations and their existing plan access boundary.
 */
#[Group('ancora_scheduling')]
final class RunExplanationPageTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_scheduling'];

  /**
   * Synthetic job rows exercise the page without running the engine.
   */
  public function testTerminalOutcomesAndAccess(): void {
    $planner = $this->drupalCreateUser(['view academic data', 'view ancora engine jobs']);
    $other = $this->drupalCreateUser(['view academic data', 'view ancora engine jobs']);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $planner);
    $storage = fn (string $type) => \Drupal::entityTypeManager()->getStorage($type);
    $one = fn (string $type, array $properties) => current($storage($type)->loadByProperties($properties));
    $period = $one('ancora_period', ['plan' => $plan->id(), 'code' => 'PER']);
    $specialty = $one('ancora_specialty', ['uid' => $planner->id(), 'code' => 'E']);
    $level = $one('ancora_level', ['uid' => $planner->id(), 'code' => 'N']);
    $source = new JobSource((int) $plan->id(), (int) $period->id(), (int) $specialty->id(), (int) $level->id(), ':');
    $scope = new JobScope('E', 'N', 'PER');
    $jobs = \Drupal::service('ancora_engine.job_repository');

    $cases = [
      [
        'state' => JobState::Succeeded,
        'result' => ['kept' => 'placements'],
        'text' => ['kept a final timetable'],
        'absent' => ['extends past the last slot'],
      ],
      [
        'state' => JobState::Succeeded,
        'result' => ['kept' => 'placements', 'overflow' => 1],
        'text' => ['1 activity extends past the last slot', 'legacy scheduling behaviour'],
      ],
      [
        'state' => JobState::Partial,
        'result' => ['kept' => 'partial', 'demands' => 5, 'unplaced' => 2],
        'text' => ['kept a draft timetable', '2 of 5 activities could not be placed', 'does not record why'],
      ],
      [
        'state' => JobState::Partial,
        'result' => ['kept' => 'partial'],
        'text' => ['kept a draft timetable', 'does not record how many'],
        'absent' => ['of 5 activities'],
      ],
      [
        'state' => JobState::Cancelled,
        'result' => ['kept' => 'partial'],
        'text' => ['run was cancelled', 'kept as a draft timetable'],
      ],
      [
        'state' => JobState::Cancelled,
        'result' => ['kept' => 'placements'],
        'text' => ['run was cancelled', 'not marked final'],
      ],
      [
        'state' => JobState::Cancelled,
        'result' => ['kept' => 'none'],
        'text' => ['run was cancelled', 'Work from this run was discarded'],
      ],
      [
        'state' => JobState::Cancelled,
        'result' => [],
        'text' => ['run was cancelled', 'does not say whether this run kept placements'],
      ],
      [
        'state' => JobState::TimedOut,
        'result' => ['kept' => 'none'],
        'error' => 'The run exceeded its time limit of 30 s and was rolled back.',
        'text' => ['exceeded its time limit', 'previous timetable is unchanged'],
        'absent' => ['activities could not be placed'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[2202E] array subscript out of range',
        'text' => ['Q-39', 'Quirk 3', 'Quirk 2', 'does not identify which, if any'],
        'absent' => ['activities could not be placed'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[23502] not-null violation',
        'text' => ['Q-05', 'case 123'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[23505] unique violation',
        'text' => ['Q-31/Q-32', 'cases 083 and 142'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[22004] null value not allowed',
        'text' => ['case 130'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[P0001] generar: activity template T has no orden',
        'text' => ['generar.sql:207'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[P0001] another error',
        'text' => ['No documented possible cause'],
        'absent' => ['generar.sql:207'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[42804] datatype mismatch',
        'text' => ['Q-28 or Q-29', 'cases 070–072 and 100'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[42703] undefined column',
        'text' => ['Q-28 or Q-29', 'cases 070–072 and 100'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => 'The placements broke an engine invariant (collision or overflow); the run was rolled back.',
        'text' => ['two-database mode', 'D12'],
      ],
      [
        'state' => JobState::Failed,
        'result' => ['kept' => 'none'],
        'error' => '[XX999] unfamiliar error',
        'text' => ['No documented possible cause'],
        'absent' => ['Documented possible causes'],
      ],
      [
        'state' => JobState::Failed,
        'result' => [],
        'text' => ['No error detail was recorded', 'previous timetable is unchanged'],
        'absent' => ['Documented possible causes', 'activities could not be placed'],
      ],
    ];

    $this->drupalLogin($planner);
    foreach ($cases as $case) {
      $id = $jobs->enqueue($scope, new JobOptions(), 30, (int) $planner->id(), source: $source);
      \Drupal::database()->update('ancora_engine_job')
        ->fields([
          'state' => $case['state']->value,
          'result' => json_encode($case['result'], JSON_THROW_ON_ERROR),
          'error' => $case['error'] ?? NULL,
          'finished' => \Drupal::time()->getRequestTime(),
        ])
        ->condition('id', $id)
        ->execute();
      $this->drupalGet('/ancora/scheduling/jobs/' . $id);
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains('What happened');
      foreach ($case['text'] as $text) {
        $this->assertSession()->pageTextContains($text);
      }
      foreach ($case['absent'] ?? [] as $text) {
        $this->assertSession()->pageTextNotContains($text);
      }
    }

    $this->drupalLogin($other);
    $this->drupalGet('/ancora/scheduling/jobs/' . $id);
    $this->assertSession()->statusCodeEquals(403);
  }

}
