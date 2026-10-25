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
 * A year or level of study (legacy table "nivel").
 */
#[ContentEntityType(
  id: 'ancora_level',
  label: new TranslatableMarkup('Level'),
  label_collection: new TranslatableMarkup('Levels'),
  label_singular: new TranslatableMarkup('level'),
  label_plural: new TranslatableMarkup('levels'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'label',
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
    'canonical' => '/admin/ancora/levels/{ancora_level}',
    'add-form' => '/admin/ancora/levels/add',
    'edit-form' => '/admin/ancora/levels/{ancora_level}/edit',
    'delete-form' => '/admin/ancora/levels/{ancora_level}/delete',
    'collection' => '/admin/ancora/levels',
  ],
  admin_permission: 'administer academic data',
  collection_permission: 'view academic data',
  base_table: 'ancora_level',
  label_count: [
    'singular' => '@count level',
    'plural' => '@count levels',
  ],
  constraints: [
    'AncoraUniqueInPlan' => ['fields' => ['code']],
    'AncoraSamePlan' => [],
  ],
)]
final class Level extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "Year 1".'),
    );
    $fields['code'] = static::codeField();

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'plan'];
  }

}
