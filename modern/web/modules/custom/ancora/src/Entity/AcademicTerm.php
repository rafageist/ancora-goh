<?php

declare(strict_types=1);

namespace Drupal\ancora\Entity;

use Drupal\ancora\AcademicTermAccessControlHandler;
use Drupal\ancora\AcademicTermInterface;
use Drupal\ancora\AcademicTermListBuilder;
use Drupal\ancora\Form\AcademicTermForm;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\views\EntityViewsData;

/**
 * Defines the academic term entity.
 */
#[ContentEntityType(
  id: 'ancora_academic_term',
  label: new TranslatableMarkup('Academic term'),
  label_collection: new TranslatableMarkup('Academic terms'),
  label_singular: new TranslatableMarkup('academic term'),
  label_plural: new TranslatableMarkup('academic terms'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'label',
  ],
  handlers: [
    'access' => AcademicTermAccessControlHandler::class,
    'list_builder' => AcademicTermListBuilder::class,
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => AcademicTermForm::class,
      'add' => AcademicTermForm::class,
      'edit' => AcademicTermForm::class,
      'delete' => ContentEntityDeleteForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
  ],
  links: [
    'canonical' => '/ancora/term/{ancora_academic_term}',
    'add-form' => '/admin/content/ancora-term/add',
    'edit-form' => '/admin/content/ancora-term/{ancora_academic_term}/edit',
    'delete-form' => '/admin/content/ancora-term/{ancora_academic_term}/delete',
    'collection' => '/admin/content/ancora-term',
  ],
  admin_permission: 'administer ancora academic terms',
  base_table: 'ancora_academic_term',
  label_count: [
    'singular' => '@count academic term',
    'plural' => '@count academic terms',
  ],
  constraints: [
    'AncoraAcademicTermDates' => [],
  ],
)]
final class AcademicTerm extends ContentEntityBase implements AcademicTermInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setDescription(new TranslatableMarkup('The name of the term, for example "First semester 2026".'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 128)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => 0])
      ->setDisplayOptions('view', ['label' => 'hidden', 'type' => 'string', 'weight' => 0])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['code'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Code'))
      ->setDescription(new TranslatableMarkup('A short unique code, for example "2026-S1".'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 32)
      ->addConstraint('UniqueField')
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => 1])
      ->setDisplayOptions('view', ['label' => 'inline', 'type' => 'string', 'weight' => 1])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['starts_on'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('First day'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATE)
      ->setDisplayOptions('form', ['type' => 'datetime_default', 'weight' => 2])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'datetime_default',
        'settings' => ['format_type' => 'html_date'],
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ends_on'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('Last day'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATE)
      ->setDisplayOptions('form', ['type' => 'datetime_default', 'weight' => 3])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'datetime_default',
        'settings' => ['format_type' => 'html_date'],
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCode(): string {
    return (string) $this->get('code')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate(): ?string {
    return $this->get('starts_on')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate(): ?string {
    return $this->get('ends_on')->value;
  }

}
