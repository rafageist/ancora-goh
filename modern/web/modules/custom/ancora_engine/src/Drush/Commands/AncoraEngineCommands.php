<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Drush\Commands;

use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_engine\Worker;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Drush commands of the scheduling engine worker.
 */
final class AncoraEngineCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(
    #[Autowire(service: 'ancora_engine.job_repository')]
    private readonly JobRepository $jobs,
    #[Autowire(service: 'ancora_engine.worker')]
    private readonly Worker $worker,
    #[Autowire(service: 'ancora_engine.settings')]
    private readonly EngineSettings $settings,
  ) {
    parent::__construct();
  }

  /**
   * Queues a timetable generation job.
   */
  #[CLI\Command(name: 'ancora-engine:enqueue')]
  #[CLI\Option(name: 'specialty', description: 'Specialty id in the engine database.')]
  #[CLI\Option(name: 'level', description: 'Level id in the engine database.')]
  #[CLI\Option(name: 'period', description: 'Period id in the engine database.')]
  #[CLI\Option(name: 'forma', description: 'Prioritization: 1 (class order) or 2 (activity count).')]
  #[CLI\Option(name: 'parche', description: 'Group activities by class (0/1).')]
  #[CLI\Option(name: 'compactar', description: 'Compaction, 0 to 10.')]
  #[CLI\Option(name: 'parche1', description: 'Maximize combinations per day (0/1).')]
  #[CLI\Option(name: 'buscalugar', description: 'Most used place (1) or nearest place (0).')]
  #[CLI\Option(name: 'time-limit', description: 'Seconds; at most the plan limit (default: the site setting).')]
  #[CLI\Option(name: 'requested-by', description: 'Drupal user id recorded as requester (default 0, the system).')]
  #[CLI\Usage(name: 'drush ancora-engine:enqueue --specialty=E --level=N --period=PER', description: 'Queue a generation with the default options.')]
  public function enqueue(
    array $options = [
      'specialty' => self::REQ,
      'level' => self::REQ,
      'period' => self::REQ,
      'forma' => '1',
      'parche' => '0',
      'compactar' => '0',
      'parche1' => '0',
      'buscalugar' => '0',
      'time-limit' => self::OPT,
      'requested-by' => '0',
    ],
  ): int {
    $scope = new JobScope((string) $options['specialty'], (string) $options['level'], (string) $options['period']);
    $job_options = JobOptions::fromArray([
      'forma' => (string) $options['forma'],
      'parche' => (string) $options['parche'],
      'compactar' => (string) $options['compactar'],
      'parche1' => (string) $options['parche1'],
      'buscalugar' => (string) $options['buscalugar'],
    ]);
    $limit = $options['time-limit'] === NULL || $options['time-limit'] === TRUE ? $this->settings->timeLimit() : (int) $options['time-limit'];
    if ($limit < 1 || $limit > $this->settings->maxTimeLimit()) {
      throw new \InvalidArgumentException(sprintf('The time limit must be between 1 and %d seconds.', $this->settings->maxTimeLimit()));
    }
    $id = $this->jobs->enqueue($scope, $job_options, $limit, (int) $options['requested-by']);
    $this->output()->writeln((string) $id);
    return self::EXIT_SUCCESS;
  }

  /**
   * Runs queued jobs of this site, one at a time.
   */
  #[CLI\Command(name: 'ancora-engine:work')]
  #[CLI\Option(name: 'once', description: 'Run at most one job, then exit.')]
  #[CLI\Option(name: 'max-jobs', description: 'Exit after this many jobs (0: no limit).')]
  #[CLI\Option(name: 'max-runtime', description: 'Do not claim a new job after this many seconds (0: no limit).')]
  #[CLI\Option(name: 'idle-sleep', description: 'Seconds to wait when no job is queued.')]
  #[CLI\Option(name: 'worker-id', description: 'Worker id (default: host:pid:random).')]
  #[CLI\Usage(name: 'drush ancora-engine:work --once', description: 'Run the oldest queued job, if any.')]
  public function work(
    array $options = [
      'once' => FALSE,
      'max-jobs' => '0',
      'max-runtime' => '0',
      'idle-sleep' => '5',
      'worker-id' => self::OPT,
    ],
  ): int {
    $worker_id = is_string($options['worker-id']) && $options['worker-id'] !== '' ? $options['worker-id'] : Worker::newWorkerId();
    $max_jobs = $options['once'] ? 1 : max(0, (int) $options['max-jobs']);
    $max_runtime = max(0, (int) $options['max-runtime']);
    $idle = max(1, (int) $options['idle-sleep']);
    $stop = FALSE;
    if (function_exists('pcntl_signal')) {
      pcntl_async_signals(TRUE);
      $handler = static function () use (&$stop): void {
        $stop = TRUE;
      };
      pcntl_signal(SIGTERM, $handler);
      pcntl_signal(SIGINT, $handler);
    }
    $started = time();
    $done = 0;
    while (!$stop) {
      $job = $this->worker->runOnce($worker_id);
      if ($job !== NULL) {
        $done++;
        $this->output()->writeln(sprintf('job %d %s', $job->id, $job->state->value));
      }
      if ($options['once'] || ($max_jobs > 0 && $done >= $max_jobs) || ($max_runtime > 0 && time() - $started >= $max_runtime)) {
        break;
      }
      if ($job === NULL) {
        sleep($idle);
      }
    }
    return self::EXIT_SUCCESS;
  }

  /**
   * Requests the cancellation of a job.
   */
  #[CLI\Command(name: 'ancora-engine:cancel')]
  #[CLI\Argument(name: 'id', description: 'Job id.')]
  #[CLI\Option(name: 'hard', description: 'Cancel the running statement (pg_cancel_backend): the run is rolled back.')]
  #[CLI\Option(name: 'by', description: 'Drupal user id recorded as canceller (default 0).')]
  public function cancel(string $id, array $options = ['hard' => FALSE, 'by' => '0']): int {
    $state = $this->worker->cancel((int) $id, (int) $options['by'], (bool) $options['hard']);
    $this->output()->writeln(sprintf('job %d %s%s', (int) $id, $state->value, $state->isTerminal() ? '' : ' (cancellation requested)'));
    return self::EXIT_SUCCESS;
  }

  /**
   * Queues a new job with the scope and options of an earlier one.
   */
  #[CLI\Command(name: 'ancora-engine:retry')]
  #[CLI\Argument(name: 'id', description: 'Id of the job to retry.')]
  #[CLI\Option(name: 'requested-by', description: 'Drupal user id recorded as requester (default 0).')]
  public function retry(string $id, array $options = ['requested-by' => '0']): int {
    $job = $this->jobs->load((int) $id);
    if ($job === NULL || !$job->state->isTerminal()) {
      throw new \InvalidArgumentException('Only an ended job can be retried.');
    }
    $new = $this->jobs->enqueue($job->scope, $job->options, min($job->timeLimit, $this->settings->maxTimeLimit()), (int) $options['requested-by'], $job->id);
    $this->output()->writeln((string) $new);
    return self::EXIT_SUCCESS;
  }

  /**
   * Recovers running jobs whose worker stopped sending heartbeats.
   */
  #[CLI\Command(name: 'ancora-engine:recover')]
  public function recover(): int {
    foreach ($this->worker->recoverStale() as $id => $state) {
      $this->output()->writeln(sprintf('job %d %s', $id, $state->value));
    }
    return self::EXIT_SUCCESS;
  }

  /**
   * Prints a job as JSON.
   */
  #[CLI\Command(name: 'ancora-engine:job')]
  #[CLI\Argument(name: 'id', description: 'Job id.')]
  public function job(string $id): int {
    $job = $this->jobs->load((int) $id);
    if ($job === NULL) {
      throw new \InvalidArgumentException('No such job.');
    }
    $this->output()->writeln(json_encode(self::export($job), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    return self::EXIT_SUCCESS;
  }

  /**
   * A job as a plain array.
   */
  private static function export(Job $job): array {
    return [
      'id' => $job->id,
      'uuid' => $job->uuid,
      'scope' => [
        'specialty' => $job->scope->specialty,
        'level' => $job->scope->level,
        'period' => $job->scope->period,
      ],
      'options' => $job->options->toArray(),
      'time_limit' => $job->timeLimit,
      'state' => $job->state->value,
      'final_timetable' => $job->state->isFinalTimetable(),
      'cancel_requested' => $job->cancelRequested,
      'cancel_mode' => $job->cancelMode,
      'requested_by' => $job->requestedBy,
      'retry_of' => $job->retryOf,
      'created' => $job->created,
      'started' => $job->started,
      'finished' => $job->finished,
      'heartbeat' => $job->heartbeat,
      'worker_id' => $job->workerId,
      'attempts' => $job->attempts,
      'result' => $job->result,
      'error' => $job->error,
    ];
  }

}
