<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_engine\Kernel;

use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\Core\Database\IntegrityConstraintViolationException;
use Drupal\Tests\ancora\Kernel\AncoraKernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Job storage and state machine: claims, fencing, cancellation, recovery.
 */
#[Group('ancora_engine')]
final class JobRepositoryTest extends AncoraKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'user', 'ancora_engine'];

  /**
   * The repository under test.
   */
  private JobRepository $jobs;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    // The partial unique index of hook_install().
    $this->container->get('module_handler')->loadInclude('ancora_engine', 'install');
    ancora_engine_install();
    $this->jobs = $this->container->get('ancora_engine.job_repository');
  }

  /**
   * Queues a job on the E2E scope.
   */
  private function enqueue(int $uid = 7): int {
    return $this->jobs->enqueue(new JobScope('E', 'N', 'PER'), new JobOptions(2, TRUE, 3, FALSE, TRUE), 120, $uid);
  }

  /**
   * Makes the heartbeat of a job look old.
   */
  private function age(int $id, int $seconds): void {
    $this->container->get('database')->update('ancora_engine_job')
      ->fields(['heartbeat' => time() - $seconds])
      ->condition('id', $id)
      ->execute();
  }

  /**
   * A queued job keeps its scope, options, limit and requester.
   */
  public function testEnqueueAndLoad(): void {
    $job = $this->jobs->load($this->enqueue());
    $this->assertSame(JobState::Queued, $job->state);
    $this->assertSame(['E', 'N', 'PER'], $job->scope->toParameters());
    $this->assertSame(['2', 'true', '3', 'false', 'true'], $job->options->toParameters());
    $this->assertSame(120, $job->timeLimit);
    $this->assertSame(7, $job->requestedBy);
    $this->assertSame(0, $job->attempts);
    $this->assertNull($job->result);
    $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $job->uuid);
  }

  /**
   * Claims are in queue order, one running job per site, idempotent.
   */
  public function testClaimIsExclusive(): void {
    $first = $this->enqueue();
    $second = $this->enqueue();
    $claimed = $this->jobs->claim('worker-1');
    $this->assertSame($first, $claimed->id);
    $this->assertSame(JobState::Running, $claimed->state);
    $this->assertSame('worker-1', $claimed->workerId);
    $this->assertSame(1, $claimed->attempts);
    $this->assertNotNull($claimed->heartbeat);

    // A second worker gets nothing while the first job runs.
    $this->assertNull($this->jobs->claim('worker-2'));
    $this->assertSame(JobState::Queued, $this->jobs->load($second)->state);

    $this->assertSame(JobState::Succeeded, $this->jobs->finish($claimed, JobState::Succeeded, ['placed' => 8]));
    $this->assertSame($second, $this->jobs->claim('worker-2')->id);
    $this->assertNull($this->jobs->claim('worker-3'));
  }

  /**
   * The database itself refuses a second running job.
   */
  public function testOneRunningJobIndex(): void {
    $this->enqueue();
    $second = $this->enqueue();
    $this->jobs->claim('worker-1');
    $this->expectException(IntegrityConstraintViolationException::class);
    $this->container->get('database')->update('ancora_engine_job')
      ->fields(['state' => 'running'])
      ->condition('id', $second)
      ->execute();
  }

  /**
   * A returned run with a pending cancellation ends cancelled, never final.
   */
  public function testCancelRequestWinsOverSuccess(): void {
    $this->enqueue();
    $job = $this->jobs->claim('worker-1');
    $this->assertSame(JobState::Running, $this->jobs->requestCancel($job->id, 3));
    $this->assertSame('soft', $this->jobs->cancelRequest($job->id));
    $state = $this->jobs->finish($job, JobState::Succeeded, ['placed' => 8, 'kept' => 'placements']);
    $this->assertSame(JobState::Cancelled, $state);
    $this->assertFalse($state->isFinalTimetable());
    $this->assertSame(3, $this->jobs->load($job->id)->cancelledBy);

    // A timeout stays a timeout: nothing was kept.
    $this->enqueue();
    $job = $this->jobs->claim('worker-1');
    $this->jobs->requestCancel($job->id, 3);
    $this->assertSame(JobState::TimedOut, $this->jobs->finish($job, JobState::TimedOut, ['kept' => 'none']));
  }

  /**
   * A queued job is cancelled at once; an ended job cannot be cancelled.
   */
  public function testCancelQueuedJob(): void {
    $id = $this->enqueue();
    $this->assertSame(JobState::Cancelled, $this->jobs->requestCancel($id, 3));
    $job = $this->jobs->load($id);
    $this->assertSame(0, $job->attempts);
    $this->assertSame('none', $job->result['kept']);
    $this->assertNull($this->jobs->claim('worker-1'));
    $this->expectException(\InvalidArgumentException::class);
    $this->jobs->requestCancel($id, 3);
  }

  /**
   * A hard request upgrades a soft one, never the reverse.
   */
  public function testHardCancelUpgrade(): void {
    $this->enqueue();
    $job = $this->jobs->claim('worker-1');
    $this->jobs->requestCancel($job->id, 3);
    $this->jobs->requestCancel($job->id, 3, TRUE);
    $this->assertSame('hard', $this->jobs->cancelRequest($job->id));
    $this->jobs->requestCancel($job->id, 3);
    $this->assertSame('hard', $this->jobs->cancelRequest($job->id));
  }

  /**
   * A stale job is requeued once, then fails; old attempts are fenced off.
   */
  public function testStaleRecovery(): void {
    $id = $this->enqueue();
    $attempt1 = $this->jobs->claim('worker-1');
    $this->assertSame([], $this->jobs->stale(60));
    $this->age($id, 120);
    $this->assertSame([$id], array_keys($this->jobs->stale(60)));

    $this->assertSame(JobState::Queued, $this->jobs->recover($attempt1, 2, 'lost'));
    // Recovering the same attempt twice does nothing.
    $this->assertNull($this->jobs->recover($attempt1, 2, 'lost'));

    $attempt2 = $this->jobs->claim('worker-2');
    $this->assertSame(2, $attempt2->attempts);
    // The first worker can no longer write a heartbeat or an outcome.
    $this->assertFalse($this->jobs->heartbeat($attempt1));
    $this->assertNull($this->jobs->finish($attempt1, JobState::Succeeded, []));
    $this->assertTrue($this->jobs->heartbeat($attempt2));

    $this->age($id, 120);
    $this->assertSame(JobState::Failed, $this->jobs->recover($attempt2, 2, 'lost again'));
    $job = $this->jobs->load($id);
    $this->assertSame(JobState::Failed, $job->state);
    $this->assertSame('lost again', $job->error);
    $this->assertSame('none', $job->result['kept']);
  }

  /**
   * A stale job whose cancellation was requested ends cancelled.
   */
  public function testStaleCancelledJob(): void {
    $this->enqueue();
    $job = $this->jobs->claim('worker-1');
    $this->jobs->requestCancel($job->id, 3);
    $job = $this->jobs->load($job->id);
    $this->assertSame(JobState::Cancelled, $this->jobs->recover($job, 2, 'lost'));
  }

  /**
   * The state machine.
   */
  public function testStateMachine(): void {
    $this->assertTrue(JobState::Queued->canTransitionTo(JobState::Running));
    $this->assertTrue(JobState::Queued->canTransitionTo(JobState::Cancelled));
    $this->assertFalse(JobState::Queued->canTransitionTo(JobState::Succeeded));
    $ends = [JobState::Succeeded, JobState::Partial, JobState::Cancelled, JobState::TimedOut, JobState::Failed];
    foreach ([...$ends, JobState::Queued] as $to) {
      $this->assertTrue(JobState::Running->canTransitionTo($to), $to->value);
    }
    foreach ($ends as $from) {
      $this->assertTrue($from->isTerminal());
      foreach (JobState::cases() as $to) {
        $this->assertFalse($from->canTransitionTo($to), $from->value . ' -> ' . $to->value);
      }
    }
    $final = array_filter(JobState::cases(), static fn (JobState $s): bool => $s->isFinalTimetable());
    $this->assertSame([JobState::Succeeded], array_values($final));

    $this->enqueue();
    $job = $this->jobs->claim('worker-1');
    $this->expectException(\LogicException::class);
    $this->jobs->finish($job, JobState::Queued, []);
  }

}
