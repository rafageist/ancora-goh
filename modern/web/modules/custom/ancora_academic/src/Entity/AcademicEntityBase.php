<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\ReferenceGuard;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Base class of the academic entities.
 *
 * Provides the plan reference of the plan records (periods, groups,
 * templates, assignments), the created and changed timestamps, field
 * definition helpers and the deletion guard: a record that other academic
 * records still reference cannot be deleted, through any API (see
 * ReferenceGuard). Plans and catalogue records have an owner instead of a
 * plan (OwnedAcademicEntityBase).
 */
abstract class AcademicEntityBase extends ContentEntityBase implements AcademicEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    if (!AcademicEntityTypes::isOwned($entity_type->id())) {
      $fields['plan'] = static::referenceField(
        'ancora_plan',
        new TranslatableMarkup('Plan'),
        new TranslatableMarkup('The plan this record belongs to.'),
        -20,
      )->setRequired(TRUE);
    }

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlanId(): ?int {
    if ($this->getEntityTypeId() === 'ancora_plan') {
      return $this->isNew() ? NULL : (int) $this->id();
    }
    if (!$this->hasField('plan')) {
      return NULL;
    }
    $target = $this->get('plan')->target_id;
    return $target === NULL ? NULL : (int) $target;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatorId(): ?int {
    $plan = $this->hasField('plan') ? $this->get('plan')->entity : NULL;
    return $plan instanceof AcademicEntityInterface ? $plan->getCreatorId() : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCode(): string {
    return $this->hasField('code') ? (string) $this->get('code')->value : '';
  }

  /**
   * Returns the integer value of a single-valued field.
   */
  protected function getInteger(string $field_name): ?int {
    $value = $this->get($field_name)->value;
    return $value === NULL || $value === '' ? NULL : (int) $value;
  }

  /**
   * Returns the referenced entity of a single-valued reference field.
   */
  protected function getReferenced(string $field_name): ?AcademicEntityInterface {
    $entity = $this->get($field_name)->entity;
    return $entity instanceof AcademicEntityInterface ? $entity : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities): void {
    parent::preDelete($storage, $entities);
    $guard = \Drupal::service(ReferenceGuard::class);
    assert($guard instanceof ReferenceGuard);
    foreach ($entities as $entity) {
      $summary = $guard->describe($guard->dependents($entity));
      if ($summary !== '') {
        throw new EntityStorageException(sprintf('%s "%s" cannot be deleted: it is still used by %s.', $entity->getEntityType()->getLabel(), $entity->label(), $summary));
      }
    }
  }

  /**
   * The range of a PostgreSQL integer, the type of the legacy columns.
   */
  public const INT_MIN = -2147483648;
  public const INT_MAX = 2147483647;

  /**
   * The length of the name and code columns.
   *
   * The legacy names (descripcion, nombre) are unbounded VARCHAR, and legacy
   * records have no code; 255 is the size of a Drupal string column, not a
   * legacy rule (#70, D6).
   */
  public const TEXT_LENGTH = 255;

  /**
   * Defines the name field.
   */
  protected static function labelField(TranslatableMarkup $label, TranslatableMarkup $description): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel($label)
      ->setDescription($description)
      ->setRequired(TRUE)
      ->setSetting('max_length', self::TEXT_LENGTH)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayOptions('view', ['label' => 'hidden', 'type' => 'string', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  /**
   * Defines the code field: required, the Drupal handle of a record.
   *
   * Legacy records have no code, only generated ids; the code identifies a
   * record in Drupal (Drush, the scenario files) and in the engine ids. It is
   * required, and unique among the creator's records of the type, or in the
   * plan (the entity constraint AncoraUniqueInScope). Any text is accepted:
   * the engine ids are numbered with the entity id, which keeps them unique
   * whatever the code contains (#70, D6).
   */
  protected static function codeField(): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Code'))
      ->setDescription(new TranslatableMarkup('A short code that identifies the record among yours.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', self::TEXT_LENGTH)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -9])
      ->setDisplayOptions('view', ['label' => 'inline', 'type' => 'string', 'weight' => -9])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  /**
   * Defines a reference to another academic record.
   */
  protected static function referenceField(string $target_type, TranslatableMarkup $label, TranslatableMarkup $description, int $weight, bool $multiple = FALSE): BaseFieldDefinition {
    return BaseFieldDefinition::create('entity_reference')
      ->setLabel($label)
      ->setDescription($description)
      ->setSetting('target_type', $target_type)
      ->setSetting('handler', 'default')
      ->setCardinality($multiple ? FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED : 1)
      ->setDisplayOptions('form', [
        'type' => $multiple ? 'options_buttons' : 'options_select',
        'weight' => $weight,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'settings' => ['link' => TRUE],
        'weight' => $weight,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  /**
   * Defines an integer field with a lower (and optional upper) bound.
   */
  protected static function integerField(TranslatableMarkup $label, TranslatableMarkup $description, int $weight, int $min, ?int $max = NULL, ?int $default = NULL): BaseFieldDefinition {
    $field = BaseFieldDefinition::create('integer')
      ->setLabel($label)
      ->setDescription($description)
      ->setRequired(TRUE)
      ->setSetting('min', $min)
      ->setDisplayOptions('form', ['type' => 'number', 'weight' => $weight])
      ->setDisplayOptions('view', ['label' => 'inline', 'type' => 'number_integer', 'weight' => $weight])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    if ($max !== NULL) {
      $field->setSetting('max', $max);
    }
    if ($default !== NULL) {
      $field->setDefaultValue($default);
    }
    return $field;
  }

  /**
   * Defines a boolean field.
   */
  protected static function booleanField(TranslatableMarkup $label, TranslatableMarkup $description, int $weight, bool $default): BaseFieldDefinition {
    return BaseFieldDefinition::create('boolean')
      ->setLabel($label)
      ->setDescription($description)
      ->setDefaultValue($default)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => ['display_label' => TRUE],
        'weight' => $weight,
      ])
      ->setDisplayOptions('view', ['label' => 'inline', 'type' => 'boolean', 'weight' => $weight])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

}
