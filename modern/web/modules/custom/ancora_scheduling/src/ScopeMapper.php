<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\Entity\Level;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Specialty;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\ancora_engine\Job\JobSource;

/**
 * Maps the Drupal records a user picks to the engine ids of a job.
 *
 * One engine database serves one site, and a site can hold several plans
 * whose codes repeat (codes are unique only within a plan). Every engine id
 * of a plan is therefore its LegacyRows id with a fixed prefix built from the
 * plan's entity id, which never changes: "p<plan id>:". The colon cannot
 * appear in a code ([A-Za-z0-9._-]), so two plans never share an engine id.
 *
 * The scope of a job is then:
 * - specialty: prefix + specialty code;
 * - level: prefix + level code;
 * - period: prefix + period code.
 *
 * The legacy owner ("creador") of the plan's engine rows is the plan's own
 * legacy user, prefix + "owner", so the legacy triggers that work per owner
 * (breakdown rows for every subject and period, activity type priorities)
 * never touch another plan.
 */
final class ScopeMapper {

  /**
   * The engine id prefix of a plan.
   */
  public static function prefix(Plan $plan): string {
    return 'p' . (int) $plan->id() . ':';
  }

  /**
   * The legacy owner of a plan's engine rows.
   */
  public static function creator(Plan $plan): string {
    return self::prefix($plan) . 'owner';
  }

  /**
   * The engine scope of a plan, period, specialty and level.
   *
   * @throws \InvalidArgumentException
   *   When a record does not belong to the plan.
   */
  public static function scope(Plan $plan, Period $period, Specialty $specialty, Level $level): JobScope {
    self::assertSamePlan($plan, $period, $specialty, $level);
    $prefix = self::prefix($plan);
    return new JobScope($prefix . $specialty->getCode(), $prefix . $level->getCode(), $prefix . $period->getCode());
  }

  /**
   * The job source of a plan, period, specialty and level.
   */
  public static function source(Plan $plan, Period $period, Specialty $specialty, Level $level): JobSource {
    self::assertSamePlan($plan, $period, $specialty, $level);
    return new JobSource((int) $plan->id(), (int) $period->id(), (int) $specialty->id(), (int) $level->id(), self::prefix($plan));
  }

  /**
   * The input snapshot hash of an export: sha256 of its canonical JSON.
   *
   * The export is deterministic (sorted lists, UUID keys, typed numbers), so
   * equal planning data always gives the same hash, and any change of a
   * record of the plan (a label, a capacity, an assignment) changes it.
   */
  public static function hash(array $export): string {
    return hash('sha256', json_encode($export, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR));
  }

  /**
   * Refuses records of another plan.
   */
  private static function assertSamePlan(Plan $plan, AcademicEntityInterface ...$records): void {
    foreach ($records as $record) {
      if ($record->getPlanId() !== (int) $plan->id()) {
        throw new \InvalidArgumentException(sprintf('%s "%s" does not belong to plan "%s".', $record->getEntityType()->getLabel(), $record->getCode(), $plan->getCode()));
      }
    }
  }

}
