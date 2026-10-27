<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Cohort membership of the student groups, as legacy brigada_de_grupo.
 *
 * Legacy (legacy-parity-inventory.md PAR-018, D10; cases 113, 136):
 * - adding a brigade to a group is mueve_o_crea_brigada_de_grupo(): a
 *   brigade is in at most one group of the same plan, classification,
 *   specialty and level (the group's "slot"); adding it to a second group
 *   of the slot MOVES it there. A group of another plan, or of another
 *   classification, is another slot: the brigade is added, not moved;
 * - the move is refused, and nothing changes, when the target group or the
 *   group the brigade would leave already has placed activities;
 * - removing a brigade from a group (custom_EliminaBrigadaDeGrupo.php) has
 *   no activity guard;
 * - nothing checks that the brigades share the group's specialty and level
 *   (dame_grupos() goes by the brigades, case 136).
 *
 * Here the cohorts field of a group is the membership. Its constraint
 * (AncoraGroupMembership) applies the guard to the cohorts a save adds, and
 * MembershipHooks removes each added cohort from the other groups of the
 * slot after the save. Whether a group has placed activities is asked to
 * the modules that store them, through
 * hook_ancora_academic_group_has_activities() (ancora_scheduling: the
 * placement entities, or the engine database in two-database mode).
 */
final class GroupMembership {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * The cohorts a save of the group adds, by id.
   *
   * @return list<int>
   *   The cohort ids in the group that the stored group does not have.
   */
  public function added(StudentGroup $group, ?StudentGroup $original = NULL): array {
    if ($original === NULL && !$group->isNew()) {
      $unchanged = $this->entityTypeManager->getStorage('ancora_student_group')->loadUnchanged($group->id());
      $original = $unchanged instanceof StudentGroup ? $unchanged : NULL;
    }
    $before = $original === NULL ? [] : $original->getCohortIds();
    return array_values(array_unique(array_diff($group->getCohortIds(), $before)));
  }

  /**
   * The other groups of the group's slot that hold a cohort.
   *
   * The slot is the plan, activity type, specialty and level of the group
   * (mueve_o_crea_brigada_de_grupo.sql).
   *
   * @return \Drupal\ancora_academic\Entity\StudentGroup[]
   *   The groups, by id.
   */
  public function sources(StudentGroup $group, int $cohort_id): array {
    $values = [];
    foreach (['plan', 'activity_type', 'specialty', 'level'] as $field) {
      $values[$field] = $group->get($field)->target_id;
      if ($values[$field] === NULL) {
        return [];
      }
    }
    $storage = $this->entityTypeManager->getStorage('ancora_student_group');
    $query = $storage->getQuery()->accessCheck(FALSE)->condition('cohorts', $cohort_id);
    foreach ($values as $field => $value) {
      $query->condition($field, $value);
    }
    if (!$group->isNew()) {
      $query->condition('id', $group->id(), '<>');
    }
    return $storage->loadMultiple($query->sort('id')->execute());
  }

  /**
   * Whether a group has placed activities (legacy "actividad" rows).
   */
  public function hasActivities(StudentGroup $group): bool {
    if ($group->isNew()) {
      return FALSE;
    }
    foreach ($this->moduleHandler->invokeAll('ancora_academic_group_has_activities', [$group]) as $answer) {
      if ($answer === TRUE) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Moves the cohorts a save added out of the other groups of the slot.
   *
   * Called after the group is saved; the guard has run in validation.
   *
   * @param \Drupal\ancora_academic\Entity\StudentGroup $group
   *   The saved group.
   * @param list<int> $added
   *   The added cohort ids.
   */
  public function move(StudentGroup $group, array $added): void {
    foreach ($added as $cohort_id) {
      foreach ($this->sources($group, $cohort_id) as $source) {
        assert($source instanceof StudentGroup);
        $kept = array_values(array_filter($source->get('cohorts')->getValue(), static fn (array $item): bool => (int) $item['target_id'] !== $cohort_id));
        $source->set('cohorts', $kept);
        $source->save();
      }
    }
  }

}
