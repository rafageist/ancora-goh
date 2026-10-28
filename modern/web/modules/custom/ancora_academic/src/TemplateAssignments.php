<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\RoomAssignment;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\Entity\TeacherAssignment;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * The teachers and rooms of an activity template (#122, PAR-051, 052, 055).
 *
 * The operations of the legacy template windows (desgloses browser,
 * www/js/app/server/ancoragoh_server_recursos.js:1-66,
 * ancoragoh_client_forms_pxact.js, ancoragoh_client_forms_lxact.js), with
 * the same values:
 * - assign teachers or rooms: the template owner's ones that the template
 *   does not have yet (DameProfesoresNoAsignadosA, DameLugaresNoAsignadosA),
 *   each with priority 1 and, for a teacher, no group yet;
 * - set the priority of an assignment to one of 1..max + 1, or 1..max when
 *   max equals the number of assignments (get_form_edit_pxact_prioridad.php,
 *   custom_getui_EditarPrioridadLxact.php); ties are allowed;
 * - set the groups of a teacher assignment, among the template's groups
 *   (DameGruposDePlantilla: the plan of the period, the activity type, the
 *   specialty and level of the subject; form_edit_pxact.tpl);
 * - remove an assignment (legacy asks first; so does the editor).
 * The room priority relevel on update and delete stays RoomPriorityHooks
 * (D9c, Q-07); teachers have none (PAR-051). Every change goes through the
 * Entity API and its constraints (AncoraUniqueInScope, AncoraSameCreator,
 * AncoraEligibleGroups); nothing is saved when one fails.
 */
final class TemplateAssignments {

  use StringTranslationTrait;

  /**
   * The kinds of assignment: kind => [entity type, resource field].
   */
  public const KINDS = [
    'teacher' => ['ancora_teacher_assignment', 'teacher', 'ancora_teacher'],
    'room' => ['ancora_room_assignment', 'room', 'ancora_room'],
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * The assignments of a template, by priority and entry order.
   *
   * @return array<int, \Drupal\ancora_academic\Entity\TeacherAssignment|\Drupal\ancora_academic\Entity\RoomAssignment>
   *   Id => assignment.
   */
  public function assignments(ActivityTemplate $template, string $kind): array {
    $storage = $this->entityTypeManager->getStorage(self::KINDS[$kind][0]);
    $storage->resetCache();
    $ids = $storage->getQuery()->accessCheck(FALSE)
      ->condition('template', $template->id())
      ->sort('priority')
      ->sort('id')
      ->execute();
    $assignments = [];
    foreach ($storage->loadMultiple($ids) as $id => $assignment) {
      $assignments[(int) $id] = $assignment;
    }
    return $assignments;
  }

  /**
   * One assignment of the template, or NULL (another template's is NULL).
   */
  public function assignment(ActivityTemplate $template, string $kind, int|string $id): TeacherAssignment|RoomAssignment|null {
    if (!isset(self::KINDS[$kind]) || !ctype_digit((string) $id)) {
      return NULL;
    }
    $assignment = $this->entityTypeManager->getStorage(self::KINDS[$kind][0])->load((int) $id);
    if (!$assignment instanceof TeacherAssignment && !$assignment instanceof RoomAssignment) {
      return NULL;
    }
    return (int) $assignment->get('template')->target_id === (int) $template->id() ? $assignment : NULL;
  }

  /**
   * The owner's teachers or rooms the template does not have, in entry order.
   *
   * @return array<int, \Drupal\Core\Entity\EntityInterface>
   *   Id => teacher or room.
   */
  public function candidates(ActivityTemplate $template, string $kind): array {
    [, $field, $type] = self::KINDS[$kind];
    $assigned = [];
    foreach ($this->assignments($template, $kind) as $assignment) {
      $assigned[(int) $assignment->get($field)->target_id] = TRUE;
    }
    $storage = $this->entityTypeManager->getStorage($type);
    $ids = $storage->getQuery()->accessCheck(FALSE)
      ->condition('uid', (int) $template->getCreatorId())
      ->sort('id')
      ->execute();
    $candidates = [];
    foreach ($storage->loadMultiple($ids) as $id => $resource) {
      if (!isset($assigned[(int) $id])) {
        $candidates[(int) $id] = $resource;
      }
    }
    return $candidates;
  }

  /**
   * The template's groups (DameGruposDePlantilla), in entry order.
   *
   * @return array<int, \Drupal\ancora_academic\Entity\StudentGroup>
   *   Id => group.
   */
  public function groups(ActivityTemplate $template): array {
    $subject = $template->getSubject();
    $period = $template->get('period')->entity;
    if ($subject === NULL || $period === NULL) {
      return [];
    }
    $storage = $this->entityTypeManager->getStorage('ancora_student_group');
    $ids = $storage->getQuery()->accessCheck(FALSE)
      ->condition('plan', $period->get('plan')->target_id)
      ->condition('activity_type', $template->get('activity_type')->target_id)
      ->condition('specialty', $subject->get('specialty')->target_id)
      ->condition('level', $subject->get('level')->target_id)
      ->sort('id')
      ->execute();
    $groups = [];
    foreach ($storage->loadMultiple($ids) as $id => $group) {
      if ($group instanceof StudentGroup) {
        $groups[(int) $id] = $group;
      }
    }
    return $groups;
  }

  /**
   * The enrolment of a group: the sum of its cohorts' (dame_matricula_total).
   */
  public function enrollment(StudentGroup $group): int {
    $total = 0;
    foreach ($group->get('cohorts')->referencedEntities() as $cohort) {
      $total += (int) $cohort->get('enrollment')->value;
    }
    return $total;
  }

  /**
   * The priorities an assignment may take: 1..max + 1, or 1..max.
   *
   * @return list<int>
   *   The priorities.
   */
  public function priorityOptions(ActivityTemplate $template, string $kind): array {
    $assignments = $this->assignments($template, $kind);
    $max = 0;
    foreach ($assignments as $assignment) {
      $max = max($max, (int) $assignment->get('priority')->value);
    }
    if ($max === count($assignments)) {
      $max--;
    }
    return range(1, max(1, $max + 1));
  }

  /**
   * Assigns one of the owner's teachers or rooms, with priority 1.
   *
   * @return array{0: list<string>, 1: ?\Drupal\Core\Entity\EntityInterface}
   *   The violation messages, and the new assignment when saved.
   */
  public function assign(ActivityTemplate $template, string $kind, int|string $resource_id): array {
    return $this->create($template, $kind, $resource_id, 1, []);
  }

  /**
   * Creates an assignment of one of the owner's unassigned teachers or rooms.
   *
   * @return array{0: list<string>, 1: ?\Drupal\Core\Entity\EntityInterface}
   *   The violation messages, and the new assignment when saved.
   */
  private function create(ActivityTemplate $template, string $kind, int|string $resource_id, int $priority, array $groups): array {
    $candidates = $this->candidates($template, $kind);
    if (!ctype_digit((string) $resource_id) || !isset($candidates[(int) $resource_id])) {
      $exists = ctype_digit((string) $resource_id) && $this->entityTypeManager->getStorage(self::KINDS[$kind][2])->load((int) $resource_id) !== NULL;
      $assigned = FALSE;
      foreach ($exists ? $this->assignments($template, $kind) : [] as $assignment) {
        $assigned = $assigned || (int) $assignment->get(self::KINDS[$kind][1])->target_id === (int) $resource_id;
      }
      $message = match (TRUE) {
        $assigned && $kind === 'teacher' => $this->t('This teacher is already assigned to this activity template.'),
        $assigned => $this->t('This room is already assigned to this activity template.'),
        $exists && $kind === 'teacher' => $this->t('That teacher is not one of the teachers of the owner of this template.'),
        $exists => $this->t('That room is not one of the rooms of the owner of this template.'),
        default => $this->t('That record does not exist any more.'),
      };
      return [[(string) $message], NULL];
    }
    $values = [
      'template' => $template->id(),
      'plan' => $template->getPlanId(),
      self::KINDS[$kind][1] => (int) $resource_id,
      'priority' => $priority,
    ];
    if ($kind === 'teacher') {
      $values['groups'] = $groups;
    }
    $assignment = $this->entityTypeManager->getStorage(self::KINDS[$kind][0])->create($values);
    $messages = $this->save($assignment);
    return [$messages, $messages === [] ? $assignment : NULL];
  }

  /**
   * Sets the priority of an assignment to one of the offered values.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function setPriority(ActivityTemplate $template, TeacherAssignment|RoomAssignment $assignment, int|string $priority): array {
    $kind = $assignment instanceof TeacherAssignment ? 'teacher' : 'room';
    $options = $this->priorityOptions($template, $kind);
    if (!ctype_digit((string) $priority) || !in_array((int) $priority, $options, TRUE)) {
      return [(string) $this->t('The priority must be one of @options.', ['@options' => implode(', ', $options)])];
    }
    if ((int) $assignment->get('priority')->value === (int) $priority) {
      return [];
    }
    return $this->save($assignment->set('priority', (int) $priority));
  }

  /**
   * Sets the groups of a teacher assignment, among the template's groups.
   *
   * @param \Drupal\ancora_academic\Entity\ActivityTemplate $template
   *   The template.
   * @param \Drupal\ancora_academic\Entity\TeacherAssignment $assignment
   *   The assignment.
   * @param list<int|string> $group_ids
   *   The groups, in any order; they are kept in the template's group order.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function setGroups(ActivityTemplate $template, TeacherAssignment $assignment, array $group_ids): array {
    $groups = $this->groups($template);
    $wanted = [];
    foreach ($group_ids as $id) {
      if (!ctype_digit((string) $id) || !isset($groups[(int) $id])) {
        return [(string) $this->t('That group does not attend this activity template.')];
      }
      $wanted[(int) $id] = TRUE;
    }
    $ordered = array_values(array_filter(array_keys($groups), static fn (int $id): bool => isset($wanted[$id])));
    if ($ordered === $assignment->getGroupIds()) {
      return [];
    }
    return $this->save($assignment->set('groups', $ordered));
  }

  /**
   * Removes an assignment; returns what restore() needs to undo it.
   *
   * @return array{kind: string, template: int, resource: int, priority: int, groups: list<int>, label: string}
   *   The removed assignment's values.
   */
  public function remove(TeacherAssignment|RoomAssignment $assignment): array {
    $kind = $assignment instanceof TeacherAssignment ? 'teacher' : 'room';
    $snapshot = [
      'kind' => $kind,
      'template' => (int) $assignment->get('template')->target_id,
      'resource' => (int) $assignment->get(self::KINDS[$kind][1])->target_id,
      'priority' => (int) $assignment->get('priority')->value,
      'groups' => $assignment instanceof TeacherAssignment ? $assignment->getGroupIds() : [],
      'label' => (string) $assignment->get(self::KINDS[$kind][1])->entity?->label(),
    ];
    $assignment->delete();
    return $snapshot;
  }

  /**
   * Re-creates a removed assignment (undo), with its priority and groups.
   *
   * The priority is restored when it is at most the template's highest
   * priority + 1 (the legacy range), otherwise it becomes that value; after
   * a room relevel (RoomPriorityHooks) this may tie with another room. The
   * groups are those that still attend the template.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function restore(ActivityTemplate $template, array $snapshot): array {
    $kind = $snapshot['kind'] ?? NULL;
    if (!isset(self::KINDS[$kind]) || (int) ($snapshot['template'] ?? 0) !== (int) $template->id()) {
      return [(string) $this->t('There is nothing to undo.')];
    }
    $max = 0;
    foreach ($this->assignments($template, $kind) as $assignment) {
      $max = max($max, (int) $assignment->get('priority')->value);
    }
    $priority = min(max(1, (int) $snapshot['priority']), $max + 1);
    $groups = array_values(array_intersect(array_map('intval', $snapshot['groups'] ?? []), array_keys($this->groups($template))));
    return $this->create($template, $kind, (int) $snapshot['resource'], $priority, $groups)[0];
  }

  /**
   * A token of the template's assignments, to detect concurrent changes.
   */
  public function state(ActivityTemplate $template): string {
    $rows = [];
    foreach (array_keys(self::KINDS) as $kind) {
      foreach ($this->assignments($template, $kind) as $id => $assignment) {
        $rows[] = [
          $kind,
          $id,
          (int) $assignment->get(self::KINDS[$kind][1])->target_id,
          (int) $assignment->get('priority')->value,
          $assignment instanceof TeacherAssignment ? $assignment->getGroupIds() : [],
        ];
      }
    }
    return hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
  }

  /**
   * Validates and saves a record.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  private function save(EntityInterface $record): array {
    $messages = [];
    foreach ($record->validate() as $violation) {
      $messages[] = strip_tags((string) $violation->getMessage());
    }
    if ($messages !== []) {
      return array_values(array_unique($messages));
    }
    $record->save();
    return [];
  }

}
