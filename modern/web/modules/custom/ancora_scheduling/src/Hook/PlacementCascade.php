<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Deleting a record deletes its placements, as the legacy foreign keys do.
 *
 * Legacy "actividad" references its template, group, teacher and room ON
 * DELETE CASCADE, and through the template its breakdown, subject and
 * period (legacy-parity-inventory.md PAR-101..103, case 114). Placements do
 * not block a delete (they are not academic records for ReferenceGuard);
 * the records the academic delete guard already protects still need their
 * dependents removed first (D5).
 *
 * An other resource is different: legacy otros_recursos_usados references
 * it ON DELETE CASCADE, so deleting it removes it from the placements that
 * used it, and the placements stay (D9d).
 */
final class PlacementCascade {

  /**
   * Referenced type => placement field.
   */
  private const FIELDS = [
    'ancora_plan' => 'plan',
    'ancora_period' => 'period',
    'ancora_activity_template' => 'template',
    'ancora_student_group' => 'student_group',
    'ancora_teacher' => 'teacher',
    'ancora_room' => 'room',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_entity_predelete().
   */
  #[Hook('entity_predelete')]
  public function entityPredelete(EntityInterface $entity): void {
    if ($entity->getEntityTypeId() === 'ancora_other_resource') {
      $this->removeResource((int) $entity->id());
      return;
    }
    $field = self::FIELDS[$entity->getEntityTypeId()] ?? NULL;
    if ($field === NULL) {
      return;
    }
    $storage = $this->entityTypeManager->getStorage('ancora_placement');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition($field, $entity->id())->execute();
    if ($ids !== []) {
      $storage->delete($storage->loadMultiple($ids));
    }
  }

  /**
   * Removes an other resource from the placements that use it.
   */
  private function removeResource(int $resource): void {
    $storage = $this->entityTypeManager->getStorage('ancora_placement');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('other_resources', $resource)->execute();
    foreach ($storage->loadMultiple($ids) as $placement) {
      $kept = array_values(array_filter(
        $placement->get('other_resources')->getValue(),
        static fn (array $item): bool => (int) $item['target_id'] !== $resource,
      ));
      $placement->set('other_resources', $kept)->save();
    }
  }

}
