<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Derivative;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The academic lists in the main menu, under their navigation section.
 *
 * The links carry no counts: a menu shows the same items to every user with
 * the same permissions, so it never reveals anything about another owner.
 */
final class AcademicMainMenuLinks extends DeriverBase implements ContainerDeriverInterface {

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
    foreach (AcademicEntityTypes::SECTIONS as $section => $entity_type_ids) {
      $weight = 0;
      foreach ($entity_type_ids as $entity_type_id) {
        $this->derivatives[$entity_type_id] = [
          'title' => $this->entityTypeManager->getDefinition($entity_type_id)->getCollectionLabel(),
          'route_name' => "entity.$entity_type_id.collection",
          'menu_name' => 'main',
          'parent' => "ancora_academic.main.$section",
          'weight' => $weight++,
        ] + $base_plugin_definition;
      }
    }
    return $this->derivatives;
  }

}
