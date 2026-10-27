<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\AcademicListBuilder;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * One restricted cell (day, slot) of a period, for one object.
 *
 * Legacy nominal restriction tables (dev/sql/tiempo/restriccion/nominal/,
 * legacy-parity-inventory.md PAR-070, PAR-071, D9a): restriccion_de_periodo,
 * _clasif_act, _asignatura, _brigada, _profesor and _lugar. A row restricts
 * the cell whatever its valor (default 1, CHECK > 0; the legacy editor
 * stores 1), and posible_inicio() then refuses every activity of the object
 * that would cover the cell (case 139). The key is UNIQUE (object, period,
 * day, slot), which also refuses a manual restriction on a cell that a
 * placement of the brigade, teacher or room occupies (the occupancy rows,
 * PAR-076, share the table).
 *
 * Kinds and their legacy tables:
 * - period: restriccion_de_periodo (the only one the 2010 UI edited:
 *   form_periodo_restricciones.tpl, custom_set_Restricciones_Periodo.php);
 * - activity_type, subject, cohort, teacher, room: restriccion_de_clasif_act,
 *   _asignatura, _brigada, _profesor, _lugar. The 2010 UI had no editor for
 *   them (A-4); the Drupal grid is the approved proposal P-2.
 * Specialty restrictions are stored by legacy but never read by a run, and
 * other resources are D9d; they are not offered here.
 *
 * Records are created and deleted with the restriction grid
 * (RestrictionGridForm, TimeRestrictionGrid); they belong to the plan of
 * their period and are deleted with their period or object, as the legacy
 * foreign keys cascade.
 */
#[ContentEntityType(
  id: 'ancora_time_restriction',
  label: new TranslatableMarkup('Time restriction'),
  label_collection: new TranslatableMarkup('Time restrictions'),
  label_singular: new TranslatableMarkup('time restriction'),
  label_plural: new TranslatableMarkup('time restrictions'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
    'list_builder' => AcademicListBuilder::class,
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  base_table: 'ancora_time_restriction',
  label_count: [
    'singular' => '@count time restriction',
    'plural' => '@count time restrictions',
  ],
  constraints: [
    'AncoraSameCreator' => [],
    'AncoraRestrictionCell' => [],
  ],
)]
final class TimeRestriction extends AcademicEntityBase {

  /**
   * Kind => the reference field of the restricted object (NULL: the period).
   */
  public const KINDS = [
    'period' => NULL,
    'activity_type' => 'activity_type',
    'subject' => 'subject',
    'cohort' => 'cohort',
    'teacher' => 'teacher',
    'room' => 'room',
  ];

  /**
   * Kind => referenced entity type.
   */
  public const TARGET_TYPES = [
    'activity_type' => 'ancora_activity_type',
    'subject' => 'ancora_subject',
    'cohort' => 'ancora_cohort',
    'teacher' => 'ancora_teacher',
    'room' => 'ancora_room',
  ];

  /**
   * Kind => legacy table and object column.
   */
  public const LEGACY_TABLES = [
    'period' => ['restriccion_de_periodo', 'periodo'],
    'activity_type' => ['restriccion_de_clasif_act', 'clasificacion'],
    'subject' => ['restriccion_de_asignatura', 'asignatura'],
    'cohort' => ['restriccion_de_brigada', 'brigada'],
    'teacher' => ['restriccion_de_profesor', 'profesor'],
    'room' => ['restriccion_de_lugar', 'lugar'],
  ];

  /**
   * Kinds whose table also holds the occupancy rows of placements.
   */
  public const OCCUPIED = ['cohort', 'teacher', 'room'];

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['period'] = static::referenceField('ancora_period', new TranslatableMarkup('Period'), new TranslatableMarkup('The period of the restricted cell.'), -10)->setRequired(TRUE);
    $fields['kind'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Restricted object'))
      ->setDescription(new TranslatableMarkup('What the restriction applies to.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 32)
      ->addPropertyConstraints('value', ['Choice' => ['choices' => array_keys(self::KINDS)]]);
    $labels = [
      'activity_type' => new TranslatableMarkup('Activity type'),
      'subject' => new TranslatableMarkup('Subject'),
      'cohort' => new TranslatableMarkup('Cohort'),
      'teacher' => new TranslatableMarkup('Teacher'),
      'room' => new TranslatableMarkup('Room'),
    ];
    foreach (self::TARGET_TYPES as $field => $target_type) {
      $fields[$field] = static::referenceField($target_type, $labels[$field], new TranslatableMarkup('The restricted object, for this kind.'), -8);
    }
    $fields['day'] = static::integerField(new TranslatableMarkup('Day'), new TranslatableMarkup('Day of the period, from 1.'), -5, 1, self::INT_MAX);
    $fields['slot'] = static::integerField(new TranslatableMarkup('Slot'), new TranslatableMarkup('Time slot of the day, from 1.'), -4, 1, self::INT_MAX);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'period', 'kind', 'day', 'slot'];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $period = $this->get('period')->entity;
    if ($period instanceof Period && $period->getPlanId() !== NULL) {
      $this->set('plan', $period->getPlanId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $target = $this->getTarget();
    $period = $this->get('period')->entity;
    return sprintf('%s, day %s slot %s (%s)',
      $target === NULL ? (string) new TranslatableMarkup('Period') : (string) $target->label(),
      $this->get('day')->value ?? '?',
      $this->get('slot')->value ?? '?',
      $period instanceof Period ? $period->getCode() : '?',
    );
  }

  /**
   * The kind of restricted object.
   */
  public function getKind(): string {
    return (string) $this->get('kind')->value;
  }

  /**
   * The restricted object; NULL for a period restriction.
   */
  public function getTarget(): ?AcademicEntityInterface {
    $field = self::KINDS[$this->getKind()] ?? NULL;
    if ($field === NULL) {
      return NULL;
    }
    $entity = $this->get($field)->entity;
    return $entity instanceof AcademicEntityInterface ? $entity : NULL;
  }

  /**
   * The id of the restricted object; NULL for a period restriction.
   */
  public function getTargetId(): ?int {
    $field = self::KINDS[$this->getKind()] ?? NULL;
    $id = $field === NULL ? NULL : $this->get($field)->target_id;
    return $id === NULL ? NULL : (int) $id;
  }

  /**
   * The restricted cell.
   *
   * @return array{0: int, 1: int}
   *   Day and slot.
   */
  public function getCell(): array {
    return [(int) $this->get('day')->value, (int) $this->get('slot')->value];
  }

}
