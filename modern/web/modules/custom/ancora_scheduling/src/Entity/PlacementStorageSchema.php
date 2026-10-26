<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Entity;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Database keys of the placements: what makes publication idempotent.
 *
 * - engine_id is unique: a placement published twice is the same record.
 * - (template, student_group) is unique, as UNIQUE (plantilla, grupo) is in
 *   the legacy table "actividad".
 */
final class PlacementStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE): array {
    $schema = parent::getEntitySchema($entity_type, $reset);
    $table = $this->storage->getBaseTable();
    $schema[$table]['unique keys'] = ($schema[$table]['unique keys'] ?? []) + [
      'ancora_placement__engine_id' => ['engine_id'],
      'ancora_placement__template_group' => ['template', 'student_group'],
    ];
    return $schema;
  }

}
