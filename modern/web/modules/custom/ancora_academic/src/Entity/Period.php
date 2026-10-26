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
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * A period: the weekly time grid of a plan.
 *
 * Legacy tables "periodo" and "periodicidad".
 */
#[ContentEntityType(
  id: 'ancora_period',
  label: new TranslatableMarkup('Period'),
  label_collection: new TranslatableMarkup('Periods'),
  label_singular: new TranslatableMarkup('period'),
  label_plural: new TranslatableMarkup('periods'),
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
    'canonical' => '/admin/ancora/periods/{ancora_period}',
    'add-form' => '/admin/ancora/periods/add',
    'edit-form' => '/admin/ancora/periods/{ancora_period}/edit',
    'delete-form' => '/admin/ancora/periods/{ancora_period}/delete',
    'collection' => '/admin/ancora/periods',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_period',
  label_count: [
    'singular' => '@count period',
    'plural' => '@count periods',
  ],
  constraints: [
    'AncoraUniqueInScope' => ['fields' => ['code']],
    'AncoraSameCreator' => [],
  ],
)]
final class Period extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "Week".'),
    );
    $fields['code'] = static::codeField();

    $fields['days'] = static::integerField(
      new TranslatableMarkup('Days'),
      new TranslatableMarkup('Days in the cycle of the period, for example 5 for Monday to Friday.'),
      1,
      1,
      31,
      5,
    );

    $fields['slots_per_day'] = static::integerField(
      new TranslatableMarkup('Time slots per day'),
      new TranslatableMarkup('Number of time slots (turns) in each day.'),
      2,
      1,
      48,
      6,
    );

    $fields['start_hour'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('First hour'))
      ->setDescription(new TranslatableMarkup('Hour at which the first slot starts, for example 8 or 8.5.'))
      ->setRequired(TRUE)
      ->setSetting('precision', 5)
      ->setSetting('scale', 2)
      ->setSetting('min', 0)
      ->setSetting('max', 24)
      ->setDefaultValue(8)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 3])
      ->setDisplayOptions('view', ['label' => 'inline', 'type' => 'number_decimal', 'weight' => 3])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['slot_duration'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Slot length in hours'))
      ->setDescription(new TranslatableMarkup('Length of each time slot in hours, for example 1 or 0.75.'))
      ->setRequired(TRUE)
      ->setSetting('precision', 5)
      ->setSetting('scale', 2)
      ->setSetting('min', 0.01)
      ->setSetting('max', 24)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 4])
      ->setDisplayOptions('view', ['label' => 'inline', 'type' => 'number_decimal', 'weight' => 4])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['plan_days'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Days of the plan'))
      ->setDescription(new TranslatableMarkup('Optional. The days of the plan (1 = its first day) on which this period applies.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('min', 1)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => 5])
      ->setDisplayOptions('view', ['label' => 'inline', 'type' => 'number_integer', 'weight' => 5])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'plan', 'days', 'slots_per_day', 'start_hour', 'slot_duration'];
  }

  /**
   * Returns the number of days in the cycle.
   */
  public function getDays(): ?int {
    return $this->getInteger('days');
  }

  /**
   * Returns the number of time slots per day.
   */
  public function getSlotsPerDay(): ?int {
    return $this->getInteger('slots_per_day');
  }

  /**
   * Returns the hour at which the first slot starts.
   */
  public function getStartHour(): float {
    return (float) $this->get('start_hour')->value;
  }

  /**
   * Returns the length of a slot in hours.
   */
  public function getSlotDuration(): float {
    return (float) $this->get('slot_duration')->value;
  }

  /**
   * Returns the days of the plan on which the period applies.
   *
   * @return list<int>
   *   Day numbers, 1 being the first day of the plan, sorted.
   */
  public function getPlanDays(): array {
    $days = array_map('intval', array_column($this->get('plan_days')->getValue(), 'value'));
    sort($days);
    return array_values(array_unique($days));
  }

}
