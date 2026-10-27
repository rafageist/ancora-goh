<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Entity\Breakdown;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Subject;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Keeps one breakdown per subject and period, as the legacy triggers do.
 *
 * Legacy (legacy-parity-inventory.md PAR-031, case 110):
 * - nuevo_desglose_cuando_nueva_asignatura: a new subject gets a breakdown
 *   for every period of every plan of its creator;
 * - nuevo_desglose_cuando_nuevo_periodo: a new period gets one for every
 *   subject of its plan's creator;
 * - both with the column defaults (Breakdown::DEFAULTS);
 * - desglose references asignatura and periodo ON DELETE CASCADE: deleting
 *   the subject or the period deletes the breakdown, whatever its options.
 *
 * BreakdownHooks calls it on every insert, owner change and delete.
 * Breakdowns are therefore never created or deleted by a user, and they do
 * not block deleting their subject or period (they are not counted by
 * ReferenceGuard). An owner change, which the legacy screens do not have
 * (drush ancora-academic:reassign-owner), creates the breakdowns of the new
 * pairs the same way.
 */
final class BreakdownMaintainer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Creates the missing breakdowns of a subject: every period of its owner.
   *
   * @return int
   *   The number of breakdowns created.
   */
  public function ensureForSubject(Subject $subject): int {
    $owner = $subject->getCreatorId();
    if ($owner === NULL) {
      return 0;
    }
    $plans = array_keys($this->load('ancora_plan', ['uid' => $owner]));
    if ($plans === []) {
      return 0;
    }
    $created = 0;
    foreach ($this->load('ancora_period', ['plan' => $plans]) as $period) {
      assert($period instanceof Period);
      $created += $this->ensure($subject, $period);
    }
    return $created;
  }

  /**
   * Creates the missing breakdowns of a period: every subject of its owner.
   *
   * @return int
   *   The number of breakdowns created.
   */
  public function ensureForPeriod(Period $period): int {
    $owner = $period->getCreatorId();
    if ($owner === NULL) {
      return 0;
    }
    $created = 0;
    foreach ($this->load('ancora_subject', ['uid' => $owner]) as $subject) {
      assert($subject instanceof Subject);
      $created += $this->ensure($subject, $period);
    }
    return $created;
  }

  /**
   * The periods of a plan, by id.
   *
   * @return array<int|string, \Drupal\ancora_academic\Entity\Period>
   *   The periods.
   */
  public function periodsOf(Plan $plan): array {
    return $this->load('ancora_period', ['plan' => $plan->id()]);
  }

  /**
   * Creates the missing breakdowns of the whole site (the update path).
   *
   * @return int
   *   The number of breakdowns created.
   */
  public function ensureAll(): int {
    $created = 0;
    foreach ($this->load('ancora_period', []) as $period) {
      assert($period instanceof Period);
      $created += $this->ensureForPeriod($period);
    }
    return $created;
  }

  /**
   * Deletes the breakdowns of a subject, period or plan (ON DELETE CASCADE).
   */
  public function deleteFor(string $field, int|string $id): void {
    $breakdowns = $this->load('ancora_breakdown', [$field => $id]);
    if ($breakdowns !== []) {
      $this->entityTypeManager->getStorage('ancora_breakdown')->delete($breakdowns);
    }
  }

  /**
   * The breakdowns of a period, by id.
   *
   * @return array<int|string, \Drupal\ancora_academic\Entity\Breakdown>
   *   The breakdowns.
   */
  public function ofPeriod(int|string $period_id): array {
    return $this->load('ancora_breakdown', ['period' => $period_id]);
  }

  /**
   * The breakdown of a subject in a period, if it exists.
   */
  public function find(int|string $subject_id, int|string $period_id): ?Breakdown {
    $found = $this->load('ancora_breakdown', ['subject' => $subject_id, 'period' => $period_id]);
    $breakdown = reset($found);
    return $breakdown instanceof Breakdown ? $breakdown : NULL;
  }

  /**
   * Creates the breakdown of a subject and period with the legacy defaults.
   *
   * @return int
   *   1 when it was created, 0 when it already existed.
   */
  private function ensure(Subject $subject, Period $period): int {
    if ($this->find($subject->id(), $period->id()) !== NULL) {
      return 0;
    }
    $this->entityTypeManager->getStorage('ancora_breakdown')->create([
      'subject' => $subject->id(),
      'period' => $period->id(),
      'plan' => $period->getPlanId(),
    ] + Breakdown::DEFAULTS)->save();
    return 1;
  }

  /**
   * Loads records by field values, without access checks, in id order.
   *
   * @return array<int|string, \Drupal\Core\Entity\EntityInterface>
   *   The records, by id.
   */
  private function load(string $entity_type_id, array $conditions): array {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $query = $storage->getQuery()->accessCheck(FALSE)->sort($storage->getEntityType()->getKey('id'));
    foreach ($conditions as $field => $value) {
      $query->condition($field, $value, is_array($value) ? 'IN' : '=');
    }
    return $storage->loadMultiple($query->execute());
  }

}
