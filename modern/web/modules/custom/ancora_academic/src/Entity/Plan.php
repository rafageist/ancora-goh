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
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\views\EntityViewsData;

/**
 * A plan: one planning cycle (legacy table "planificacion").
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
  collection_permission: 'view academic data',
  base_table: 'ancora_plan',
  label_count: [
    'singular' => '@count plan',
    'plural' => '@count plans',
  ],
)]
final class Plan extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "First semester 2026".'),
    );
    $fields['code'] = static::codeField()
      ->setDescription(new TranslatableMarkup('A short code, unique on the site. Letters, digits, ".", "_" and "-".'))
      ->addConstraint('UniqueField');

    $fields['starts_on'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('First day'))
      ->setDescription(new TranslatableMarkup('Optional. Used to align plans that overlap in time.'))
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

    $fields['duration_days'] = static::integerField(
      new TranslatableMarkup('Length in days'),
      new TranslatableMarkup('Calendar days the plan covers.'),
      1,
      1,
      3660,
      30,
    );

    $fields['active'] = static::booleanField(
      new TranslatableMarkup('Active'),
      new TranslatableMarkup('Inactive plans are kept for reference.'),
      2,
      TRUE,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'starts_on', 'duration_days', 'active'];
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
