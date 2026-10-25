<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicEntityInterface;
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
 * Provides the plan reference (every record but a plan belongs to one plan),
 * the created and changed timestamps, field definition helpers and the
 * deletion guard: a record that other academic records still reference
 * cannot be deleted, through any API (see ReferenceGuard).
 */
abstract class AcademicEntityBase extends ContentEntityBase implements AcademicEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    if ($entity_type->id() !== 'ancora_plan') {
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
    if (!$this->hasField('plan')) {
      return $this->isNew() ? NULL : (int) $this->id();
    }
    $target = $this->get('plan')->target_id;
    return $target === NULL ? NULL : (int) $target;
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
   * Defines the name field.
   */
  protected static function labelField(TranslatableMarkup $label, TranslatableMarkup $description): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel($label)
      ->setDescription($description)
      ->setRequired(TRUE)
      ->setSetting('max_length', 128)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayOptions('view', ['label' => 'hidden', 'type' => 'string', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  /**
   * Defines the code field: short, required, unique in the plan.
   *
   * The uniqueness is checked by the entity constraint AncoraUniqueInPlan,
   * which each entity type declares.
   */
  protected static function codeField(): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Code'))
      ->setDescription(new TranslatableMarkup('A short code, unique in the plan. Letters, digits, ".", "_" and "-".'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 32)
      ->addPropertyConstraints('value', [
        'Regex' => [
          'pattern' => '/^[A-Za-z0-9._-]+$/',
          'message' => 'The code may contain only letters, digits, ".", "_" and "-".',
        ],
      ])
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
