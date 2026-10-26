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
 * A teaching group: cohorts that attend one activity type together.
 *
 * Legacy tables "grupo" and "brigada_de_grupo".
 */
#[ContentEntityType(
  id: 'ancora_student_group',
  label: new TranslatableMarkup('Student group'),
  label_collection: new TranslatableMarkup('Student groups'),
  label_singular: new TranslatableMarkup('student group'),
  label_plural: new TranslatableMarkup('student groups'),
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
    'canonical' => '/admin/ancora/groups/{ancora_student_group}',
    'add-form' => '/admin/ancora/groups/add',
    'edit-form' => '/admin/ancora/groups/{ancora_student_group}/edit',
    'delete-form' => '/admin/ancora/groups/{ancora_student_group}/delete',
    'collection' => '/admin/ancora/groups',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_student_group',
  label_count: [
    'singular' => '@count student group',
    'plural' => '@count student groups',
  ],
  constraints: [
    'AncoraUniqueInScope' => ['fields' => ['code']],
    'AncoraSameCreator' => [],
    'AncoraGroupCohorts' => [],
  ],
)]
final class StudentGroup extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "Lecture group 1".'),
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

    $fields['activity_type'] = static::referenceField(
      'ancora_activity_type',
      new TranslatableMarkup('Activity type'),
      new TranslatableMarkup('The kind of activity this group attends together.'),
      3,
    )->setRequired(TRUE);

    $fields['cohorts'] = static::referenceField(
      'ancora_cohort',
      new TranslatableMarkup('Cohorts'),
      new TranslatableMarkup('The cohorts in the group. They must have the specialty and level of the group.'),
      4,
      TRUE,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'plan', 'specialty', 'level', 'activity_type', 'cohorts'];
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
   * Returns the activity type.
   */
  public function getActivityType(): ?AcademicEntityInterface {
    return $this->getReferenced('activity_type');
  }

  /**
   * Returns the IDs of the cohorts in the group.
   *
   * @return list<int>
   *   The referenced IDs, in field order.
   */
  public function getCohortIds(): array {
    return array_map('intval', array_column($this->get('cohorts')->getValue(), 'target_id'));
  }

}
