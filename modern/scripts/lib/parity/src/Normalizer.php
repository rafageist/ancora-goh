<?php

declare(strict_types=1);

namespace Ancora\Parity;

use PgSql\Connection;

/**
 * Builds the canonical form of one database.
 */
final class Normalizer {

  /**
   * The compared tables in foreign key order.
   *
   * - type: the scenario record type of a row (natural key lookup), or NULL;
   * - natural: the natural key columns after the type; "user:" marks the
   *   creator, "ref:" a reference resolved to its key first;
   * - derived: for rows that are no scenario record, the columns whose
   *   resolved values form the key;
   * - refs: foreign key column => referenced table;
   * - where: an SQL condition on the compared rows (the manual restriction
   *   rows; the occupancy rows of placements are compared as placements).
   */
  public const TABLES = [
    'planificacion' => [
      'type' => 'plan',
      'natural' => ['user:creador', 'descripcion'],
      'refs' => [],
    ],
    'periodo' => [
      'type' => 'period',
      'natural' => ['ref:planificacion', 'descripcion'],
      'refs' => ['planificacion' => 'planificacion'],
    ],
    'periodicidad' => [
      'derived' => ['periodo', 'dia'],
      'refs' => ['periodo' => 'periodo'],
    ],
    'clasificacion_de_actividad' => [
      'type' => 'activity_type',
      'natural' => ['user:creador', 'descripcion'],
      'refs' => [],
    ],
    'especialidad' => [
      'type' => 'specialty',
      'natural' => ['user:creador', 'descripcion'],
      'refs' => [],
    ],
    'nivel' => [
      'type' => 'level',
      'natural' => ['user:creador', 'descripcion'],
      'refs' => [],
    ],
    'brigada' => [
      'type' => 'cohort',
      'natural' => ['user:creador', 'descripcion'],
      'refs' => ['especialidad' => 'especialidad', 'nivel' => 'nivel'],
    ],
    'grupo' => [
      'type' => 'student_group',
      'natural' => ['ref:planificacion', 'descripcion'],
      'refs' => [
        'clasificacion' => 'clasificacion_de_actividad',
        'especialidad' => 'especialidad',
        'planificacion' => 'planificacion',
        'nivel' => 'nivel',
      ],
    ],
    'brigada_de_grupo' => [
      'refs' => ['brigada' => 'brigada', 'grupo' => 'grupo'],
    ],
    'asignatura' => [
      'type' => 'subject',
      'natural' => ['user:creador', 'descripcion'],
      'refs' => ['especialidad' => 'especialidad', 'nivel' => 'nivel'],
    ],
    'desglose' => [
      'derived' => ['asignatura', 'periodo'],
      'refs' => ['asignatura' => 'asignatura', 'periodo' => 'periodo'],
    ],
    'plantilla_de_actividad' => [
      'type' => 'activity_template',
      'natural' => ['ref:desglose.asignatura', 'ref:desglose.periodo', 'orden'],
      'refs' => ['desglose' => 'desglose', 'clasificacion' => 'clasificacion_de_actividad'],
    ],
    'profesor' => [
      'type' => 'teacher',
      'natural' => ['user:creador', 'nombre'],
      'refs' => [],
    ],
    'profesor_para_actividad' => [
      'type' => 'teacher_assignment',
      'natural' => ['ref:plantilla', 'ref:profesor'],
      'refs' => ['plantilla' => 'plantilla_de_actividad', 'profesor' => 'profesor'],
    ],
    'profesor_para_grupo' => [
      'refs' => ['profesor_para_actividad' => 'profesor_para_actividad', 'grupo' => 'grupo'],
    ],
    'lugar' => [
      'type' => 'room',
      'natural' => ['user:creador', 'nombre'],
      'refs' => [],
    ],
    'lugar_para_actividad' => [
      'refs' => ['plantilla' => 'plantilla_de_actividad', 'lugar' => 'lugar'],
    ],
    // Manual time restrictions (D9a).
    'restriccion_de_periodo' => [
      'derived' => ['periodo', 'dia', 'turno'],
      'refs' => ['periodo' => 'periodo'],
    ],
    'restriccion_de_clasif_act' => [
      'derived' => ['clasificacion', 'periodo', 'dia', 'turno'],
      'refs' => ['clasificacion' => 'clasificacion_de_actividad', 'periodo' => 'periodo'],
    ],
    'restriccion_de_asignatura' => [
      'derived' => ['asignatura', 'periodo', 'dia', 'turno'],
      'refs' => ['asignatura' => 'asignatura', 'periodo' => 'periodo'],
    ],
    'restriccion_de_brigada' => [
      'derived' => ['brigada', 'periodo', 'dia', 'turno'],
      'refs' => ['brigada' => 'brigada', 'periodo' => 'periodo'],
      'where' => 'NOT por_actividad',
    ],
    'restriccion_de_profesor' => [
      'derived' => ['profesor', 'periodo', 'dia', 'turno'],
      'refs' => ['profesor' => 'profesor', 'periodo' => 'periodo'],
      'where' => 'NOT por_actividad',
    ],
    'restriccion_de_lugar' => [
      'derived' => ['lugar', 'periodo', 'dia', 'turno'],
      'refs' => ['lugar' => 'lugar', 'periodo' => 'periodo'],
      'where' => 'NOT por_actividad',
    ],
    // D9b.
    'receso' => [
      'derived' => ['periodo', 'despues_del_turno'],
      'refs' => ['periodo' => 'periodo'],
    ],
    'zpriori' => [
      'derived' => ['clasificacion', 'periodo', 'dia', 'turno'],
      'refs' => ['clasificacion' => 'clasificacion_de_actividad', 'periodo' => 'periodo'],
    ],
    'higiene_para_profesor' => [
      'derived' => ['profesor', 'periodo'],
      'refs' => ['profesor' => 'profesor', 'periodo' => 'periodo'],
    ],
    'higiene_para_brigada' => [
      'derived' => ['brigada', 'periodo'],
      'refs' => ['brigada' => 'brigada', 'periodo' => 'periodo'],
    ],
    // D9c.
    'lugar_fijo' => [
      'derived' => ['grupo', 'lugar'],
      'refs' => ['grupo' => 'grupo', 'lugar' => 'lugar'],
    ],
    'acceso_prohibido' => [
      'derived' => ['grupo', 'lugar'],
      'refs' => ['grupo' => 'grupo', 'lugar' => 'lugar'],
    ],
    'costo_de_traslado' => [
      'derived' => ['origen', 'destino'],
      'refs' => ['origen' => 'lugar', 'destino' => 'lugar'],
    ],
    // D9d. The usage rows (otros_recursos_usados) are compared with the
    // placements of the runs, their occupancy rows by count (other_tables).
    'otro_recurso' => [
      'type' => 'other_resource',
      'natural' => ['user:creador', 'nombre'],
      'refs' => [],
    ],
    'otro_grupo_de_recursos_para_actividad' => [
      'type' => 'resource_group',
      'natural' => ['ref:plantilla', 'prioridad'],
      'refs' => ['plantilla' => 'plantilla_de_actividad'],
    ],
    'otro_recurso_para_actividad' => [
      'refs' => ['grupo_de_recursos' => 'otro_grupo_de_recursos_para_actividad', 'otro_recurso' => 'otro_recurso'],
    ],
  ];

