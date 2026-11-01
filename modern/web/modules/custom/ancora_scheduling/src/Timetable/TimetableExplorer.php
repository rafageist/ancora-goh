<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * The legacy timetable explorer, read-only (#119, D14, PAR-091).
 *
 * Legacy: control/custom/custom_getui_ExploradorHorarios.php (the periods of
 * the user's plans), custom_getui_TreeBrigadas.php with tree_brigadas.tpl
 * (the user's specialties and levels, then their brigades),
 * custom_getui_ExploradorList{Profesores,Lugares}.php (every user's
 * teachers and rooms, getAll(), Q-23), custom_getui_ExploradorList
 * OtrosRecursos.php (the user's other resources), and
 * get_minibrowser_horarios.php:41-59, which reads the activities of the
 * posted period for the posted brigade (every group the brigade is in now,
 * dame_lista_id_brigadas_de_grupo), teacher, room or other resource
 * (otros_recursos_usados). When several ids are posted the last test wins:
 * idbrg, then idprofe, then idlug, then idres (Q-38).
 *
 * The legacy handler trusts the posted ids: a forged period id shows any
 * user's activities (like Q-12). Here the period must be one of a plan the
 * account may view, and each selector one of the lists the account is
 * offered; anything else is refused, never shown.
 *
 * The placements are the ones the plan timetable shows (TimetableReader):
 * both engine modes, with the final or draft state of each scope.
 *
 * The timetable viewer (#121, PAR-092) is the same read with the lists of
 * the legacy public viewer (get_browser_publico.php:13-24,
 * public_tree_brigadas.tpl:18-34, get_public_list_{profesores,lugares}.php,
 * get_public_list_otros_recursos.php:11-13): the periods of every plan of
 * the site, every user's brigades, teachers and rooms, and the viewer's own
 * other resources. Its grid is the explorer's
 * (get_minibrowser_publico.php:44-51 runs the same four queries). Only
 * authenticated users reach it; the legacy anonymous mode is not provided
 * (security exception until #62).
 */
final class TimetableExplorer {

  /**
   * Selector => entity type, in the legacy order (the last one set wins).
   */
  public const SELECTORS = [
    'brigade' => 'ancora_cohort',
    'teacher' => 'ancora_teacher',
    'room' => 'ancora_room',
    'resource' => 'ancora_other_resource',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly TimetableReader $reader,
  ) {}

  /**
   * The periods the account may explore: of the plans it may view.
   *
   * In the viewer, the periods of every plan of the site (the legacy public
   * viewer loads every planificacion).
   *
   * @return array<int, \Drupal\ancora_academic\Entity\Period>
   *   Periods by id, by plan and id.
   */
  public function periods(AccountInterface $account, bool $viewer = FALSE): array {
    $plans = $this->entityTypeManager->getStorage('ancora_plan');
    $out = [];
    foreach ($plans->loadMultiple($plans->getQuery()->accessCheck(FALSE)->sort('id')->execute()) as $plan) {
      if (!$viewer && !$plan->access('view', $account)) {
        continue;
      }
      $storage = $this->entityTypeManager->getStorage('ancora_period');
      foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->condition('plan', $plan->id())->sort('id')->execute()) as $id => $period) {
        $out[(int) $id] = $period;
      }
    }
    return $out;
  }

  /**
   * The records offered for each selector, for a period's plan owner.
   *
   * Brigades and other resources are the plan owner's (the legacy lists
   * filter by creador); teachers and rooms are every user's (Q-23). In the
   * viewer: every user's brigades, teachers and rooms, and the viewer's own
   * other resources (the session user, get_public_list_otros_recursos.php).
   *
   * @return array<string, array<int, array{label: string, owner: string}>>
   *   Selector => id => label and owner name (empty in the viewer).
   */
  public function options(?Period $period, ?AccountInterface $viewer = NULL): array {
    $owner = (int) $period?->getCreatorId();
    $out = [];
    foreach (self::SELECTORS as $selector => $type) {
      $storage = $this->entityTypeManager->getStorage($type);
      $query = $storage->getQuery()->accessCheck(FALSE)->sort('label')->sort('id');
      if ($viewer !== NULL) {
        if ($selector === 'resource') {
          $query->condition('uid', (int) $viewer->id());
        }
      }
      elseif ($selector === 'brigade' || $selector === 'resource') {
        $query->condition('uid', $owner);
      }
      $out[$selector] = [];
      foreach ($storage->loadMultiple($query->execute()) as $id => $record) {
        $out[$selector][(int) $id] = [
          'label' => (string) $record->label(),
          // The viewer names no account: its lists show the names only.
          'owner' => $viewer === NULL ? (string) ($record->getOwner()?->getDisplayName() ?? '') : '',
        ];
      }
    }
    return $out;
  }

  /**
   * Explores a period for the posted selectors.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The viewer.
   * @param string|int|null $period_id
   *   The posted period id.
   * @param array<string, mixed> $selectors
   *   The posted ids by selector (brigade, teacher, room, resource).
   * @param bool $viewer
   *   The timetable viewer's lists instead of the explorer's.
   *
   * @return array
   *   - status: 'ok', 'no_period' (none posted), 'period_refused' (not a
   *     period the account may explore), 'no_selector', or
   *     'selector_refused' (the record is not offered);
   *   - period, selector, record (id and label) when known;
   *   - options: the lists (when the period is known);
   *   - scopes: the TimetableReader scopes of the period (state flags);
   *   - placements: the matching placements, each with its scope's "draft"
   *     flag.
   */
  public function explore(AccountInterface $account, string|int|null $period_id, array $selectors, bool $viewer = FALSE): array {
    $result = [
      'status' => 'no_period',
      'period' => NULL,
      'selector' => NULL,
      'record' => NULL,
      'options' => [],
      'scopes' => [],
      'placements' => [],
    ];
    // The viewer's lists do not depend on the period (the legacy lists).
    if ($viewer) {
      $result['options'] = $this->options(NULL, $account);
    }
    if ($period_id === NULL || $period_id === '') {
      return $result;
    }
    $periods = $this->periods($account, $viewer);
    $period = ctype_digit((string) $period_id) ? ($periods[(int) $period_id] ?? NULL) : NULL;
    if (!$period instanceof Period) {
      return ['status' => 'period_refused'] + $result;
    }
    $result['period'] = $period;
    $result['options'] = $this->options($period, $viewer ? $account : NULL);

    // The last selector of the legacy order that was posted wins.
    $chosen = NULL;
    foreach (array_keys(self::SELECTORS) as $selector) {
      $value = $selectors[$selector] ?? NULL;
      if (is_scalar($value) && (string) $value !== '') {
        $chosen = [$selector, (string) $value];
      }
    }
    if ($chosen === NULL) {
      return ['status' => 'no_selector'] + $result;
    }
    [$selector, $value] = $chosen;
    $result['selector'] = $selector;
    $record = ctype_digit($value) ? ($result['options'][$selector][(int) $value] ?? NULL) : NULL;
    if ($record === NULL) {
      return ['status' => 'selector_refused'] + $result;
    }
    $id = (int) $value;
    $result['record'] = ['id' => $id] + $record;

    $plan = $period->get('plan')->entity;
    if (!$plan instanceof Plan) {
      return ['status' => 'period_refused'] + $result;
    }
    $data = $this->reader->read($plan, $period);
    $result['scopes'] = $data['scopes'];
    $groups = $selector === 'brigade' ? $this->groupsOf($id) : [];
    foreach ($data['scopes'] as $scope) {
      $draft = $scope['job'] !== NULL && !$scope['final'];
      foreach ($scope['placements'] as $p) {
        $match = match ($selector) {
          'brigade' => in_array($p['group_id'], $groups, TRUE),
          'teacher' => $p['teacher_id'] === $id,
          'room' => $p['room_id'] === $id,
          default => in_array($id, $p['resource_ids'], TRUE),
        };
        if ($match) {
          $result['placements'][] = $p + ['draft' => $draft];
        }
      }
    }
    $result['status'] = 'ok';
    return $result;
  }

  /**
   * The groups a brigade (cohort) is in now, by id.
   *
   * @return list<int>
   *   Group ids.
   */
  private function groupsOf(int $cohort): array {
    $ids = $this->entityTypeManager->getStorage('ancora_student_group')->getQuery()
      ->accessCheck(FALSE)
      ->condition('cohorts', $cohort)
      ->execute();
    return array_values(array_map('intval', $ids));
  }

}
