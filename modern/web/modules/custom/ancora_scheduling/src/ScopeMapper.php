<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\Entity\Level;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Specialty;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\ancora_engine\Job\JobSource;

/**
 * Maps the Drupal records a user picks to the engine ids of a job.
 *
 * One engine database serves one site and holds every user's data, as one
 * legacy database held every user's rows. Engine ids are numbered with the
 * entity id (LegacyRows::engineId(): "0000000012:E"): unique on the site,
 * stable when a code is edited, and in creation order like the legacy
 * replica ids (inventory PAR-110).
 *
 * The scope of a job is the engine ids of its specialty, level and period.
 * The specialty and the level must be the plan creator's: the legacy
 * generator window offers only the user's own specialties, levels and
 * periods (get_data_especialidades.php, get_data_niveles.php,
 * get_data_planificaciones.php).
 *
 * The legacy owner ("creador") of a user's engine rows is that user's legacy
 * user, creatorOf(): the triggers that work per creador (breakdown rows for
 * every subject and period of the creador's plans, activity type priorities)
 * then act across that user's plans, as in legacy.
 */
final class ScopeMapper {

  /**
   * The id marker recorded in a job source: the separator of numbered ids.
   *
   * The engine lists the placement digest with the ids cut after it, so the
   * digest of a synced plan uses the codes (EngineClient::PLACEMENTS).
   */
  public const ID_MARKER = LegacyRows::ID_SEPARATOR;

  /**
   * The legacy user id of a Drupal user in the engine database.
   */
  public static function creatorOf(int $uid): string {
    return 'u' . $uid;
  }

  /**
   * The legacy owner of a plan's engine rows: its creator's legacy user.
   */
  public static function creator(Plan $plan): string {
    return self::creatorOf((int) $plan->getCreatorId());
  }

  /**
   * The engine id of a record with a code.
   */
  public static function engineId(AcademicEntityInterface $record): string {
    return LegacyRows::engineId((int) $record->id(), $record->getCode());
  }

  /**
   * The engine scope of a plan, period, specialty and level.
   *
   * @throws \InvalidArgumentException
   *   When the period is not the plan's, or the specialty or the level is
   *   not the plan creator's.
   */
  public static function scope(Plan $plan, Period $period, Specialty $specialty, Level $level): JobScope {
    self::assertScope($plan, $period, $specialty, $level);
    return new JobScope(self::engineId($specialty), self::engineId($level), self::engineId($period));
  }

  /**
   * The job source of a plan, period, specialty and level.
   */
  public static function source(Plan $plan, Period $period, Specialty $specialty, Level $level): JobSource {
    self::assertScope($plan, $period, $specialty, $level);
    return new JobSource((int) $plan->id(), (int) $period->id(), (int) $specialty->id(), (int) $level->id(), self::ID_MARKER);
  }

  /**
   * The input snapshot hash of an export: sha256 of its canonical JSON.
   *
   * The export is deterministic (sorted lists, UUID keys, typed numbers), so
   * equal planning data always gives the same hash, and any change of a
   * record of the plan or of its creator's catalogues (a label, a capacity,
   * an assignment) changes it.
   */
  public static function hash(array $export): string {
    return hash('sha256', json_encode($export, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR));
  }

  /**
   * Refuses a period of another plan, or a specialty or level of another user.
   */
  private static function assertScope(Plan $plan, Period $period, AcademicEntityInterface ...$catalogue): void {
    if ($period->getPlanId() !== (int) $plan->id()) {
      throw new \InvalidArgumentException(sprintf('Period "%s" does not belong to plan "%s".', $period->getCode(), $plan->getCode()));
    }
    foreach ($catalogue as $record) {
      if ($record->getCreatorId() !== $plan->getCreatorId()) {
        throw new \InvalidArgumentException(sprintf('%s "%s" does not belong to the creator of plan "%s".', $record->getEntityType()->getLabel(), $record->getCode(), $plan->getCode()));
      }
    }
  }

}
