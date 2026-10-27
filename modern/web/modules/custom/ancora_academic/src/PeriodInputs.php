<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Entity\BreakTime;
use Drupal\ancora_academic\Entity\LoadLimit;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\PriorityZone;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Breaks, priority zones and daily load limits of a period (D9b).
 *
 * Each save replaces the records of one editor (all or nothing), as the
 * legacy zone editor does (custom_set_zpriori.php deletes the
 * classification's rows of the period and inserts the cells whose zone is
 * not 1). Breaks and load limits had no legacy editor (A-4; P-2): their
 * editors write the rows the engine reads, with the legacy keys.
 */
final class PeriodInputs {

  /**
   * The references of each input type, for the cascades.
   */
  public const REFERENCES = [
    'ancora_break' => ['plan', 'period'],
    'ancora_priority_zone' => ['plan', 'period', 'activity_type'],
    'ancora_load_limit' => ['plan', 'period', 'teacher', 'cohort'],
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
  ) {}

  /**
   * The breaks of a period, by the slot they follow.
   *
   * @return array<int, \Drupal\ancora_academic\Entity\BreakTime>
   *   The breaks.
   */
  public function breaks(int|string $period_id): array {
    $out = [];
    foreach ($this->load('ancora_break', ['period' => $period_id]) as $break) {
      assert($break instanceof BreakTime);
      $out[$break->getAfterSlot()] = $break;
    }
    ksort($out);
    return $out;
  }

  /**
   * The start hour of a slot, breaks included (dame_hora_inicial_turno).
   */
  public function slotStart(Period $period, int $slot): float {
    $hour = $period->getStartHour() + ($slot - 1) * $period->getSlotDuration();
    foreach ($this->breaks($period->id()) as $after => $break) {
      if ($after < $slot) {
        $hour += $break->getLength();
      }
    }
    return $hour;
  }

  /**
   * Replaces the breaks of a period.
   *
   * @param \Drupal\ancora_academic\Entity\Period $period
   *   The period.
   * @param array<int, float|null> $lengths
   *   After slot => length in hours; NULL or no entry: no break.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function saveBreaks(Period $period, array $lengths): array {
    $values = [];
    foreach ($lengths as $after => $length) {
      if ($length !== NULL) {
        $values[] = ['after_slot' => (int) $after, 'length' => $length];
      }
    }
    return $this->replace('ancora_break', ['period' => $period->id()], $period, $values);
  }

  /**
   * The zones of an activity type in a period, by "day:slot".
   *
   * @return array<string, int>
   *   The stored zones (cells without a record are zone 1).
   */
  public function zones(int|string $activity_type_id, int|string $period_id): array {
    $out = [];
    foreach ($this->load('ancora_priority_zone', ['activity_type' => $activity_type_id, 'period' => $period_id]) as $zone) {
      assert($zone instanceof PriorityZone);
      $out[$zone->get('day')->value . ':' . $zone->get('slot')->value] = (int) $zone->get('zone')->value;
    }
    return $out;
  }

  /**
   * Replaces the zones of an activity type in a period.
   *
   * @param int|string $activity_type_id
   *   The activity type.
   * @param \Drupal\ancora_academic\Entity\Period $period
   *   The period.
   * @param array<string, int> $zones
   *   Zone by "day:slot"; zone 1 is not stored.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function saveZones(int|string $activity_type_id, Period $period, array $zones): array {
    $values = [];
    foreach ($zones as $cell => $zone) {
      [$day, $slot] = array_map('intval', explode(':', (string) $cell));
      if ((int) $zone !== 1) {
        $values[] = ['activity_type' => $activity_type_id, 'day' => $day, 'slot' => $slot, 'zone' => (int) $zone];
      }
    }
    return $this->replace('ancora_priority_zone', ['activity_type' => $activity_type_id, 'period' => $period->id()], $period, $values);
  }

  /**
   * The daily load limits of a period, by kind and object id.
   *
   * @return array<int, int>
   *   Object id => limit.
   */
  public function limits(int|string $period_id, string $kind): array {
    $field = LoadLimit::KINDS[$kind][0];
    $out = [];
    foreach ($this->load('ancora_load_limit', ['period' => $period_id, 'kind' => $kind]) as $limit) {
      $out[(int) $limit->get($field)->target_id] = (int) $limit->get('max_daily_load')->value;
    }
    return $out;
  }

  /**
   * Replaces the daily load limits of one kind in a period.
   *
   * @param \Drupal\ancora_academic\Entity\Period $period
   *   The period.
   * @param string $kind
   *   The kind: "teacher" or "cohort".
   * @param array<int, int|null> $limits
   *   Object id => limit; NULL: no limit.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function saveLimits(Period $period, string $kind, array $limits): array {
    $field = LoadLimit::KINDS[$kind][0];
    $values = [];
    foreach ($limits as $id => $limit) {
      if ($limit !== NULL) {
        $values[] = ['kind' => $kind, $field => (int) $id, 'max_daily_load' => (int) $limit];
      }
    }
    return $this->replace('ancora_load_limit', ['period' => $period->id(), 'kind' => $kind], $period, $values);
  }

  /**
   * Deletes the inputs of a period, plan or object (ON DELETE CASCADE).
   */
  public function deleteFor(string $field, int|string $id): void {
    foreach (self::REFERENCES as $entity_type_id => $fields) {
      if (!in_array($field, $fields, TRUE)) {
        continue;
      }
      $records = $this->load($entity_type_id, [$field => $id]);
      if ($records !== []) {
        $this->entityTypeManager->getStorage($entity_type_id)->delete($records);
      }
    }
  }

  /**
   * Replaces the records matching some conditions by new ones, validated.
   *
   * @return list<string>
   *   The violation messages.
   */
  private function replace(string $entity_type_id, array $conditions, Period $period, array $values): array {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $transaction = $this->database->startTransaction();
    try {
      // The old records go first, so that the new ones are validated
      // against the rest only; a violation rolls everything back.
      $existing = $this->load($entity_type_id, $conditions);
      if ($existing !== []) {
        $storage->delete($existing);
      }
      $messages = [];
      foreach ($values as $value) {
        $record = $storage->create($value + ['period' => $period->id(), 'plan' => $period->getPlanId()]);
        foreach ($record->validate() as $violation) {
          $messages[] = strip_tags((string) $violation->getMessage());
        }
        if ($messages === []) {
          $record->save();
        }
      }
      if ($messages !== []) {
        $transaction->rollBack();
        $storage->resetCache();
        return array_values(array_unique($messages));
      }
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }
    return [];
  }

  /**
   * Loads records by field values, without access checks, in id order.
   *
   * @return array<int|string, \Drupal\Core\Entity\EntityInterface>
   *   The records.
   */
  private function load(string $entity_type_id, array $conditions): array {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $query = $storage->getQuery()->accessCheck(FALSE)->sort('id');
    foreach ($conditions as $field => $value) {
      $query->condition($field, $value);
    }
    return $storage->loadMultiple($query->execute());
  }

}
