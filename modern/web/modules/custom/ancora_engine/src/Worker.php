<?php

declare(strict_types=1);

namespace Drupal\ancora_engine;

use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\Engine\JobInputInterface;
use Drupal\ancora_engine\Engine\JobOutputInterface;
use Drupal\ancora_engine\Engine\PublicationException;
use Drupal\ancora_engine\Engine\RunResult;
use Drupal\ancora_engine\Engine\WorkspaceClient;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobState;
use Drupal\Component\Datetime\TimeInterface;
use Psr\Log\LoggerInterface;

/**
 * The engine worker: claims one job of the site, runs it, records the outcome.
 *
 * It runs only from Drush (a CLI process with its own resource limits), never
 * inside a web request.
 *
 * Outcomes:
 * - succeeded: generar() returned normally, no cancellation was requested,
 *   every demand placed, no collision, no overflow (committed);
 * - partial: generar() returned normally without cancellation but some
 *   demands are unplaced (committed, a draft);
 * - cancelled: a cancellation was requested. Soft: generar() stopped at its
 *   next check and the placements made so far are committed (result kept =
 *   partial). Hard (or a soft request not honoured within the grace time):
 *   pg_cancel_backend, the run is rolled back (kept = none);
 * - timed_out: statement_timeout (or the worker's wall-clock guard) stopped
 *   the run; rolled back, nothing of it is kept;
 * - failed: anything else (rolled back), including placements that break an
 *   engine invariant.
 *
 * Every run first deletes the scope's previous placements in the same
 * transaction as generar() (replace semantics), so a retry or a requeued run
 * starts fresh, and a rolled-back run leaves the previous placements as they
 * were.
 *
 * A job with a source (Drupal academic records) is synced first, in that same
 * transaction, by the job input service (JobInputInterface): a failed sync
 * fails the job and leaves the engine database untouched.
 *
 * Workspace mode (EngineSettings::isWorkspace(), #95): the run happens in a
 * job-scoped workspace of the site database (WorkspaceClient), loaded with
 * the site's records AND its stored placements; nothing is deleted first
 * (legacy continuation, no D7). When generar() returned (complete, partial
 * or soft-cancelled), its new placements are published as Drupal records
 * (JobOutputInterface) in one transaction BEFORE the outcome is recorded;
 * if nothing could be published (stale input, invalid output, fenced run,
 * save failure) the job fails and nothing of the run is kept. The workspace
 * is closed after every attempt (dropped, or kept for diagnosis), and the
 * workspaces of lost attempts are dropped at the start of every cycle.
 */
final class Worker {

  public function __construct(
    private readonly JobRepository $jobs,
    private readonly EngineClient $engine,
    private readonly EngineSettings $settings,
    private readonly TimeInterface $time,
    private readonly LoggerInterface $logger,
    private readonly ?JobInputInterface $input = NULL,
    private readonly ?WorkspaceClient $workspace = NULL,
    private readonly ?JobInputInterface $workspaceInput = NULL,
    private readonly ?JobOutputInterface $output = NULL,
  ) {}

  /**
   * A worker id: host, process and a random part.
   */
  public static function newWorkerId(): string {
    return substr((gethostname() ?: 'host') . ':' . getmypid() . ':' . bin2hex(random_bytes(4)), 0, 128);
  }

  /**
   * Recovers stale jobs, then claims and runs at most one job.
   *
   * @return \Drupal\ancora_engine\Job\Job|null
   *   The job after the run, or NULL when nothing was claimed.
   */
  public function runOnce(string $workerId): ?Job {
    $this->recoverStale();
    if ($this->settings->isWorkspace() && $this->workspace !== NULL) {
      try {
        foreach ($this->workspace->cleanup() as $key) {
          $this->logger->warning('Workspace @key of a lost or failed attempt was dropped.', ['@key' => $key]);
        }
      }
      catch (\RuntimeException $e) {
        $this->logger->warning('Workspace cleanup failed: @message', ['@message' => $e->getMessage()]);
      }
    }
    $job = $this->jobs->claim($workerId);
    if ($job === NULL) {
      return NULL;
    }
    $this->logger->info('Job @id claimed by @worker (attempt @attempt).', [
      '@id' => $job->id,
      '@worker' => $workerId,
      '@attempt' => $job->attempts,
    ]);
    $this->run($job);
    return $this->jobs->load($job->id);
  }

