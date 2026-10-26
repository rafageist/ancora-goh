<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The academic entity types, in the order in which a planner enters them.
 *
 * The order is used by the planning overview, the menu links and the tests.
 * Every type lives under /admin/ancora/<segment>.
 */
final class AcademicEntityTypes {

  /**
   * Entity type ID => administrative path segment.
   */
  public const SEGMENTS = [
    'ancora_plan' => 'plans',
    'ancora_period' => 'periods',
    'ancora_activity_type' => 'activity-types',
    'ancora_specialty' => 'specialties',
    'ancora_level' => 'levels',
    'ancora_cohort' => 'cohorts',
    'ancora_student_group' => 'groups',
    'ancora_subject' => 'subjects',
    'ancora_activity_template' => 'templates',
    'ancora_room' => 'rooms',
    'ancora_teacher' => 'teachers',
    'ancora_teacher_assignment' => 'teacher-assignments',
    'ancora_room_assignment' => 'room-assignments',
  ];

  /**
   * Returns the entity type IDs in planning order.
   *
   * @return list<string>
   *   The entity type IDs.
   */
  public static function ids(): array {
    return array_keys(self::SEGMENTS);
  }

  /**
   * Returns a one-line description of each entity type, for the overview.
   *
   * @return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup>
   *   Descriptions keyed by entity type ID.
   */
  public static function descriptions(): array {
    return [
      'ancora_plan' => new TranslatableMarkup('A planning cycle, for example one semester. Every other record belongs to a plan.'),
      'ancora_period' => new TranslatableMarkup('The weekly grid of a plan: days, time slots per day, first hour and slot length.'),
      'ancora_activity_type' => new TranslatableMarkup('Kinds of activity (lecture, lab...) with their length in slots, priority and cost.'),
      'ancora_specialty' => new TranslatableMarkup('Degree programmes or specialties.'),
      'ancora_level' => new TranslatableMarkup('Years or levels of study.'),
      'ancora_cohort' => new TranslatableMarkup('Student cohorts of a specialty and level, with their enrolment.'),
      'ancora_student_group' => new TranslatableMarkup('Teaching groups: the cohorts that attend one kind of activity together.'),
      'ancora_subject' => new TranslatableMarkup('Subjects of a specialty and level.'),
      'ancora_activity_template' => new TranslatableMarkup('The weekly breakdown of a subject in a period: one template per weekly activity.'),
      'ancora_room' => new TranslatableMarkup('Rooms and their capacity.'),
      'ancora_teacher' => new TranslatableMarkup('Teachers.'),
      'ancora_teacher_assignment' => new TranslatableMarkup('Which teacher may give an activity template, to which groups, with which priority.'),
      'ancora_room_assignment' => new TranslatableMarkup('Which rooms an activity template may use, with which priority.'),
    ];
  }

}
