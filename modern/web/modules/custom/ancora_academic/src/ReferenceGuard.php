<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * The academic records that reference a record, and the legacy cascade.
 *
 * Legacy deletes a record directly by key (control/data/del/*.php) and every
 * foreign key is ON DELETE CASCADE (PAR-100..103; cases 114, 145): a delete
 * never fails because of a reference. A record held in a single reference
 * (a legacy foreign key column) is deleted with what it references, through
 * the Entity API, so its own cascades run too. A record that holds it in a
 * multi-value reference (a legacy link table: the cohorts of a group, the
 * groups of a teacher assignment) only loses the link and stays.
 *
 * Grid records, breakdowns and placements have their own cascade hooks
 * (RestrictionHooks, RoomConstraints, SharedResources, BreakdownHooks,
 * PlacementCascade), with the same legacy semantics.
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
    $counts = [];
    foreach ($this->references($entity) as [$entity_type_id, , $ids]) {
      $counts[$entity_type_id] = ($counts[$entity_type_id] ?? 0) + count($ids);
    }
    return $counts;
  }

  /**
   * Applies the legacy cascade of an entity that is being deleted.
   *
   * Single references: the referencing records are deleted. Multi-value
   * references: the link is removed and the record saved.
   */
  public function cascade(EntityInterface $entity): void {
    foreach ($this->references($entity) as [$entity_type_id, $field, $ids, $multiple]) {
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      $records = $storage->loadMultiple($ids);
      if (!$multiple) {
        if ($records !== []) {
          $storage->delete($records);
        }
        continue;
      }
      foreach ($records as $record) {
        $kept = array_values(array_filter(
          $record->get($field)->getValue(),
          static fn (array $item): bool => (string) $item['target_id'] !== (string) $entity->id(),
        ));
        $record->set($field, $kept)->save();
      }
    }
  }

  /**
   * The academic records that reference an entity, by field.
   *
   * @return list<array{0: string, 1: string, 2: list<int|string>, 3: bool}>
   *   Entity type, field, referencing ids, and whether the field is
   *   multi-value.
   */
  private function references(EntityInterface $entity): array {
    if ($entity->isNew()) {
      return [];
    }
    $out = [];
    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      foreach ($this->entityFieldManager->getBaseFieldDefinitions($entity_type_id) as $name => $definition) {
        if ($definition->getType() !== 'entity_reference' || $definition->getSetting('target_type') !== $entity->getEntityTypeId()) {
          continue;
        }
        $ids = array_values($this->entityTypeManager->getStorage($entity_type_id)->getQuery()
          ->accessCheck(FALSE)
          ->condition($name, $entity->id())
          ->sort('id')
          ->execute());
        if ($ids !== []) {
          $out[] = [$entity_type_id, $name, $ids, $definition->getCardinality() !== 1];
        }
      }
    }
    return $out;
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
