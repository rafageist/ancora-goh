<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Controller;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * The planning overview: every academic list, in the order of data entry.
 */
final class PlanningController extends ControllerBase {

  /**
   * Builds the planning overview.
   */
  public function overview(): array {
    $descriptions = AcademicEntityTypes::descriptions();
    $cache = (new CacheableMetadata())->addCacheContexts(['user.permissions']);
    $steps = [];
    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      $entity_type = $this->entityTypeManager()->getDefinition($entity_type_id);
      $storage = $this->entityTypeManager()->getStorage($entity_type_id);
      $count = (int) $storage->getQuery()->accessCheck(TRUE)->count()->execute();
      $cache->addCacheTags($entity_type->getListCacheTags());

      $add = $this->entityTypeManager()->getAccessControlHandler($entity_type_id)->createAccess(NULL, NULL, [], TRUE);
      $cache->addCacheableDependency($add);
      $url = Url::fromRoute("entity.$entity_type_id.collection")->toString(TRUE);
      $cache->addCacheableDependency($url);
      $add_url = NULL;
      if ($add->isAllowed()) {
        $add_url = Url::fromRoute("entity.$entity_type_id.add_form")->toString(TRUE);
        $cache->addCacheableDependency($add_url);
      }
      $steps[] = [
        'label' => $entity_type->getCollectionLabel(),
        'description' => $descriptions[$entity_type_id],
        'count' => $count,
        'url' => $url->getGeneratedUrl(),
        'add_url' => $add_url?->getGeneratedUrl(),
        'add_label' => $this->t('Add @type', ['@type' => $entity_type->getSingularLabel()]),
      ];
    }

    $build = [
      '#theme' => 'ancora_academic_planning',
      '#intro' => $this->t('Enter the planning data in this order. The scheduling engine reads one plan and period at a time.'),
      '#steps' => $steps,
    ];
    $cache->applyTo($build);
    return $build;
  }

}
