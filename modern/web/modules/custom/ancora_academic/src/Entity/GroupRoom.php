<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * A fixed or forbidden room of a student group (D9c).
 *
 * Legacy tables lugar_fijo and acceso_prohibido (dev/sql/recurso/
 * asignacion/), both PRIMARY KEY (grupo, lugar); no screen wrote them
 * (A-4; proposal P-2, approved with D9). generar.sql:1228-1234 chooses the
 * first fixed room of the group that is still among the filtered rooms;
 * only one row is read, so the editor keeps one fixed room per group.
 * Forbidden rooms are filtered by quita_segun_prohibidos(), which generar()
 * calls with a quoted group id, so they never take effect (quirk Q-30,
 * case 141); they are kept as data, as legacy keeps them.
 */
#[ContentEntityType(
  id: 'ancora_group_room',
  label: new TranslatableMarkup('Group room'),
  label_collection: new TranslatableMarkup('Group rooms'),
  label_singular: new TranslatableMarkup('group room'),
  label_plural: new TranslatableMarkup('group rooms'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_group_room',
  label_count: [
    'singular' => '@count group room',
    'plural' => '@count group rooms',
  ],
  constraints: [
    'AncoraSameCreator' => [],
    'AncoraUniqueInScope' => [
      'fields' => ['kind', 'student_group', 'room'],
      'message' => 'The group already has this room.',
    ],
  ],
)]
final class GroupRoom extends AcademicEntityBase {

  /**
   * Kind => legacy table.
   */
  public const KINDS = [
    'fixed' => 'lugar_fijo',
    'forbidden' => 'acceso_prohibido',
  ];

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['kind'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Kind'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 32)
      ->addPropertyConstraints('value', ['Choice' => ['choices' => array_keys(self::KINDS)]]);
    $fields['student_group'] = static::referenceField('ancora_student_group', new TranslatableMarkup('Student group'), new TranslatableMarkup('The group.'), -9)->setRequired(TRUE);
    $fields['room'] = static::referenceField('ancora_room', new TranslatableMarkup('Room'), new TranslatableMarkup('The room.'), -8)->setRequired(TRUE);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'student_group', 'room', 'kind'];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $group = $this->get('student_group')->entity;
    if ($group instanceof StudentGroup && $group->getPlanId() !== NULL) {
      $this->set('plan', $group->getPlanId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $group = $this->get('student_group')->entity;
    $room = $this->get('room')->entity;
    return sprintf('%s: %s %s', $group ? $group->label() : '?', (string) $this->get('kind')->value, $room ? $room->label() : '?');
  }

}
