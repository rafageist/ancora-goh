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
   * The types owned by the user who creates them (legacy column "creador").
   *
   * Legacy: planificacion, clasificacion_de_actividad, especialidad, nivel,
   * brigada, asignatura, profesor and lugar carry "creador" and no plan
   * (docs/modernization/legacy-parity-inventory.md PAR-001, PAR-003). A
   * user's catalogue records serve every plan of that user. The other types
   * belong to a plan, and their creator is the plan's.
   */
  public const OWNED = [
    'ancora_plan',
    'ancora_activity_type',
    'ancora_specialty',
    'ancora_level',
    'ancora_cohort',
    'ancora_subject',
    'ancora_room',
    'ancora_teacher',
  ];

  /**
   * Types whose records the module keeps, as legacy triggers do.
   *
   * Not part of the data entry order (SEGMENTS): the user never creates or
   * deletes them, only edits them. Legacy "desglose": one per subject and
   * period of the same creator (BreakdownMaintainer). They belong to a plan.
   */
  public const DERIVED = [
    'ancora_breakdown',
  ];

  /**
   * The sections of the planner navigation (#105): section => entity types.
   *
   * Only a grouping of the lists for the menus and section pages; the data
   * entry order stays the order of SEGMENTS.
   */
  public const SECTIONS = [
    'plans' => [
      'ancora_plan',
      'ancora_period',
    ],
    'catalogues' => [
      'ancora_teacher',
      'ancora_room',
      'ancora_subject',
      'ancora_specialty',
      'ancora_level',
      'ancora_cohort',
      'ancora_student_group',
      'ancora_activity_type',
    ],
    'assignments' => [
      'ancora_breakdown',
      'ancora_activity_template',
      'ancora_teacher_assignment',
      'ancora_room_assignment',
    ],
  ];

  /**
   * Returns the title and introduction of each navigation section.
   *
   * @return array<string, array{title: \Drupal\Core\StringTranslation\TranslatableMarkup, intro: \Drupal\Core\StringTranslation\TranslatableMarkup}>
   *   Keyed by section.
   */
  public static function sections(): array {
    return [
      'plans' => [
        'title' => new TranslatableMarkup('Plans & periods'),
        'intro' => new TranslatableMarkup('A plan is one planning cycle; its periods are the weekly time grids the timetable is generated for.'),
      ],
      'catalogues' => [
        'title' => new TranslatableMarkup('Academic catalogues'),
        'intro' => new TranslatableMarkup('Your teachers, rooms, subjects, specialties, levels, cohorts, groups and activity types. Catalogue records serve all your plans; groups belong to one plan.'),
      ],
      'assignments' => [
        'title' => new TranslatableMarkup('Assignments & templates'),
        'intro' => new TranslatableMarkup('The breakdown of each subject in each period (its scheduling options), its weekly activities, and which teachers and rooms may take them.'),
      ],
    ];
  }

  /**
   * Whether records of a type are owned by their creator (not by a plan).
   */
  public static function isOwned(string $entity_type_id): bool {
    return in_array($entity_type_id, self::OWNED, TRUE);
  }

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
   * Returns every academic entity type: data entry types, then derived ones.
   *
   * @return list<string>
   *   The entity type IDs.
   */
  public static function all(): array {
    return [...self::ids(), ...self::DERIVED];
  }

  /**
   * Returns a one-line description of each entity type, for the overview.
   *
   * @return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup>
   *   Descriptions keyed by entity type ID.
   */
  public static function descriptions(): array {
    return [
      'ancora_plan' => new TranslatableMarkup('A planning cycle, for example one semester. Periods, groups, templates and assignments belong to a plan; your catalogues serve all your plans.'),
      'ancora_period' => new TranslatableMarkup('The weekly grid of a plan: days, time slots per day, first hour and slot length.'),
      'ancora_activity_type' => new TranslatableMarkup('Kinds of activity (lecture, lab...) with their length in slots, priority and cost.'),
      'ancora_specialty' => new TranslatableMarkup('Degree programmes or specialties.'),
      'ancora_level' => new TranslatableMarkup('Years or levels of study.'),
      'ancora_cohort' => new TranslatableMarkup('Student cohorts of a specialty and level, with their enrolment.'),
      'ancora_student_group' => new TranslatableMarkup('Teaching groups: the cohorts that attend one kind of activity together.'),
      'ancora_subject' => new TranslatableMarkup('Subjects of a specialty and level.'),
      'ancora_activity_template' => new TranslatableMarkup('The weekly breakdown of a subject in a period: one template per weekly activity.'),
      'ancora_breakdown' => new TranslatableMarkup('The scheduling options of each subject in each period: days between its activities, their order, and activities on the same day. One per subject and period, created automatically.'),
      'ancora_room' => new TranslatableMarkup('Rooms and their capacity.'),
      'ancora_teacher' => new TranslatableMarkup('Teachers.'),
      'ancora_teacher_assignment' => new TranslatableMarkup('Which teacher may give an activity template, to which groups, with which priority.'),
      'ancora_room_assignment' => new TranslatableMarkup('Which rooms an activity template may use, with which priority.'),
    ];
  }

}
