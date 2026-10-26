<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Sync;

use Drupal\ancora_engine\Engine\EngineClient;
use PgSql\Connection as PgConnection;

/**
 * Writes the legacy rows of one plan into an engine database.
 *
 * Replace semantics: after a sync, the plan's rows in the engine database are
 * exactly the rows built by LegacyRows from the current export. The plan's
 * rows are the ones whose id (or owning column) starts with the plan's
 * prefix (ScopeMapper). Rows of other plans are never read or written.
 *
 * The sync is a difference, not a delete and reinsert: unchanged rows are not
 * touched, so the placements of the plan's other scopes, which reference
 * them, survive a sync (the legacy foreign keys cascade on delete). It runs
 * in two phases:
 * 1. delete, in reverse foreign key order, the plan's rows whose key is no
 *    longer exported (the cascades remove what depended on them);
 * 2. for each table in foreign key order, read the plan's current rows again
 *    (triggers and cascades may have changed them), insert the missing ones
 *    and update the ones that differ.
 *
 * The legacy triggers stay enabled (the runtime role cannot disable them) and
 * are respected:
 * - nuevo_desglose_si_nueva_asignatura / _nuevo_periodo create a breakdown
 *   (desglose) row for every subject and period of the same owner. Those
 *   rows are reused: a breakdown is matched by (subject, period), its id is
 *   whatever the trigger gave it, and the activity templates are pointed at
 *   that id. A breakdown row is inserted only when no trigger created it.
 * - The template order triggers renumber "orden" after an insert or a
 *   delete. The sync sets the exported order afterwards, through two updates
 *   with actualizacion_interna = TRUE (the flag the triggers skip), via
 *   negative values so the (desglose, orden) unique key never conflicts.
 * - The room priority triggers compact priorities after a delete; the
 *   priorities are set after the deletes, with actualizacion_interna = TRUE.
 *
 * Every statement uses bound parameters; table and column names come only
 * from the constants of this class.
 */
final class LegacyTableSync {

  /**
   * The legacy tables in foreign key order: key columns and owner column.
   *
   * The owner column holds a prefixed id of the plan, and selects its rows.
   */
  public const TABLES = [
    'planificacion' => [['id'], 'id'],
    'periodo' => [['id'], 'id'],
    'periodicidad' => [['id'], 'id'],
    'clasificacion_de_actividad' => [['id'], 'id'],
    'especialidad' => [['id'], 'id'],
    'nivel' => [['id'], 'id'],
    'brigada' => [['id'], 'id'],
    'grupo' => [['id'], 'id'],
    'brigada_de_grupo' => [['brigada', 'grupo'], 'grupo'],
    'asignatura' => [['id'], 'id'],
    'desglose' => [['asignatura', 'periodo'], 'asignatura'],
    'plantilla_de_actividad' => [['id'], 'id'],
    'profesor' => [['id'], 'id'],
    'profesor_para_actividad' => [['id'], 'id'],
    'profesor_para_grupo' => [['profesor_para_actividad', 'grupo'], 'grupo'],
    'lugar' => [['id'], 'id'],
    'lugar_para_actividad' => [['plantilla', 'lugar'], 'plantilla'],
  ];

  /**
   * Columns that are never compared (generated or internal).
   */
  private const NOT_COMPARED = [
    // The breakdown id is the trigger's; templates are remapped to it.
    'desglose' => ['id'],
    // Set by the order pass, after the inserts and updates.
    'plantilla_de_actividad' => ['orden'],
  ];

  /**
   * Tables updated with actualizacion_interna = TRUE (their triggers skip).
   */
  private const INTERNAL_FLAG = ['plantilla_de_actividad', 'lugar_para_actividad'];

  /**
   * Row changes: table => [inserted, updated, deleted].
   */
  private array $changes = [];

  /**
   * Rows whose actualizacion_interna flag must be reset: table => keys.
   */
  private array $flagged = [];

  public function __construct(
    private readonly EngineClient $engine,
    private readonly PgConnection $connection,
  ) {}

