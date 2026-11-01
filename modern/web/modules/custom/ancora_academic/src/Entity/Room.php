<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicListBuilder;
use Drupal\ancora_academic\Form\AcademicDeleteForm;
use Drupal\ancora_academic\Form\AcademicEntityForm;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * A room (legacy table "lugar").
 */
#[ContentEntityType(
  id: 'ancora_room',
  label: new TranslatableMarkup('Room'),
  label_collection: new TranslatableMarkup('Rooms'),
  label_singular: new TranslatableMarkup('room'),
  label_plural: new TranslatableMarkup('rooms'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'label',
    'owner' => 'uid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
    'list_builder' => AcademicListBuilder::class,
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => AcademicEntityForm::class,
      'add' => AcademicEntityForm::class,
      'edit' => AcademicEntityForm::class,
      'delete' => AcademicDeleteForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
  ],
  links: [
    'canonical' => '/admin/ancora/rooms/{ancora_room}',
    'add-form' => '/admin/ancora/rooms/add',
    'edit-form' => '/admin/ancora/rooms/{ancora_room}/edit',
    'delete-form' => '/admin/ancora/rooms/{ancora_room}/delete',
    'collection' => '/admin/ancora/rooms',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_room',
  label_count: [
    'singular' => '@count room',
    'plural' => '@count rooms',
  ],
  constraints: [
    'AncoraUniqueInScope' => ['fields' => ['code']],
    'AncoraSameCreator' => [],
  ],
)]
final class Room extends OwnedAcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = static::labelField(
      new TranslatableMarkup('Name'),
      new TranslatableMarkup('For example "Room 101".'),
    );
    $fields['code'] = static::codeField();

    $fields['capacity'] = static::integerField(
      new TranslatableMarkup('Capacity'),
      new TranslatableMarkup('Number of seats, compared with the enrolment of the cohorts.'),
      1,
      0,
      NULL,
      0,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'code', 'uid', 'capacity'];
  }

  /**
   * Returns the number of seats.
   */
  public function getCapacity(): ?int {
    return $this->getInteger('capacity');
  }

}
