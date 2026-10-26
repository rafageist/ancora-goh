<?php

declare(strict_types=1);

namespace Drupal\ancora_engine;

use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\Engine\JobInputInterface;
use Drupal\ancora_engine\Engine\RunResult;
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
 */
final class Worker {

  public function __construct(
    private readonly JobRepository $jobs,
    private readonly EngineClient $engine,
    private readonly EngineSettings $settings,
    private readonly TimeInterface $time,
    private readonly LoggerInterface $logger,
    private readonly ?JobInputInterface $input = NULL,
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
            $this->engine->signalSoftCancel($job->scope);
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

    try {
      $run = $this->engine->run($job, $tick, $this->input);
    }
    catch (\RuntimeException $e) {
      $run = new RunResult(RunResult::ERROR, message: $e->getMessage());
    }

    [$state, $result, $error] = $this->outcome($job, $run);
    if ($run->status !== RunResult::RETURNED && $run->stoppedBy !== RunResult::STOP_FENCED) {
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
          $this->engine->clearCancelRequest($job->scope);
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
        $this->engine->signalSoftCancel($job->scope);
      }
    }
    return $state;
  }

}