  /**
   * Syncs the rows of one plan.
   *
   * @param array<string, list<array<string, scalar|null>>> $rows
   *   The result of LegacyRows::build() for the plan, with its prefix.
   * @param string $prefix
   *   The plan's engine id prefix.
   * @param string $creator
   *   The plan's legacy owner, as used in $rows.
   *
   * @return array
   *   inserted, updated and deleted row counts, and the changes per table.
   *
   * @throws \Drupal\ancora_engine\Engine\EngineException
   */
  public function sync(array $rows, string $prefix, string $creator): array {
    if ($prefix === '' || array_diff_key(self::TABLES, $rows) !== []) {
      throw new \InvalidArgumentException('A plan sync needs a prefix and the rows of every legacy table.');
    }
    $this->changes = array_fill_keys(array_keys(self::TABLES), [0, 0, 0]);
    $this->flagged = [];
    $this->ensureCreator($creator);

    // 1. Deletes, children first.
    foreach (array_reverse(self::TABLES, TRUE) as $table => [$key, $owner]) {
      $wanted = [];
      foreach ($rows[$table] as $row) {
        $wanted[self::keyOf($row, $key)] = TRUE;
      }
      foreach ($this->current($table, $key, $owner, $prefix) as $id => $row) {
        if (!isset($wanted[$id])) {
          $this->changes[$table][2] += $this->delete($table, $key, $row);
        }
      }
    }

    // 2. Inserts and updates, parents first, against fresh state.
    $breakdowns = [];
    foreach (self::TABLES as $table => [$key, $owner]) {
      $desired = $rows[$table];
      if ($table === 'plantilla_de_actividad') {
        $desired = array_map(static function (array $row) use ($breakdowns): array {
          if (!isset($breakdowns[$row['desglose']])) {
            throw new \RuntimeException(sprintf('Template %s has no breakdown row.', $row['id']));
          }
          $row['desglose'] = $breakdowns[$row['desglose']];
          return $row;
        }, $desired);
      }
      $this->upsert($table, $key, $owner, $prefix, $desired);
      if ($table === 'desglose') {
        // Exported breakdown id => the id of the row in the engine.
        $actual = $this->current($table, $key, $owner, $prefix);
        foreach ($rows['desglose'] as $row) {
          $breakdowns[$row['id']] = $actual[self::keyOf($row, $key)]['id'];
        }
      }
      if ($table === 'plantilla_de_actividad') {
        $this->fixOrder($desired, $prefix);
      }
    }
    $this->resetFlags();

    $totals = [0, 0, 0];
    $tables = [];
    foreach ($this->changes as $table => $counts) {
      foreach ($counts as $i => $count) {
        $totals[$i] += $count;
      }
      if (array_sum($counts) > 0) {
        $tables[$table] = ['inserted' => $counts[0], 'updated' => $counts[1], 'deleted' => $counts[2]];
      }
    }
    return [
      'inserted' => $totals[0],
      'updated' => $totals[1],
      'deleted' => $totals[2],
      'tables' => $tables,
    ];
  }

  /**
   * Creates the plan's legacy owner when it does not exist.
   *
   * It has no usable password: nobody logs in to the legacy application with
   * it. It only owns the plan's rows.
   */
  private function ensureCreator(string $creator): void {
    $this->engine->affected($this->connection, "INSERT INTO seguridad.usuario (id, nombre, info, correo, nick, contrasena) VALUES ($1, $1, 'Owner of a plan synced from Drupal', 'noreply@example.invalid', $1, '!unusable: synced plan owner') ON CONFLICT DO NOTHING", [$creator]);
  }

  /**
   * The plan's current rows of a table, keyed by their key.
   *
   * @return array<string, array<string, string|null>>
   *   Rows as text values.
   */
  private function current(string $table, array $key, string $owner, string $prefix): array {
    $rows = [];
    $sql = sprintf('SELECT * FROM %s WHERE starts_with(%s, $1)', self::ident($table), self::ident($owner));
    foreach ($this->engine->rows($this->connection, $sql, [$prefix]) as $row) {
      $rows[self::keyOf($row, $key)] = $row;
    }
    return $rows;
  }

  /**
   * Inserts the missing rows and updates the differing ones.
   */
  private function upsert(string $table, array $key, string $owner, string $prefix, array $desired): void {
    $current = $this->current($table, $key, $owner, $prefix);
    $skip = self::NOT_COMPARED[$table] ?? [];
    foreach ($desired as $row) {
      $id = self::keyOf($row, $key);
      if (!isset($current[$id])) {
        $columns = array_keys($row);
        $sql = sprintf(
          'INSERT INTO %s (%s) VALUES (%s)',
          self::ident($table),
          implode(', ', array_map(self::ident(...), $columns)),
          implode(', ', array_map(static fn (int $i): string => '$' . ($i + 1), array_keys($columns))),
        );
        $this->engine->affected($this->connection, $sql, array_map(self::text(...), array_values($row)));
        $this->changes[$table][0]++;
        continue;
      }
      $set = [];
      foreach ($row as $column => $value) {
        if (!in_array($column, $key, TRUE) && !in_array($column, $skip, TRUE) && !self::same($current[$id][$column] ?? NULL, $value)) {
          $set[$column] = $value;
        }
      }
      if ($set === []) {
        continue;
      }
      if (in_array($table, self::INTERNAL_FLAG, TRUE)) {
        $set['actualizacion_interna'] = TRUE;
        $this->flagged[$table][$id] = $row;
      }
      $this->update($table, $key, $row, $set);
      $this->changes[$table][1]++;
    }
  }

