<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Controller;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\JobRepository;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The planner workspace (#105): landing page, plan page, timetables, runs.
 *
 * Presentation only: every list and count comes from access-checked entity
 * queries, so a user sees only their own records (the legacy creador rule,
 * AcademicQueryAccess), and only the jobs of the plans they may view. The
 * pages vary by user and permissions and carry the list cache tags of the
 * records they count.
 */
final class WorkspaceController extends ControllerBase {

  use AutowireTrait;

  /**
   * The records of a plan shown on its page, in data entry order.
   */
  private const PLAN_RECORDS = [
    'ancora_period',
    'ancora_student_group',
    'ancora_breakdown',
    'ancora_activity_template',
    'ancora_teacher_assignment',
    'ancora_room_assignment',
  ];

  /**
   * The number of recent runs on the landing and plan pages.
   */
  private const RECENT_RUNS = 5;

  public function __construct(
    #[Autowire(service: 'ancora_engine.job_repository')]
    private readonly JobRepository $jobs,
    private readonly DateFormatterInterface $dateFormatter,
  ) {}

  /**
   * The landing page of the organization site.
   */
  public function landing(): array {
    $cache = (new CacheableMetadata())->addCacheContexts(['user.roles:authenticated', 'user.permissions']);
    $account = $this->currentUser();

    if ($account->isAnonymous()) {
      $build = [
        '#theme' => 'ancora_workspace_welcome',
        '#title' => $this->t('Welcome'),
        '#login_url' => Url::fromRoute('user.login')->toString(),
      ];
      $cache->applyTo($build);
      return $build;
    }
    if (!$account->hasPermission(AcademicAccessControlHandler::VIEW) && !$account->hasPermission(AcademicAccessControlHandler::ADMINISTER)) {
      $build = [
        '#theme' => 'ancora_workspace_welcome',
        '#no_access' => TRUE,
      ];
      $cache->applyTo($build);
      return $build;
    }

    $cache->addCacheContexts(['user']);
    $plans = $this->viewablePlans($cache);
    $rows = [];
    foreach ($plans as $plan) {
      $counts = [];
      foreach (['ancora_period', 'ancora_student_group', 'ancora_activity_template'] as $entity_type_id) {
        $counts[] = $this->count($entity_type_id, $cache, (int) $plan->id());
      }
      $rows[] = [
        Link::fromTextAndUrl($plan->label(), Url::fromRoute('ancora_scheduling.plan', ['ancora_plan' => $plan->id()])),
        $plan->getCode(),
        $plan->isActive() ? $this->t('Yes') : $this->t('No'),
        ...$counts,
        ['data' => $this->planActions($plan)],
      ];
    }
    $create = $this->addLink('ancora_plan', $this->t('Create a plan'), $cache);

    $build = [
      '#theme' => 'ancora_workspace_landing',
      '#plans' => $rows === [] ? NULL : [
        '#type' => 'table',
        '#header' => [
          $this->t('Plan'),
          $this->t('Code'),
          $this->t('Active'),
          $this->t('Periods'),
          $this->t('Student groups'),
          $this->t('Activity templates'),
          $this->t('Actions'),
        ],
        '#rows' => $rows,
        '#attributes' => ['class' => ['ancora-workspace__plans']],
      ],
      '#create' => $create,
      '#getting_started' => $rows === [] ? $this->gettingStarted() : NULL,
      '#catalogues' => $this->catalogueCounts($cache),
      '#quick_links' => $this->quickLinks($plans),
      '#runs' => $this->runsTable($this->jobs->forPlans(array_keys($plans), self::RECENT_RUNS), NULL, FALSE),
      '#attached' => ['library' => ['ancora_scheduling/workspace']],
    ];
    $cache->addCacheTags([JobRepository::CACHE_TAG]);
    $cache->applyTo($build);
    return $build;
  }

  /**
   * The page of one plan: periods, records, catalogues, actions and runs.
   */
  public function plan(Plan $ancora_plan): array {
    $plan = $ancora_plan;
    $cache = (new CacheableMetadata())
      ->addCacheContexts(['user', 'user.permissions'])
      ->addCacheTags([JobRepository::CACHE_TAG])
      ->addCacheableDependency($plan);

    $facts = [
      [$this->t('Code'), $plan->getCode()],
      [$this->t('First day'), $plan->getStartDate() ?? $this->t('Not set')],
      [$this->t('Length in days'), (string) $plan->getDurationDays()],
      [$this->t('Active'), $plan->isActive() ? $this->t('Yes') : $this->t('No')],
    ];

    $actions = [];
    $edit = $plan->toUrl('edit-form', ['query' => $this->getDestinationArray()]);
    $cache->addCacheableDependency($edit->access(NULL, TRUE));
    if ($edit->access()) {
      $actions[] = ['title' => $this->t('Edit plan'), 'url' => $edit];
    }
    $request = Url::fromRoute('ancora_scheduling.request', [], ['query' => ['plan' => $plan->id()]]);
    if ($request->access()) {
      $actions[] = ['title' => $this->t('Request a timetable'), 'url' => $request];
    }
    $timetable = Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => $plan->id()]);
    $actions[] = ['title' => $this->t('View timetable'), 'url' => $timetable];

    // The periods, each with its timetable.
    $period_storage = $this->entityTypeManager()->getStorage('ancora_period');
    $period_ids = $period_storage->getQuery()->accessCheck(TRUE)->condition('plan', $plan->id())->sort('code')->execute();
    $cache->addCacheTags($this->entityTypeManager()->getDefinition('ancora_period')->getListCacheTags());
    $period_rows = [];
    foreach ($period_storage->loadMultiple($period_ids) as $period) {
      assert($period instanceof Period);
      $links = [
        'timetable' => [
          'title' => $this->t('Timetable'),
          'url' => Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => $plan->id()], ['query' => ['period' => $period->id()]]),
        ],
      ];
      $period_edit = $period->toUrl('edit-form', ['query' => $this->getDestinationArray()]);
      if ($period_edit->access()) {
        $links['edit'] = ['title' => $this->t('Edit'), 'url' => $period_edit];
      }
      // The breakdown options of the period's subjects (legacy desglose).
      $links['breakdowns'] = [
        'title' => $this->t('Subject breakdowns'),
        'url' => Url::fromRoute('entity.ancora_breakdown.collection', [], ['query' => ['period' => $period->id()]]),
      ];
      $period_rows[] = [
        $period->label(),
        $period->getCode(),
        (string) $period->getDays(),
        (string) $period->getSlotsPerDay(),
        self::hour($period->getStartHour()),
        (string) $period->getSlotDuration(),
        [
          'data' => [
            '#theme' => 'links',
            '#links' => $links,
            '#attributes' => ['class' => ['ancora-inline-links']],
          ],
        ],
      ];
    }

    // The plan's own records and the owner's catalogues, with counts.
    $records = [];
    foreach (self::PLAN_RECORDS as $entity_type_id) {
      $records[] = $this->recordSummary($entity_type_id, $cache, $plan);
    }
    $catalogues = [];
    foreach (AcademicEntityTypes::OWNED as $entity_type_id) {
      if ($entity_type_id !== 'ancora_plan') {
        $catalogues[] = $this->recordSummary($entity_type_id, $cache);
      }
    }

    $build = [
      '#theme' => 'ancora_workspace_plan',
      '#facts' => $facts,
      '#actions' => ['#theme' => 'links', '#links' => $actions, '#attributes' => ['class' => ['ancora-actions']]],
      '#periods' => [
        '#type' => 'table',
        '#caption' => $this->t('Periods'),
        '#header' => [
          $this->t('Period'),
          $this->t('Code'),
          $this->t('Days'),
          $this->t('Slots per day'),
          $this->t('First hour'),
          $this->t('Slot length (hours)'),
          $this->t('Actions'),
        ],
        '#rows' => $period_rows,
        '#empty' => $this->t('This plan has no period yet. Add one to define the weekly time grid.'),
      ],
      '#records' => $records,
      '#catalogues' => $catalogues,
      '#runs' => $this->runsTable($this->jobs->forPlans([(int) $plan->id()], self::RECENT_RUNS), $this->t('Recent timetable runs of this plan'), TRUE),
      '#attached' => ['library' => ['ancora_scheduling/workspace']],
    ];
    $cache->applyTo($build);
    return $build;
  }

  /**
   * Title of the plan page.
   */
  public function planTitle(Plan $ancora_plan): string {
    return (string) $ancora_plan->label();
  }

  /**
   * Every plan and period of the user, each with its timetable.
   */
  public function timetables(): array {
    $cache = (new CacheableMetadata())
      ->addCacheContexts(['user', 'user.permissions'])
      ->addCacheTags([JobRepository::CACHE_TAG])
      ->addCacheTags($this->entityTypeManager()->getDefinition('ancora_period')->getListCacheTags());
    $plans = $this->viewablePlans($cache);
    $rows = [];
    $period_storage = $this->entityTypeManager()->getStorage('ancora_period');
    foreach ($plans as $plan) {
      $ids = $period_storage->getQuery()->accessCheck(TRUE)->condition('plan', $plan->id())->sort('code')->execute();
      $periods = $period_storage->loadMultiple($ids);
      if ($periods === []) {
        $rows[] = [
          Link::fromTextAndUrl($plan->label(), Url::fromRoute('ancora_scheduling.plan', ['ancora_plan' => $plan->id()])),
          ['data' => $this->t('No period yet'), 'colspan' => 3],
        ];
        continue;
      }
      foreach ($periods as $period) {
        $latest = $this->jobs->forSource((int) $plan->id(), (int) $period->id(), 1);
        $job = $latest === [] ? NULL : reset($latest);
        $rows[] = [
          Link::fromTextAndUrl($plan->label(), Url::fromRoute('ancora_scheduling.plan', ['ancora_plan' => $plan->id()])),
          $period->label(),
          $job instanceof Job ? SchedulingController::stateLabel($job) : $this->t('No run yet'),
          Link::fromTextAndUrl($this->t('View timetable'), Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => $plan->id()], ['query' => ['period' => $period->id()]])),
        ];
      }
    }
    $build = [
      'intro' => [
        '#markup' => '<p>' . $this->t('The timetable of each of your plans and periods. A draft is never a final timetable.') . '</p>',
      ],
      'table' => [
        '#type' => 'table',
        '#header' => [$this->t('Plan'), $this->t('Period'), $this->t('Latest run'), $this->t('Timetable')],
        '#rows' => $rows,
        '#empty' => $this->t('You have no plan yet.'),
      ],
    ];
    $cache->applyTo($build);
    return $build;
  }

  /**
   * The timetable runs of the user's plans.
   */
  public function jobs(): array {
    $cache = (new CacheableMetadata())->addCacheContexts(['user', 'user.permissions']);
    $plans = $this->viewablePlans($cache);
    $build = [
      'intro' => [
        '#markup' => '<p>' . $this->t('The timetable runs of your plans, newest first. The engine worker runs them in the background.') . '</p>',
      ],
      'table' => $this->runsTable($this->jobs->forPlans(array_keys($plans), 50), NULL, FALSE),
    ];
    $request = Url::fromRoute('ancora_scheduling.request');
    if ($request->access()) {
      $build['request'] = [
        '#type' => 'link',
        '#title' => $this->t('Request a timetable'),
        '#url' => $request,
        '#attributes' => ['class' => ['button', 'button--primary']],
      ];
    }
    $cache->applyTo($build);
    $build['#cache']['max-age'] = 0;
    return $build;
  }

  /**
   * The plans the user may view, by id, sorted by label.
   *
   * @return \Drupal\ancora_academic\Entity\Plan[]
   *   The plans.
   */
  private function viewablePlans(CacheableMetadata $cache): array {
    $storage = $this->entityTypeManager()->getStorage('ancora_plan');
    $cache->addCacheTags($this->entityTypeManager()->getDefinition('ancora_plan')->getListCacheTags());
    $ids = $storage->getQuery()->accessCheck(TRUE)->sort('label')->sort('id')->execute();
    $plans = [];
    foreach ($storage->loadMultiple($ids) as $id => $plan) {
      $access = $plan->access('view', NULL, TRUE);
      $cache->addCacheableDependency($access);
      if ($access->isAllowed() && $plan instanceof Plan) {
        $plans[(int) $id] = $plan;
      }
    }
    return $plans;
  }

  /**
   * The number of records of a type the user may see, optionally of a plan.
   */
  private function count(string $entity_type_id, CacheableMetadata $cache, ?int $plan_id = NULL): int {
    $cache->addCacheTags($this->entityTypeManager()->getDefinition($entity_type_id)->getListCacheTags());
    $query = $this->entityTypeManager()->getStorage($entity_type_id)->getQuery()->accessCheck(TRUE);
    if ($plan_id !== NULL) {
      $query->condition('plan', $plan_id);
    }
    return (int) $query->count()->execute();
  }

  /**
   * A link to the add form of a type, or NULL without access.
   */
  private function addLink(string $entity_type_id, TranslatableMarkup $title, CacheableMetadata $cache, array $query = []): ?array {
    $url = Url::fromRoute("entity.$entity_type_id.add_form", [], ['query' => $query]);
    $access = $url->access(NULL, TRUE);
    $cache->addCacheableDependency($access);
    if (!$access->isAllowed()) {
      return NULL;
    }
    return [
      '#type' => 'link',
      '#title' => $title,
      '#url' => $url,
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
  }

  /**
   * One record type: label, count, list link and add link.
   *
   * With a plan, the list is narrowed to the plan and a new record is
   * pre-filled with it (AcademicListBuilder, AcademicEntityForm).
   */
  private function recordSummary(string $entity_type_id, CacheableMetadata $cache, ?Plan $plan = NULL): array {
    $entity_type = $this->entityTypeManager()->getDefinition($entity_type_id);
    $query = $plan === NULL ? [] : ['plan' => $plan->id()];
    $add_query = $plan === NULL ? $this->getDestinationArray() : $query + $this->getDestinationArray();
    // Types without an add form (subject breakdowns) are kept by the module.
    $add = $entity_type->hasLinkTemplate('add-form')
      ? $this->addLink($entity_type_id, $this->t('Add @type', ['@type' => $entity_type->getSingularLabel()]), $cache, $add_query)
      : NULL;
    if ($add !== NULL) {
      $add['#attributes']['class'] = ['ancora-workspace__add'];
    }
    return [
      'label' => $entity_type->getCollectionLabel(),
      'count' => $this->count($entity_type_id, $cache, $plan === NULL ? NULL : (int) $plan->id()),
      'list' => [
        '#type' => 'link',
        '#title' => $this->t('List'),
        '#url' => Url::fromRoute("entity.$entity_type_id.collection", [], ['query' => $query]),
        '#attributes' => ['aria-label' => $this->t('List of @label', ['@label' => $entity_type->getCollectionLabel()])],
      ],
      'add' => $add,
    ];
  }

  /**
   * The actions of a plan row on the landing page.
   */
  private function planActions(Plan $plan): array {
    $links = [
      'open' => [
        'title' => $this->t('Open'),
        'url' => Url::fromRoute('ancora_scheduling.plan', ['ancora_plan' => $plan->id()]),
        'attributes' => ['aria-label' => $this->t('Open plan @plan', ['@plan' => $plan->label()])],
      ],
      'timetable' => [
        'title' => $this->t('Timetable'),
        'url' => Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => $plan->id()]),
        'attributes' => ['aria-label' => $this->t('Timetable of @plan', ['@plan' => $plan->label()])],
      ],
    ];
    $request = Url::fromRoute('ancora_scheduling.request', [], ['query' => ['plan' => $plan->id()]]);
    if ($request->access()) {
      $links['generate'] = [
        'title' => $this->t('Generate'),
        'url' => $request,
        'attributes' => ['aria-label' => $this->t('Generate a timetable for @plan', ['@plan' => $plan->label()])],
      ];
    }
    return ['#theme' => 'links', '#links' => $links, '#attributes' => ['class' => ['ancora-inline-links']]];
  }

  /**
   * The getting-started steps of a user with no plan.
   */
  private function gettingStarted(): array {
    $items = [];
    $sections = AcademicEntityTypes::sections();
    foreach (array_keys(AcademicEntityTypes::SECTIONS) as $section) {
      $items[] = [
        '#type' => 'link',
        '#title' => $sections[$section]['title'],
        '#url' => Url::fromRoute("ancora_academic.section.$section"),
        '#suffix' => ': ' . $sections[$section]['intro'],
      ];
    }
    $items[] = [
      '#markup' => $this->t('Then request a timetable and open it from the plan page.'),
    ];
    return ['#theme' => 'item_list', '#list_type' => 'ol', '#items' => $items];
  }

  /**
   * The user's catalogue counts (records that serve every plan).
   */
  private function catalogueCounts(CacheableMetadata $cache): array {
    $items = [];
    foreach (AcademicEntityTypes::SECTIONS['catalogues'] as $entity_type_id) {
      if (!AcademicEntityTypes::isOwned($entity_type_id)) {
        continue;
      }
      $entity_type = $this->entityTypeManager()->getDefinition($entity_type_id);
      $items[] = [
        'label' => $entity_type->getCollectionLabel(),
        'count' => $this->count($entity_type_id, $cache),
        'url' => Url::fromRoute("entity.$entity_type_id.collection")->toString(),
      ];
    }
    return $items;
  }

  /**
   * The demo entry links (#104 item 5), each only when the user may use it.
   *
   * @param \Drupal\ancora_academic\Entity\Plan[] $plans
   *   The user's plans.
   */
  private function quickLinks(array $plans): array {
    $timetable = count($plans) === 1
      ? Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => array_key_first($plans)])
      : Url::fromRoute('ancora_scheduling.timetables');
    $candidates = [
      'planning' => [$this->t('Planning'), Url::fromRoute('ancora_academic.planning')],
      'plans' => [$this->t('Plans'), Url::fromRoute('entity.ancora_plan.collection')],
      'periods' => [$this->t('Periods'), Url::fromRoute('entity.ancora_period.collection')],
      'teachers' => [$this->t('Teachers'), Url::fromRoute('entity.ancora_teacher.collection')],
      'rooms' => [$this->t('Rooms'), Url::fromRoute('entity.ancora_room.collection')],
      'subjects' => [$this->t('Subjects'), Url::fromRoute('entity.ancora_subject.collection')],
      'groups' => [$this->t('Groups'), Url::fromRoute('entity.ancora_student_group.collection')],
      'generate' => [$this->t('Generate timetable'), Url::fromRoute('ancora_scheduling.request')],
      'timetable' => [$this->t('View timetable'), $timetable],
      'jobs' => [$this->t('Job status'), Url::fromRoute('ancora_scheduling.jobs')],
    ];
    $links = [];
    foreach ($candidates as $key => [$title, $url]) {
      if ($url->access()) {
        $links[$key] = ['title' => $title, 'url' => $url];
      }
    }
    return ['#theme' => 'links', '#links' => $links, '#attributes' => ['class' => ['ancora-quick-links']]];
  }

  /**
   * A table of runs: id, plan, period, scope, state and dates.
   *
   * @param \Drupal\ancora_engine\Job\Job[] $jobs
   *   Jobs of plans the user may view.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $caption
   *   The table caption, if any.
   * @param bool $hide_plan
   *   Leave out the plan column (the page of one plan).
   */
  private function runsTable(array $jobs, ?TranslatableMarkup $caption, bool $hide_plan): array {
    $storage = fn (string $type) => $this->entityTypeManager()->getStorage($type);
    $label = static fn ($entity): string => match (TRUE) {
      $entity === NULL => (string) new TranslatableMarkup('(deleted)'),
      !$entity->access('view') => (string) new TranslatableMarkup('(restricted)'),
      default => (string) $entity->label(),
    };
    $rows = [];
    foreach ($jobs as $job) {
      $source = $job->source;
      $row = [Link::fromTextAndUrl($this->t('Run @id', ['@id' => $job->id]), Url::fromRoute('ancora_scheduling.job', ['job' => $job->id]))];
      if (!$hide_plan) {
        $row[] = $label($storage('ancora_plan')->load($source->planId));
      }
      $row[] = $label($storage('ancora_period')->load($source->periodId));
      $row[] = $label($storage('ancora_specialty')->load($source->specialtyId)) . ', ' . $label($storage('ancora_level')->load($source->levelId));
      $row[] = SchedulingController::stateLabel($job);
      $row[] = $this->dateFormatter->format($job->created, 'short');
      $rows[] = $row;
    }
    $header = [$this->t('Run')];
    if (!$hide_plan) {
      $header[] = $this->t('Plan');
    }
    array_push($header, $this->t('Period'), $this->t('Specialty, level'), $this->t('State'), $this->t('Queued'));
    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No timetable has been requested yet.'),
    ];
    if ($caption !== NULL) {
      $table['#caption'] = $caption;
    }
    return $table;
  }

  /**
   * An hour as HH:MM.
   */
  public static function hour(float $hour): string {
    return sprintf('%02d:%02d', (int) floor($hour), (int) round(fmod($hour, 1) * 60));
  }

}
