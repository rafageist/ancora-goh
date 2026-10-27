<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicListBuilder;
use Drupal\ancora_academic\Form\AcademicDeleteForm;
use Drupal\ancora_academic\Form\AcademicEntityForm;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * A shared resource other than a teacher or a room (legacy "otro_recurso").
 *
 * Legacy: dev/sql/recurso/otro_recurso.sql (id, nombre, creador), a
 * catalogue record of its creator (PAR-003, PAR-057), entered with
 * add_OtroRecurso (the name; creador from the session). Activity templates
 * need resources through resource groups (ResourceGroup); a placed activity
 * records the resources it got (Placement "other_resources", legacy
 * otros_recursos_usados).
 */
#[ContentEntityType(
  id: 'ancora_other_resource',
  label: new TranslatableMarkup('Other resource'),
  label_collection: new TranslatableMarkup('Other resources'),
  label_singular: new TranslatableMarkup('other resource'),
  label_plural: new TranslatableMarkup('other resources'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'label',
    'owner' => 'uid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
    'list_builder' => AcademicListBuilder::class,
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => AcademicEntityForm::class,
      'add' => AcademicEntityForm::class,
      'edit' => AcademicEntityForm::class,
      'delete' => AcademicDeleteForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
  ],
  links: [
    'canonical' => '/admin/ancora/other-resources/{ancora_other_resource}',
    'add-form' => '/admin/ancora/other-resources/add',
    'edit-form' => '/admin/ancora/other-resources/{ancora_other_resource}/edit',
    'delete-form' => '/admin/ancora/other-resources/{ancora_other_resource}/delete',
    'collection' => '/admin/ancora/other-resources',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_other_resource',
  label_count: [
    'singular' => '@count other resource',
    'plural' => '@count other resources',
  ],
  constraints: [
    'AncoraUniqueInScope' => ['fields' => ['code']],
    'AncoraSameCreator' => [],
  ],
)]
final class OtherResource extends OwnedAcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('The name of the resource, for example a projector.'),
    );
    $fields['code'] = static::codeField();

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'uid'];
  }

}
