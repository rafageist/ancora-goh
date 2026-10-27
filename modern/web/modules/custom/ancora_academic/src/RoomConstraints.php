<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Entity\Room;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Fixed and forbidden rooms of a group, and travel costs (D9c).
 *
 * Each save replaces one editor's records, all or nothing, as the legacy
 * travel cost editor does pair by pair (custom_set_costotraslado.php:
 * delete the pair, insert it when the cost is not 0).
 */
final class RoomConstraints {

  /**
   * The references of each type, for the cascades.
   */
  public const REFERENCES = [
    'ancora_group_room' => ['plan', 'student_group', 'room'],
    'ancora_travel_cost' => ['origin', 'destination'],
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
  ) {}

  /**
   * The rooms of a group of one kind ("fixed", "forbidden"), in id order.
   *
   * @return list<int>
   *   Room ids.
   */
  public function groupRooms(int|string $group_id, string $kind): array {
    $records = $this->load('ancora_group_room', ['student_group' => $group_id, 'kind' => $kind]);
    return array_values(array_map(static fn ($r): int => (int) $r->get('room')->target_id, $records));
  }

  /**
   * Replaces the fixed room and the forbidden rooms of a group.
   *
   * @param \Drupal\ancora_academic\Entity\StudentGroup $group
   *   The group.
   * @param int|null $fixed
   *   The fixed room, or NULL for none (legacy reads one).
   * @param list<int> $forbidden
   *   The forbidden rooms.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function saveGroupRooms(StudentGroup $group, ?int $fixed, array $forbidden): array {
    $values = [];
    if ($fixed !== NULL) {
      $values[] = ['kind' => 'fixed', 'room' => $fixed];
    }
    foreach (array_unique($forbidden) as $room) {
      $values[] = ['kind' => 'forbidden', 'room' => (int) $room];
    }
    return $this->replace('ancora_group_room', ['student_group' => $group->id()], array_map(
      static fn (array $v): array => $v + ['student_group' => $group->id(), 'plan' => $group->getPlanId()],
      $values,
    ));
  }

  /**
   * The travel costs from a room, by destination room id.
   *
   * @return array<int, int>
   *   Destination id => cost.
   */
  public function travelCosts(int|string $origin_id): array {
    $out = [];
    foreach ($this->load('ancora_travel_cost', ['origin' => $origin_id]) as $cost) {
      $out[(int) $cost->get('destination')->target_id] = (int) $cost->get('cost')->value;
    }
    return $out;
  }

  /**
   * Replaces the travel costs from a room; a cost of 0 is no row.
   *
   * @param \Drupal\ancora_academic\Entity\Room $origin
   *   The room.
   * @param array<int, int> $costs
   *   Destination id => cost.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function saveTravelCosts(Room $origin, array $costs): array {
    $values = [];
    foreach ($costs as $destination => $cost) {
      if ((int) $cost !== 0) {
        $values[] = [
          'origin' => $origin->id(),
          'destination' => (int) $destination,
          'cost' => (int) $cost,
          'uid' => $origin->getCreatorId(),
        ];
      }
    }
    return $this->replace('ancora_travel_cost', ['origin' => $origin->id()], $values);
  }

  /**
   * Deletes the records of a group, room or plan (ON DELETE CASCADE).
   */
  public function deleteFor(string $referenced_type, int|string $id): void {
    foreach (self::REFERENCES as $entity_type_id => $fields) {
      foreach ($fields as $own) {
        $matches = match ($own) {
          'plan' => $referenced_type === 'ancora_plan',
          'student_group' => $referenced_type === 'ancora_student_group',
          default => $referenced_type === 'ancora_room',
        };
        if (!$matches) {
          continue;
        }
        $records = $this->load($entity_type_id, [$own => $id]);
        if ($records !== []) {
          $this->entityTypeManager->getStorage($entity_type_id)->delete($records);
        }
      }
    }
  }

  /**
   * Replaces the records matching conditions by new ones, validated.
   *
   * @return list<string>
   *   The violation messages.
   */
  private function replace(string $entity_type_id, array $conditions, array $values): array {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $transaction = $this->database->startTransaction();
    try {
      $existing = $this->load($entity_type_id, $conditions);
      if ($existing !== []) {
        $storage->delete($existing);
      }
      $messages = [];
      foreach ($values as $value) {
        $record = $storage->create($value);
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
