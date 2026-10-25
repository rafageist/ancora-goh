<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Controller;

use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Status pages of the generation jobs. They only read the job table.
 */
final class JobController extends ControllerBase {

  use AutowireTrait;

  public function __construct(
    #[Autowire(service: 'ancora_engine.job_repository')]
    private readonly JobRepository $jobs,
    private readonly DateFormatterInterface $dateFormatter,
  ) {}

  /**
   * The list of recent jobs.
   */
  public function list(): array {
    $rows = [];
    foreach ($this->jobs->recent() as $job) {
      $rows[] = [
        Link::createFromRoute((string) $job->id, 'ancora_engine.job', ['job' => $job->id]),
        $job->scope->label(),
        $this->stateLabel($job),
        $this->placedSummary($job),
        $this->dateFormatter->format($job->created, 'short'),
      ];
    }
    $build['add'] = [
      '#type' => 'link',
      '#title' => $this->t('Queue a timetable generation'),
      '#url' => Url::fromRoute('ancora_engine.job_add'),
      '#access' => $this->currentUser()->hasPermission('request ancora engine jobs'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
    $build['jobs'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Job'),
        $this->t('Scope'),
        $this->t('State'),
        $this->t('Placed'),
        $this->t('Queued'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('No generation job yet.'),
    ];
    $build['#cache']['max-age'] = 0;
    return $build;
  }

  /**
   * One job.
   */
  public function view(string|int $job): array {
    $entry = $this->load((int) $job);
    $result = $entry->result ?? [];
    $items = [
      [$this->t('Scope'), $entry->scope->label()],
      [$this->t('State'), $this->stateLabel($entry)],
      [$this->t('Options'), json_encode($entry->options->toArray(), JSON_THROW_ON_ERROR)],
      [$this->t('Time limit'), $this->t('@s s', ['@s' => $entry->timeLimit])],
      [$this->t('Attempts'), (string) $entry->attempts],
      [$this->t('Cancellation requested'), $entry->cancelRequested ? ($entry->cancelMode ?? 'soft') : $this->t('No')],
    ];
    foreach (['demands', 'placed', 'unplaced', 'collisions', 'overflow', 'digest', 'duration_ms', 'kept'] as $key) {
      if (array_key_exists($key, $result)) {
        $items[] = [$key, is_scalar($result[$key]) ? (string) $result[$key] : json_encode($result[$key])];
      }
    }
    if ($entry->error !== NULL) {
      $items[] = [$this->t('Error'), $entry->error];
    }
    $build['details'] = [
      '#type' => 'table',
      '#rows' => $items,
    ];
    $build['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel this job'),
      '#url' => Url::fromRoute('ancora_engine.job_cancel', ['job' => $entry->id]),
      '#access' => Url::fromRoute('ancora_engine.job_cancel', ['job' => $entry->id])->access(),
      '#attributes' => ['class' => ['button', 'button--danger']],
    ];
    $build['#cache']['max-age'] = 0;
    return $build;
  }

  /**
   * Title of the job page.
   */
  public function title(string|int $job): TranslatableMarkup {
    return $this->t('Generation job @id', ['@id' => (int) $job]);
  }

  /**
   * Loads a job or responds 404.
   */
  private function load(int $id): Job {
    $job = $this->jobs->load($id);
    if ($job === NULL) {
      throw new NotFoundHttpException();
    }
    return $job;
  }

  /**
   * A state label that never presents a draft as a final timetable.
   */
  private function stateLabel(Job $job): TranslatableMarkup {
    $kept = $job->result['kept'] ?? 'none';
    return match ($job->state) {
      JobState::Queued => $this->t('Queued'),
      JobState::Running => $job->cancelRequested ? $this->t('Running (cancellation requested)') : $this->t('Running'),
      JobState::Succeeded => $this->t('Succeeded: final timetable'),
      JobState::Partial => $this->t('Partial: draft, some activities could not be placed'),
      JobState::Cancelled => $kept === 'none' ? $this->t('Cancelled: nothing kept') : $this->t('Cancelled: draft with the placements made before the cancellation'),
      JobState::TimedOut => $this->t('Timed out: nothing kept'),
      JobState::Failed => $this->t('Failed: nothing kept'),
    };
  }

  /**
   * The "placed / demands" summary, when known.
   */
  private function placedSummary(Job $job): string {
    $result = $job->result ?? [];
    return isset($result['placed'], $result['demands']) ? $result['placed'] . ' / ' . $result['demands'] : '';
  }

}
