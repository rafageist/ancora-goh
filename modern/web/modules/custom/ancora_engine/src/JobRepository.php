<?php

declare(strict_types=1);

namespace Drupal\ancora_engine;

use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\ancora_engine\Job\JobState;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Database\Connection;

/**
 * Storage and state machine of the generation jobs (site's Drupal database).
 *
 * Every state change is a conditional UPDATE on the expected current state,
 * so concurrent workers, recoveries and cancellations cannot overwrite each
 * other. A run is fenced by its attempt number: a worker whose job was
 * recovered (requeued or failed) and claimed again can no longer write a
 * heartbeat or an outcome.
 */
final class JobRepository {

  /**
   * Advisory lock key that serializes claims within the site's database.
   */
  private const CLAIM_LOCK = 710071;

  public function __construct(
    private readonly Connection $database,
    private readonly TimeInterface $time,
    private readonly UuidInterface $uuid,
  ) {}

  /**
   * Queues a job.
   *
   * @return int
   *   The job id.
   */
  public function enqueue(JobScope $scope, JobOptions $options, int $timeLimit, int $requestedBy, ?int $retryOf = NULL): int {
    if ($timeLimit < 1) {
      throw new \InvalidArgumentException('The time limit must be a positive number of seconds.');
    }
    $now = $this->time->getCurrentTime();
    return (int) $this->database->insert('ancora_engine_job')
      ->fields([
        'uuid' => $this->uuid->generate(),
        'scope_specialty' => $scope->specialty,
        'scope_level' => $scope->level,
        'scope_period' => $scope->period,
        'options' => json_encode($options->toArray(), JSON_THROW_ON_ERROR),
        'time_limit' => $timeLimit,
        'state' => JobState::Queued->value,
        'requested_by' => $requestedBy,
        'retry_of' => $retryOf,
        'created' => $now,
        'changed' => $now,
      ])
      ->execute();
  }

  /**
   * Loads a job.
   */
  public function load(int $id): ?Job {
    $row = $this->database->select('ancora_engine_job', 'j')
      ->fields('j')
      ->condition('id', $id)
      ->execute()
      ->fetchObject();
    return $row ? Job::fromRow($row) : NULL;
  }

  /**
   * The most recent jobs, newest first.
   *
   * @return \Drupal\ancora_engine\Job\Job[]
   *   Jobs keyed by id.
   */
  public function recent(int $limit = 50): array {
    $jobs = [];
    $rows = $this->database->select('ancora_engine_job', 'j')
      ->fields('j')
      ->orderBy('id', 'DESC')
      ->range(0, $limit)
      ->execute();
    foreach ($rows as $row) {
      $jobs[(int) $row->id] = Job::fromRow($row);
    }
    return $jobs;
  }

  /**
   * Claims the oldest queued job, unless a job of the site is running.
   *
   * Claims are serialized by a transaction-level advisory lock; the queued
   * row is selected FOR UPDATE SKIP LOCKED and switched with a conditional
   * UPDATE; the partial unique index on running jobs is the last guard.
   *
   * @return \Drupal\ancora_engine\Job\Job|null
   *   The claimed job (state running, attempts incremented), or NULL.
   */
  public function claim(string $workerId): ?Job {
    $transaction = $this->database->startTransaction();
    try {
      $this->database->query('SELECT pg_advisory_xact_lock(:key)', [':key' => self::CLAIM_LOCK]);
      $running = $this->database->query("SELECT 1 FROM {ancora_engine_job} WHERE state = 'running' LIMIT 1")->fetchField();
      if ($running) {
        $transaction->commitOrRelease();
        return NULL;
      }
      $id = $this->database->query("SELECT id FROM {ancora_engine_job} WHERE state = 'queued' ORDER BY id LIMIT 1 FOR UPDATE SKIP LOCKED")->fetchField();
      if (!$id) {
        $transaction->commitOrRelease();
        return NULL;
      }
      $now = $this->time->getCurrentTime();
      $claimed = $this->affected(
        "UPDATE {ancora_engine_job} SET state = 'running', worker_id = :worker, started = :now, heartbeat = :now, changed = :now, finished = NULL, attempts = attempts + 1 WHERE id = :id AND state = 'queued'",
        [':worker' => $workerId, ':now' => $now, ':id' => $id],
      );
      $transaction->commitOrRelease();
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }
    return $claimed === 1 ? $this->load((int) $id) : NULL;
  }

  /**
   * Records a heartbeat of a running job.
   *
   * @return bool
   *   FALSE when the run is fenced off (recovered, or no longer running).
   */
  public function heartbeat(Job $job): bool {
    return $this->affected(
      "UPDATE {ancora_engine_job} SET heartbeat = :now WHERE id = :id AND state = 'running' AND attempts = :attempt",
      [':now' => $this->time->getCurrentTime(), ':id' => $job->id, ':attempt' => $job->attempts],
    ) === 1;
  }

  /**
   * Whether a cancellation of the job has been requested, and how.
   *
   * @return string|null
   *   'soft', 'hard' or NULL.
   */
  public function cancelRequest(int $id): ?string {
    $row = $this->database->query('SELECT cancel_requested, cancel_mode FROM {ancora_engine_job} WHERE id = :id', [':id' => $id])->fetchObject();
    return ($row && $row->cancel_requested) ? ($row->cancel_mode ?: 'soft') : NULL;
  }

