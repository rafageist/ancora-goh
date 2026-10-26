<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicEntityInterface;
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
 * A subject of one specialty and level (legacy table "asignatura").
 */
#[ContentEntityType(
  id: 'ancora_subject',
  label: new TranslatableMarkup('Subject'),
  label_collection: new TranslatableMarkup('Subjects'),
  label_singular: new TranslatableMarkup('subject'),
  label_plural: new TranslatableMarkup('subjects'),
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
    'canonical' => '/admin/ancora/subjects/{ancora_subject}',
    'add-form' => '/admin/ancora/subjects/add',
    'edit-form' => '/admin/ancora/subjects/{ancora_subject}/edit',
    'delete-form' => '/admin/ancora/subjects/{ancora_subject}/delete',
    'collection' => '/admin/ancora/subjects',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_subject',
  label_count: [
    'singular' => '@count subject',
    'plural' => '@count subjects',
  ],
  constraints: [
    'AncoraUniqueInScope' => ['fields' => ['code']],
    'AncoraSameCreator' => [],
  ],
)]
final class Subject extends OwnedAcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "Calculus".'),
    );
    $fields['code'] = static::codeField();

    $fields['specialty'] = static::referenceField(
      'ancora_specialty',
      new TranslatableMarkup('Specialty'),
      new TranslatableMarkup('The specialty (degree programme).'),
      1,
    )->setRequired(TRUE);

    $fields['level'] = static::referenceField(
      'ancora_level',
      new TranslatableMarkup('Level'),
      new TranslatableMarkup('The year or level of study.'),
      2,
    )->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'uid', 'specialty', 'level'];
  }

  /**
   * Returns the specialty.
   */
  public function getSpecialty(): ?AcademicEntityInterface {
    return $this->getReferenced('specialty');
  }

  /**
   * Returns the level.
   */
  public function getLevel(): ?AcademicEntityInterface {
    return $this->getReferenced('level');
  }

}
