<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Controller;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\PeriodInputs;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Timetable\CrossPlanConflictDetector;
use Drupal\ancora_scheduling\Timetable\CrossPlanConflictView;
use Drupal\ancora_scheduling\Timetable\PlacementModification;
use Drupal\ancora_scheduling\Timetable\TimetableExplorer;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The page of the legacy timetable explorer (#119, D14, PAR-091).
 *
 * ?period=<id> and one or more of brigade, teacher, room, resource (ids;
 * the last one in that order wins, as legacy). TimetableExplorer decides
 * what may be shown. The grid is the legacy slot by day grid
 * (minibrowser_horarios.tpl): numbered slots and days, each activity in its
 * start cell with its activity type, subject, teacher and room; the slot
 * hours are shown too, as on the plan timetable. Legacy keeps one activity
 * per start cell (the last one written, Q-36); every activity is listed
 * here. Activities of a draft (a partial or cancelled run) are marked.
 */
final class ExplorerController extends ControllerBase {

  use AutowireTrait;

  public function __construct(
    private readonly TimetableExplorer $explorer,
    private readonly RequestStack $requestStack,
    private readonly PeriodInputs $inputs,
    private readonly PlacementModification $modification,
    private readonly CrossPlanConflictDetector $conflicts,
    private readonly CrossPlanConflictView $conflictView,
  ) {}

  /**
   * The explorer page.
   */
  public function page(): array {
    return $this->build(FALSE);
  }

  /**
   * The timetable viewer (#121, PAR-092): every plan of the site, read-only.
   *
   * The legacy public viewer's lists; authenticated users only (the route
   * requires a login and its own permission, and nothing here reads a
   * request value to change that).
   */
  public function viewer(): array {
    return $this->build(TRUE);
  }

  /**
   * The page of the explorer or of the viewer.
   */
  private function build(bool $viewer): array {
    $query = $this->requestStack->getCurrentRequest()->query;
    $cache = (new CacheableMetadata())
      ->addCacheContexts([
        'user',
        'user.roles:authenticated',
        'url.query_args:period',
        ...array_map(static fn (string $s): string => "url.query_args:$s", array_keys(TimetableExplorer::SELECTORS)),
      ])
      ->addCacheTags([JobRepository::CACHE_TAG]);
    foreach ([...AcademicEntityTypes::ids(), 'ancora_placement', 'ancora_placement_change'] as $entity_type_id) {
      $cache->addCacheTags($this->entityTypeManager()->getDefinition($entity_type_id)->getListCacheTags());
    }
    $selectors = [];
    foreach (array_keys(TimetableExplorer::SELECTORS) as $selector) {
      $selectors[$selector] = $query->get($selector);
    }
    $result = $this->explorer->explore($this->currentUser(), $query->get('period'), $selectors, $viewer);
    $periods = $this->explorer->periods($this->currentUser(), $viewer);

    $intro = $viewer
      ? $this->t('The timetables of every plan of this organization, as the legacy timetable viewer shows them: pick a period, then a cohort, a teacher, a room or one of your other resources. Read-only.')
      : $this->t('Pick one of your periods, then a cohort, a teacher, a room or an other resource, as in the legacy timetable explorer. The teacher and room lists include those of every planner of this site, as in the legacy explorer; only the activities of your periods are shown.');
    $build = [
      'intro' => ['#markup' => '<p>' . $intro . '</p>'],
      'form' => $this->form($periods, $result, $viewer ? 'ancora_scheduling.viewer' : 'ancora_scheduling.explorer'),
    ];
    $message = match ($result['status']) {
      'period_refused' => $viewer ? $this->t('That period is not in the timetable viewer.') : $this->t('That period is not one of yours.'),
      'selector_refused' => $this->t('That record is not in the lists of this period.'),
      'no_selector' => $this->t('Pick a cohort, a teacher, a room or an other resource.'),
      'no_period' => $periods === [] ? $this->t('There is no period yet.') : $this->t('Pick a period, then a cohort, a teacher, a room or an other resource.'),
      default => NULL,
    };
    if ($message !== NULL) {
      $build['message'] = ['#markup' => '<p class="ancora-explorer__message">' . $message . '</p>'];
    }
    if ($result['status'] === 'ok') {
      foreach ($result['scopes'] as $scope) {
        if ($scope['error'] !== NULL) {
          $cache->setCacheMaxAge(0);
        }
      }
      $build['status'] = $this->scopes($result['scopes']);
      // A scrolling wrapper keeps the grid usable on a narrow screen.
      $build['grid'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['ancora-timetable__grid-wrapper']],
        'table' => $this->grid($result['period'], $result['placements'], $result['record']['label'], $viewer),
      ];
      // Cross-plan conflicts of the period (#133): the explorer only; the
      // viewer's read-only scope does not grow.
      $plan = $result['period']->get('plan')->entity;
      if (!$viewer && $plan instanceof Plan) {
        $build['cross_plan'] = $this->conflictView->section($this->conflicts->forPlan($plan, $this->currentUser(), $result['period']));
        $cache = $cache->merge($this->conflicts->cacheability());
      }
    }
    $build['#attached']['library'][] = 'ancora_scheduling/timetable';
    $cache->applyTo($build);
    return $build;
  }

  /**
   * The GET form: the period and one list per selector.
   */
  private function form(array $periods, array $result, string $route): array {
    $form = [
      '#type' => 'html_tag',
      '#tag' => 'form',
      '#attributes' => [
        'method' => 'get',
        'class' => ['ancora-explorer__form'],
        'action' => Url::fromRoute($route)->toString(),
      ],
    ];
    $grouped = [];
    foreach ($periods as $id => $period) {
      $plan = $period->get('plan')->entity;
      $grouped[$plan === NULL ? '?' : (string) $plan->label()][(int) $id] = (string) $period->label();
    }
    $period = $result['period'];
    $form['period'] = $this->select('period', $this->t('Period'), $grouped, $period?->id());
    $labels = [
      'brigade' => $this->t('Cohort'),
      'teacher' => $this->t('Teacher'),
      'room' => $this->t('Room'),
      'resource' => $this->t('Other resource'),
    ];
    foreach ($result['options'] as $selector => $records) {
      $options = [];
      foreach ($records as $id => $record) {
        $options[$id] = $record['label'] . (($selector === 'teacher' || $selector === 'room') && $record['owner'] !== '' ? ' (' . $record['owner'] . ')' : '');
      }
      $value = $result['selector'] === $selector ? ($result['record']['id'] ?? NULL) : NULL;
      $form[$selector] = $this->select($selector, $labels[$selector], $options, $value);
    }
    $form['submit'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#attributes' => ['type' => 'submit', 'class' => ['button']],
      '#value' => $this->t('Show'),
    ];
    return $form;
  }

  /**
   * A plain select element of the GET form.
   */
  private function select(string $name, TranslatableMarkup $label, array $options, int|string|null $value): array {
    $html = '<label for="ancora-explorer-' . $name . '">' . $label . '</label> <select id="ancora-explorer-' . $name . '" name="' . $name . '"><option value="">' . $this->t('- None -') . '</option>';
    foreach ($options as $key => $option) {
      if (is_array($option)) {
        $html .= '<optgroup label="' . htmlspecialchars((string) $key) . '">';
        foreach ($option as $k => $v) {
          $html .= $this->option((string) $k, (string) $v, $value);
        }
        $html .= '</optgroup>';
        continue;
      }
      $html .= $this->option((string) $key, (string) $option, $value);
    }
    return [
      '#markup' => '<div class="ancora-explorer__field">' . $html . '</select></div>',
      '#allowed_tags' => ['div', 'label', 'select', 'option', 'optgroup'],
    ];
  }

  /**
   * One option element, escaped.
   */
  private function option(string $key, string $label, int|string|null $value): string {
    $selected = $key === (string) $value ? ' selected' : '';
    return '<option value="' . htmlspecialchars($key) . '"' . $selected . '>' . htmlspecialchars($label) . '</option>';
  }

  /**
   * The state of each scope's timetable, as the plan timetable says it.
   */
  private function scopes(array $scopes): array {
    $items = [];
    foreach ($scopes as $scope) {
      $state = match (TRUE) {
        $scope['error'] !== NULL => $this->t('the scheduling engine could not be reached'),
        $scope['job'] === NULL => $this->t('no timetable yet'),
        $scope['final'] => $this->t('final timetable'),
        default => $this->t('DRAFT: not a final timetable'),
      };
      $items[] = $this->t('@specialty, @level: @state', [
        '@specialty' => $scope['specialty'],
        '@level' => $scope['level'],
        '@state' => $state,
      ]);
    }
    return $items === []
      ? ['#markup' => '<p class="ancora-explorer__message">' . $this->t('No timetable has been requested for this period yet.') . '</p>']
      : ['#theme' => 'item_list', '#items' => $items, '#attributes' => ['class' => ['ancora-explorer__scopes']]];
  }

  /**
   * The slot by day grid: each placement in its start cell.
   */
  private function grid(Period $period, array $placements, string $record, bool $viewer = FALSE): array {
    // Moving one placement (#130): the explorer only, workspace mode, the
    // placements the user may edit. The viewer stays read-only.
    $movable = [];
    if (!$viewer && $this->modification->isAvailable()) {
      $ids = array_values(array_filter(array_column($placements, 'placement_id')));
      foreach ($this->entityTypeManager()->getStorage('ancora_placement')->loadMultiple($ids) as $id => $entity) {
        if ($entity->access('update')) {
          $movable[(int) $id] = TRUE;
        }
      }
    }
    $days = range(1, max(1, (int) $period->getDays()));
    $rows = [];
    for ($slot = 1; $slot <= max(1, (int) $period->getSlotsPerDay()); $slot++) {
      $start = $this->inputs->slotStart($period, $slot);
      $row = [
        [
          'data' => ['#markup' => $slot . '<br><span class="ancora-timetable__time">' . WorkspaceController::hour($start) . '</span>'],
          'header' => TRUE,
        ],
      ];
      foreach ($days as $day) {
        $items = [];
        foreach ($placements as $p) {
          if ($p['day'] !== $day || $p['slot'] !== $slot) {
            continue;
          }
          // As minibrowser_horarios.tpl: classification, subject, teacher,
          // room.
          $lines = array_map('htmlspecialchars', [$p['activity_type'], $p['subject'], $p['teacher'], $p['room']]);
          if ($p['draft']) {
            $lines[] = '<strong>' . $this->t('Draft') . '</strong>';
          }
          if (!$viewer && !empty($p['moved'])) {
            $lines[] = '<em class="ancora-placement__moved">' . $this->t('Moved by hand') . '</em>';
          }
          if (isset($movable[(int) ($p['placement_id'] ?? 0)])) {
            $url = Url::fromRoute('ancora_scheduling.placement_move', ['ancora_placement' => $p['placement_id']])->toString();
            $lines[] = '<a class="ancora-placement__move" href="' . htmlspecialchars($url) . '" aria-label="' . htmlspecialchars((string) $this->t('Move @subject of @group', [
              '@subject' => $p['subject'],
              '@group' => $p['group'],
            ])) . '">' . $this->t('Move') . '</a>';
          }
          $items[] = [
            '#markup' => implode('<br>', $lines),
            '#prefix' => '<div class="ancora-placement' . ($p['draft'] ? ' is-draft' : '') . '" data-group="' . htmlspecialchars($p['group_code']) . '">',
            '#suffix' => '</div>',
          ];
        }
        $row[] = ['data' => $items, 'data-day' => $day, 'data-slot' => $slot];
      }
      $rows[] = $row;
    }
    return [
      '#type' => 'table',
      '#caption' => $this->t('@record: @count activities', ['@record' => $record, '@count' => count($placements)]),
      '#header' => [$this->t('Slot'), ...array_map(fn (int $d) => $this->t('Day @day', ['@day' => $d]), $days)],
      '#rows' => $rows,
      '#attributes' => ['class' => ['ancora-explorer__grid']],
    ];
  }

}