  /**
   * Resolved key of every row id, by table.
   *
   * @var array<string, array<string, string>>
   */
  private array $keys = [];

  /**
   * Raw rows by table and id, for references into derived keys.
   *
   * @var array<string, array<string, array<string, string|null>>>
   */
  private array $raw = [];

  public function __construct(
    private readonly Scenario $scenario,
    private readonly Connection $db,
    private readonly array $users,
  ) {}

  /**
   * The canonical form.
   *
   * @return array<string, mixed>
   *   tables, other_tables (row counts of every other table) and runs.
   */
  public function canonical(): array {
    $index = $this->scenario->naturalIndex();
    $tables = [];
    foreach (self::TABLES as $table => $spec) {
      $rows = pg_fetch_all(pg_query($this->db, "SELECT * FROM $table" . (isset($spec['where']) ? " WHERE {$spec['where']}" : ''))) ?: [];
      $out = [];
      foreach ($rows as $row) {
        $key = NULL;
        if (isset($spec['type'])) {
          $parts = [$spec['type']];
          foreach ($spec['natural'] as $column) {
            $parts[] = $this->part($column, $row, $spec['refs']);
          }
          $natural = implode("\x1F", $parts);
          $key = $index[$natural] ?? '?' . implode('|', $parts);
        }
        elseif (isset($spec['derived'])) {
          $key = implode('@', array_map(fn (string $c): string => $this->resolve($spec['refs'][$c] ?? NULL, $row[$c]), $spec['derived']));
        }
        if (array_key_exists('id', $row)) {
          $this->keys[$table][(string) $row['id']] = (string) $key;
          $this->raw[$table][(string) $row['id']] = $row;
        }
        $canonical = [];
        foreach ($row as $column => $value) {
          $canonical[$column] = match (TRUE) {
            $column === 'id' => $key,
            $column === 'creador' => $this->user($value),
            isset($spec['refs'][$column]) => $this->resolve($spec['refs'][$column], $value),
            default => $value,
          };
        }
        ksort($canonical);
        $out[] = $canonical;
      }
      usort($out, static fn (array $a, array $b): int => strcmp(json_encode($a), json_encode($b)));
      $tables[$table] = $out;
    }
    return [
      'tables' => $tables,
      'owners' => $this->owners(),
      'other_tables' => $this->otherTables(),
      'runs' => $this->runs(),
    ];
  }

