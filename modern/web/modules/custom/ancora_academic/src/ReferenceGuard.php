<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Finds the academic records that reference a record.
 *
 * Used to refuse deleting a record that is still in use: a plan with any
 * record, a subject with templates, a template with assignments, and so on.
 * Nothing cascades; dependents are deleted first, explicitly.
 */
final class ReferenceGuard {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EntityFieldManagerInterface $entityFieldManager,
  ) {}

  /**
   * Counts the academic records that reference an entity.
   *
   * @return array<string, int>
   *   Number of referencing records keyed by entity type ID; only non-zero
   *   counts are returned.
   */
  public function dependents(EntityInterface $entity): array {
    if ($entity->isNew()) {
      return [];
    }
    $counts = [];
    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      foreach ($this->entityFieldManager->getBaseFieldDefinitions($entity_type_id) as $name => $definition) {
        if ($definition->getType() !== 'entity_reference' || $definition->getSetting('target_type') !== $entity->getEntityTypeId()) {
          continue;
        }
        $count = (int) $this->entityTypeManager->getStorage($entity_type_id)->getQuery()
          ->accessCheck(FALSE)
          ->condition($name, $entity->id())
          ->count()
          ->execute();
        if ($count > 0) {
          $counts[$entity_type_id] = ($counts[$entity_type_id] ?? 0) + $count;
        }
      }
    }
    return $counts;
  }

  /**
   * Describes dependent counts in words, for example "2 periods, 1 subject".
   *
   * @param array<string, int> $counts
   *   Counts keyed by entity type ID, as returned by dependents().
   */
  public function describe(array $counts): string {
    $parts = [];
    foreach ($counts as $entity_type_id => $count) {
      $type = $this->entityTypeManager->getDefinition($entity_type_id);
      $parts[] = (string) $type->getCountLabel($count);
    }
    return implode(', ', $parts);
  }

}
