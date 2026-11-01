<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\Entity\AcademicEntityBase;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * One manual move of a placement (#130): the audit record and the undo.
 *
 * Written by PlacementModification::commit() in the same transaction as the
 * placement's new day and slot. It belongs to the placement's plan, so it
 * has the plan's access rules (AcademicAccessControlHandler: the plan's
 * creator with the academic permissions). It is never edited; deleting the
 * placement deletes its changes (PlacementCascade).
 *
 * - reason: "move", or "undo" (reverts names the change undone);
 * - version: the site input version the destination was checked against.
 */
#[ContentEntityType(
  id: 'ancora_placement_change',
  label: new TranslatableMarkup('Placement change'),
  label_collection: new TranslatableMarkup('Placement changes'),
  label_singular: new TranslatableMarkup('placement change'),
  label_plural: new TranslatableMarkup('placement changes'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
    'views_data' => EntityViewsData::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_placement_change',
  label_count: [
    'singular' => '@count placement change',
    'plural' => '@count placement changes',
  ],
)]
final class PlacementChange extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['placement'] = static::referenceField('ancora_placement', new TranslatableMarkup('Placement'), new TranslatableMarkup('The placement moved.'), -10)->setRequired(TRUE);
    $labels = [
      'from_day' => new TranslatableMarkup('Day before'),
      'from_slot' => new TranslatableMarkup('Slot before'),
      'to_day' => new TranslatableMarkup('Day after'),
      'to_slot' => new TranslatableMarkup('Slot after'),
    ];
    foreach ($labels as $name => $label) {
      $fields[$name] = static::integerField($label, new TranslatableMarkup('Day or first slot of the period, from 1.'), -9, 1)->setRequired(TRUE);
    }
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Moved by'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE);
    $fields['reason'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Reason'))
      ->setDescription(new TranslatableMarkup('"move" or "undo".'))
      ->setSetting('max_length', 16)
      ->setRequired(TRUE);
    $fields['reverts'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Undoes'))
      ->setSetting('target_type', 'ancora_placement_change');
    $fields['version'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Checked input version'))
      ->setSetting('max_length', 64)
      ->setRequired(TRUE);
    $fields['check_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Check'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'placement', 'reason'];
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return sprintf('Day %d, slot %d → day %d, slot %d', $this->getInteger('from_day') ?? 0, $this->getInteger('from_slot') ?? 0, $this->getInteger('to_day') ?? 0, $this->getInteger('to_slot') ?? 0);
  }

}
