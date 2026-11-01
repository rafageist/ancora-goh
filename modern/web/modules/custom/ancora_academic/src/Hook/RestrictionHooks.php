<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Hook;

use Drupal\ancora_academic\Entity\TimeRestriction;
use Drupal\ancora_academic\PeriodInputs;
use Drupal\ancora_academic\RoomConstraints;
use Drupal\ancora_academic\TimeRestrictionGrid;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Period inputs are deleted with their period, plan or object.
 *
 * The legacy restriccion_de_*, receso, zpriori and higiene_para_* rows
 * reference their object and period ON DELETE CASCADE; they never block a
 * delete.
 */
final class RestrictionHooks {

  /**
   * Entity type => the restriction field that references it.
   */
  private const FIELDS = [
    'ancora_plan' => 'plan',
    'ancora_period' => 'period',
    'ancora_activity_type' => 'activity_type',
    'ancora_subject' => 'subject',
    'ancora_cohort' => 'cohort',
    'ancora_teacher' => 'teacher',
    'ancora_room' => 'room',
  ];

  public function __construct(
    private readonly TimeRestrictionGrid $grid,
    private readonly PeriodInputs $inputs,
    private readonly RoomConstraints $rooms,
  ) {}

  /**
   * Implements hook_ENTITY_TYPE_view(): a link to the restriction grid.
   */
  #[Hook('ancora_period_view')]
  #[Hook('ancora_student_group_view')]
  #[Hook('ancora_activity_type_view')]
  #[Hook('ancora_subject_view')]
  #[Hook('ancora_cohort_view')]
  #[Hook('ancora_teacher_view')]
  #[Hook('ancora_room_view')]
  public function view(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, string $view_mode): void {
    if (($view_mode !== 'full' && $view_mode !== 'default') || !$entity->access('update')) {
      return;
    }
    if ($entity->getEntityTypeId() === 'ancora_student_group') {
      // Fixed and forbidden rooms (D9c); groups have no time restrictions.
      $build['ancora_group_rooms'] = [
        '#type' => 'link',
        '#title' => new TranslatableMarkup('Fixed and forbidden rooms'),
        '#url' => Url::fromRoute('ancora_academic.room_constraints.group_rooms', [], ['query' => ['group' => $entity->id()]]),
        '#weight' => 101,
        '#prefix' => '<p class="ancora-group-rooms-link">',
        '#suffix' => '</p>',
      ];
      return;
    }
    $kind = array_search($entity->getEntityTypeId(), TimeRestriction::TARGET_TYPES, TRUE);
    $query = $kind === FALSE
      ? ['kind' => 'period', 'period' => $entity->id()]
      : ['kind' => $kind, 'target' => $entity->id()];
    if ($entity->getEntityTypeId() === 'ancora_room') {
      $build['ancora_travel_costs'] = [
        '#type' => 'link',
        '#title' => new TranslatableMarkup('Travel costs'),
        '#url' => Url::fromRoute('ancora_academic.room_constraints.travel_costs', [], ['query' => ['room' => $entity->id()]]),
        '#weight' => 102,
        '#prefix' => '<p class="ancora-travel-costs-link">',
        '#suffix' => '</p>',
      ];
    }
    if ($entity->getEntityTypeId() === 'ancora_activity_type') {
      $build['ancora_priority_zones'] = [
        '#type' => 'link',
        '#title' => new TranslatableMarkup('Priority zones'),
        '#url' => Url::fromRoute('ancora_academic.period_inputs.zones', [], ['query' => ['activity_type' => $entity->id()]]),
        '#weight' => 102,
        '#prefix' => '<p class="ancora-zones-link">',
        '#suffix' => '</p>',
      ];
    }
    $build['ancora_time_restrictions'] = [
      '#type' => 'link',
      '#title' => new TranslatableMarkup('Time restrictions'),
      '#url' => Url::fromRoute('ancora_academic.restrictions', [], ['query' => $query]),
      '#weight' => 101,
      '#prefix' => '<p class="ancora-restrictions-link">',
      '#suffix' => '</p>',
      '#cache' => ['contexts' => ['user']],
    ];
  }

  /**
   * Implements hook_entity_predelete().
   */
  #[Hook('entity_predelete')]
  public function entityPredelete(EntityInterface $entity): void {
    // Fixed and forbidden rooms, travel costs (D9c).
    if (in_array($entity->getEntityTypeId(), ['ancora_plan', 'ancora_student_group', 'ancora_room'], TRUE)) {
      $this->rooms->deleteFor($entity->getEntityTypeId(), $entity->id());
    }
    $field = self::FIELDS[$entity->getEntityTypeId()] ?? NULL;
    if ($field !== NULL && !$entity instanceof TimeRestriction) {
      $this->grid->deleteFor($field, $entity->id());
      // Breaks, zones and load limits (D9b) cascade the same way.
      $this->inputs->deleteFor($field, $entity->id());
    }
  }

}
