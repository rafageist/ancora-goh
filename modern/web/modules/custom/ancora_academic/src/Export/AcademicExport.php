<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Export;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\ActivityType;
use Drupal\ancora_academic\Entity\Breakdown;
use Drupal\ancora_academic\Entity\Cohort;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Room;
use Drupal\ancora_academic\Entity\RoomAssignment;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\Entity\Subject;
use Drupal\ancora_academic\Entity\TeacherAssignment;
use Drupal\ancora_academic\Entity\TimeRestriction;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Exports the planning dataset of one plan (and optionally one period).
 *
 * The result is a plain PHP array of scalars and lists, the contract between
 * the academic module and the scheduling engine sync (#71). It is documented
 * in docs/modernization/academic-domain-mapping.md. In short:
 *
 * - "schema" is the contract version (SCHEMA).
 * - Every record has a "key", the entity UUID: stable across edits, unique
 *   across plans and sites. References between records use keys. Every
 *   record also has its "id", the entity ID, which grows in creation order
 *   (LegacyRows numbers the engine ids with it).
 * - Every record with a code has its "code", unique among the creator's
 *   records of the type (catalogues) or in the plan.
 * - "owner" is the user ID of the plan's creator. The catalogues (activity
 *   types, specialties, levels, cohorts, subjects, rooms, teachers) are all
 *   the records of that user: legacy catalogues belong to their creador and
 *   serve every plan of that creador (legacy-parity-inventory.md PAR-003).
 *   The plan's own records are its periods, groups, templates and
 *   assignments.
 * - Lists are sorted deterministically, so equal data gives equal output.
 * - With a period, "periods" holds only that period, and templates and their
 *   assignments are only those of that period. Everything else is plan-wide.
 * - "breakdowns" holds the options of the stored subject breakdowns of the
 *   periods (legacy "desglose", D4). A subject and period without a stored
 *   breakdown has the legacy defaults (LegacyRows).
 * - "time_restrictions" holds the restricted cells of the periods (legacy
 *   restriccion_de_*, D9a): kind, the restricted object's key (NULL for
 *   the period itself), day and slot.
 * - "breaks", "priority_zones" and "load_limits" hold the other inputs of
 *   the periods (legacy receso, zpriori, higiene_para_*, D9b).
 * - "group_rooms" holds the fixed and forbidden rooms of the plan's groups,
 *   "travel_costs" the owner's travel costs between rooms (legacy
 *   lugar_fijo, acceso_prohibido, costo_de_traslado; D9c).
 * - "other_resources" holds the owner's other resources (a catalogue),
 *   "resource_groups" the resource groups of the exported templates, in
 *   entity id order, each with its resources ("members", entity id order):
 *   legacy otro_recurso, otro_grupo_de_recursos_para_actividad,
 *   otro_recurso_para_actividad (D9d). Their order is the legacy entry
 *   order, which generar() depends on (SharedResources).
 * - "issues" lists data the engine cannot use as is (for example a template
 *   that requires a teacher and has none); the export never fails for them.
 *
 * The export reads with access checks disabled: the caller (a Drush command,
 * the engine job) decides who may export.
 */
final class AcademicExport {

  /**
   * The version of the export contract.
   */
  public const SCHEMA = 'ancora_academic.export.v7';

