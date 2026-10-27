<?php

/**
 * @file
 * Hooks of the Áncora academic module.
 */

declare(strict_types=1);

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
 * @return bool
 *   TRUE when the group has at least one placed activity.
 */
function hook_ancora_academic_group_has_activities(StudentGroup $group): bool {
  return FALSE;
}
