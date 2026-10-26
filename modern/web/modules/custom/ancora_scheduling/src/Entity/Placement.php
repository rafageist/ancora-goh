<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Entity;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\Entity\AcademicEntityBase;
use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * One placed activity of a timetable: legacy table "actividad".
 *
 * A placement is an activity template given to a student group on a day and
 * slot of the template's period, with a teacher and a room. The engine
 * creates it. Planners do not enter placements, as in the legacy application,
 * which has no editor for them. Since #95 the placements of the workspace
 * mode are kept here, in the site's own database. They are the input of the
 * next run (legacy continuation, PAR-085) and the output the worker publishes
 * (PlacementPublisher).
 *
 * - engine_id: the id the engine gave the row (crear_id_de_replica). It is
 *   unique, so publishing a job twice cannot create a second record. It is
 *   loaded back as the id of the row in the next workspace.
 * - The (template, group) pair is unique, as UNIQUE (plantilla, grupo) is in
 *   legacy.
 * - The owner is the plan's creator (AcademicEntityBase::getCreatorId()). In
 *   legacy, actividad.creador is the subject's creator, the same user
 *   (PAR-008).
 * - job: the job whose run created it.
 * - Deleting a referenced record deletes its placements, as the legacy
 *   foreign keys cascade (PAR-101..103, PlacementCascade).
 */
#[ContentEntityType(
  id: 'ancora_placement',
  label: new TranslatableMarkup('Placement'),
  label_collection: new TranslatableMarkup('Placements'),
  label_singular: new TranslatableMarkup('placement'),
  label_plural: new TranslatableMarkup('placements'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => AcademicAccessControlHandler::class,
    'views_data' => EntityViewsData::class,
    'storage_schema' => PlacementStorageSchema::class,
  ],
  admin_permission: 'administer academic data',
  base_table: 'ancora_placement',
  label_count: [
    'singular' => '@count placement',
    'plural' => '@count placements',
  ],
)]
final class Placement extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['period'] = static::referenceField('ancora_period', new TranslatableMarkup('Period'), new TranslatableMarkup('The period of the template.'), -10)->setRequired(TRUE);
    $fields['template'] = static::referenceField('ancora_activity_template', new TranslatableMarkup('Activity template'), new TranslatableMarkup('The weekly activity placed.'), -9)->setRequired(TRUE);
    $fields['student_group'] = static::referenceField('ancora_student_group', new TranslatableMarkup('Student group'), new TranslatableMarkup('The group the activity is given to.'), -8)->setRequired(TRUE);
    $fields['teacher'] = static::referenceField('ancora_teacher', new TranslatableMarkup('Teacher'), new TranslatableMarkup('Empty when the template requires no teacher.'), -7);
    $fields['room'] = static::referenceField('ancora_room', new TranslatableMarkup('Room'), new TranslatableMarkup('Empty when the template requires no room.'), -6);
    $fields['day'] = static::integerField(new TranslatableMarkup('Day'), new TranslatableMarkup('Day of the period, from 1.'), -5, 1);
    $fields['slot'] = static::integerField(new TranslatableMarkup('Slot'), new TranslatableMarkup('First slot of the day, from 1.'), -4, 1);
    $fields['placed_at'] = BaseFieldDefinition::create('float')
      ->setLabel(new TranslatableMarkup('Placement moment'))
      ->setDescription(new TranslatableMarkup('The engine value momento_de_colocacion (tiempo.moment_now).'));
    $fields['engine_id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Engine id'))
      ->setDescription(new TranslatableMarkup('The id the engine gave the placement. Unique.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255);
    $fields['job'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Job'))
      ->setDescription(new TranslatableMarkup('The generation job whose run created the placement.'))
      ->setRequired(TRUE)
      ->setSetting('unsigned', TRUE);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'period', 'day', 'slot'];
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $template = $this->get('template')->entity;
    $group = $this->get('student_group')->entity;
    return sprintf('%s / %s: day %d, slot %d', $template ? $template->label() : '?', $group ? $group->label() : '?', $this->getInteger('day') ?? 0, $this->getInteger('slot') ?? 0);
  }

  /**
   * Returns the template.
   */
  public function getTemplate(): ?ActivityTemplate {
    $template = $this->get('template')->entity;
    return $template instanceof ActivityTemplate ? $template : NULL;
  }

  /**
   * Returns the engine id.
   */
  public function getEngineId(): string {
    return (string) $this->get('engine_id')->value;
  }

}
