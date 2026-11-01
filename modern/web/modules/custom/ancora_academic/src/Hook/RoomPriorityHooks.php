<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Hook;

use Drupal\ancora_academic\Entity\RoomAssignment;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * The legacy priority upkeep of the rooms of a template (PAR-055, D9c).
 *
 * The triggers of lugar_para_actividad (case 141):
 * - nivela_prioridad_lxact_after_delete: when no other room of the
 *   template keeps the deleted priority, the template's higher priorities
 *   move down by one (the gap is closed);
 * - nivela_prioridad_lxact_after_update (quirk Q-07): when a priority
 *   changes and NO room assignment of any template keeps the old one, the
 *   template's priorities above the old one move down by one, the updated
 *   row included; otherwise nothing happens.
 * The Drupal records follow the same rules, so that the engine rows (which
 * the sync writes from the records) equal the legacy ones.
 */
final class RoomPriorityHooks {

  /**
   * TRUE while this class saves records (its own saves do not relevel).
   */
  private static bool $running = FALSE;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_ENTITY_TYPE_delete() for room assignments.
   */
  #[Hook('ancora_room_assignment_delete')]
  public function delete(EntityInterface $entity): void {
    assert($entity instanceof RoomAssignment);
    $priority = (int) $entity->get('priority')->value;
    $template = $entity->get('template')->target_id;
    if ($template === NULL || $this->count(['template' => $template, 'priority' => $priority]) > 0) {
      return;
    }
    $this->shift((int) $template, $priority);
  }

  /**
   * Implements hook_ENTITY_TYPE_update() for room assignments.
   */
  #[Hook('ancora_room_assignment_update')]
  public function update(EntityInterface $entity): void {
    $original = $entity->getOriginal();
    if (self::$running || !$original instanceof RoomAssignment) {
      return;
    }
    $old = (int) $original->get('priority')->value;
    if ($old === (int) $entity->get('priority')->value || $this->count(['priority' => $old]) > 0) {
      return;
    }
    $this->shift((int) $entity->get('template')->target_id, $old);
  }

  /**
   * Moves the template's priorities above a priority down by one.
   */
  private function shift(int $template, int $priority): void {
    $storage = $this->entityTypeManager->getStorage('ancora_room_assignment');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('template', $template)->condition('priority', $priority, '>')->execute();
    self::$running = TRUE;
    try {
      foreach ($storage->loadMultiple($ids) as $assignment) {
        $assignment->set('priority', (int) $assignment->get('priority')->value - 1)->save();
      }
    }
    finally {
      self::$running = FALSE;
    }
  }

  /**
   * Counts the room assignments with some values.
   */
  private function count(array $conditions): int {
    $query = $this->entityTypeManager->getStorage('ancora_room_assignment')->getQuery()->accessCheck(FALSE);
    foreach ($conditions as $field => $value) {
      $query->condition($field, $value);
    }
    return (int) $query->count()->execute();
  }

}
