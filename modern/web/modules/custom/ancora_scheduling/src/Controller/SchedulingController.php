<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Controller;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Timetable\TimetableReader;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The status page of a requested run, and the timetable of a plan.
 */
final class SchedulingController extends ControllerBase {

  use AutowireTrait;

  /**
   * The filters of the timetable: query key => reader list.
   */
  private const FILTERS = ['group' => 'groups', 'teacher' => 'teachers', 'room' => 'rooms'];

  public function __construct(
    #[Autowire(service: 'ancora_engine.job_repository')]
    private readonly JobRepository $jobs,
    #[Autowire(service: 'ancora_scheduling.timetable')]
    private readonly TimetableReader $reader,
    private readonly DateFormatterInterface $dateFormatter,
    private readonly RequestStack $requestStack,
  ) {}

  /**
   * The status of one run: state, outcome and the way to its timetable.
   */
  public function job(string|int $job): array {
    $entry = $this->jobs->load((int) $job);
    if ($entry === NULL) {
      throw new NotFoundHttpException();
    }
    $result = $entry->result ?? [];
    $rows = [];
    $plan = NULL;
    if ($entry->source !== NULL) {
      $storage = $this->entityTypeManager();
      $plan = $storage->getStorage('ancora_plan')->load($entry->source->planId);
      $records = [
        (string) $this->t('Plan') => $plan,
        (string) $this->t('Period') => $storage->getStorage('ancora_period')->load($entry->source->periodId),
        (string) $this->t('Specialty') => $storage->getStorage('ancora_specialty')->load($entry->source->specialtyId),
        (string) $this->t('Level') => $storage->getStorage('ancora_level')->load($entry->source->levelId),
      ];
      foreach ($records as $title => $record) {
        $rows[] = [$title, $record === NULL ? $this->t('(deleted)') : $record->label()];
      }
    }
    else {
      $rows[] = [$this->t('Engine scope'), $entry->scope->label()];
    }
    $rows[] = [$this->t('State'), self::stateLabel($entry)];
    $rows[] = [$this->t('Queued'), $this->dateFormatter->format($entry->created, 'short')];
    if ($entry->finished !== NULL) {
      $rows[] = [$this->t('Finished'), $this->dateFormatter->format($entry->finished, 'short')];
    }
    $metrics = [
      'demands' => $this->t('Activities to place'),
      'placed' => $this->t('Placed'),
      'unplaced' => $this->t('Not placed'),
      'collisions' => $this->t('Collisions'),
      'overflow' => $this->t('Overflow'),
      'digest' => $this->t('Placement digest'),
      'input_hash' => $this->t('Input snapshot (sha256)'),
    ];
    foreach ($metrics as $key => $title) {
      if (isset($result[$key]) && is_scalar($result[$key])) {
        $rows[] = [$title, (string) $result[$key]];
      }
    }
    if ($entry->error !== NULL) {
      $rows[] = [$this->t('Error'), $entry->error];
    }
    $build['details'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#attributes' => ['class' => ['ancora-scheduling-job']],
    ];
    $links = [];
    if ($plan instanceof Plan && $plan->access('view')) {
      $links[] = [
        'title' => $this->t('Timetable of this plan'),
        'url' => Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => $plan->id()], ['query' => ['period' => $entry->source->periodId]]),
      ];
    }
    $cancel = Url::fromRoute('ancora_engine.job_cancel', ['job' => $entry->id]);
    if ($cancel->access()) {
      $links[] = ['title' => $this->t('Cancel this run'), 'url' => $cancel];
    }
    $build['links'] = ['#theme' => 'links', '#links' => $links];
    $build['#cache']['max-age'] = 0;
    return $build;
  }

  /**
   * Title of the status page.
   */
  public function jobTitle(string|int $job): TranslatableMarkup {
    return $this->t('Timetable run @id', ['@id' => (int) $job]);
  }

  /**
   * The timetable of a plan and one of its periods.
   */
  public function timetable(Plan $ancora_plan): array {
    $plan = $ancora_plan;
    $cache = (new CacheableMetadata())
      ->addCacheContexts([
        'user.permissions',
        'url.query_args:period',
        'url.query_args:group',
        'url.query_args:teacher',
        'url.query_args:room',
      ])
      ->addCacheTags([JobRepository::CACHE_TAG])
      ->addCacheableDependency($plan);
    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      $cache->addCacheTags($this->entityTypeManager()->getDefinition($entity_type_id)->getListCacheTags());
    }

    $periods = $this->entityTypeManager()->getStorage('ancora_period')->loadByProperties(['plan' => $plan->id()]);
    uasort($periods, static fn (Period $a, Period $b): int => strcmp($a->getCode(), $b->getCode()));
    $requested = (int) $this->requestStack->getCurrentRequest()->query->get('period');
    $period = $periods[$requested] ?? reset($periods);
    if (!$period instanceof Period) {
      $build = ['#markup' => '<p>' . $this->t('This plan has no period yet.') . '</p>'];
      $cache->applyTo($build);
      return $build;
    }

    $data = $this->reader->read($plan, $period);
    $filters = [];
    $query = $this->requestStack->getCurrentRequest()->query;
    foreach (self::FILTERS as $key => $list) {
      $value = (string) $query->get($key, '');
      $filters[$key] = [
        'options' => $data[$list],
        'value' => isset($data[$list][$value]) ? $value : '',
      ];
    }

    $scopes = [];
    foreach ($data['scopes'] as $scope) {
      if ($scope['error'] !== NULL) {
        $cache->setCacheMaxAge(0);
      }
      $placements = array_filter($scope['placements'], static function (array $p) use ($filters): bool {
        return ($filters['group']['value'] === '' || $p['group_code'] === $filters['group']['value'])
          && ($filters['teacher']['value'] === '' || $p['teacher_code'] === $filters['teacher']['value'])
          && ($filters['room']['value'] === '' || $p['room_code'] === $filters['room']['value']);
      });
      $scopes[] = [
        'title' => $this->t('@specialty, @level', ['@specialty' => $scope['specialty'], '@level' => $scope['level']]),
        'final' => $scope['final'],
        'draft' => $scope['job'] !== NULL && !$scope['final'],
        'status' => $this->scopeStatus($scope),
        'stale' => $scope['stale'],
        'active' => $scope['active'] === NULL ? NULL : $this->t('Job @id is @state.', [
          '@id' => $scope['active']->id,
          '@state' => $scope['active']->state->value,
        ]),
        'error' => $scope['error'] === NULL ? NULL : $this->t('The scheduling engine could not be reached; try again later.'),
        'job_url' => $scope['job'] === NULL ? NULL : Url::fromRoute('ancora_scheduling.job', ['job' => $scope['job']->id])->toString(),
        'count' => count($scope['placements']),
        'grid' => $scope['job'] === NULL ? NULL : $this->grid($period, $placements),
      ];
    }

    $period_links = [];
    foreach ($periods as $id => $candidate) {
      $period_links[] = [
        'label' => $candidate->label(),
        'url' => Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => $plan->id()], ['query' => ['period' => $id]])->toString(),
        'current' => $id === (int) $period->id(),
      ];
    }
    $request_url = Url::fromRoute('ancora_scheduling.request', [], ['query' => ['plan' => $plan->id()]]);

    $build = [
      '#theme' => 'ancora_scheduling_timetable',
      '#period' => $period->label(),
      '#period_id' => (int) $period->id(),
      '#periods' => count($period_links) > 1 ? $period_links : [],
      '#filters' => $filters,
      '#scopes' => $scopes,
      '#request_url' => $request_url->access() ? $request_url->toString() : NULL,
      '#attached' => ['library' => ['ancora_scheduling/timetable']],
    ];
    $cache->applyTo($build);
    return $build;
  }

  /**
   * Title of the timetable page.
   */
  public function timetableTitle(Plan $ancora_plan): TranslatableMarkup {
    return $this->t('Timetable: @plan', ['@plan' => $ancora_plan->label()]);
  }

  /**
   * The day by slot grid of a period.
   */
  private function grid(Period $period, array $placements): array {
    $days = range(1, max(1, (int) $period->getDays()));
    $slots = [];
    for ($slot = 1; $slot <= max(1, (int) $period->getSlotsPerDay()); $slot++) {
      $start = $period->getStartHour() + ($slot - 1) * $period->getSlotDuration();
      $cells = [];
      foreach ($days as $day) {
        $cells[$day] = array_values(array_filter($placements, static fn (array $p): bool => $p['day'] === $day && $slot >= $p['slot'] && $slot < $p['slot'] + $p['slots']));
      }
      $slots[] = [
        'number' => $slot,
        'time' => sprintf('%02d:%02d', (int) floor($start), (int) round(fmod($start, 1) * 60)),
        'cells' => $cells,
      ];
    }
    return ['days' => $days, 'slots' => $slots];
  }

  /**
   * What the placements of a scope are.
   */
  private function scopeStatus(array $scope): TranslatableMarkup {
    $job = $scope['job'];
    if ($job === NULL) {
      return $this->t('No timetable yet.');
    }
    if ($scope['final']) {
      return $this->t('Final timetable, generated by job @id.', ['@id' => $job->id]);
    }
    if ($job->state === JobState::Partial) {
      return $this->t('No final timetable. Draft of job @id: some activities could not be placed.', ['@id' => $job->id]);
    }
    return $this->t('No final timetable. Draft of job @id: the run was cancelled, these are the placements made before the cancellation.', ['@id' => $job->id]);
  }

  /**
   * A state label that never presents a draft as a final timetable.
   */
  public static function stateLabel(Job $job): TranslatableMarkup {
    $kept = $job->result['kept'] ?? 'none';
    return match ($job->state) {
      JobState::Queued => new TranslatableMarkup('Queued'),
      JobState::Running => $job->cancelRequested ? new TranslatableMarkup('Running (cancellation requested)') : new TranslatableMarkup('Running'),
      JobState::Succeeded => new TranslatableMarkup('Succeeded: final timetable'),
      JobState::Partial => new TranslatableMarkup('Partial: draft, some activities could not be placed'),
      JobState::Cancelled => $kept === 'none' ? new TranslatableMarkup('Cancelled: nothing kept') : new TranslatableMarkup('Cancelled: draft with the placements made before the cancellation'),
      JobState::TimedOut => new TranslatableMarkup('Timed out: nothing kept'),
      JobState::Failed => new TranslatableMarkup('Failed: nothing kept'),
    };
  }

}
