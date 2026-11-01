<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Derivative;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * An "Add ..." action on every academic list.
 */
final class AcademicLocalActions extends DeriverBase implements ContainerDeriverInterface {

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
    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $this->derivatives[$entity_type_id] = [
        'title' => new TranslatableMarkup('Add @type', ['@type' => $entity_type->getSingularLabel()]),
        'route_name' => "entity.$entity_type_id.add_form",
        'appears_on' => ["entity.$entity_type_id.collection"],
      ] + $base_plugin_definition;
    }
    return $this->derivatives;
  }

}