  /**
   * The catalogue lists: export key => entity type ID.
   */
  public const CATALOGUES = [
    'activity_types' => 'ancora_activity_type',
    'specialties' => 'ancora_specialty',
    'levels' => 'ancora_level',
    'cohorts' => 'ancora_cohort',
    'subjects' => 'ancora_subject',
    'rooms' => 'ancora_room',
    'teachers' => 'ancora_teacher',
    'other_resources' => 'ancora_other_resource',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Exports a plan, optionally narrowed to one of its periods.
   *
   * @return array<string, mixed>
   *   The export; see the class documentation.
   *
   * @throws \InvalidArgumentException
   *   When the period does not belong to the plan.
   */
  public function export(Plan $plan, ?Period $period = NULL): array {
    if ($period !== NULL && $period->getPlanId() !== (int) $plan->id()) {
      throw new \InvalidArgumentException(sprintf('Period "%s" does not belong to plan "%s".', $period->getCode(), $plan->getCode()));
    }

    $periods = $period !== NULL ? [$period] : $this->load('ancora_period', $plan);
    $period_ids = array_map(static fn (Period $p): int => (int) $p->id(), $periods);
    $templates = array_filter(
      $this->load('ancora_activity_template', $plan),
      static fn (ActivityTemplate $t): bool => in_array((int) $t->get('period')->target_id, $period_ids, TRUE),
    );
    $template_ids = array_map(static fn (ActivityTemplate $t): int => (int) $t->id(), $templates);
    $in_scope = static fn (TeacherAssignment|RoomAssignment $a): bool => in_array((int) $a->get('template')->target_id, $template_ids, TRUE);
    $teacher_assignments = array_filter($this->load('ancora_teacher_assignment', $plan), $in_scope);
    $room_assignments = array_filter($this->load('ancora_room_assignment', $plan), $in_scope);
    $groups = $this->load('ancora_student_group', $plan);

    $owner = (int) $plan->getCreatorId();
    $export = [
      'schema' => self::SCHEMA,
      'owner' => $owner,
      'plan' => [
        'key' => $plan->uuid(),
        'id' => (int) $plan->id(),
        'code' => $plan->getCode(),
        'label' => (string) $plan->label(),
        'starts_on' => $plan->getStartDate(),
        'duration_days' => $plan->getDurationDays(),
        'active' => $plan->isActive(),
      ],
      'periods' => $this->sortByCode(array_map(static fn (Period $p): array => [
        'key' => $p->uuid(),
        'id' => (int) $p->id(),
        'code' => $p->getCode(),
        'label' => (string) $p->label(),
        'days' => $p->getDays(),
        'slots_per_day' => $p->getSlotsPerDay(),
        'start_hour' => $p->getStartHour(),
        'slot_duration' => $p->getSlotDuration(),
        'plan_days' => $p->getPlanDays(),
      ], $periods)),
      'student_groups' => $this->sortByCode(array_map(fn (StudentGroup $g): array => $this->basic($g) + [
        'activity_type' => $this->key($g, 'activity_type'),
        'specialty' => $this->key($g, 'specialty'),
        'level' => $this->key($g, 'level'),
        'cohorts' => $this->keys($g, 'cohorts'),
      ], $groups)),
      'activity_templates' => $this->sortTemplates(array_map(fn (ActivityTemplate $t): array => [
        'key' => $t->uuid(),
        'id' => (int) $t->id(),
        'subject' => $this->key($t, 'subject'),
        'subject_code' => (string) $t->getSubject()?->getCode(),
        'period' => $this->key($t, 'period'),
        'period_code' => (string) $t->getPeriod()?->getCode(),
        'sequence' => $t->getSequence(),
        'activity_type' => $this->key($t, 'activity_type'),
        'requires_teacher' => $t->requiresTeacher(),
        'requires_room' => $t->requiresRoom(),
      ], $templates)),
    ] + $this->catalogueLists($owner);

    $template_order = array_flip(array_column($export['activity_templates'], 'key'));
    $export['teacher_assignments'] = $this->sortAssignments(array_map(fn (TeacherAssignment $a): array => [
      'key' => $a->uuid(),
      'id' => (int) $a->id(),
      'template' => $this->key($a, 'template'),
      'teacher' => $this->key($a, 'teacher'),
      'teacher_code' => (string) $a->getTeacher()?->getCode(),
      'priority' => $a->getPriority(),
      'groups' => $this->keys($a, 'groups'),
    ], $teacher_assignments), $template_order, 'teacher_code');
    $export['room_assignments'] = $this->sortAssignments(array_map(fn (RoomAssignment $a): array => [
      'key' => $a->uuid(),
      'id' => (int) $a->id(),
      'template' => $this->key($a, 'template'),
      'room' => $this->key($a, 'room'),
      'room_code' => (string) $a->getRoom()?->getCode(),
      'priority' => $a->getPriority(),
    ], $room_assignments), $template_order, 'room_code');

    $export['resource_groups'] = $this->resourceGroups($template_ids);
    $export['breakdowns'] = $this->breakdowns($period_ids);
    $export['time_restrictions'] = $this->timeRestrictions($period_ids);
    $export += $this->periodInputs($period_ids);
    $export['group_rooms'] = $this->groupRooms(array_map(static fn (StudentGroup $g): int => (int) $g->id(), $groups));
    $export['issues'] = $this->issues($export);
    return $export;
  }

  /**
   * Exports the catalogues of a user who may have no plan.
   *
   * The legacy database holds every user's catalogue rows, whether or not
   * the user has a plan; the engine sync mirrors that. The result has the
   * shape of export(), with "plan" NULL and no plan records.
   *
   * @return array<string, mixed>
   *   The export.
   */
  public function catalogue(int $owner): array {
    return [
      'schema' => self::SCHEMA,
      'owner' => $owner,
      'plan' => NULL,
      'periods' => [],
      'student_groups' => [],
      'activity_templates' => [],
    ] + $this->catalogueLists($owner) + [
      'teacher_assignments' => [],
      'room_assignments' => [],
      'breakdowns' => [],
      'time_restrictions' => [],
      'breaks' => [],
      'priority_zones' => [],
      'load_limits' => [],
      'group_rooms' => [],
      'resource_groups' => [],
      'issues' => [],
    ];
  }

  /**
   * The catalogue lists of a user, sorted by code.
   *
   * @return array<string, list<array<string, mixed>>>
   *   The lists, by export key.
   */
  private function catalogueLists(int $owner): array {
    $load = fn (string $entity_type_id): array => $this->loadOwned($entity_type_id, $owner);
    return [
      'activity_types' => $this->sortByCode(array_map(fn (ActivityType $t): array => $this->basic($t) + [
        'duration_slots' => $t->getDurationSlots(),
        'priority' => $t->getPriority(),
        'cost' => $t->getCost(),
      ], $load('ancora_activity_type'))),
      'specialties' => $this->sortByCode(array_map($this->basic(...), $load('ancora_specialty'))),
      'levels' => $this->sortByCode(array_map($this->basic(...), $load('ancora_level'))),
      'cohorts' => $this->sortByCode(array_map(fn (Cohort $c): array => $this->basic($c) + [
        'specialty' => $this->key($c, 'specialty'),
        'level' => $this->key($c, 'level'),
        'enrollment' => $c->getEnrollment(),
      ], $load('ancora_cohort'))),
      'subjects' => $this->sortByCode(array_map(fn (Subject $s): array => $this->basic($s) + [
        'specialty' => $this->key($s, 'specialty'),
        'level' => $this->key($s, 'level'),
      ], $load('ancora_subject'))),
      'rooms' => $this->sortByCode(array_map(fn (Room $r): array => $this->basic($r) + [
        'capacity' => $r->getCapacity(),
      ], $load('ancora_room'))),
      'teachers' => $this->sortByCode(array_map($this->basic(...), $load('ancora_teacher'))),
      'other_resources' => $this->sortByCode(array_map($this->basic(...), $load('ancora_other_resource'))),
      'travel_costs' => array_map(fn ($c): array => [
        'key' => (string) $c->uuid(),
        'id' => (int) $c->id(),
        'origin' => $this->key($c, 'origin'),
        'destination' => $this->key($c, 'destination'),
        'cost' => (int) $c->get('cost')->value,
      ], $load('ancora_travel_cost')),
    ];
  }

  /**
   * The stored breakdowns of some periods, by subject and period code.
   *
   * @param list<int> $period_ids
   *   The periods.
   *
   * @return list<array<string, mixed>>
   *   key, id, subject, period (keys) and the four legacy options.
   */
  private function breakdowns(array $period_ids): array {
    if ($period_ids === []) {
      return [];
    }
    $storage = $this->entityTypeManager->getStorage('ancora_breakdown');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('period', $period_ids, 'IN')->sort('id')->execute();
    $records = [];
    foreach ($storage->loadMultiple($ids) as $breakdown) {
      assert($breakdown instanceof Breakdown);
      $records[] = [
        'key' => (string) $breakdown->uuid(),
        'id' => (int) $breakdown->id(),
        'subject' => $this->key($breakdown, 'subject'),
        'subject_code' => (string) $breakdown->getSubject()?->getCode(),
        'period' => $this->key($breakdown, 'period'),
        'period_code' => (string) $breakdown->getPeriod()?->getCode(),
      ] + $breakdown->legacyOptions();
    }
    $order = static fn (array $b): array => [$b['subject_code'], $b['period_code'], $b['id']];
    usort($records, static fn (array $a, array $b): int => $order($a) <=> $order($b));
    return $records;
  }

  /**
   * The resource groups of some templates and their resources, by id.
   *
   * @param list<int> $template_ids
   *   The templates.
   *
   * @return list<array<string, mixed>>
   *   key, id, template, priority, needed, essential and members (key, id,
   *   resource, priority).
   */
  private function resourceGroups(array $template_ids): array {
    if ($template_ids === []) {
      return [];
    }
    $groups = $this->entityTypeManager->getStorage('ancora_resource_group');
    $members = $this->entityTypeManager->getStorage('ancora_resource_member');
    $out = [];
    foreach ($groups->loadMultiple($groups->getQuery()->accessCheck(FALSE)->condition('template', $template_ids, 'IN')->sort('id')->execute()) as $g) {
      $list = [];
      foreach ($members->loadMultiple($members->getQuery()->accessCheck(FALSE)->condition('resource_group', $g->id())->sort('id')->execute()) as $m) {
        $list[] = [
          'key' => (string) $m->uuid(),
          'id' => (int) $m->id(),
          'resource' => $this->key($m, 'other_resource'),
          'priority' => (int) $m->get('priority')->value,
        ];
      }
      $out[] = [
        'key' => (string) $g->uuid(),
        'id' => (int) $g->id(),
        'template' => $this->key($g, 'template'),
        'priority' => (int) $g->get('priority')->value,
        'needed' => (int) $g->get('needed')->value,
        'essential' => (bool) $g->get('essential')->value,
        'members' => $list,
      ];
    }
    return $out;
  }

  /**
   * The fixed and forbidden rooms of some groups, in entity id order.
   *
   * @param list<int> $group_ids
   *   The groups.
   *
   * @return list<array<string, mixed>>
   *   key, id, kind, group and room keys.
   */
  private function groupRooms(array $group_ids): array {
    if ($group_ids === []) {
      return [];
    }
    $storage = $this->entityTypeManager->getStorage('ancora_group_room');
    $out = [];
    foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->condition('student_group', $group_ids, 'IN')->sort('id')->execute()) as $r) {
      $out[] = [
        'key' => (string) $r->uuid(),
        'id' => (int) $r->id(),
        'kind' => (string) $r->get('kind')->value,
        'group' => $this->key($r, 'student_group'),
        'room' => $this->key($r, 'room'),
      ];
    }
    return $out;
  }

  /**
   * The breaks, priority zones and daily load limits of some periods.
   *
   * @param list<int> $period_ids
   *   The periods.
   *
   * @return array{breaks: list<array>, priority_zones: list<array>, load_limits: list<array>}
   *   The records, in entity id order, with keys for the references.
   */
  private function periodInputs(array $period_ids): array {
    $out = ['breaks' => [], 'priority_zones' => [], 'load_limits' => []];
    if ($period_ids === []) {
      return $out;
    }
    $load = function (string $entity_type_id) use ($period_ids): array {
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      return $storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->condition('period', $period_ids, 'IN')->sort('id')->execute());
    };
    foreach ($load('ancora_break') as $break) {
      $out['breaks'][] = [
        'key' => (string) $break->uuid(),
        'id' => (int) $break->id(),
        'period' => $this->key($break, 'period'),
        'after_slot' => (int) $break->get('after_slot')->value,
        'length' => (float) $break->get('length')->value,
      ];
    }
    foreach ($load('ancora_priority_zone') as $zone) {
      $out['priority_zones'][] = [
        'key' => (string) $zone->uuid(),
        'id' => (int) $zone->id(),
        'period' => $this->key($zone, 'period'),
        'activity_type' => $this->key($zone, 'activity_type'),
        'day' => (int) $zone->get('day')->value,
        'slot' => (int) $zone->get('slot')->value,
        'zone' => (int) $zone->get('zone')->value,
      ];
    }
    foreach ($load('ancora_load_limit') as $limit) {
      $kind = (string) $limit->get('kind')->value;
      $out['load_limits'][] = [
        'key' => (string) $limit->uuid(),
        'id' => (int) $limit->id(),
        'period' => $this->key($limit, 'period'),
        'kind' => $kind,
        'target' => $this->key($limit, $kind),
        'max_daily_load' => (int) $limit->get('max_daily_load')->value,
      ];
    }
    return $out;
  }

  /**
   * The restricted cells of some periods, in entity id order.
   *
   * @param list<int> $period_ids
   *   The periods.
   *
   * @return list<array<string, mixed>>
   *   key, id, period (key), kind, target (key or NULL), day, slot.
   */
  private function timeRestrictions(array $period_ids): array {
    if ($period_ids === []) {
      return [];
    }
    $storage = $this->entityTypeManager->getStorage('ancora_time_restriction');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('period', $period_ids, 'IN')->sort('id')->execute();
    $records = [];
    foreach ($storage->loadMultiple($ids) as $restriction) {
      assert($restriction instanceof TimeRestriction);
      $field = TimeRestriction::KINDS[$restriction->getKind()] ?? NULL;
      [$day, $slot] = $restriction->getCell();
      $records[] = [
        'key' => (string) $restriction->uuid(),
        'id' => (int) $restriction->id(),
        'period' => $this->key($restriction, 'period'),
        'kind' => $restriction->getKind(),
        'target' => $field === NULL ? NULL : $this->key($restriction, $field),
        'day' => $day,
        'slot' => $slot,
      ];
    }
    return $records;
  }

  /**
   * Loads every record of a type owned by a user.
   *
   * @return list<\Drupal\ancora_academic\AcademicEntityInterface>
   *   The records, by ID.
   */
  private function loadOwned(string $entity_type_id, int $owner): array {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', $owner)
      ->sort($storage->getEntityType()->getKey('id'))
      ->execute();
    return array_values($storage->loadMultiple($ids));
  }

  /**
   * Loads every record of a type in a plan: periods, groups, templates...
   *
   * @return list<\Drupal\ancora_academic\AcademicEntityInterface>
   *   The records, by ID.
   */
  private function load(string $entity_type_id, Plan $plan): array {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('plan', $plan->id())
      ->sort($storage->getEntityType()->getKey('id'))
      ->execute();
    return array_values($storage->loadMultiple($ids));
  }

  /**
   * Returns the key, ID, code and label of a record.
   *
   * @return array{key: string, id: int, code: string, label: string}
   *   The basic values.
   */
  private function basic(AcademicEntityInterface $entity): array {
    return [
      'key' => (string) $entity->uuid(),
      'id' => (int) $entity->id(),
      'code' => $entity->getCode(),
      'label' => (string) $entity->label(),
    ];
  }

  /**
   * Returns the key of the record a single-valued field references.
   */
  private function key(AcademicEntityInterface $entity, string $field_name): ?string {
    $referenced = $entity->get($field_name)->entity;
    return $referenced ? (string) $referenced->uuid() : NULL;
  }

  /**
   * Returns the keys of the records a multi-valued field references, sorted.
   *
   * @return list<string>
   *   The keys.
   */
  private function keys(AcademicEntityInterface $entity, string $field_name): array {
    $codes = [];
    foreach ($entity->get($field_name)->referencedEntities() as $referenced) {
      assert($referenced instanceof AcademicEntityInterface);
      $codes[$referenced->getCode() . "\0" . $referenced->uuid()] = (string) $referenced->uuid();
    }
    ksort($codes, SORT_STRING);
    return array_values($codes);
  }

  /**
   * Sorts records by code.
   *
   * @param list<array<string, mixed>> $records
   *   The records.
   *
   * @return list<array<string, mixed>>
   *   The sorted records.
   */
  private function sortByCode(array $records): array {
    usort($records, static fn (array $a, array $b): int => strcmp($a['code'], $b['code']));
    return $records;
  }

  /**
   * Sorts templates by subject code, period code and sequence.
   *
   * @param list<array<string, mixed>> $templates
   *   The templates.
   *
   * @return list<array<string, mixed>>
   *   The sorted templates.
   */
  private function sortTemplates(array $templates): array {
    $order = static fn (array $t): array => [$t['subject_code'], $t['period_code'], $t['sequence']];
    usort($templates, static fn (array $a, array $b): int => $order($a) <=> $order($b));
    return $templates;
  }

  /**
   * Sorts assignments by template order, priority and resource code.
   *
   * @param list<array<string, mixed>> $assignments
   *   The assignments.
   * @param array<string, int> $template_order
   *   Position of each template key in the sorted template list.
   * @param string $code
   *   The name of the resource code column.
   *
   * @return list<array<string, mixed>>
   *   The sorted assignments.
   */
  private function sortAssignments(array $assignments, array $template_order, string $code): array {
    $order = static fn (array $a): array => [$template_order[$a['template']] ?? PHP_INT_MAX, $a['priority'], $a[$code]];
    usort($assignments, static fn (array $a, array $b): int => $order($a) <=> $order($b));
    return $assignments;
  }

  /**
   * Finds data the engine cannot use as is.
   *
   * @param array<string, mixed> $export
   *   The export without issues.
   *
   * @return list<array{type: string, key: string, message: string}>
   *   The issues, in template order.
   */
  private function issues(array $export): array {
    $teachers = array_count_values(array_column($export['teacher_assignments'], 'template'));
    $rooms = array_count_values(array_column($export['room_assignments'], 'template'));
    $subjects = array_column($export['subjects'], NULL, 'key');
    $taught = [];
    foreach ($export['teacher_assignments'] as $assignment) {
      foreach ($assignment['groups'] as $group_key) {
        $taught[$assignment['template']][$group_key] = TRUE;
      }
    }
    $issues = [];
    foreach ($export['activity_templates'] as $template) {
      $key = $template['key'];
      $name = sprintf('%s #%d (%s)', $template['subject_code'], $template['sequence'], $template['period_code']);
      $subject = $subjects[$template['subject']] ?? ['specialty' => NULL, 'level' => NULL];
      $eligible = array_filter($export['student_groups'], static fn (array $g): bool => $g['activity_type'] === $template['activity_type']
        && $g['specialty'] === $subject['specialty']
        && $g['level'] === $subject['level']);
      if (!$eligible) {
        $issues[] = self::issue('template_without_groups', $key, "No student group attends $name.");
      }
      if ($template['requires_teacher'] && empty($teachers[$key])) {
        $issues[] = self::issue('template_without_teacher', $key, "$name requires a teacher and has none.");
      }
      elseif ($template['requires_teacher']) {
        foreach ($eligible as $group) {
          if (empty($taught[$key][$group['key']])) {
            $issues[] = self::issue('group_without_teacher', $key, "No teacher of $name teaches group {$group['code']}.");
          }
        }
      }
      if ($template['requires_room'] && empty($rooms[$key])) {
        $issues[] = self::issue('template_without_room', $key, "$name requires a room and has none.");
      }
    }
    return $issues;
  }

  /**
   * Builds an issue record.
   *
   * @return array{type: string, key: string, message: string}
   *   The issue.
   */
  private static function issue(string $type, string $key, string $message): array {
    return ['type' => $type, 'key' => $key, 'message' => $message];
  }

}
