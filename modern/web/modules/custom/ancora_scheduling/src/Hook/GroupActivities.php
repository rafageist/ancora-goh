<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Hook;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\EngineSettings;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Whether a student group has placed activities (the legacy cohort guard).
 *
 * The answer to hook_ancora_academic_group_has_activities(), the input of
 * the legacy guard of mueve_o_crea_brigada_de_grupo (GroupMembership, D10):
 * - workspace mode (#95): the placements are entities of this site;
 * - two-database mode: the "actividad" rows of the group's engine id in the
 *   engine database (numbered ids, LegacyRows::engineId(); the code part
 *   may have changed since the last sync, so only the number is matched).
 *   If the engine database cannot be read, the answer is NULL (unknown) and
 *   the change is refused: the guard never fails open;
 * - two-database mode with no engine database configured: no run can have
 *   placed anything, so FALSE.
 */
final class GroupActivities {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    #[Autowire(service: 'ancora_engine.settings')]
    private readonly EngineSettings $settings,
    #[Autowire(service: 'ancora_engine.engine_client')]
    private readonly EngineClient $engine,
    #[Autowire(service: 'logger.channel.ancora_scheduling')]
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * Implements hook_ancora_academic_cell_occupied() (D9a).
   *
   * Whether a placement of the cohort (through any group that has it), the
   * teacher or the room covers the cell, its duration included: the cells
   * of the occupancy rows restringe_segun_actividad() writes. Workspace
   * mode reads the placement entities; two-database mode the occupancy
   * rows (por_actividad) of the engine database. Unknown is NULL.
   */
  #[Hook('ancora_academic_cell_occupied')]
  public function cellOccupied(string $kind, int $target_id, Period $period, int $day, int $slot): ?bool {
    if ($this->settings->isWorkspace()) {
      $storage = $this->entityTypeManager->getStorage('ancora_placement');
      $query = $storage->getQuery()->accessCheck(FALSE)->condition('period', $period->id())->condition('day', $day)->condition('slot', $slot, '<=');
      if ($kind === 'cohort') {
        $groups = $this->entityTypeManager->getStorage('ancora_student_group')->getQuery()->accessCheck(FALSE)->condition('cohorts', $target_id)->execute();
        if ($groups === []) {
          return FALSE;
        }
        $query->condition('student_group', $groups, 'IN');
      }
      else {
        $query->condition($kind, $target_id);
      }
      foreach ($storage->loadMultiple($query->execute()) as $placement) {
        $template = $placement->get('template')->entity;
        $duration = $template instanceof ActivityTemplate ? (int) ($template->getActivityType()?->getDurationSlots() ?? 1) : 1;
        if ((int) $placement->get('slot')->value + max(1, $duration) - 1 >= $slot) {
          return TRUE;
        }
      }
      return FALSE;
    }
    if (!$this->settings->isConfigured()) {
      return FALSE;
    }
    [$table, $column] = LegacyRows::RESTRICTION_TABLES[$kind];
    $number = static fn (int $id): string => '^' . preg_quote(sprintf('%010d', $id) . LegacyRows::ID_SEPARATOR, NULL);
    try {
      $connection = $this->engine->connectReadOnly();
      try {
        $sql = sprintf('SELECT EXISTS (SELECT 1 FROM %s WHERE %s ~ $1 AND periodo ~ $2 AND dia = $3 AND turno = $4 AND por_actividad) AS found', $table, $column);
        return ($this->engine->rows($connection, $sql, [$number($target_id), $number((int) $period->id()), $day, $slot])[0]['found'] ?? 'f') === 't';
      }
      finally {
        pg_close($connection);
      }
    }
    catch (\RuntimeException $e) {
      $this->logger->warning('The occupancy of a cell could not be read from the engine database; the restriction is refused: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Implements hook_ancora_academic_group_has_activities().
   */
  #[Hook('ancora_academic_group_has_activities')]
  public function hasActivities(StudentGroup $group): ?bool {
    if ($this->settings->isWorkspace()) {
      return (int) $this->entityTypeManager->getStorage('ancora_placement')->getQuery()
        ->accessCheck(FALSE)
        ->condition('student_group', $group->id())
        ->count()
        ->execute() > 0;
    }
    if (!$this->settings->isConfigured()) {
      // No engine database: no run could have placed anything.
      return FALSE;
    }
    $prefix = '^' . preg_quote(sprintf('%010d', (int) $group->id()) . LegacyRows::ID_SEPARATOR, NULL);
    try {
      $connection = $this->engine->connectReadOnly();
      try {
        return ($this->engine->rows($connection, 'SELECT EXISTS (SELECT 1 FROM actividad WHERE grupo ~ $1) AS found', [$prefix])[0]['found'] ?? 'f') === 't';
      }
      finally {
        pg_close($connection);
      }
    }
    catch (\RuntimeException $e) {
      $this->logger->warning('The placed activities of group @id could not be read from the engine database; the membership change is refused: @message', [
        '@id' => $group->id(),
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

}
