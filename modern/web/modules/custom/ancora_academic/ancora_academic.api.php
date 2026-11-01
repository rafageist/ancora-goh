<?php

/**
 * @file
 * Hooks of the Áncora academic module.
 */

declare(strict_types=1);

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\StudentGroup;

/**
 * Tells whether a student group has placed activities.
 *
 * The legacy cohort move (mueve_o_crea_brigada_de_grupo) is refused when
 * the target group or the group the cohort leaves has "actividad" rows
 * (GroupMembership). The module that stores the placements answers.
 *
 * @param \Drupal\ancora_academic\Entity\StudentGroup $group
 *   A saved group.
 *
 * @return bool|null
 *   TRUE when the group has at least one placed activity, FALSE when it has
 *   none, NULL when it cannot be known right now (the change is then
 *   refused).
 */
function hook_ancora_academic_group_has_activities(StudentGroup $group): ?bool {
  return FALSE;
}

/**
 * Tells whether a placement of an object occupies a cell of a period.
 *
 * The legacy restriction tables of brigades, teachers and rooms hold both
 * the manual restrictions and the occupancy rows of placements, under one
 * UNIQUE (object, period, day, slot): a manual restriction cannot be
 * entered on an occupied cell (TimeRestrictionGrid, D9a).
 *
 * @param string $kind
 *   The kind: "cohort", "teacher" or "room".
 * @param int $target_id
 *   The entity id of the cohort, teacher or room.
 * @param \Drupal\ancora_academic\Entity\Period $period
 *   The period.
 * @param int $day
 *   The day.
 * @param int $slot
 *   The slot.
 *
 * @return bool|null
 *   TRUE when a placement covers the cell, FALSE when none does, NULL when
 *   it cannot be known right now (the restriction is then refused).
 */
function hook_ancora_academic_cell_occupied(string $kind, int $target_id, Period $period, int $day, int $slot): ?bool {
  return FALSE;
}
