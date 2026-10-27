<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicListBuilder;
use Drupal\ancora_academic\Form\BreakdownForm;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * The breakdown of a subject in a period, with its scheduling options.
 *
 * Legacy table "desglose" (dev/sql/actividad/desglose.sql): one row per
 * subject and period, created by the triggers nuevo_desglose_si_nueva_
 * asignatura and nuevo_desglose_si_nuevo_periodo for every period of every
 * plan of the subject's creator, with the column defaults min_dias 1,
 * max_dias 5, respetar_orden TRUE, mismo_dia FALSE (case 110). The legacy UI
 * never creates or deletes one: it only edits the four options
 * (view/custom/form_desglose.tpl, control/data/set/set_Desglose.php), and
 * the foreign keys delete it with its subject or period. Here the records
 * are kept by BreakdownMaintainer in the same way, and only the options are
 * editable (legacy-parity-inventory.md PAR-031, PAR-032, D4).
 *
 * The values are not validated beyond the legacy column type (integer):
 * the legacy form posts free text and the handler stores any integer,
 * including zero, negative values and a minimum above the maximum. Their
 * effect on generar() is characterized by case 135.
 */
#[ContentEntityType(
  id: 'ancora_breakdown',
  label: new TranslatableMarkup('Subject breakdown'),
  label_collection: new TranslatableMarkup('Subject breakdowns'),
  label_singular: new TranslatableMarkup('subject breakdown'),
  label_plural: new TranslatableMarkup('subject breakdowns'),
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
      'default' => BreakdownForm::class,
      'edit' => BreakdownForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
  ],
  links: [
    'canonical' => '/admin/ancora/breakdowns/{ancora_breakdown}',
    'edit-form' => '/admin/ancora/breakdowns/{ancora_breakdown}/edit',
    'collection' => '/admin/ancora/breakdowns',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_breakdown',
  label_count: [
    'singular' => '@count subject breakdown',
    'plural' => '@count subject breakdowns',
  ],
  constraints: [
    'AncoraUniqueInScope' => [
      'fields' => ['subject', 'period'],
      'message' => 'The subject already has a breakdown in this period.',
    ],
    'AncoraSameCreator' => [],
  ],
)]
final class Breakdown extends AcademicEntityBase {

  /**
   * The legacy column defaults (dev/sql/actividad/desglose.sql).
   */
  public const DEFAULTS = [
    'min_days' => 1,
    'max_days' => 5,
    'respect_order' => TRUE,
    'same_day' => FALSE,
  ];

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    // Set from the period (preSave()); never chosen in a form.
    $fields['plan']->setDisplayOptions('form', ['region' => 'hidden']);

    $fields['subject'] = static::referenceField(
      'ancora_subject',
      new TranslatableMarkup('Subject'),
      new TranslatableMarkup('The subject.'),
      -10,
    )->setRequired(TRUE)->setDisplayOptions('form', ['region' => 'hidden']);

    $fields['period'] = static::referenceField(
      'ancora_period',
      new TranslatableMarkup('Period'),
      new TranslatableMarkup('The period.'),
      -9,
    )->setRequired(TRUE)->setDisplayOptions('form', ['region' => 'hidden']);

    // The labels are those of the legacy form (form_desglose.tpl:23-26).
    $fields['min_days'] = static::legacyInteger(
      new TranslatableMarkup('Minimum number of days between activities'),
      new TranslatableMarkup('Read only while the order of the activities is respected. 0 or less allows two activities of the subject on the same day.'),
      0,
      self::DEFAULTS['min_days'],
    );
    $fields['max_days'] = static::legacyInteger(
      new TranslatableMarkup('Maximum number of days between activities'),
      new TranslatableMarkup('Read only while the order of the activities is respected.'),
      1,
      self::DEFAULTS['max_days'],
    );
    $fields['respect_order'] = static::booleanField(
      new TranslatableMarkup('Respect the order of the activities'),
      new TranslatableMarkup('The activity templates are placed on successive days in their order, between the minimum and maximum number of days apart.'),
      2,
      self::DEFAULTS['respect_order'],
    );
    $fields['same_day'] = static::booleanField(
      new TranslatableMarkup('Allow more than one activity on the same day'),
      new TranslatableMarkup('Read only when the order is not respected. Then, unchecked, the engine places only the first activity template of the subject for each group, on any day (legacy behaviour, quirk Q-04).'),
      3,
      self::DEFAULTS['same_day'],
    );

    return $fields;
  }

  /**
   * An integer option: any value of the legacy INTEGER column.
   */
  private static function legacyInteger(TranslatableMarkup $label, TranslatableMarkup $description, int $weight, int $default): BaseFieldDefinition {
    return BaseFieldDefinition::create('integer')
      ->setLabel($label)
      ->setDescription($description)
      ->setRequired(TRUE)
      // The range of a PostgreSQL integer, the type of the legacy column.
      ->setSetting('min', -2147483648)
      ->setSetting('max', 2147483647)
      ->setDefaultValue($default)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => $weight])
      ->setDisplayOptions('view', ['label' => 'inline', 'type' => 'number_integer', 'weight' => $weight])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'subject', 'period', 'min_days', 'max_days', 'respect_order', 'same_day', 'templates'];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    // The breakdown belongs to the plan of its period, as the legacy row
    // does through periodo.planificacion.
    $period = $this->getPeriod();
    if ($period !== NULL && $period->getPlanId() !== NULL) {
      $this->set('plan', $period->getPlanId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $subject = $this->getSubject();
    $period = $this->getPeriod();
    return sprintf('%s (%s)', $subject ? $subject->label() : '?', $period ? $period->label() : '?');
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
   * The options as the legacy columns of "desglose".
   *
   * @return array{min_dias: int, max_dias: int, respetar_orden: bool, mismo_dia: bool}
   *   The legacy row values.
   */
  public function legacyOptions(): array {
    return [
      'min_dias' => (int) $this->get('min_days')->value,
      'max_dias' => (int) $this->get('max_days')->value,
      'respetar_orden' => (bool) $this->get('respect_order')->value,
      'mismo_dia' => (bool) $this->get('same_day')->value,
    ];
  }

}