  /**
   * One natural key part of a row.
   */
  private function part(string $column, array $row, array $refs): string {
    if (str_starts_with($column, 'user:')) {
      return $this->user($row[substr($column, 5)]);
    }
    if (str_starts_with($column, 'ref:desglose.')) {
      $desglose = $this->raw['desglose'][(string) $row['desglose']] ?? NULL;
      $inner = substr($column, 13);
      return $desglose === NULL ? '?' : $this->resolve(self::TABLES['desglose']['refs'][$inner], $desglose[$inner]);
    }
    if (str_starts_with($column, 'ref:')) {
      $c = substr($column, 4);
      return $this->resolve($refs[$c], $row[$c]);
    }
    return (string) $row[$column];
  }

  /**
   * The key of a referenced row.
   */
  private function resolve(?string $table, ?string $id): string {
    if ($id === NULL) {
      return '(null)';
    }
    return $this->keys[$table][$id] ?? "?unknown:$id";
  }

  /**
   * The scenario user key of a legacy user id.
   */
  private function user(?string $id): string {
    return $this->users[$id] ?? "?user:$id";
  }

  /**
   * The number of distinct legacy users owning the rows of each user.
   *
   * The legacy application has one "creador" per person; this shows when
   * one person's rows are spread over several legacy users.
   *
   * @return array<string, int>
   *   Distinct "creador" values by scenario user key.
   */
  private function owners(): array {
    $owners = [];
    foreach (self::TABLES as $table => $spec) {
      if (($spec['natural'][0] ?? '') !== 'user:creador') {
        continue;
      }
      foreach (pg_fetch_all_columns(pg_query($this->db, "SELECT DISTINCT creador FROM $table"), 0) as $id) {
        $owners[$this->user($id)][$id] = TRUE;
      }
    }
    ksort($owners);
    return array_map('count', $owners);
  }

