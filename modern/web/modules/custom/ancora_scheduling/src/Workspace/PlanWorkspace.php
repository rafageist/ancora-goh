<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Workspace;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\PeriodInputs;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\Form\PlacementMoveForm;
use Drupal\ancora_scheduling\Timetable\CrossPlanConflictDetector;
use Drupal\ancora_scheduling\Timetable\CrossPlanConflictView;
use Drupal\ancora_scheduling\Timetable\PlacementModification;
use Drupal\ancora_scheduling\Timetable\TimetableReader;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The integrated planning workspace of one plan (#141, S1a): read-only.
 *
 * The URL is the state: ?period, ?lens (all, teacher, group, room), ?obj
 * (the record of the lens) and ?sel (the selected placement). Every value
 * is checked against what this plan and this account offer; a value that
 * is not offered is a 404, never another owner's data. The route itself
 * has the plan timetable's access (view academic data and view access to
 * the plan).
 *
 * The regions (context bar, perspectives and rail, canvas, inspector) are
 * render arrays built from the existing read models only: TimetableReader
 * (the placements every timetable shows), PeriodInputs (slot times, breaks
 * included), CrossPlanConflictDetector (#133: read-only warnings, and
 * "not evaluated" for an unknown calendar) and the move history of #130.
 * Nothing here writes academic or scheduling data, and no validity is
 * computed: the workspace only shows.
 */
final class PlanWorkspace {

  use StringTranslationTrait;

  /**
   * The perspectives, in their order.
   */
  public const LENSES = ['all', 'teacher', 'group', 'room'];