  /**
   * Runs a claimed job and records its outcome.
   */
  public function run(Job $job): ?JobState {
    $interval = $this->settings->heartbeatInterval();
    $grace = $this->settings->cancelGrace();
    $last_beat = $last_check = microtime(TRUE);
    $signalled_at = NULL;
    $tick = function (float $elapsed) use ($job, $interval, $grace, &$last_beat, &$last_check, &$signalled_at): ?string {
      $now = microtime(TRUE);
      if ($now - $last_beat >= $interval) {
        $last_beat = $now;
        if (!$this->jobs->heartbeat($job)) {
          return RunResult::STOP_FENCED;
        }
      }
      // The engine's statement_timeout is the hard stop; this guard only
      // covers a server that does not apply it.
      if ($elapsed > $job->timeLimit + 10) {
        return RunResult::STOP_WALL_CLOCK;
      }
      if ($now - $last_check < 0.5) {
        return NULL;
      }
      $last_check = $now;
      $mode = $this->jobs->cancelRequest($job->id);
      if ($mode !== NULL) {
        if ($mode === 'hard') {
          return RunResult::STOP_HARD_CANCEL;
        }
        if ($signalled_at === NULL) {
          $signalled_at = microtime(TRUE);
          try {
            $this->softCancel($job);
          }
          catch (\RuntimeException $e) {
            $this->logger->warning('Job @id: the soft cancellation could not be forwarded: @message', [
              '@id' => $job->id,
              '@message' => $e->getMessage(),
            ]);
          }
        }
        elseif (microtime(TRUE) - $signalled_at > $grace) {
          return RunResult::STOP_HARD_CANCEL;
        }
      }
      return NULL;
    };

    if ($this->settings->isWorkspace()) {
      $run = $this->runWorkspace($job, $tick);
    }
    else {
      try {
        $run = $this->engine->run($job, $tick, $this->input);
      }
      catch (\RuntimeException $e) {
        $run = new RunResult(RunResult::ERROR, message: $e->getMessage());
      }
    }

    [$state, $result, $error] = $this->outcome($job, $run);
    if (!$this->settings->isWorkspace() && $run->status !== RunResult::RETURNED && $run->stoppedBy !== RunResult::STOP_FENCED) {
      // A consumed cancellation row comes back when its run rolls back. (A
      // fenced run leaves it alone: it may belong to the next attempt.)
      try {
        $this->engine->clearCancelRequest($job->scope);
      }
      catch (\RuntimeException) {
        // The next run deletes it before it starts.
      }
    }
    $written = $this->jobs->finish($job, $state, $result, $error);
    if ($written === NULL) {
      $this->logger->warning('Job @id: the outcome of attempt @attempt was discarded (the job was recovered meanwhile).', [
        '@id' => $job->id,
        '@attempt' => $job->attempts,
      ]);
      return NULL;
    }
    $this->logger->info('Job @id ended @state.', ['@id' => $job->id, '@state' => $written->value]);
    return $written;
  }

  /**
   * Runs a job in a workspace and publishes what it placed.
   */
  private function runWorkspace(Job $job, callable $tick): RunResult {
    if ($this->workspace === NULL || $this->workspaceInput === NULL || $this->output === NULL) {
      return new RunResult(RunResult::ERROR, message: 'The workspace mode needs the workspace input and output services (ancora_scheduling).');
    }
    if ($job->source === NULL) {
      return new RunResult(RunResult::ERROR, message: 'The workspace mode runs only jobs with a source (Drupal records).');
    }
    try {
      $run = $this->workspace->run($job, $tick, $this->workspaceInput);
    }
    catch (\RuntimeException $e) {
      $run = new RunResult(RunResult::ERROR, message: $e->getMessage(), workspace: WorkspaceClient::key($job));
    }
    try {
      if ($run->status === RunResult::RETURNED) {
        try {
          $published = $this->output->publish($job, $run);
          $run = new RunResult($run->status, $run->metrics, $run->durationMs, $run->sqlstate, $run->message, $run->stoppedBy, $run->cancelRowLeft, 0,
            $run->input + ['publication' => $published + ['output' => count($run->output)]], $run->output, $run->workspace);
        }
        catch (PublicationException $e) {
          $run = new RunResult(RunResult::ERROR, $run->metrics, $run->durationMs, NULL, $e->getMessage(), $e->reason === PublicationException::FENCED ? RunResult::STOP_FENCED : $run->stoppedBy,
            FALSE, 0, $run->input + ['publication' => ['failed' => $e->reason, 'output' => count($run->output)]], [], $run->workspace);
        }
      }
    }
    finally {
      try {
        if ($run->workspace !== NULL) {
          $this->workspace->close($run->workspace);
        }
      }
      catch (\RuntimeException $e) {
        $this->logger->warning('Job @id: its workspace could not be closed (the next cycle drops it): @message', [
          '@id' => $job->id,
          '@message' => $e->getMessage(),
        ]);
      }
    }
    return $run;
  }

