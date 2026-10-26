<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Controller;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The planning overview: every academic list, in the order of data entry.
 *
 * The section pages of the planner navigation (#105) show the lists of one
 * section (AcademicEntityTypes::SECTIONS) the same way.
 */
final class PlanningController extends ControllerBase {

  /**
   * Builds the planning overview.
   */
  public function overview(): array {
    return $this->build(
      AcademicEntityTypes::ids(),
      $this->t('Enter the planning data in this order. The scheduling engine reads one plan and period at a time.'),
    );
  }

  /**
   * Builds the page of one navigation section.
   */
  public function section(string $section): array {
    if (!isset(AcademicEntityTypes::SECTIONS[$section])) {
      throw new NotFoundHttpException();
    }
    return $this->build(AcademicEntityTypes::SECTIONS[$section], AcademicEntityTypes::sections()[$section]['intro']);
  }

  /**
   * Title of a section page.
   */
  public function sectionTitle(string $section): TranslatableMarkup {
    return AcademicEntityTypes::sections()[$section]['title'] ?? $this->t('Planning');
  }

  /**
   * The list of record types with the user's counts and add links.
   *
   * @param list<string> $entity_type_ids
   *   The entity types, in display order.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $intro
   *   The introductory sentence.
   */
  private function build(array $entity_type_ids, TranslatableMarkup $intro): array {
    $descriptions = AcademicEntityTypes::descriptions();
    // The counts are the user's own records (AcademicQueryAccess).
    $cache = (new CacheableMetadata())->addCacheContexts(['user.permissions', 'user']);
    $steps = [];
    foreach ($entity_type_ids as $entity_type_id) {
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
      '#intro' => $intro,
      '#steps' => $steps,
    ];
    $cache->applyTo($build);
    return $build;
  }

}
