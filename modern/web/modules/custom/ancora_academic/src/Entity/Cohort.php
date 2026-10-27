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
 * A cohort of students of one specialty and level (legacy table "brigada").
 */
#[ContentEntityType(
  id: 'ancora_cohort',
  label: new TranslatableMarkup('Cohort'),
  label_collection: new TranslatableMarkup('Cohorts'),
  label_singular: new TranslatableMarkup('cohort'),
  label_plural: new TranslatableMarkup('cohorts'),
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
    'canonical' => '/admin/ancora/cohorts/{ancora_cohort}',
    'add-form' => '/admin/ancora/cohorts/add',
    'edit-form' => '/admin/ancora/cohorts/{ancora_cohort}/edit',
    'delete-form' => '/admin/ancora/cohorts/{ancora_cohort}/delete',
    'collection' => '/admin/ancora/cohorts',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_cohort',
  label_count: [
    'singular' => '@count cohort',
    'plural' => '@count cohorts',
  ],
  constraints: [
    'AncoraUniqueInScope' => ['fields' => ['code']],
    'AncoraSameCreator' => [],
  ],
)]
final class Cohort extends OwnedAcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "Cohort 1".'),
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

    $fields['enrollment'] = static::integerField(
      new TranslatableMarkup('Enrolment'),
      new TranslatableMarkup('Number of students, compared with room capacity.'),
      3,
      // Any integer, as the legacy column (matricula INTEGER default 0) and
      // its free-text input store it (#70, D6; case 136).
      self::INT_MIN,
      self::INT_MAX,
      0,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'uid', 'specialty', 'level', 'enrollment'];
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

  /**
   * Returns the number of students.
   */
  public function getEnrollment(): ?int {
    return $this->getInteger('enrollment');
  }

}
