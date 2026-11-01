<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicListBuilder;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\Form\AcademicDeleteForm;
use Drupal\ancora_academic\Form\AcademicEntityForm;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\views\EntityViewsData;

/**
 * A plan: one planning cycle (legacy table "planificacion").
 *
 * Dates as the legacy form enters them (PAR-010): a first and a last day
 * (two date pickers, required by the form); the length is derived as the
 * legacy fdm does (fdm_Planificacion_fecha_inicial.php: day number of the
 * last day minus that of the first, + 1, LegacyRows::legacyDayNumber()),
 * without a range check. "active" is a Drupal flag: the legacy form never
 * posts "activa", so the engine row has NULL (LegacyRows).
 */
#[ContentEntityType(
  id: 'ancora_plan',
  label: new TranslatableMarkup('Plan'),
  label_collection: new TranslatableMarkup('Plans'),
  label_singular: new TranslatableMarkup('plan'),
  label_plural: new TranslatableMarkup('plans'),
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
    'canonical' => '/admin/ancora/plans/{ancora_plan}',
    'add-form' => '/admin/ancora/plans/add',
    'edit-form' => '/admin/ancora/plans/{ancora_plan}/edit',
    'delete-form' => '/admin/ancora/plans/{ancora_plan}/delete',
    'collection' => '/admin/ancora/plans',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_plan',
  label_count: [
    'singular' => '@count plan',
    'plural' => '@count plans',
  ],
  constraints: [
    'AncoraUniqueInScope' => ['fields' => ['code']],
  ],
)]
final class Plan extends OwnedAcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "First semester 2026".'),
    );
    $fields['code'] = static::codeField();

    $fields['starts_on'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('First day'))
      ->setDescription(new TranslatableMarkup('The first day of the plan. Real-date restrictions use it to align plans that overlap in time.'))
      ->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATE)
      ->setDisplayOptions('form', ['type' => 'datetime_default', 'weight' => 0])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'datetime_default',
        'settings' => ['format_type' => 'html_date'],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ends_on'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('Last day'))
      ->setDescription(new TranslatableMarkup('The last day of the plan.'))
      ->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATE)
      ->setDisplayOptions('form', ['type' => 'datetime_default', 'weight' => 1])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'datetime_default',
        'settings' => ['format_type' => 'html_date'],
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Derived from the two days (preSave), as the legacy fdm derives
    // cant_dias; no range check (a BIGINT without CHECK).
    $fields['duration_days'] = static::integerField(
      new TranslatableMarkup('Length in days'),
      new TranslatableMarkup('Calendar days the plan covers.'),
      2,
      self::INT_MIN,
      self::INT_MAX,
      30,
    )->setDisplayOptions('form', ['region' => 'hidden']);

    // A Drupal flag; not on the form (the legacy form has none).
    $fields['active'] = static::booleanField(
      new TranslatableMarkup('Active'),
      new TranslatableMarkup('Inactive plans are kept for reference.'),
      3,
      TRUE,
    )->setDisplayOptions('form', ['region' => 'hidden']);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'uid', 'starts_on', 'ends_on', 'duration_days', 'active'];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $start = $this->get('starts_on')->value;
    $end = $this->get('ends_on')->value;
    if ($start !== NULL && $end !== NULL) {
      $this->set('duration_days', LegacyRows::legacyDayNumber($end) - LegacyRows::legacyDayNumber($start) + 1);
    }
  }

  /**
   * Returns the first day (Y-m-d), if set.
   */
  public function getStartDate(): ?string {
    return $this->get('starts_on')->value;
  }

  /**
   * Returns the length in days.
   */
  public function getDurationDays(): ?int {
    return $this->getInteger('duration_days');
  }

  /**
   * Whether the plan is active.
   */
  public function isActive(): bool {
    return (bool) $this->get('active')->value;
  }

}
