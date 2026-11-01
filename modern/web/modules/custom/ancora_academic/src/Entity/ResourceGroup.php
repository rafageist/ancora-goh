<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * A group of other resources an activity template needs (D9d).
 *
 * Legacy: dev/sql/recurso/asignacion/
 * otro_grupo_de_recursos_para_actividad.sql: id, plantilla, prioridad
 * (default 1), necesarios (default 1), imprescindibles (default TRUE),
 * creador. The legacy window (ancoragoh_server_recursos.js:68-75) adds a
 * group with exactly these values; "needed" is chosen in 1..(resources in
 * the group), "essential" is a checkbox, the priority in 1..max + 1
 * (custom_getui_EditarPrioridadRxact.php). Deleting a group closes the
 * priority gap of its template only when no group of any template keeps
 * the priority (trigger nivela_prioridad_rxact_after_delete, Q-06,
 * SharedResourceHooks). Edited only with the template's resource editor
 * (SharedResourcesForm). It belongs to the template's plan.
 *
 * How generar() uses the groups (case 142): see SharedResources.
 */
#[ContentEntityType(
  id: 'ancora_resource_group',
  label: new TranslatableMarkup('Resource group'),
  label_collection: new TranslatableMarkup('Resource groups'),
  label_singular: new TranslatableMarkup('resource group'),
  label_plural: new TranslatableMarkup('resource groups'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_resource_group',
  label_count: [
    'singular' => '@count resource group',
    'plural' => '@count resource groups',
  ],
  constraints: [
    'AncoraSameCreator' => [],
  ],
)]
final class ResourceGroup extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['template'] = static::referenceField('ancora_activity_template', new TranslatableMarkup('Activity template'), new TranslatableMarkup('The weekly activity that needs the resources.'), -9)->setRequired(TRUE);
    // The legacy columns are INTEGER without a CHECK; the legacy window
    // offers 1 and more for both.
    $fields['priority'] = static::integerField(new TranslatableMarkup('Priority'), new TranslatableMarkup('The priority of the group.'), -8, 1, self::INT_MAX, 1);
    $fields['needed'] = static::integerField(new TranslatableMarkup('Needed'), new TranslatableMarkup('How many resources of the group the activity needs.'), -7, 1, self::INT_MAX, 1);
    $fields['essential'] = static::booleanField(new TranslatableMarkup('Essential'), new TranslatableMarkup('Whether the activity cannot be placed without them.'), -6, TRUE);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'template', 'priority', 'needed', 'essential'];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $template = $this->get('template')->entity;
    if ($template instanceof ActivityTemplate && $template->getPlanId() !== NULL) {
      $this->set('plan', $template->getPlanId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $template = $this->get('template')->entity;
    return sprintf('%s: resource group %d', $template ? $template->label() : '?', (int) $this->id());
  }

}
