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
 * A kind of activity, such as a lecture or a lab.
 *
 * Legacy table "clasificacion_de_actividad".
 */
#[ContentEntityType(
  id: 'ancora_activity_type',
  label: new TranslatableMarkup('Activity type'),
  label_collection: new TranslatableMarkup('Activity types'),
  label_singular: new TranslatableMarkup('activity type'),
  label_plural: new TranslatableMarkup('activity types'),
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
    'canonical' => '/admin/ancora/activity-types/{ancora_activity_type}',
    'add-form' => '/admin/ancora/activity-types/add',
    'edit-form' => '/admin/ancora/activity-types/{ancora_activity_type}/edit',
    'delete-form' => '/admin/ancora/activity-types/{ancora_activity_type}/delete',
    'collection' => '/admin/ancora/activity-types',
  ],
  admin_permission: 'administer academic data',
  collection_permission: 'view academic data',
  base_table: 'ancora_activity_type',
  label_count: [
    'singular' => '@count activity type',
    'plural' => '@count activity types',
  ],
  constraints: [
    'AncoraUniqueInPlan' => ['fields' => ['code']],
    'AncoraSamePlan' => [],
  ],
)]
final class ActivityType extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "Lecture".'),
    );
    $fields['code'] = static::codeField();

    $fields['duration_slots'] = static::integerField(
      new TranslatableMarkup('Length in slots'),
      new TranslatableMarkup('How many consecutive time slots one activity of this type takes.'),
      1,
      1,
      48,
      1,
    );

    $fields['priority'] = static::integerField(
      new TranslatableMarkup('Priority'),
      new TranslatableMarkup('Scheduling order: the engine places types with priority 1 first.'),
      2,
      1,
      NULL,
      1,
    );

    $fields['cost'] = static::integerField(
      new TranslatableMarkup('Cost'),
      new TranslatableMarkup('Relative effort of one activity, used to balance teachers and days.'),
      3,
      0,
      NULL,
      1,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'plan', 'duration_slots', 'priority', 'cost'];
  }

  /**
   * Returns the length in time slots.
   */
  public function getDurationSlots(): ?int {
    return $this->getInteger('duration_slots');
  }

  /**
   * Returns the scheduling priority.
   */
  public function getPriority(): ?int {
    return $this->getInteger('priority');
  }

  /**
   * Returns the cost.
   */
  public function getCost(): ?int {
    return $this->getInteger('cost');
  }

}
