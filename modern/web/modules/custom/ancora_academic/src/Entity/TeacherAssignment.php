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
 * A teacher who may give an activity template to some student groups.
 *
 * Legacy tables "profesor_para_actividad" (template, teacher, priority) and
 * "profesor_para_grupo" (the groups of that assignment).
 */
#[ContentEntityType(
  id: 'ancora_teacher_assignment',
  label: new TranslatableMarkup('Teacher assignment'),
  label_collection: new TranslatableMarkup('Teacher assignments'),
  label_singular: new TranslatableMarkup('teacher assignment'),
  label_plural: new TranslatableMarkup('teacher assignments'),
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
    'canonical' => '/admin/ancora/teacher-assignments/{ancora_teacher_assignment}',
    'add-form' => '/admin/ancora/teacher-assignments/add',
    'edit-form' => '/admin/ancora/teacher-assignments/{ancora_teacher_assignment}/edit',
    'delete-form' => '/admin/ancora/teacher-assignments/{ancora_teacher_assignment}/delete',
    'collection' => '/admin/ancora/teacher-assignments',
  ],
  admin_permission: 'administer academic data',
  list_cache_contexts: ['user'],
  collection_permission: 'view academic data',
  base_table: 'ancora_teacher_assignment',
  label_count: [
    'singular' => '@count teacher assignment',
    'plural' => '@count teacher assignments',
  ],
  constraints: [
    'AncoraUniqueInScope' => [
      'fields' => ['template', 'teacher'],
      'message' => 'This teacher is already assigned to this activity template.',
    ],
    'AncoraSameCreator' => [],
    'AncoraEligibleGroups' => [],
  ],
)]
final class TeacherAssignment extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['template'] = static::referenceField(
      'ancora_activity_template',
      new TranslatableMarkup('Activity template'),
      new TranslatableMarkup('The weekly activity the teacher may give.'),
      -10,
    )->setRequired(TRUE);

    $fields['teacher'] = static::referenceField(
      'ancora_teacher',
      new TranslatableMarkup('Teacher'),
      new TranslatableMarkup('The teacher.'),
      -9,
    )->setRequired(TRUE);

    $fields['priority'] = static::integerField(
      new TranslatableMarkup('Priority'),
      new TranslatableMarkup('1 is preferred. The engine tries teachers in priority order and balances their load.'),
      -8,
      1,
      NULL,
      1,
    );

    $fields['groups'] = static::referenceField(
      'ancora_student_group',
      new TranslatableMarkup('Student groups'),
      new TranslatableMarkup('The groups this teacher gives the activity to. Each must attend the activity type of the template and have the specialty and level of its subject.'),
      -7,
      TRUE,
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'teacher', 'template', 'priority', 'groups'];
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    $teacher = $this->get('teacher')->entity;
    $template = $this->get('template')->entity;
    return sprintf('%s: %s', $teacher ? $teacher->label() : '?', $template ? $template->label() : '?');
  }

  /**
   * Returns the activity template.
   */
  public function getTemplate(): ?ActivityTemplate {
    $template = $this->get('template')->entity;
    return $template instanceof ActivityTemplate ? $template : NULL;
  }

  /**
   * Returns the teacher.
   */
  public function getTeacher(): ?Teacher {
    $teacher = $this->get('teacher')->entity;
    return $teacher instanceof Teacher ? $teacher : NULL;
  }

  /**
   * Returns the priority (1 is preferred).
   */
  public function getPriority(): ?int {
    return $this->getInteger('priority');
  }

  /**
   * Returns the IDs of the student groups of the assignment.
   *
   * @return list<int>
   *   The referenced IDs, in field order.
   */
  public function getGroupIds(): array {
    return array_map('intval', array_column($this->get('groups')->getValue(), 'target_id'));
  }

}
