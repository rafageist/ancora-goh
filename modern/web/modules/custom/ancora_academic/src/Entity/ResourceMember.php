<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * An other resource of a resource group (D9d).
 *
 * Legacy: dev/sql/recurso/asignacion/otro_recurso_para_actividad.sql:
 * grupo_de_recursos, prioridad (default 1), otro_recurso, PRIMARY KEY
 * (grupo_de_recursos, otro_recurso). The legacy window adds the checked
 * resources together, all with the group's highest priority + 1
 * (form_rxact.tpl, dame_max_priori_otro_recurso), and has no editor for
 * the priority of one resource. It belongs to the group's plan.
 */
#[ContentEntityType(
  id: 'ancora_resource_member',
  label: new TranslatableMarkup('Resource of a group'),
  label_collection: new TranslatableMarkup('Resources of groups'),
  label_singular: new TranslatableMarkup('resource of a group'),
  label_plural: new TranslatableMarkup('resources of groups'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_resource_member',
  label_count: [
    'singular' => '@count resource of a group',
    'plural' => '@count resources of groups',
  ],
  constraints: [
    'AncoraSameCreator' => [],
    'AncoraUniqueInScope' => [
      'fields' => ['resource_group', 'other_resource'],
      'message' => 'The resource is already in this group.',
    ],
  ],
)]
final class ResourceMember extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['resource_group'] = static::referenceField('ancora_resource_group', new TranslatableMarkup('Resource group'), new TranslatableMarkup('The group.'), -9)->setRequired(TRUE);
    $fields['other_resource'] = static::referenceField('ancora_other_resource', new TranslatableMarkup('Other resource'), new TranslatableMarkup('The resource.'), -8)->setRequired(TRUE);
    $fields['priority'] = static::integerField(new TranslatableMarkup('Priority'), new TranslatableMarkup('The engine takes the resources in priority order, then the least used.'), -7, self::INT_MIN, self::INT_MAX, 1);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'resource_group', 'other_resource', 'priority'];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $group = $this->get('resource_group')->entity;
    if ($group instanceof ResourceGroup && $group->getPlanId() !== NULL) {
      $this->set('plan', $group->getPlanId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $resource = $this->get('other_resource')->entity;
    return sprintf('%s in group %d', $resource ? $resource->label() : '?', (int) $this->get('resource_group')->target_id);
  }

}
