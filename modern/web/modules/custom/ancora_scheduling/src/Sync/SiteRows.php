<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Sync;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * The legacy rows of the whole site, and of its stored placements.
 *
 * Site() is the logic EngineSync has used since #70 (moved here unchanged so
 * that the workspace mode builds the same rows): every plan with its
 * creator's catalogues, and the catalogues of users without a plan, each
 * owned by the legacy user of its creator.
 *
 * placements() turns the placement entities (#95) into "actividad" rows, in
 * entity id order: the order in which the worker published them, which is
 * the order of their rows in the workspace that made them (legacy entry
 * order). The row id is the engine id the engine gave the placement.
 */
final class SiteRows {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AcademicExport $export,
    private readonly LegacyRows $legacyRows,
  ) {}

  /**
   * The legacy rows of the whole site.
   *
   * @return array{0: array<string, list<array<string, scalar|null>>>, 1: list<string>, 2: int}
   *   The rows by table (LegacyRows order), the legacy users who own them,
   *   and the number of export issues of every plan.
   */
  public function site(): array {
    $owners = [];
    foreach (AcademicEntityTypes::OWNED as $entity_type_id) {
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      foreach ($storage->getAggregateQuery()->accessCheck(FALSE)->groupBy('uid')->execute() as $row) {
        $owners[(int) $row['uid']] = TRUE;
      }
    }
    ksort($owners);
    $plans = $this->entityTypeManager->getStorage('ancora_plan')->getQuery()
      ->accessCheck(FALSE)
      ->sort('id')
      ->execute();
    $by_owner = [];
    foreach ($this->entityTypeManager->getStorage('ancora_plan')->loadMultiple($plans) as $plan) {
      assert($plan instanceof Plan);
      $by_owner[(int) $plan->getCreatorId()][] = $plan;
    }

    $rows = array_fill_keys(LegacyRows::TABLES, []);
    $seen = [];
    $issues = 0;
    $creators = [];
    foreach (array_keys($owners) as $owner) {
      $creator = ScopeMapper::creatorOf($owner);
      $creators[] = $creator;
      $exports = [];
      foreach ($by_owner[$owner] ?? [] as $plan) {
        $exports[] = $this->export->export($plan);
      }
      $exports = $exports ?: [$this->export->catalogue($owner)];
      foreach ($exports as $export) {
        $issues += count($export['issues']);
        foreach ($this->legacyRows->build($export, $creator, TRUE) as $table => $list) {
          foreach ($list as $row) {
            // A user's catalogue rows are in the export of each of the
            // user's plans; they are written once.
            $key = $table . "\x1F" . LegacyTableSync::keyOf($row, LegacyTableSync::TABLES[$table][0]);
            if (!isset($seen[$key])) {
              $seen[$key] = TRUE;
              $rows[$table][] = $row;
            }
          }
        }
      }
    }
    return [$rows, $creators, $issues];
  }

  /**
   * The stored placements as rows of "actividad", in entity id order.
   *
   * The template, group, teacher and room ids are the engine ids the sync
   * gives those records (template ids from the site rows, the others by
   * LegacyRows::engineId()); "creador" is the plan creator's legacy user.
   *
   * @param array<string, list<array<string, scalar|null>>> $rows
   *   The site rows of the same snapshot (site()).
   *
   * @return list<array<string, scalar|null>>
   *   Rows with id, plantilla, grupo, profesor, lugar, dia, turno,
   *   momento_de_colocacion, creador.
   */
  public function placements(array $rows): array {
    $templates = self::idsByEntity($rows['plantilla_de_actividad']);
    $groups = self::idsByEntity($rows['grupo']);
    $teachers = self::idsByEntity($rows['profesor']);
    $rooms = self::idsByEntity($rows['lugar']);
    $storage = $this->entityTypeManager->getStorage('ancora_placement');
    $ids = $storage->getQuery()->accessCheck(FALSE)->sort('id')->execute();
    $out = [];
    foreach (array_chunk($ids, 200) as $chunk) {
      foreach ($storage->loadMultiple($chunk) as $placement) {
        assert($placement instanceof Placement);
        $id = static fn (array $map, ?string $target) => $target === NULL ? NULL : ($map[(int) $target] ?? NULL);
        $teacher = $placement->get('teacher')->target_id;
        $room = $placement->get('room')->target_id;
        $out[] = [
          'id' => $placement->getEngineId(),
          'plantilla' => $id($templates, $placement->get('template')->target_id),
          'grupo' => $id($groups, $placement->get('student_group')->target_id),
          'profesor' => $teacher === NULL ? NULL : $id($teachers, $teacher),
          'lugar' => $room === NULL ? NULL : $id($rooms, $room),
          'dia' => (int) $placement->get('day')->value,
          'turno' => (int) $placement->get('slot')->value,
          'momento_de_colocacion' => $placement->get('placed_at')->value === NULL ? NULL : (float) $placement->get('placed_at')->value,
          'creador' => ScopeMapper::creatorOf((int) $placement->getCreatorId()),
        ];
      }
    }
    return $out;
  }

  /**
   * The version of a snapshot: a hash of the site rows and the placements.
   */
  public static function version(array $rows, array $placements): string {
    return hash('sha256', json_encode([$rows, $placements], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
  }

  /**
   * Engine ids of a table's rows, keyed by entity id (numbered engine ids).
   */
  public static function idsByEntity(array $rows): array {
    $map = [];
    foreach ($rows as $row) {
      if (preg_match('/^([0-9]{10})' . preg_quote(LegacyRows::ID_SEPARATOR, '/') . '/', (string) $row['id'], $m)) {
        $map[(int) $m[1]] = (string) $row['id'];
      }
    }
    return $map;
  }

}
