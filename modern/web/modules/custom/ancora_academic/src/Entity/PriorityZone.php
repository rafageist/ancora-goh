<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The priority zone of one cell for an activity type (legacy "zpriori").
 *
 * Legacy: dev/sql/tiempo/zpriori/zpriori.sql: PRIMARY KEY (clasificacion,
 * periodo, dia, turno), valor default 1 CHECK (valor > 0). A missing cell
 * is zone 1 (dame_zpriori). The legacy editor (form_zpriori.tpl,
 * custom_set_zpriori.php) offers 1..days x slots per cell and stores only
 * the cells whose zone is not 1. The zones change placements only with the
 * run option parche1, which no screen sends (Q-19; case 140).
 */
#[ContentEntityType(
  id: 'ancora_priority_zone',
  label: new TranslatableMarkup('Priority zone'),
  label_collection: new TranslatableMarkup('Priority zones'),
  label_singular: new TranslatableMarkup('priority zone'),
  label_plural: new TranslatableMarkup('priority zones'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_priority_zone',
  label_count: [
    'singular' => '@count priority zone',
    'plural' => '@count priority zones',
  ],
  constraints: [
    'AncoraSameCreator' => [],
    'AncoraUniqueInScope' => [
      'fields' => ['activity_type', 'period', 'day', 'slot'],
      'message' => 'The cell already has a zone for this activity type.',
    ],
  ],
)]
final class PriorityZone extends PeriodInputBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['activity_type'] = static::referenceField('ancora_activity_type', new TranslatableMarkup('Activity type'), new TranslatableMarkup('The activity type.'), -9)->setRequired(TRUE);
    $fields['day'] = static::integerField(new TranslatableMarkup('Day'), new TranslatableMarkup('Day of the period.'), 1, self::INT_MIN, self::INT_MAX);
    $fields['slot'] = static::integerField(new TranslatableMarkup('Slot'), new TranslatableMarkup('Slot of the day.'), 2, self::INT_MIN, self::INT_MAX);
    // Legacy CHECK (valor > 0).
    $fields['zone'] = static::integerField(new TranslatableMarkup('Zone'), new TranslatableMarkup('1 is the first zone.'), 3, 1, self::INT_MAX, 1);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return sprintf('Zone %s, day %s slot %s (%s)', $this->get('zone')->value ?? '?', $this->get('day')->value ?? '?', $this->get('slot')->value ?? '?', $this->periodCode());
  }

}
