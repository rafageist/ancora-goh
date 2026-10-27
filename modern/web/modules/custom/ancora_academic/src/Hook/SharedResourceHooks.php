<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Hook;

use Drupal\ancora_academic\Entity\ResourceGroup;
use Drupal\ancora_academic\SharedResources;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * The upkeep of resource groups and their resources (D9d).
 *
 * - Cascades: the legacy otro_grupo_de_recursos_para_actividad and
 *   otro_recurso_para_actividad rows reference the template, the group and
 *   the resource ON DELETE CASCADE; they never block a delete.
 * - The priority relevel of trigger nivela_prioridad_rxact_after_delete
 *   (PAR-058, quirk Q-06, case 112): when no group of ANY template keeps
 *   the deleted group's priority, the higher priorities of its template
 *   move down by one. There is no trigger on update.
 * - The link to the resource editor of a template.
 */
final class SharedResourceHooks {

  public function __construct(
    private readonly SharedResources $resources,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_entity_predelete(): the cascades.
   */
  #[Hook('entity_predelete')]
  public function entityPredelete(EntityInterface $entity): void {
    $types = ['ancora_plan', 'ancora_activity_template', 'ancora_resource_group', 'ancora_other_resource'];
    if (in_array($entity->getEntityTypeId(), $types, TRUE)) {
      $this->resources->deleteFor($entity->getEntityTypeId(), $entity->id());
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_delete() for resource groups: the relevel.
   */
  #[Hook('ancora_resource_group_delete')]
  public function groupDelete(EntityInterface $entity): void {
    assert($entity instanceof ResourceGroup);
    $priority = (int) $entity->get('priority')->value;
    $storage = $this->entityTypeManager->getStorage('ancora_resource_group');
    $kept = (int) $storage->getQuery()->accessCheck(FALSE)->condition('priority', $priority)->count()->execute();
    $template = $entity->get('template')->target_id;
    if ($kept > 0 || $template === NULL) {
      return;
    }
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('template', $template)->condition('priority', $priority, '>')->execute();
    foreach ($storage->loadMultiple($ids) as $group) {
      $group->set('priority', (int) $group->get('priority')->value - 1)->save();
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_view() for templates: the editor link.
   */
  #[Hook('ancora_activity_template_view')]
  public function templateView(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, string $view_mode): void {
    if (($view_mode !== 'full' && $view_mode !== 'default') || !$entity->access('update')) {
      return;
    }
    $build['ancora_template_resources'] = [
      '#type' => 'link',
      '#title' => new TranslatableMarkup('Other resources'),
      '#url' => Url::fromRoute('ancora_academic.template_resources', [], ['query' => ['template' => $entity->id()]]),
      '#weight' => 103,
      '#prefix' => '<p class="ancora-template-resources-link">',
      '#suffix' => '</p>',
    ];
  }

}
