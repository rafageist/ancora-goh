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
 * A teacher (legacy table "profesor").
 */
#[ContentEntityType(
  id: 'ancora_teacher',
  label: new TranslatableMarkup('Teacher'),
  label_collection: new TranslatableMarkup('Teachers'),
  label_singular: new TranslatableMarkup('teacher'),
  label_plural: new TranslatableMarkup('teachers'),
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
    'canonical' => '/admin/ancora/teachers/{ancora_teacher}',
    'add-form' => '/admin/ancora/teachers/add',
    'edit-form' => '/admin/ancora/teachers/{ancora_teacher}/edit',
    'delete-form' => '/admin/ancora/teachers/{ancora_teacher}/delete',
    'collection' => '/admin/ancora/teachers',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_teacher',
  label_count: [
    'singular' => '@count teacher',
    'plural' => '@count teachers',
  ],
  constraints: [
    'AncoraUniqueInScope' => ['fields' => ['code']],
    'AncoraSameCreator' => [],
  ],
)]
final class Teacher extends OwnedAcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('The full name of the teacher.'),
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
