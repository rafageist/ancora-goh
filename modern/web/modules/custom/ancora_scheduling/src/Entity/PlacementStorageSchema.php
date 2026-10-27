<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Entity;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Database keys of the placements: what makes publication idempotent.
 *
 * - engine_id is unique: a placement published twice is the same record.
 * - (template, student_group) is unique, as UNIQUE (plantilla, grupo) is in
 *   the legacy table "actividad".
 * - placed_at is double precision, as momento_de_colocacion is: a "float"
 *   field is single precision (real) by default, which keeps only about 7
 *   digits and loses the hour part of the moment (#96).
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

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping): array {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    if ($storage_definition->getName() === 'placed_at') {
      $schema['fields']['placed_at']['size'] = 'big';
    }
    return $schema;
  }

}
