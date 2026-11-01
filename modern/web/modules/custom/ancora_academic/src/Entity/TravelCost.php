<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The cost of moving from one room to another (legacy costo_de_traslado).
 *
 * Legacy: dev/sql/recurso/costo_de_traslado.sql: PRIMARY KEY (origen,
 * destino), costo BIGINT; costs are directional. The legacy editor of a room
 * (form_edit_lugar_costos_de_traslado.tpl, custom_set_costotraslado.php)
 * lists the owner's other rooms with a cost 0..999, 0 meaning "irrelevant":
 * saving deletes the pair and inserts it only when the cost is not 0. The
 * costs decide the room only with buscalugar = TRUE (the nearest room,
 * busca_lugar_menos_costoso; case 141). Owned by the rooms' owner, like the
 * rooms (a catalogue record).
 */
#[ContentEntityType(
  id: 'ancora_travel_cost',
  label: new TranslatableMarkup('Travel cost'),
  label_collection: new TranslatableMarkup('Travel costs'),
  label_singular: new TranslatableMarkup('travel cost'),
  label_plural: new TranslatableMarkup('travel costs'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'owner' => 'uid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_travel_cost',
  label_count: [
    'singular' => '@count travel cost',
    'plural' => '@count travel costs',
  ],
  constraints: [
    'AncoraSameCreator' => [],
    'AncoraUniqueInScope' => [
      'fields' => ['origin', 'destination'],
      'message' => 'The cost from this room to that room is already set.',
    ],
  ],
)]
final class TravelCost extends OwnedAcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['origin'] = static::referenceField('ancora_room', new TranslatableMarkup('From room'), new TranslatableMarkup('The room of the earlier activity.'), -9)->setRequired(TRUE);
    $fields['destination'] = static::referenceField('ancora_room', new TranslatableMarkup('To room'), new TranslatableMarkup('The room of the next activity.'), -8)->setRequired(TRUE);
    // The legacy editor offers 0..999 and stores 1..999; the column is a
    // BIGINT without a CHECK.
    $fields['cost'] = static::integerField(new TranslatableMarkup('Cost'), new TranslatableMarkup('The travel cost.'), 1, self::INT_MIN, self::INT_MAX);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'uid', 'origin', 'destination', 'cost'];
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $from = $this->get('origin')->entity;
    $to = $this->get('destination')->entity;
    return sprintf('%s → %s', $from ? $from->label() : '?', $to ? $to->label() : '?');
  }

}
