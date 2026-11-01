<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_engine\Functional;

use Drupal\ancora_engine\Job\JobState;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Job pages: permissions, queueing without running, cancellation access.
 */
#[Group('ancora_engine')]
final class JobPagesTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_engine'];

  /**
   * The job repository.
   */
  private function jobs(): object {
    return $this->container->get('ancora_engine.job_repository');
  }

  /**
   * Submits the queue form.
   */
  private function submitJob(array $overrides = []): void {
    $this->drupalGet('admin/ancora/engine/add');
    $this->submitForm($overrides + [
      'specialty' => 'E',
      'level' => 'N',
      'period' => 'PER',
      'forma' => '2',
      'compactar' => '3',
      'parche' => TRUE,
      'time_limit' => '60',
    ], 'Queue');
  }

  /**
   * Access by permission, and queueing a job without running it.
   */
  public function testPermissionsAndQueue(): void {
    foreach (['admin/ancora/engine', 'admin/ancora/engine/add'] as $path) {
      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(403);
    }

    $viewer = $this->drupalCreateUser(['view ancora engine jobs']);
    $this->drupalLogin($viewer);
    $this->drupalGet('admin/ancora/engine');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('No generation job yet.');
    $this->assertSession()->linkNotExists('Queue a timetable generation');
    $this->drupalGet('admin/ancora/engine/add');
    $this->assertSession()->statusCodeEquals(403);

    $requester = $this->drupalCreateUser(['view ancora engine jobs', 'request ancora engine jobs']);
    $this->drupalLogin($requester);
    $this->submitJob();
    $this->assertSession()->pageTextContains('queued');
    $jobs = $this->jobs()->recent();
    $this->assertCount(1, $jobs);
    $job = reset($jobs);
    // Queued only: the request never runs the engine (none is configured).
    $this->assertSame(JobState::Queued, $job->state);
    $this->assertSame((int) $requester->id(), $job->requestedBy);
    $this->assertSame(['forma' => 2, 'parche' => TRUE, 'compactar' => 3, 'parche1' => FALSE, 'buscalugar' => FALSE], $job->options->toArray());
    $this->assertSame(60, $job->timeLimit);
    $this->assertSession()->addressEquals('admin/ancora/engine/' . $job->id);
    $this->assertSession()->pageTextContains('Queued');
  }

  /**
   * Invalid input queues nothing.
   */
  public function testValidation(): void {
    $this->drupalLogin($this->drupalCreateUser(['view ancora engine jobs', 'request ancora engine jobs']));
    $this->submitJob(['compactar' => '11']);
    $this->submitJob(['time_limit' => '121']);
    $this->submitJob(['specialty' => "E\tX"]);
    $this->assertSame([], $this->jobs()->recent());
  }

  /**
   * Only the requester or an engine administrator may cancel a running job.
   */
  public function testCancelAccess(): void {
    $owner = $this->drupalCreateUser(['view ancora engine jobs', 'request ancora engine jobs']);
    $other = $this->drupalCreateUser(['view ancora engine jobs', 'request ancora engine jobs']);
    $admin = $this->drupalCreateUser(['view ancora engine jobs', 'administer ancora engine']);
    $this->drupalLogin($owner);
    $this->submitJob();
    $this->submitJob();
    [$second, $first] = array_keys($this->jobs()->recent());

    $this->drupalLogin($other);
    $this->drupalGet("admin/ancora/engine/$first/cancel");
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($owner);
    $this->drupalGet("admin/ancora/engine/$first");
    $this->clickLink('Cancel this job');
    $this->submitForm([], 'Cancel the job');
    $this->assertSession()->pageTextContains('Cancelled: nothing kept');
    $this->assertSame(JobState::Cancelled, $this->jobs()->load($first)->state);
    // An ended job cannot be cancelled again.
    $this->drupalGet("admin/ancora/engine/$first/cancel");
    $this->assertSession()->statusCodeEquals(403);

    // A running job is only flagged; the worker forwards the request.
    $this->assertSame($second, $this->jobs()->claim('test-worker')->id);
    $this->drupalLogin($admin);
    $this->drupalGet("admin/ancora/engine/$second/cancel");
    $this->submitForm([], 'Cancel the job');
    $this->assertSession()->pageTextContains('Running (cancellation requested)');
    $job = $this->jobs()->load($second);
    $this->assertSame(JobState::Running, $job->state);
    $this->assertTrue($job->cancelRequested);
    $this->assertSame((int) $admin->id(), $job->cancelledBy);
  }

}
