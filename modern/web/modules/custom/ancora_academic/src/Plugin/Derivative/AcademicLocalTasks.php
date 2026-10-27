<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Derivative;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * View, edit and delete tabs of every academic record.
 */
final class AcademicLocalTasks extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    foreach (AcademicEntityTypes::all() as $entity_type_id) {
      $base_route = "entity.$entity_type_id.canonical";
      $this->derivatives["$entity_type_id.canonical"] = [
        'title' => new TranslatableMarkup('View'),
        'route_name' => $base_route,
        'base_route' => $base_route,
      ] + $base_plugin_definition;
      $this->derivatives["$entity_type_id.edit_form"] = [
        'title' => new TranslatableMarkup('Edit'),
        'route_name' => "entity.$entity_type_id.edit_form",
        'base_route' => $base_route,
      ] + $base_plugin_definition;
      if (in_array($entity_type_id, AcademicEntityTypes::DERIVED, TRUE)) {
        // Kept by the module; never deleted by a user.
        continue;
      }
      $this->derivatives["$entity_type_id.delete_form"] = [
        'title' => new TranslatableMarkup('Delete'),
        'route_name' => "entity.$entity_type_id.delete_form",
        'base_route' => $base_route,
        'weight' => 10,
      ] + $base_plugin_definition;
    }
    return $this->derivatives;
  }

}