  /**
   * The placement field of each perspective's record.
   */
  private const LENS_FIELD = ['teacher' => 'teacher_id', 'group' => 'group_id', 'room' => 'room_id'];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly TimetableReader $reader,
    private readonly PeriodInputs $inputs,
    private readonly CrossPlanConflictDetector $conflicts,
    private readonly CrossPlanConflictView $conflictView,
    private readonly PlacementModification $modification,
    private readonly DateFormatterInterface $dateFormatter,
    private readonly EntityLastInstalledSchemaRepositoryInterface $installed,
    private readonly FormBuilderInterface $formBuilder,
  ) {}

  /**
   * Resolves the workspace state from the query.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   A period, perspective, record or placement this plan does not offer.
   */
  public function state(Plan $plan, AccountInterface $account, InputBag $query, ?InputBag $post = NULL): array {
    $storage = $this->entityTypeManager->getStorage('ancora_period');
    $periods = $storage->loadByProperties(['plan' => $plan->id()]);
    uasort($periods, static fn (Period $a, Period $b): int => strcmp($a->getCode(), $b->getCode()) ?: $a->id() <=> $b->id());
    $value = static fn (string $key): string => is_scalar($query->all()[$key] ?? NULL) ? (string) $query->all()[$key] : (isset($query->all()[$key]) ? "\0" : '');

    $period = NULL;
    $requested = $value('period');
    if ($requested !== '') {
      $period = ctype_digit($requested) ? ($periods[(int) $requested] ?? NULL) : NULL;
      if ($period === NULL) {
        throw new NotFoundHttpException();
      }
    }
    elseif ($periods !== []) {
      $period = reset($periods);
    }

    $lens = $value('lens');
    $lens = $lens === '' ? 'all' : $lens;
    if (!in_array($lens, self::LENSES, TRUE)) {
      throw new NotFoundHttpException();
    }

    $data = $period === NULL ? ['scopes' => []] : $this->reader->read($plan, $period);
    $placements = [];
    foreach ($data['scopes'] as $scope) {
      $draft = $scope['job'] !== NULL && !$scope['final'];
      foreach ($scope['placements'] as $p) {
        $p['draft'] = $draft;
        $placements[] = $p;
      }
    }
    $by_id = [];
    foreach ($placements as $p) {
      if (!empty($p['placement_id'])) {
        $by_id[(int) $p['placement_id']] = $p;
      }
    }
    $options = $this->options($plan, $placements);

    $obj = NULL;
    $requested = $value('obj');
    if ($requested !== '') {
      if ($lens === 'all' || !ctype_digit($requested) || !isset($options[$lens][(int) $requested])) {
        throw new NotFoundHttpException();
      }
      $obj = (int) $requested;
    }

    $sel = NULL;
    $requested = $value('sel');
    if ($requested !== '') {
      if (!ctype_digit($requested) || !isset($by_id[(int) $requested])) {
        throw new NotFoundHttpException();
      }
      $sel = (int) $requested;
    }

    // The move of the selected placement (S1b): its check and the change an
    // undo goes back from. The form validates them against the placement
    // (a check of another placement is no check); here they must be ids,
    // with a selection, of a placement this account may move.
    $move = [];
    foreach (['check', 'undo'] as $key) {
      $requested = $value($key);
      if ($requested !== '') {
        if (!ctype_digit($requested) || $sel === NULL) {
          throw new NotFoundHttpException();
        }
        $move[$key] = (int) $requested;
      }
    }
    $entity = $sel === NULL ? NULL : $this->entityTypeManager->getStorage('ancora_placement')->load($sel);
    $movable = $entity instanceof Placement && $this->modification->isAvailable() && $entity->access('update', $account);
    if ($move !== [] && !$movable) {
      throw new AccessDeniedHttpException();
    }
    // The cell a preview step chose (display only; the form decides).
    $preview = NULL;
    foreach (array_keys($post?->all() ?? []) as $name) {
      if (preg_match('/^op_preview_(\d+)_(\d+)$/', (string) $name, $m)) {
        $preview = [(int) $m[1], (int) $m[2]];
      }
    }

    return [
      'check' => $move['check'] ?? NULL,
      'undo' => $move['undo'] ?? NULL,
      'movable' => $movable,
      'placement' => $entity,
      'preview' => $preview,
      'plan' => $plan,
      'account' => $account,
      'periods' => $periods,
      'period' => $period,
      'lens' => $lens,
      'obj' => $obj,
      'sel' => $sel,
      'scopes' => $data['scopes'],
      'placements' => $placements,
      'by_id' => $by_id,
      'options' => $options,
    ];
  }

  /**
   * The cache metadata of every region: user, query and the inputs.
   */
  public function cacheability(Plan $plan): CacheableMetadata {
    $cache = (new CacheableMetadata())
      ->addCacheContexts(['user', 'user.permissions', 'url.query_args'])
      ->addCacheTags([JobRepository::CACHE_TAG])
      ->addCacheableDependency($plan);
    foreach ([...AcademicEntityTypes::ids(), 'ancora_placement', 'ancora_placement_change'] as $entity_type_id) {
      if ($this->entityTypeManager->hasDefinition($entity_type_id)) {
        $cache->addCacheTags($this->entityTypeManager->getDefinition($entity_type_id)->getListCacheTags());
      }
    }
    return $cache->merge($this->conflicts->cacheability());
  }

  /**
   * The regions of the workspace, keyed by region name.
   *
   * @return array<string, array>
   *   bar, nav, canvas and inspector render arrays.
   */
  public function regions(array $state): array {
    $cross = $state['period'] === NULL
      ? ['evaluated' => FALSE, 'unknown' => [], 'conflicts' => []]
      : $this->conflicts->forPlan($state['plan'], $state['account'], $state['period']);
    $reason = $state['period'] === NULL ? NULL : $this->conflicts->unknownReason($state['plan'], $state['period']);
    $by_placement = [];
    foreach ($cross['conflicts'] as $c) {
      if (($c['mine']['placement_id'] ?? NULL) !== NULL) {
        $by_placement[(int) $c['mine']['placement_id']][] = $c;
      }
    }
    $state['cross'] = $cross + ['reason' => $reason, 'by_placement' => $by_placement];

    return [
      'bar' => $this->bar($state),
      'nav' => $this->nav($state),
      'canvas' => $this->canvas($state),
      'inspector' => $this->inspector($state),
    ];
  }

  /**
   * The workspace URL of a state, with some values changed.
   */
  public function url(array $state, array $changes = []): Url {
    // A move belongs to its selection and period: changing either ends it.
    $keep = !array_key_exists('sel', $changes) && !array_key_exists('period', $changes);
    // Always in this order: one state, one URL.
    $values = array_replace([
      'period' => $state['period']?->id(),
      'lens' => $state['lens'],
      'obj' => $state['obj'],
      'sel' => $state['sel'],
      'check' => $keep ? ($state['check'] ?? NULL) : NULL,
      'undo' => $keep ? ($state['undo'] ?? NULL) : NULL,
    ], array_intersect_key($changes, array_flip(['period', 'lens', 'obj', 'sel', 'check', 'undo'])));
    if ($values['lens'] === 'all') {
      $values['lens'] = NULL;
      $values['obj'] = NULL;
    }
    $query = array_filter($values, static fn ($v): bool => $v !== NULL && $v !== '');
    return Url::fromRoute('ancora_scheduling.plan_workspace', ['ancora_plan' => $state['plan']->id()], ['query' => $query]);
  }

  /**
   * The records each perspective offers.
   *
   * Groups: the plan's student groups. Teachers and rooms: the plan
   * owner's, and any other one a placement of this period uses (a shared
   * catalogue record, Q-23).
   *
   * @return array<string, array<int, string>>
   *   Perspective => id => label, by label.
   */
  private function options(Plan $plan, array $placements): array {
    $owner = (int) $plan->getCreatorId();
    $out = [];
    foreach (['teacher' => 'ancora_teacher', 'room' => 'ancora_room', 'group' => 'ancora_student_group'] as $lens => $type) {
      $storage = $this->entityTypeManager->getStorage($type);
      $query = $storage->getQuery()->accessCheck(FALSE);
      $lens === 'group' ? $query->condition('plan', $plan->id()) : $query->condition('uid', $owner);
      $ids = array_map('intval', $query->execute());
      foreach ($placements as $p) {
        if (!empty($p[self::LENS_FIELD[$lens]])) {
          $ids[] = (int) $p[self::LENS_FIELD[$lens]];
        }
      }
      $out[$lens] = [];
      foreach ($storage->loadMultiple(array_unique($ids)) as $id => $record) {
        $out[$lens][(int) $id] = (string) $record->label();
      }
      natcasesort($out[$lens]);
    }
    return $out;
  }

  /**
   * The placements of the current perspective.
   */
  private function shown(array $state): array {
    if ($state['lens'] === 'all') {
      return $state['placements'];
    }
    if ($state['obj'] === NULL) {
      return [];
    }
    $field = self::LENS_FIELD[$state['lens']];
    return array_values(array_filter($state['placements'], static fn (array $p): bool => (int) ($p[$field] ?? 0) === $state['obj']));
  }

  /**
   * The context bar: plan, period, publication state, cross-plan state.
   */
  private function bar(array $state): array {
    $plan = $state['plan'];
    $plans = [];
    $storage = $this->entityTypeManager->getStorage('ancora_plan');
    $ids = $storage->getQuery()->accessCheck(TRUE)->sort('label')->sort('id')->execute();
    foreach ($storage->loadMultiple($ids) as $id => $other) {
      if ((int) $id !== (int) $plan->id() && $other->access('view', $state['account'])) {
        $plans[] = [
          'label' => $other->label(),
          'url' => Url::fromRoute('ancora_scheduling.plan_workspace', ['ancora_plan' => $id])->toString(),
        ];
      }
    }
    $periods = [];
    foreach ($state['periods'] as $id => $period) {
      $periods[] = [
        'label' => $period->label(),
        'url' => $this->url($state, ['period' => $id, 'obj' => NULL, 'sel' => NULL])->toString(),
        'current' => $state['period'] !== NULL && (int) $id === (int) $state['period']->id(),
      ];
    }
    $scopes = [];
    foreach ($state['scopes'] as $scope) {
      $scopes[] = [
        'label' => $this->t('@specialty, @level', ['@specialty' => $scope['specialty'], '@level' => $scope['level']]),
        'state' => match (TRUE) {
          $scope['error'] !== NULL => 'error',
          $scope['job'] === NULL => 'none',
          $scope['final'] => 'final',
          default => 'draft',
        },
        'text' => match (TRUE) {
          $scope['error'] !== NULL => $this->t('the scheduling engine could not be reached'),
          $scope['job'] === NULL => $this->t('no timetable yet'),
          $scope['final'] => $this->t('final timetable (run @id)', ['@id' => $scope['job']->id]),
          default => $this->t('draft, not final (run @id)', ['@id' => $scope['job']->id]),
        },
        'stale' => $scope['stale'],
        'active' => $scope['active'] === NULL ? NULL : $this->t('Run @id is @state.', [
          '@id' => $scope['active']->id,
          '@state' => $scope['active']->state->value,
        ]),
        'job_url' => $scope['job'] === NULL ? NULL : Url::fromRoute('ancora_scheduling.job', ['job' => $scope['job']->id])->toString(),
      ];
    }
    $cross = $state['cross'];
    $links = [
      [
        'title' => $this->t('Plan page'),
        'url' => Url::fromRoute('ancora_scheduling.plan', ['ancora_plan' => $plan->id()]),
      ],
    ];
    if ($state['period'] !== NULL) {
      $links[] = [
        'title' => $this->t('Classic timetable page'),
        'url' => Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => $plan->id()], ['query' => ['period' => $state['period']->id()]]),
      ];
    }
    $request = Url::fromRoute('ancora_scheduling.request', [], ['query' => ['plan' => $plan->id()]]);
    if ($request->access($state['account'])) {
      $links[] = ['title' => $this->t('Request a timetable'), 'url' => $request];
    }
    return [
      '#theme' => 'ancora_workspace_bar',
      '#plan' => $plan->label(),
      '#plans' => $plans,
      '#period' => $state['period']?->label(),
      '#periods' => $periods,
      '#scopes' => $scopes,
      '#cross' => [
        'evaluated' => $cross['evaluated'],
        'count' => count($cross['conflicts']),
        'reason' => $cross['reason'] === NULL ? NULL : $this->conflictView->reason($cross['reason']),
        'url' => $this->url($state, ['sel' => NULL])->setOption('fragment', 'ancora-inspector-title')->toString(),
      ],
      '#links' => ['#theme' => 'links', '#links' => $links, '#attributes' => ['class' => ['ancora-wsbar__links']]],
    ];
  }

  /**
   * The perspectives and the record rail of the current perspective.
   */
  private function nav(array $state): array {
    $labels = [
      'all' => $this->t('All activities'),
      'teacher' => $this->t('Teachers'),
      'group' => $this->t('Groups'),
      'room' => $this->t('Rooms'),
    ];
    $lenses = [];
    foreach (self::LENSES as $lens) {
      $lenses[] = [
        'lens' => $lens,
        'label' => $labels[$lens],
        'url' => $this->url($state, ['lens' => $lens, 'obj' => NULL])->toString(),
        'current' => $lens === $state['lens'],
      ];
    }
    $records = [];
    if ($state['lens'] !== 'all') {
      foreach ($state['options'][$state['lens']] as $id => $label) {
        $count = count(array_filter($state['placements'], static fn (array $p): bool => (int) ($p[self::LENS_FIELD[$state['lens']]] ?? 0) === $id));
        $records[] = [
          'label' => $label,
          'count' => $count,
          'url' => $this->url($state, ['obj' => $id])->toString(),
          'current' => $id === $state['obj'],
        ];
      }
    }
    return [
      '#theme' => 'ancora_workspace_nav',
      '#lenses' => $lenses,
      '#lens_label' => $labels[$state['lens']],
      '#records' => $records,
      '#all' => $state['lens'] === 'all',
    ];
  }

  /**
   * The canvas: the period's time grid with one block per placement.
   */
  private function canvas(array $state): array {
    $period = $state['period'];
    if ($period === NULL) {
      return [
        '#theme' => 'ancora_workspace_canvas',
        '#heading' => $this->t('This plan has no period yet.'),
        '#empty' => TRUE,
      ];
    }
    $shown = $this->shown($state);
    $object = $state['obj'] === NULL ? NULL : $state['options'][$state['lens']][$state['obj']];
    $heading = match (TRUE) {
      $state['lens'] === 'all' => $this->formatPlural(count($shown), '@period: all activities, 1 activity', '@period: all activities, @count activities', ['@period' => $period->label()]),
      $object === NULL => $this->t('Pick a record in the list to see its week.'),
      default => $this->formatPlural(count($shown), '@record: 1 activity', '@record: @count activities', ['@record' => $object]),
    };
    if ($state['scopes'] === []) {
      return [
        '#theme' => 'ancora_workspace_canvas',
        '#heading' => $heading,
        '#empty' => TRUE,
        '#message' => $this->t('No timetable has been requested for this period yet.'),
      ];
    }
    $layout = $this->layout($period, $shown);
    $cross = $state['cross'];
    foreach ($layout['days'] as &$day) {
      foreach ($day['blocks'] as &$block) {
        $id = (int) ($block['placement_id'] ?? 0);
        $block['url'] = $id === 0 ? NULL : $this->url($state, ['sel' => $id])->toString();
        $block['selected'] = $id !== 0 && $id === $state['sel'];
        $block['warnings'] = count($cross['by_placement'][$id] ?? []);
        $block['not_evaluated'] = !$cross['evaluated'];
      }
      unset($block);
    }
    unset($day);
    $overlay = $this->overlay($state, $layout);
    if ($overlay !== NULL) {
      foreach ($layout['days'] as &$day) {
        $day['overlay'] = $overlay['days'][$day['number']] ?? [];
      }
      unset($day);
    }
    return [
      '#theme' => 'ancora_workspace_canvas',
      '#heading' => $heading,
      '#overlay' => $overlay,
      '#rows' => $layout['rows'],
      '#days' => $layout['days'],
      '#template' => 'auto ' . implode(' ', array_map(static fn (array $row): string => $row['type'] === 'slot' ? 'minmax(4.75rem, auto)' : '1.75rem', $layout['rows'])),
      '#empty' => $shown === [] && $state['lens'] !== 'all' && $object === NULL,
      '#status' => $heading,
    ];
  }

  /**
   * The destinations of the move in progress, drawn on the canvas (S1b).
   *
   * Only a finished, current check of the selected placement, as the move
   * form shows it: valid cells (a submit button of the inspector's move
   * form, by its form attribute), refused cells with their reasons, the
   * scheduler's warnings, and the #133 cross-plan warnings per cell, or
   * "not evaluated" for an unknown calendar. Nothing is decided here: the
   * cells and reasons are the check's (PlacementModification::check()).
   */
  private function overlay(array $state, array $layout): ?array {
    if ($state['check'] === NULL || !$state['movable']) {
      return NULL;
    }
    $placement = $state['placement'];
    $check = $this->modification->check($placement, $state['check']);
    if ($check === NULL || $check['state'] !== 'done' || $check['expired']) {
      return NULL;
    }
    $undo = NULL;
    if ($state['undo'] !== NULL && ($last = $this->modification->lastChange($placement)) !== NULL && (int) $last->id() === $state['undo']) {
      $undo = [(int) $last->get('from_day')->value, (int) $last->get('from_slot')->value];
    }
    $lines = [];
    foreach ($layout['rows'] as $row) {
      if ($row['type'] === 'slot') {
        $lines[$row['number']] = $row['line'];
      }
    }
    $cells = $check['result']['cells'] ?? [];
    $from = $check['result']['from'];
    $candidates = array_map(static fn (array $c): array => [$c['day'], $c['slot']], $cells);
    $cross = $this->conflicts->forCandidates($placement, $candidates, $state['account']);
    $days = [];
    $valid = 0;
    foreach ($cells as $cell) {
      [$day, $slot] = [(int) $cell['day'], (int) $cell['slot']];
      if (!isset($lines[$slot])) {
        continue;
      }
      $here = [$day, $slot] === [(int) $from['day'], (int) $from['slot']];
      $allowed = $cell['ok'] && !$here && ($undo === NULL || [$day, $slot] === $undo);
      $valid += $allowed ? 1 : 0;
      $conflicts = $here ? [] : ($cross['cells']["$day.$slot"] ?? []);
      $days[$day][] = [
        'day' => $day,
        'slot' => $slot,
        'line' => $lines[$slot],
        'allowed' => $allowed,
        'here' => $here,
        'preview' => $state['preview'] === [$day, $slot],
        'reasons' => match (TRUE) {
          $here => [(string) $this->t('it is here now')],
          !$cell['ok'] => array_map(fn (string $r): string => (string) $this->modification->label($r), $cell['reasons']),
          !$allowed => [(string) $this->t('an undo goes back to where it was')],
          default => [],
        },
        'warnings' => array_map(fn (string $w): string => (string) $this->modification->label($w), $cell['warnings'] ?? []),
        'cross' => $conflicts === [] ? NULL : (string) $this->conflictView->cellWarning($conflicts),
        'cross_count' => count($conflicts),
        'name' => "op_preview_{$day}_{$slot}",
        'value' => (string) $this->t('Day @day, slot @slot', ['@day' => $day, '@slot' => $slot]),
      ];
    }
    return [
      'days' => $days,
      'valid' => $valid,
      'undo' => $undo !== NULL,
      'evaluated' => $cross['evaluated'],
      'reason' => $cross['evaluated'] ? NULL : (string) $this->conflictView->reason($cross['reason']),
      'activity' => $state['by_id'][$state['sel']]['activity'] ?? '',
    ];
  }

  /**
   * The time grid of a period for some placements (CanvasLayout).
   */
  private function layout(Period $period, array $placements): array {
    $breaks = [];
    foreach ($this->inputs->breaks($period->id()) as $after => $break) {
      $breaks[$after] = $break->getLength();
    }
    return CanvasLayout::build(
      (int) $period->getDays(),
      (int) $period->getSlotsPerDay(),
      $period->getSlotDuration(),
      $breaks,
      fn (int $slot): float => $this->inputs->slotStart($period, $slot),
      $placements,
    );
  }

  /**
   * The inspector: the selected activity, or the period's cross-plan state.
   */
  private function inspector(array $state): array {
    $cross = $state['cross'];
    $build = [
      '#theme' => 'ancora_workspace_inspector',
      '#selected' => $state['sel'] !== NULL,
    ];
    if ($state['sel'] === NULL) {
      $build['#title'] = $this->t('Details');
      $build['#summary'] = $state['period'] === NULL ? NULL : $this->conflictView->section($cross, 'ancora-inspector-cross-plan');
      $build['#hint'] = $this->t('Select an activity in the timetable to see its details here.');
      return $build;
    }
    $p = $state['by_id'][$state['sel']];
    $period = $state['period'];
    $layout = $this->layout($period, [$p]);
    $block = $layout['days'][$p['day'] - 1]['blocks'][0] ?? $p + [
      'start' => '',
      'end' => '',
      'breaks' => [],
      'first_slot' => $p['slot'],
      'last_slot' => $p['slot'],
    ];

    $when = $this->formatPlural((int) $p['slots'], 'Day @day, slot @first (@start–@end)', 'Day @day, slots @first–@last, @count slots (@start–@end)', [
      '@day' => $p['day'],
      '@first' => $block['first_slot'],
      '@last' => $block['last_slot'],
      '@start' => $block['start'],
      '@end' => $block['end'],
    ]);
    $facts = [
      [$this->t('Subject'), $p['subject']],
      [$this->t('Activity type'), $p['activity_type']],
      [$this->t('Group'), $p['group']],
      [$this->t('Teacher'), $p['teacher'] === '' ? $this->t('none') : $p['teacher']],
      [$this->t('Room'), $p['room'] === '' ? $this->t('none') : $p['room']],
      [$this->t('Other resources'), $p['resources'] === [] ? $this->t('none') : implode(', ', $p['resources'])],
      [$this->t('When'), $when],
    ];
    foreach ($block['breaks'] as $break) {
      $facts[] = [
        $this->t('Break inside'),
        $this->t('@start–@end (the activity continues after it)', ['@start' => $break['start'], '@end' => $break['end']]),
      ];
    }
    $facts[] = [
      $this->t('Timetable'),
      $p['draft'] ? $this->t('Draft: not a final timetable') : $this->t('Final timetable'),
    ];

    // Its occupancy, from every perspective: the same block everywhere.
    $pivots = [];
    foreach (['teacher' => 'teacher_id', 'room' => 'room_id', 'group' => 'group_id'] as $lens => $field) {
      if (!empty($p[$field]) && isset($state['options'][$lens][(int) $p[$field]])) {
        $pivots[] = [
          'title' => $this->t("@record's week", ['@record' => $state['options'][$lens][(int) $p[$field]]]),
          'url' => $this->url($state, ['lens' => $lens, 'obj' => (int) $p[$field]]),
          'attributes' => ['data-ancora-nav' => 'record'],
        ];
      }
    }

    $conflicts = $cross['by_placement'][$state['sel']] ?? [];
    $cross_text = match (TRUE) {
      !$cross['evaluated'] => $this->t('Dates unknown / cross-plan conflicts not evaluated (@reason).', ['@reason' => $this->conflictView->reason($cross['reason'])]),
      $conflicts === [] => $this->t('No overlap found with the dated periods of your plans.'),
      default => NULL,
    };

    $history = [];
    $entity = $this->entityTypeManager->getStorage('ancora_placement')->load($state['sel']);
    if ($entity instanceof Placement && $this->installed->getLastInstalledDefinition('ancora_placement_change') !== NULL) {
      foreach ($this->modification->history($entity) as $change) {
        $history[] = $this->t('@when, @who: day @fd, slot @fs → day @td, slot @ts@undo', [
          '@when' => $this->dateFormatter->format((int) $change->get('created')->value, 'short'),
          '@who' => $change->get('uid')->entity?->getDisplayName() ?? '?',
          '@fd' => $change->get('from_day')->value,
          '@fs' => $change->get('from_slot')->value,
          '@td' => $change->get('to_day')->value,
          '@ts' => $change->get('to_slot')->value,
          '@undo' => $change->get('reason')->value === 'undo' ? ' (' . $this->t('undo') . ')' : '',
        ]);
      }
    }
    // The move, in place (S1b): the move form of #130 with the workspace
    // context; it posts to, and returns to, this workspace URL.
    $move = NULL;
    if ($state['movable']) {
      $query = $this->url($state, ['check' => NULL, 'undo' => NULL])->getOption('query');
      $move = $this->formBuilder->getForm(PlacementMoveForm::class, $state['placement'], [
        'plan' => (int) $state['plan']->id(),
        'query' => $query,
      ]);
      // The form shows the moves and the undo.
      $history = [];
    }
    $build += [
      '#move' => $move,
      '#title' => $p['activity'],
      '#facts' => $facts,
      '#pivots' => [
        '#theme' => 'links',
        '#links' => $pivots,
        '#attributes' => ['class' => ['ancora-inspector__pivots']],
      ],
      '#cross_text' => $cross_text,
      '#conflicts' => array_map(fn (array $c): TranslatableMarkup => $this->conflictView->conflict($c), $conflicts),
      '#history' => $history,
      '#close_url' => $this->url($state, ['sel' => NULL])->toString(),
    ];
    return $build;
  }

}