  /**
   * Sets the exported order of the plan's templates.
   *
   * The order triggers may have renumbered templates during the inserts and
   * deletes. Moving through negative values keeps UNIQUE (desglose, orden)
   * satisfied at every step; the internal flag keeps the triggers out.
   */
  private function fixOrder(array $desired, string $prefix): void {
    $current = $this->current('plantilla_de_actividad', ['id'], 'id', $prefix);
    $wrong = array_filter($desired, static fn (array $row): bool => (int) $current[$row['id']]['orden'] !== (int) $row['orden']);
    foreach ([-1, 1] as $sign) {
      foreach ($wrong as $row) {
        $this->update('plantilla_de_actividad', ['id'], $row, [
          'orden' => $sign * (int) $row['orden'],
          'actualizacion_interna' => TRUE,
        ]);
      }
    }
    foreach ($wrong as $row) {
      $this->flagged['plantilla_de_actividad'][$row['id']] = $row;
      $this->changes['plantilla_de_actividad'][1]++;
    }
  }

  /**
   * Clears actualizacion_interna on the rows the sync flagged.
   */
  private function resetFlags(): void {
    foreach ($this->flagged as $table => $rows) {
      foreach ($rows as $row) {
        $this->update($table, self::TABLES[$table][0], $row, ['actualizacion_interna' => FALSE]);
      }
    }
  }

  /**
   * Updates one row by its key.
   */
  private function update(string $table, array $key, array $row, array $set): void {
    $params = [];
    $assign = [];
    foreach ($set as $column => $value) {
      $params[] = self::text($value);
      $assign[] = self::ident($column) . ' = $' . count($params);
    }
    $where = [];
    foreach ($key as $column) {
      $params[] = self::text($row[$column]);
      $where[] = self::ident($column) . ' = $' . count($params);
    }
    $this->engine->affected($this->connection, sprintf('UPDATE %s SET %s WHERE %s', self::ident($table), implode(', ', $assign), implode(' AND ', $where)), $params);
  }

  /**
   * Deletes one row by its key.
   *
   * @return int
   *   1 when the row was deleted, 0 when a cascade had already removed it.
   */
  private function delete(string $table, array $key, array $row): int {
    $params = [];
    $where = [];
    foreach ($key as $column) {
      $params[] = self::text($row[$column]);
      $where[] = self::ident($column) . ' = $' . count($params);
    }
    return $this->engine->affected($this->connection, sprintf('DELETE FROM %s WHERE %s', self::ident($table), implode(' AND ', $where)), $params);
  }

  /**
   * The key of a row, as one string.
   */
  private static function keyOf(array $row, array $key): string {
    return implode("\x1F", array_map(static fn (string $column): string => (string) self::text($row[$column] ?? NULL), $key));
  }

  /**
   * A value as a bound text parameter (PostgreSQL input format).
   */
  private static function text(string|int|float|bool|null $value): ?string {
    return match (TRUE) {
      $value === NULL => NULL,
      is_bool($value) => $value ? 't' : 'f',
      is_float($value) => var_export($value, TRUE),
      default => (string) $value,
    };
  }

  /**
   * Whether a stored text value equals an exported value.
   */
  private static function same(?string $current, string|int|float|bool|null $desired): bool {
    if ($current === NULL || $desired === NULL) {
      return $current === NULL && $desired === NULL;
    }
    if (is_int($desired) || is_float($desired)) {
      return is_numeric($current) && (float) $current === (float) $desired;
    }
    return $current === self::text($desired);
  }

  /**
   * A quoted identifier from the constants of this class.
   */
  private static function ident(string $name): string {
    if (!preg_match('/^[a-z_]+$/', $name)) {
      throw new \LogicException(sprintf('Unexpected identifier "%s".', $name));
    }
    return '"' . $name . '"';
  }

}