  /**
   * Forwards a soft cancellation to the engine of the site's mode.
   */
  private function softCancel(Job $job): void {
    if ($this->settings->isWorkspace() && $this->workspace !== NULL) {
      $this->workspace->signalSoftCancel($job);
      return;
    }
    $this->engine->signalSoftCancel($job->scope);
  }

  /**
   * Maps a run to the job state, the result metrics and the error summary.
   *
   * @return array
   *   The state, the result array and the error summary (or NULL).
   */
  private function outcome(Job $job, RunResult $run): array {
    $result = $run->metrics + [
      'duration_ms' => $run->durationMs,
      'replaced' => $run->replaced,
      'attempt' => $job->attempts,
      'mode' => $this->settings->mode(),
    ];
    if ($run->input !== []) {
      // The versioned input snapshot of a synced job.
      $result['input_hash'] = $run->input['hash'] ?? NULL;
      $result['input'] = $run->input;
    }
    if ($run->status === RunResult::RETURNED) {
      $complete = ($run->metrics['unplaced'] ?? 1) === 0;
      $result['partial'] = !$complete;
      $result['kept'] = $complete ? 'placements' : 'partial';
      if ($this->jobs->cancelRequest($job->id) !== NULL) {
        return [JobState::Cancelled, $result, NULL];
      }
      // finish() still turns these into cancelled if a request arrives
      // before the outcome is written.
      return [$complete ? JobState::Succeeded : JobState::Partial, $result, NULL];
    }

    $result['partial'] = FALSE;
    $result['kept'] = 'none';
    if ($run->status === RunResult::INVALID) {
      return [JobState::Failed, $result, $run->message];
    }
    if ($run->isStatementTimeout() || $run->stoppedBy === RunResult::STOP_WALL_CLOCK) {
      $error = sprintf('The run exceeded its time limit of %d s and was rolled back.', $job->timeLimit);
      return [JobState::TimedOut, $result, $error];
    }
    if ($run->stoppedBy === RunResult::STOP_HARD_CANCEL || ($run->sqlstate === '57014' && $this->jobs->cancelRequest($job->id) !== NULL)) {
      return [JobState::Cancelled, $result, NULL];
    }
    $error = trim(($run->sqlstate ? '[' . $run->sqlstate . '] ' : '') . ($run->message ?? 'Unknown engine error.'));
    return [JobState::Failed, $result, $error];
  }

  /**
   * Recovers running jobs whose worker stopped sending heartbeats.
   *
   * The engine backend of the stale attempt is terminated first (its
   * transaction rolls back) and the job is requeued only when no backend of
   * that attempt is left, so two runs of a scope never overlap. If that
   * cannot be confirmed, the job fails instead.
   *
   * @return array
   *   New states keyed by job id.
   */
  public function recoverStale(): array {
    $recovered = [];
    foreach ($this->jobs->stale($this->settings->staleAfter()) as $job) {
      try {
        $stopped = $this->engine->terminateJob($job);
      }
      catch (\RuntimeException $e) {
        $stopped = FALSE;
      }
      if ($stopped) {
        try {
          // Workspace mode: the attempt's cancellation rows go with its
          // workspace (cleanup()).
          if (!$this->settings->isWorkspace()) {
            $this->engine->clearCancelRequest($job->scope);
          }
        }
        catch (\RuntimeException) {
          // The next run deletes it before it starts.
        }
        $state = $this->jobs->recover($job, $this->settings->maxAttempts(), sprintf('Worker lost (no heartbeat for %d s) during attempt %d; its engine run was terminated and rolled back.', $this->settings->staleAfter(), $job->attempts));
      }
      else {
        $state = $this->jobs->recover($job, 0, sprintf('Worker lost during attempt %d; its engine run could not be confirmed stopped.', $job->attempts));
      }
      if ($state !== NULL) {
        $recovered[$job->id] = $state;
        $this->logger->warning('Stale job @id recovered: @state.', ['@id' => $job->id, '@state' => $state->value]);
      }
    }
    return $recovered;
  }

  /**
   * Requests the cancellation of a job and forwards it to the engine.
   *
   * Called from the CLI. The web form only flags the job (no engine
   * connection inside a web request); the worker forwards the flag within
   * one poll interval.
   */
  public function cancel(int $id, int $uid, bool $hard): JobState {
    $state = $this->jobs->requestCancel($id, $uid, $hard);
    if ($state === JobState::Running) {
      $job = $this->jobs->load($id);
      if ($hard) {
        $this->engine->cancelJob($job);
      }
      else {
        $this->softCancel($job);
      }
    }
    return $state;
  }

}
