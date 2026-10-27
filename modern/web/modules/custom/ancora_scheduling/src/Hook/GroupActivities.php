<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Hook;

use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\Engine\EngineException;
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
 *   If the engine database cannot be read, the group counts as having no
 *   activity and a warning is logged: there, the next run deletes and
 *   replaces the scope's placements anyway (D7).
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
   * Implements hook_ancora_academic_group_has_activities().
   */
  #[Hook('ancora_academic_group_has_activities')]
  public function hasActivities(StudentGroup $group): bool {
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
    catch (EngineException $e) {
      $this->logger->warning('The placed activities of group @id could not be read from the engine database: @message', [
        '@id' => $group->id(),
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

}