  /**
   * Row counts of every other table of the public and generador schemas.
   *
   * @return array<string, int>
   *   Counts by schema-qualified table name.
   */
  private function otherTables(): array {
    $compared = array_merge(array_keys(self::TABLES), ['actividad']);
    $tables = pg_fetch_all(pg_query($this->db, "SELECT schemaname || '.' || tablename AS t FROM pg_tables WHERE schemaname IN ('public', 'generador') ORDER BY 1")) ?: [];
    $counts = [];
    foreach ($tables as $row) {
      $name = $row['t'];
      if (in_array(preg_replace('/^public\./', '', $name), $compared, TRUE)) {
        continue;
      }
      $counts[$name] = (int) pg_fetch_result(pg_query($this->db, 'SELECT count(*) FROM ' . pg_escape_identifier($this->db, explode('.', $name)[0]) . '.' . pg_escape_identifier($this->db, explode('.', $name)[1])), 0, 0);
    }
    return $counts;
  }

  /**
   * The outcome of every scenario run, by run key.
   *
   * A placement line ends with "+" and the keys of the other resources it
   * uses, in the order of their rows, when it uses any (D9d).
   *
   * @return array<string, array<string, mixed>>
   *   metrics, digest, e2e_digest and placements.
   */
  private function runs(): array {
    $placements = pg_fetch_all(pg_query($this->db, 'SELECT * FROM actividad')) ?: [];
    $uses = [];
    foreach (pg_fetch_all(pg_query($this->db, 'SELECT otro_recurso, actividad FROM otros_recursos_usados ORDER BY ctid')) ?: [] as $use) {
      $uses[$use['actividad']][] = $this->resolve('otro_recurso', $use['otro_recurso']);
    }
    $ids = [];
    foreach ($this->keys as $table => $map) {
      $ids[$table] = array_flip($map);
    }
    $runs = [];
    foreach ($this->scenario->runs() as $run) {
      $lines = [];
      $e2e = [];
      foreach ($placements as $p) {
        $template = $this->keys['plantilla_de_actividad'][$p['plantilla']] ?? NULL;
        $record = $this->scenario->records[$template] ?? NULL;
        $subject = $this->scenario->records[$record['subject'] ?? ''] ?? NULL;
        if ($record === NULL || $record['period'] !== $run['period'] || $subject['specialty'] !== $run['specialty'] || $subject['level'] !== $run['level']) {
          continue;
        }
        $teacher = $p['profesor'] === NULL ? '(none)' : $this->resolve('profesor', $p['profesor']);
        $room = $p['lugar'] === NULL ? '(none)' : $this->resolve('lugar', $p['lugar']);
        $group = $this->resolve('grupo', $p['grupo']);
        $lines[] = implode('/', [$template, $group, $teacher, $room, $p['dia'], $p['turno'], $this->user($p['creador'])])
          . (isset($uses[$p['id']]) ? '+' . implode(',', $uses[$p['id']]) : '');
        $label = $this->scenario->records[$teacher]['label'] ?? $teacher;
        $e2e[$template . "\0" . $group] = implode('/', [$template, $group, $label, $room, $p['dia'], $p['turno']]);
      }
      sort($lines, SORT_STRING);
      ksort($e2e, SORT_STRING);
      $metrics = NULL;
      $scope = [
        $ids['especialidad'][$run['specialty']] ?? NULL,
        $ids['nivel'][$run['level']] ?? NULL,
        $ids['periodo'][$run['period']] ?? NULL,
      ];
      if (!in_array(NULL, $scope, TRUE)) {
        $metrics = preg_replace('/^METRIC /', '', (string) pg_fetch_result(pg_query_params($this->db, 'SELECT tst.metrics($1, $2, $3)', $scope), 0, 0));
      }
      $runs[$run['key']] = [
        'metrics' => $metrics,
        'digest' => md5(implode(',', $lines)),
        'e2e_digest' => md5(implode(',', $e2e)),
        'placements' => $lines,
      ];
    }
    return $runs;
  }

}
