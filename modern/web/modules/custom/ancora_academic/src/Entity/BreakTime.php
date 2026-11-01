<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * A break after a slot of a period (legacy "receso").
 *
 * Legacy: dev/sql/tiempo/configuracion/receso.sql: PRIMARY KEY (periodo,
 * despues_del_turno), duracion DOUBLE PRECISION. dame_hora_inicial_turno()
 * adds the length of every break after an earlier slot to the start hour
 * of a slot; only the hours change, never the placements (case 140). The
 * 2010 UI had no editor (A-4; proposal P-2, approved with D9).
 */
#[ContentEntityType(
  id: 'ancora_break',
  label: new TranslatableMarkup('Break'),
  label_collection: new TranslatableMarkup('Breaks'),
  label_singular: new TranslatableMarkup('break'),
  label_plural: new TranslatableMarkup('breaks'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_break',
  label_count: [
    'singular' => '@count break',
    'plural' => '@count breaks',
  ],
  constraints: [
    'AncoraSameCreator' => [],
    'AncoraUniqueInScope' => [
      'fields' => ['period', 'after_slot'],
      'message' => 'The period already has a break after this slot.',
    ],
  ],
)]
final class BreakTime extends PeriodInputBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['after_slot'] = static::integerField(new TranslatableMarkup('After slot'), new TranslatableMarkup('The break comes after this slot.'), 1, self::INT_MIN, self::INT_MAX);
    $fields['length'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Length in hours'))
      ->setDescription(new TranslatableMarkup('Added to the start hour of every later slot.'))
      ->setRequired(TRUE)
      ->setSetting('precision', 8)
      ->setSetting('scale', 2);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return sprintf('Break after slot %s (%s)', $this->get('after_slot')->value ?? '?', $this->periodCode());
  }

  /**
   * The slot the break follows.
   */
  public function getAfterSlot(): int {
    return (int) $this->get('after_slot')->value;
  }

  /**
   * The length in hours.
   */
  public function getLength(): float {
    return (float) $this->get('length')->value;
  }

}
