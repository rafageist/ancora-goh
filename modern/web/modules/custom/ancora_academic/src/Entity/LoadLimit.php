<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The daily load limit of a teacher or cohort in a period (legacy hygiene).
 *
 * Legacy: dev/sql/actividad/higiene/higiene_para_{profesor,brigada}.sql:
 * gasto_maximo_diario INTEGER NOT NULL, part of the PRIMARY KEY with the
 * period and the object, so legacy allows several limits per object, of
 * which the first row found wins; no screen ever wrote one (A-4). The
 * editor keeps one limit per object and period. es_higienico_profesor() /
 * _brigada() skip a day when the candidate's cost, counted once for the
 * candidate and once more for every activity of the object that day,
 * exceeds the limit (assessment 6.21; case 140).
 */
#[ContentEntityType(
  id: 'ancora_load_limit',
  label: new TranslatableMarkup('Daily load limit'),
  label_collection: new TranslatableMarkup('Daily load limits'),
  label_singular: new TranslatableMarkup('daily load limit'),
  label_plural: new TranslatableMarkup('daily load limits'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_load_limit',
  label_count: [
    'singular' => '@count daily load limit',
    'plural' => '@count daily load limits',
  ],
  constraints: [
    'AncoraSameCreator' => [],
  ],
)]
final class LoadLimit extends PeriodInputBase {

  /**
   * Kind => the reference field and the legacy table and column.
   */
  public const KINDS = [
    'teacher' => ['teacher', 'higiene_para_profesor', 'profesor'],
    'cohort' => ['cohort', 'higiene_para_brigada', 'brigada'],
  ];

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['kind'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Limited object'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 32)
      ->addPropertyConstraints('value', ['Choice' => ['choices' => array_keys(self::KINDS)]]);
    $fields['teacher'] = static::referenceField('ancora_teacher', new TranslatableMarkup('Teacher'), new TranslatableMarkup('The teacher, for a teacher limit.'), -8);
    $fields['cohort'] = static::referenceField('ancora_cohort', new TranslatableMarkup('Cohort'), new TranslatableMarkup('The cohort, for a cohort limit.'), -7);
    $fields['max_daily_load'] = static::integerField(new TranslatableMarkup('Maximum daily load'), new TranslatableMarkup('The largest total cost of the activities of one day.'), 1, self::INT_MIN, self::INT_MAX);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $field = self::KINDS[(string) $this->get('kind')->value][0] ?? 'teacher';
    $target = $this->get($field)->entity;
    return sprintf('%s: %s (%s)', $target ? $target->label() : '?', $this->get('max_daily_load')->value ?? '?', $this->periodCode());
  }

}
