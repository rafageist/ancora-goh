<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Hook;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;

/**
 * Filters access-checked academic queries to the current user's records.
 *
 * The query counterpart of AcademicAccessControlHandler, for every query
 * with the tag "<entity type>_access": access-checked entity queries (the
 * administrative lists, the planning overview counts, the options of every
 * reference widget and the ValidReference constraint) and Views. It is the
 * legacy filter "creador = $_SESSION['user']" of the data entry lists
 * (docs/modernization/legacy-parity-inventory.md PAR-002):
 *
 * - plans and catalogue records: the owner is the current user;
 * - records of a plan: the plan's owner is the current user;
 * - without "view academic data": nothing;
 * - "administer academic data": no filter.
 *
 * Teachers and rooms are filtered too. Every user may view each of them
 * (quirk Q-23, see AcademicAccessControlHandler), but the data entry lists
 * and the assignment choices are the creator's, as in legacy
 * (get_minibrowser_profesores.php, dame_profesores_no_asignados_a.sql).
 * Queries without access checking (exports, the engine sync, the deletion
 * guard) are not changed.
 */
final class AcademicQueryAccess {

  public function __construct(
    private readonly AccountInterface $currentUser,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
  ) {}

  /**
   * Implements hook_query_TAG_alter() for the academic entity types.
   */
  #[Hook('query_ancora_plan_access_alter')]
  #[Hook('query_ancora_period_access_alter')]
  #[Hook('query_ancora_activity_type_access_alter')]
  #[Hook('query_ancora_specialty_access_alter')]
  #[Hook('query_ancora_level_access_alter')]
  #[Hook('query_ancora_cohort_access_alter')]
  #[Hook('query_ancora_student_group_access_alter')]
  #[Hook('query_ancora_subject_access_alter')]
  #[Hook('query_ancora_activity_template_access_alter')]
  #[Hook('query_ancora_teacher_access_alter')]
  #[Hook('query_ancora_teacher_assignment_access_alter')]
  #[Hook('query_ancora_room_access_alter')]
  #[Hook('query_ancora_room_assignment_access_alter')]
  #[Hook('query_ancora_breakdown_access_alter')]
  #[Hook('query_ancora_time_restriction_access_alter')]
  #[Hook('query_ancora_break_access_alter')]
  #[Hook('query_ancora_priority_zone_access_alter')]
  #[Hook('query_ancora_load_limit_access_alter')]
  public function alter(AlterableInterface $query): void {
    if (!$query instanceof SelectInterface || $this->currentUser->hasPermission(AcademicAccessControlHandler::ADMINISTER)) {
      return;
    }
    foreach ([...AcademicEntityTypes::all(), ...AcademicEntityTypes::GRID] as $entity_type_id) {
      if (!$query->hasTag($entity_type_id . '_access')) {
        continue;
      }
      $alias = $this->baseAlias($query, (string) $this->entityTypeManager->getDefinition($entity_type_id)->getBaseTable());
      if ($alias === NULL) {
        // Fail closed: a tagged query whose base table cannot be found
        // cannot be filtered by owner, so it returns nothing.
        $query->where('1 = 0');
        continue;
      }
      if (!$this->currentUser->hasPermission(AcademicAccessControlHandler::VIEW)) {
        $query->where('1 = 0');
      }
      elseif (AcademicEntityTypes::isOwned($entity_type_id)) {
        $query->condition("$alias.uid", (int) $this->currentUser->id());
      }
      else {
        // A subquery: running a query inside the alter would nest it in the
        // statement being built.
        $plans = $this->database->select((string) $this->entityTypeManager->getDefinition('ancora_plan')->getBaseTable(), 'ancora_own_plan')
          ->fields('ancora_own_plan', ['id'])
          ->condition('ancora_own_plan.uid', (int) $this->currentUser->id());
        $query->condition("$alias.plan", $plans, 'IN');
      }
    }
  }

  /**
   * The alias of a base table in a select query.
   */
  private function baseAlias(SelectInterface $query, string $base_table): ?string {
    foreach ($query->getTables() as $alias => $table) {
      if (($table['table'] ?? NULL) === $base_table) {
        return (string) $alias;
      }
    }
    return NULL;
  }

}
