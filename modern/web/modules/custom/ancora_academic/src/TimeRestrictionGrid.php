<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\TimeRestriction;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * The restricted cells of one object in one period (D9a).
 *
 * The Drupal counterpart of the legacy period grid
 * (custom_set_Restricciones_Periodo.php): a save gets the whole day x slot
 * grid; a checked cell gets a row if it has none, an unchecked cell loses
 * its row. The same grid edits the engine-only kinds (activity type,
 * subject, cohort, teacher, room; A-4, P-2), with the legacy row semantics
 * (TimeRestriction). A save is all or nothing.
 */
final class TimeRestrictionGrid {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly Connection $database,
  ) {}

  /**
   * The restrictions of an object in a period, by "day:slot".
   *
   * @return array<string, \Drupal\ancora_academic\Entity\TimeRestriction>
   *   The records.
   */
  public function load(string $kind, ?int $target_id, int $period_id): array {
    $storage = $this->entityTypeManager->getStorage('ancora_time_restriction');
    $query = $storage->getQuery()->accessCheck(FALSE)
      ->condition('kind', $kind)
      ->condition('period', $period_id);
    $field = TimeRestriction::KINDS[$kind];
    if ($field !== NULL) {
      $query->condition($field, (int) $target_id);
    }
    $cells = [];
    foreach ($storage->loadMultiple($query->sort('id')->execute()) as $restriction) {
      assert($restriction instanceof TimeRestriction);
      [$day, $slot] = $restriction->getCell();
      $cells["$day:$slot"] = $restriction;
    }
    return $cells;
  }

  /**
   * Whether another record already restricts the cell for the object.
   */
  public function exists(string $kind, ?int $target_id, int $period_id, int $day, int $slot, ?int $except = NULL): bool {
    $found = $this->load($kind, $target_id, $period_id)["$day:$slot"] ?? NULL;
    return $found !== NULL && (int) $found->id() !== $except;
  }

  /**
   * Whether a placement of the object occupies the cell; NULL: unknown.
   *
   * Asks hook_ancora_academic_cell_occupied() of the modules that store the
   * placements; every implementation is called (invokeAll() drops NULL).
   */
  public function occupied(string $kind, int $target_id, Period $period, int $day, int $slot): ?bool {
    $answers = [];
    $this->moduleHandler->invokeAllWith('ancora_academic_cell_occupied', static function (callable $hook) use ($kind, $target_id, $period, $day, $slot, &$answers): void {
      $answers[] = $hook($kind, $target_id, $period, $day, $slot);
    });
    if (in_array(TRUE, $answers, TRUE)) {
      return TRUE;
    }
    return in_array(NULL, $answers, TRUE) ? NULL : FALSE;
  }

  /**
   * Replaces the restricted cells of an object in a period.
   *
   * @param string $kind
   *   The kind (TimeRestriction::KINDS).
   * @param int|null $target_id
   *   The restricted object; NULL for the period itself.
   * @param \Drupal\ancora_academic\Entity\Period $period
   *   The period.
   * @param list<array{0: int, 1: int}> $cells
   *   The cells (day, slot) that must be restricted; every other cell of
   *   the period is not.
   *
   * @return list<string>
   *   The violation messages; empty when the grid was saved. Nothing is
   *   saved when there is any.
   */
  public function save(string $kind, ?int $target_id, Period $period, array $cells): array {
    $existing = $this->load($kind, $target_id, (int) $period->id());
    $wanted = [];
    foreach ($cells as [$day, $slot]) {
      $wanted["$day:$slot"] = [(int) $day, (int) $slot];
    }
    $storage = $this->entityTypeManager->getStorage('ancora_time_restriction');
    $new = [];
    $messages = [];
    foreach (array_diff_key($wanted, $existing) as [$day, $slot]) {
      $values = [
        'period' => $period->id(),
        'plan' => $period->getPlanId(),
        'kind' => $kind,
        'day' => $day,
        'slot' => $slot,
      ];
      if (TimeRestriction::KINDS[$kind] !== NULL) {
        $values[TimeRestriction::KINDS[$kind]] = $target_id;
      }
      $restriction = $storage->create($values);
      foreach ($restriction->validate() as $violation) {
        $messages[] = strip_tags((string) $violation->getMessage());
      }
      $new[] = $restriction;
    }
    if ($messages !== []) {
      return array_values(array_unique($messages));
    }
    $transaction = $this->database->startTransaction();
    try {
      $removed = array_diff_key($existing, $wanted);
      if ($removed !== []) {
        $storage->delete($removed);
      }
      foreach ($new as $restriction) {
        $restriction->save();
      }
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }
    return [];
  }

  /**
   * Deletes the restrictions of a period, plan or object (ON DELETE CASCADE).
   */
  public function deleteFor(string $field, int|string $id): void {
    $storage = $this->entityTypeManager->getStorage('ancora_time_restriction');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition($field, $id)->execute();
    if ($ids !== []) {
      $storage->delete($storage->loadMultiple($ids));
    }
  }

}
