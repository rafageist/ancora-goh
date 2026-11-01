<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Derivative;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * One administration menu link per academic list, under "Planning".
 */
final class AcademicMenuLinks extends DeriverBase implements ContainerDeriverInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): static {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $descriptions = AcademicEntityTypes::descriptions();
    $weight = 0;
    foreach (AcademicEntityTypes::all() as $entity_type_id) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $this->derivatives[$entity_type_id] = [
        'title' => $entity_type->getCollectionLabel(),
        'description' => $descriptions[$entity_type_id],
        'route_name' => "entity.$entity_type_id.collection",
        'parent' => 'ancora_academic.planning',
        'weight' => $weight++,
      ] + $base_plugin_definition;
    }
    return $this->derivatives;
  }

}