  /**
   * Records the outcome of a run.
   *
   * When $state is succeeded or partial and a cancellation was requested in
   * the meantime, the job ends cancelled instead: a cancelled run is never
   * presented as a final timetable.
   *
   * @return \Drupal\ancora_engine\Job\JobState|null
   *   The state written, or NULL when the run was fenced off.
   */
  public function finish(Job $job, JobState $state, array $result, ?string $error = NULL): ?JobState {
    if (!JobState::Running->canTransitionTo($state) || $state === JobState::Queued) {
      throw new \LogicException(sprintf('A run cannot end in state %s.', $state->value));
    }
    $returned = in_array($state, [JobState::Succeeded, JobState::Partial], TRUE);
    $state_sql = $returned ? "CASE WHEN cancel_requested = 1 THEN 'cancelled' ELSE :state END" : ':state';
    $now = $this->time->getCurrentTime();
    $updated = $this->affected(
      "UPDATE {ancora_engine_job} SET state = $state_sql, result = :result, error = :error, finished = :now, changed = :now WHERE id = :id AND state = 'running' AND attempts = :attempt",
      [
        ':state' => $state->value,
        ':result' => json_encode($result, JSON_THROW_ON_ERROR),
        ':error' => $error === NULL ? NULL : mb_substr($error, 0, 1024),
        ':now' => $now,
        ':id' => $job->id,
        ':attempt' => $job->attempts,
      ],
    );
    return $updated === 1 ? $this->load($job->id)?->state : NULL;
  }

  /**
   * Requests the cancellation of a job.
   *
   * A queued job is cancelled at once (it never ran). A running job is
   * flagged; the worker forwards the request to the engine (soft: a
   * generador.cancelar row; hard: pg_cancel_backend).
   *
   * @return \Drupal\ancora_engine\Job\JobState
   *   The state of the job after the request.
   *
   * @throws \InvalidArgumentException
   *   When the job does not exist or has already ended.
   */
  public function requestCancel(int $id, int $uid, bool $hard = FALSE): JobState {
    $now = $this->time->getCurrentTime();
    $queued = $this->affected(
      "UPDATE {ancora_engine_job} SET state = 'cancelled', cancel_requested = 1, cancel_mode = :mode, cancelled_by = :uid, finished = :now, changed = :now, result = :result WHERE id = :id AND state = 'queued'",
      [
        ':mode' => $hard ? 'hard' : 'soft',
        ':uid' => $uid,
        ':now' => $now,
        ':id' => $id,
        ':result' => json_encode(['kept' => 'none', 'partial' => FALSE], JSON_THROW_ON_ERROR),
      ],
    );
    if ($queued === 1) {
      return JobState::Cancelled;
    }
    // A hard request may upgrade an earlier soft one, never the reverse.
    $running = $this->affected(
      "UPDATE {ancora_engine_job} SET cancel_requested = 1, cancel_mode = CASE WHEN cancel_mode = 'hard' THEN 'hard' ELSE :mode END, cancelled_by = COALESCE(cancelled_by, :uid), changed = :now WHERE id = :id AND state = 'running'",
      [':mode' => $hard ? 'hard' : 'soft', ':uid' => $uid, ':now' => $now, ':id' => $id],
    );
    if ($running === 1) {
      return JobState::Running;
    }
    throw new \InvalidArgumentException(sprintf('Job %d does not exist or has already ended.', $id));
  }

  /**
   * Running jobs whose heartbeat is older than the given number of seconds.
   *
   * @return \Drupal\ancora_engine\Job\Job[]
   *   Jobs keyed by id.
   */
  public function stale(int $seconds): array {
    $jobs = [];
    $rows = $this->database->query(
      "SELECT * FROM {ancora_engine_job} WHERE state = 'running' AND COALESCE(heartbeat, started, 0) < :cutoff ORDER BY id",
      [':cutoff' => $this->time->getCurrentTime() - $seconds],
    );
    foreach ($rows as $row) {
      $jobs[(int) $row->id] = Job::fromRow($row);
    }
    return $jobs;
  }

  /**
   * Recovers a stale job whose engine run is known to be stopped.
   *
   * The job is requeued while it has attempts left; it fails after its last
   * attempt, and is cancelled when a cancellation was requested. The update
   * is fenced: it only applies to the attempt that was found stale.
   *
   * @return \Drupal\ancora_engine\Job\JobState|null
   *   The new state, or NULL when the job changed in the meantime.
   */
  public function recover(Job $job, int $maxAttempts, string $reason): ?JobState {
    if ($job->cancelRequested) {
      $state = JobState::Cancelled;
    }
    elseif ($job->attempts < $maxAttempts) {
      $state = JobState::Queued;
    }
    else {
      $state = JobState::Failed;
    }
    $now = $this->time->getCurrentTime();
    $terminal = $state !== JobState::Queued;
    $updated = $this->affected(
      "UPDATE {ancora_engine_job} SET state = :state, worker_id = NULL, heartbeat = NULL, error = :error, changed = :now, finished = :finished, result = :result WHERE id = :id AND state = 'running' AND attempts = :attempt",
      [
        ':state' => $state->value,
        ':error' => mb_substr($reason, 0, 1024),
        ':now' => $now,
        ':finished' => $terminal ? $now : NULL,
        ':result' => $terminal ? json_encode(['kept' => 'none', 'partial' => FALSE], JSON_THROW_ON_ERROR) : NULL,
        ':id' => $job->id,
        ':attempt' => $job->attempts,
      ],
    );
    return $updated === 1 ? $state : NULL;
  }

  /**
   * Runs a data-changing statement.
   *
   * @return int
   *   The number of affected rows.
   */
  private function affected(string $sql, array $args): int {
    $statement = $this->database->prepareStatement($sql, [], TRUE);
    $statement->execute($args);
    return $statement->rowCount();
  }

}
