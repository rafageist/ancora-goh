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
 * One weekly activity of a subject in a period.
 *
 * Legacy tables "desglose" (subject x period) and "plantilla_de_actividad"
 * (one row per weekly activity, ordered by "orden"). The engine creates one
 * activity per template and eligible student group.
 */
#[ContentEntityType(
  id: 'ancora_activity_template',
  label: new TranslatableMarkup('Activity template'),
  label_collection: new TranslatableMarkup('Activity templates'),
  label_singular: new TranslatableMarkup('activity template'),
  label_plural: new TranslatableMarkup('activity templates'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
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
    'canonical' => '/admin/ancora/templates/{ancora_activity_template}',
    'add-form' => '/admin/ancora/templates/add',
    'edit-form' => '/admin/ancora/templates/{ancora_activity_template}/edit',
    'delete-form' => '/admin/ancora/templates/{ancora_activity_template}/delete',
    'collection' => '/admin/ancora/templates',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_activity_template',
  label_count: [
    'singular' => '@count activity template',
    'plural' => '@count activity templates',
  ],
  constraints: [
    'AncoraUniqueInScope' => [
      'fields' => ['subject', 'period', 'sequence'],
      'message' => 'The subject already has an activity template with this number in this period.',
    ],
    'AncoraSameCreator' => [],
  ],
)]
final class ActivityTemplate extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['subject'] = static::referenceField(
      'ancora_subject',
      new TranslatableMarkup('Subject'),
      new TranslatableMarkup('The subject this weekly activity belongs to.'),
      -10,
    )->setRequired(TRUE);

    $fields['period'] = static::referenceField(
      'ancora_period',
      new TranslatableMarkup('Period'),
      new TranslatableMarkup('The period in which the activity takes place every cycle.'),
      -9,
    )->setRequired(TRUE);

    // Kept as legacy keeps "orden" (OrderUpkeep, D13): a new template is
    // appended, the order changes only with the moves, a delete renumbers.
    // Not on the forms (the legacy forms post it as a hidden field).
    $fields['sequence'] = static::integerField(
      new TranslatableMarkup('Number'),
      new TranslatableMarkup('Order of the activity within the subject and period: 1 for the first weekly activity, 2 for the second...'),
      -8,
      1,
    )->setRequired(FALSE)->setDisplayOptions('form', ['region' => 'hidden']);

    $fields['activity_type'] = static::referenceField(
      'ancora_activity_type',
      new TranslatableMarkup('Activity type'),
      new TranslatableMarkup('The kind of activity. It sets the length and selects the student groups that attend it.'),
      -7,
    )->setRequired(TRUE);

    $fields['requires_teacher'] = static::booleanField(
      new TranslatableMarkup('Requires a teacher'),
      new TranslatableMarkup('The engine must assign one of the teachers of the template.'),
      -6,
      TRUE,
    );

    $fields['requires_room'] = static::booleanField(
      new TranslatableMarkup('Requires a room'),
      new TranslatableMarkup('The engine must assign one of the rooms of the template.'),
      -5,
      TRUE,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'subject', 'period', 'sequence', 'activity_type', 'requires_teacher', 'requires_room'];
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $subject = $this->get('subject')->entity;
    $period = $this->get('period')->entity;
    return sprintf('%s #%s (%s)',
      $subject ? $subject->label() : '?',
      $this->get('sequence')->value ?? '?',
      $period instanceof Period ? $period->getCode() : '?',
    );
  }

  /**
   * Returns the subject.
   */
  public function getSubject(): ?Subject {
    $subject = $this->get('subject')->entity;
    return $subject instanceof Subject ? $subject : NULL;
  }

  /**
   * Returns the period.
   */
  public function getPeriod(): ?Period {
    $period = $this->get('period')->entity;
    return $period instanceof Period ? $period : NULL;
  }

  /**
   * Returns the activity type.
   */
  public function getActivityType(): ?ActivityType {
    $type = $this->get('activity_type')->entity;
    return $type instanceof ActivityType ? $type : NULL;
  }

  /**
   * Returns the order of the activity within its subject and period.
   */
  public function getSequence(): ?int {
    return $this->getInteger('sequence');
  }

  /**
   * Whether the engine must assign a teacher.
   */
  public function requiresTeacher(): bool {
    return (bool) $this->get('requires_teacher')->value;
  }

  /**
   * Whether the engine must assign a room.
   */
  public function requiresRoom(): bool {
    return (bool) $this->get('requires_room')->value;
  }

}
