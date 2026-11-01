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
 * A room that an activity template may use (legacy "lugar_para_actividad").
 */
#[ContentEntityType(
  id: 'ancora_room_assignment',
  label: new TranslatableMarkup('Room assignment'),
  label_collection: new TranslatableMarkup('Room assignments'),
  label_singular: new TranslatableMarkup('room assignment'),
  label_plural: new TranslatableMarkup('room assignments'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
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
    'canonical' => '/admin/ancora/room-assignments/{ancora_room_assignment}',
    'add-form' => '/admin/ancora/room-assignments/add',
    'edit-form' => '/admin/ancora/room-assignments/{ancora_room_assignment}/edit',
    'delete-form' => '/admin/ancora/room-assignments/{ancora_room_assignment}/delete',
    'collection' => '/admin/ancora/room-assignments',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_room_assignment',
  label_count: [
    'singular' => '@count room assignment',
    'plural' => '@count room assignments',
  ],
  constraints: [
    'AncoraUniqueInScope' => [
      'fields' => ['template', 'room'],
      'message' => 'This room is already assigned to this activity template.',
    ],
    'AncoraSameCreator' => [],
  ],
)]
final class RoomAssignment extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['template'] = static::referenceField(
      'ancora_activity_template',
      new TranslatableMarkup('Activity template'),
      new TranslatableMarkup('The weekly activity that may use the room.'),
      -10,
    )->setRequired(TRUE);

    $fields['room'] = static::referenceField(
      'ancora_room',
      new TranslatableMarkup('Room'),
      new TranslatableMarkup('The room.'),
      -9,
    )->setRequired(TRUE);

    $fields['priority'] = static::integerField(
      new TranslatableMarkup('Priority'),
      new TranslatableMarkup('1 is preferred. The engine tries rooms in priority order.'),
      -8,
      1,
      NULL,
      1,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'room', 'template', 'priority'];
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $room = $this->get('room')->entity;
    $template = $this->get('template')->entity;
    return sprintf('%s: %s', $room ? $room->label() : '?', $template ? $template->label() : '?');
  }

  /**
   * Returns the activity template.
   */
  public function getTemplate(): ?ActivityTemplate {
    $template = $this->get('template')->entity;
    return $template instanceof ActivityTemplate ? $template : NULL;
  }

  /**
   * Returns the room.
   */
  public function getRoom(): ?Room {
    $room = $this->get('room')->entity;
    return $room instanceof Room ? $room : NULL;
  }

  /**
   * Returns the priority (1 is preferred).
   */
  public function getPriority(): ?int {
    return $this->getInteger('priority');
  }

}
