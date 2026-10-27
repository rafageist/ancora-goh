<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Export;

/**
 * Translates an AcademicExport into rows of the legacy engine tables.
 *
 * Pure PHP: no database access. The result maps each legacy table name to a
 * list of rows (column => value), in an order that satisfies the foreign
 * keys. It reproduces the semantics of dev/e2e/fixture.sql: for the fixture
 * dataset, the rows equal the fixture rows (see FixtureExportTest). Writing
 * them to an engine database is the engine sync's job (#71), not this
 * module's.
 *
 * Legacy identifiers are derived from the codes:
 * - plan, period, activity type, specialty, level, cohort, group, subject,
 *   room, teacher: the code;
 * - activity template: subject code + sequence ("A1"), with "@" + period code
 *   appended when the export has more than one period;
 * - subject breakdown (desglose): subject id + "@" + period id;
 * - period day (periodicidad): period id + "@" + day;
 * - teacher assignment: template code + "@" + teacher code.
 *
 * With $numbered (the engine sync), every id that is not derived from
 * other ids is prefixed with the record's entity id, zero-padded to ten
 * digits, and ":" (engineId()): "0000000012:G1". Legacy ids are generated
 * replicas (getReplica(): table, time, random characters; inventory
 * PAR-110). They are opaque, unique in the database, and sort in entry
 * order, and generar() reads some tables in id order: its result depends on
 * that order (docs/modernization/legacy-parity-harness.md). Entity ids grow
 * in creation order and are unique per type on the site, so numbered ids
 * keep the legacy order and never collide between users or plans; the code
 * after ":" keeps them readable. Without $numbered the ids are the codes,
 * which reproduces dev/e2e/fixture.sql exactly (FixtureExportTest).
 *
 * An export without a plan (AcademicExport::catalogue()) gives only the
 * catalogue rows of its owner.
 *
 * The legacy schema creates a desglose row for every subject and period by
 * trigger (nuevo_desglose_cuando_nueva_asignatura / _nuevo_periodo). The rows
 * here contain those desglose rows explicitly, with the options of the
 * stored breakdown (export "breakdowns", D4) or the legacy defaults when the
 * subject and period have none; a loader that keeps the triggers must reuse
 * the trigger rows instead (LegacyTableSync updates their options).
 */
final class LegacyRows {

  /**
   * Legacy defaults of a desglose row (dev/sql/actividad/desglose.sql).
   */
  public const BREAKDOWN_DEFAULTS = [
    'min_dias' => 1,
    'max_dias' => 5,
    'respetar_orden' => TRUE,
    'mismo_dia' => FALSE,
  ];

  /**
   * The separator between the entity id and the code of a numbered id.
   */
  public const ID_SEPARATOR = ':';

  /**
   * The engine id of a record: its entity id, zero-padded, ":" and its code.
   */
  public static function engineId(int $entity_id, string $code): string {
    return sprintf('%010d', $entity_id) . self::ID_SEPARATOR . $code;
  }

  /**
   * The legacy tables, in foreign key order.
   */
  public const TABLES = [
    'planificacion', 'periodo', 'periodicidad', 'clasificacion_de_actividad',
    'especialidad', 'nivel', 'brigada', 'grupo', 'brigada_de_grupo',
    'asignatura', 'desglose', 'plantilla_de_actividad', 'profesor',
    'profesor_para_actividad', 'profesor_para_grupo', 'lugar',
    'lugar_para_actividad', 'restriccion_de_periodo',
    'restriccion_de_clasif_act', 'restriccion_de_asignatura',
    'restriccion_de_brigada', 'restriccion_de_profesor',
    'restriccion_de_lugar', 'receso', 'zpriori', 'higiene_para_profesor',
    'higiene_para_brigada',
  ];

  /**
   * Time restriction kind => legacy table and object column (D9a).
   */
  public const RESTRICTION_TABLES = [
    'period' => ['restriccion_de_periodo', NULL],
    'activity_type' => ['restriccion_de_clasif_act', 'clasificacion'],
    'subject' => ['restriccion_de_asignatura', 'asignatura'],
    'cohort' => ['restriccion_de_brigada', 'brigada'],
    'teacher' => ['restriccion_de_profesor', 'profesor'],
    'room' => ['restriccion_de_lugar', 'lugar'],
  ];

  /**
   * The tables whose rows also carry por_actividad and actividad.
   */
  private const OCCUPANCY_TABLES = ['restriccion_de_brigada', 'restriccion_de_profesor', 'restriccion_de_lugar'];

  /**
   * Builds the legacy rows of an export.
   *
   * @param array<string, mixed> $export
   *   The result of AcademicExport::export() or ::catalogue().
   * @param string $creator
   *   The legacy user ID of the export's owner ("creador").
   * @param bool $numbered
   *   Number the ids with the entity ids (engineId()), as the engine sync
   *   does; otherwise the ids are the codes.
   *
   * @return array<string, list<array<string, scalar|null>>>
   *   Rows keyed by legacy table name, in foreign key order.
   */
  public function build(array $export, string $creator = 'admin-superuser', bool $numbered = FALSE): array {
    if (($export['schema'] ?? NULL) !== AcademicExport::SCHEMA) {
      throw new \InvalidArgumentException('Unsupported export schema.');
    }
    $make = static fn (array $record, string $code): string => $numbered ? self::engineId($record['id'], $code) : $code;
    $ids = [];
    $coded = [
      'periods', 'activity_types', 'specialties', 'levels', 'cohorts',
      'student_groups', 'subjects', 'rooms', 'teachers',
    ];
    foreach ($coded as $list) {
      foreach ($export[$list] as $record) {
        $ids[$record['key']] = $make($record, $record['code']);
      }
    }
    $code = static fn (?string $key): ?string => $key === NULL ? NULL : ($ids[$key] ?? NULL);
    $plan = $export['plan'];
    $several_periods = count($export['periods']) > 1;

    $rows = array_fill_keys(self::TABLES, []);
    $options = [];
    foreach ($export['breakdowns'] as $breakdown) {
      $options[$breakdown['subject'] . "\x1F" . $breakdown['period']] = array_intersect_key($breakdown, self::BREAKDOWN_DEFAULTS);
    }

    $plan_id = NULL;
    if ($plan !== NULL) {
      $plan_id = $make($plan, $plan['code']);
      $rows['planificacion'][] = [
        'id' => $plan_id,
        'descripcion' => $plan['label'],
        'fecha_inicial' => $plan['starts_on'] === NULL ? 0 : self::legacyDayNumber($plan['starts_on']),
        'cant_dias' => $plan['duration_days'],
        'activa' => $plan['active'],
        'creador' => $creator,
      ];
    }
    foreach ($export['periods'] as $period) {
      $rows['periodo'][] = [
        'id' => $code($period['key']),
        'descripcion' => $period['label'],
        'cant_dias' => $period['days'],
        'cant_turnos' => $period['slots_per_day'],
        'hora_inicial' => $period['start_hour'],
        'duracion_del_turno' => $period['slot_duration'],
        'planificacion' => $plan_id,
      ];
      foreach ($period['plan_days'] as $day) {
        $rows['periodicidad'][] = [
          'id' => $code($period['key']) . '@' . $day,
          'periodo' => $code($period['key']),
          'dia' => $day,
        ];
      }
    }
    foreach ($export['activity_types'] as $type) {
      $rows['clasificacion_de_actividad'][] = [
        'id' => $code($type['key']),
        'descripcion' => $type['label'],
        'duracion' => $type['duration_slots'],
        'creador' => $creator,
        'prioridad' => $type['priority'],
        'gasto' => $type['cost'],
      ];
    }
    foreach (['specialties' => 'especialidad', 'levels' => 'nivel'] as $list => $table) {
      foreach ($export[$list] as $record) {
        $rows[$table][] = ['id' => $code($record['key']), 'descripcion' => $record['label'], 'creador' => $creator];
      }
    }
    foreach ($export['cohorts'] as $cohort) {
      $rows['brigada'][] = [
        'id' => $code($cohort['key']),
        'descripcion' => $cohort['label'],
        'especialidad' => $code($cohort['specialty']),
        'nivel' => $code($cohort['level']),
        'matricula' => $cohort['enrollment'],
        'creador' => $creator,
      ];
    }
    foreach ($export['student_groups'] as $group) {
      $rows['grupo'][] = [
        'id' => $code($group['key']),
        'descripcion' => $group['label'],
        'clasificacion' => $code($group['activity_type']),
        'especialidad' => $code($group['specialty']),
        'planificacion' => $plan_id,
        'nivel' => $code($group['level']),
      ];
      foreach ($group['cohorts'] as $cohort_key) {
        $rows['brigada_de_grupo'][] = ['brigada' => $code($cohort_key), 'grupo' => $code($group['key'])];
      }
    }
    foreach ($export['subjects'] as $subject) {
      $rows['asignatura'][] = [
        'id' => $code($subject['key']),
        'descripcion' => $subject['label'],
        'especialidad' => $code($subject['specialty']),
        'nivel' => $code($subject['level']),
        'creador' => $creator,
      ];
      foreach ($export['periods'] as $period) {
        $rows['desglose'][] = [
          'id' => $code($subject['key']) . '@' . $code($period['key']),
          'asignatura' => $code($subject['key']),
          'periodo' => $code($period['key']),
        ] + ($options[$subject['key'] . "\x1F" . $period['key']] ?? []) + self::BREAKDOWN_DEFAULTS;
      }
    }

    $template_ids = [];
    $template_codes = [];
    foreach ($export['activity_templates'] as $template) {
      $template_code = $template['subject_code'] . $template['sequence'] . ($several_periods ? '@' . $template['period_code'] : '');
      $template_codes[$template['key']] = $template_code;
      $template_ids[$template['key']] = $make($template, $template_code);
      $rows['plantilla_de_actividad'][] = [
        'id' => $template_ids[$template['key']],
        'desglose' => $code($template['subject']) . '@' . $code($template['period']),
        'orden' => $template['sequence'],
        'clasificacion' => $code($template['activity_type']),
        'exige_profesor' => $template['requires_teacher'],
        'exige_lugar' => $template['requires_room'],
      ];
    }
    foreach ($export['teachers'] as $teacher) {
      $rows['profesor'][] = ['id' => $code($teacher['key']), 'nombre' => $teacher['label'], 'creador' => $creator];
    }
    foreach ($export['teacher_assignments'] as $assignment) {
      $assignment_id = $make($assignment, $template_codes[$assignment['template']] . '@' . $assignment['teacher_code']);
      $rows['profesor_para_actividad'][] = [
        'id' => $assignment_id,
        'plantilla' => $template_ids[$assignment['template']],
        'profesor' => $code($assignment['teacher']),
        'prioridad' => $assignment['priority'],
      ];
      foreach ($assignment['groups'] as $group_key) {
        $rows['profesor_para_grupo'][] = ['profesor_para_actividad' => $assignment_id, 'grupo' => $code($group_key)];
      }
    }
    foreach ($export['rooms'] as $room) {
      $rows['lugar'][] = [
        'id' => $code($room['key']),
        'nombre' => $room['label'],
        'capacidad' => $room['capacity'],
        'creador' => $creator,
      ];
    }
    foreach ($export['room_assignments'] as $assignment) {
      $rows['lugar_para_actividad'][] = [
        'plantilla' => $template_ids[$assignment['template']],
        'lugar' => $code($assignment['room']),
        'prioridad' => $assignment['priority'],
      ];
    }
    // Manual restriction rows (valor 1, as the legacy editor stores it; the
    // occupancy rows of placements are the engine's, never exported).
    foreach ($export['time_restrictions'] as $restriction) {
      [$table, $column] = self::RESTRICTION_TABLES[$restriction['kind']];
      $row = ['id' => $make($restriction, 'r' . $restriction['id'])];
      if ($column !== NULL) {
        $row[$column] = $code($restriction['target']);
      }
      $row += [
        'periodo' => $code($restriction['period']),
        'dia' => $restriction['day'],
        'turno' => $restriction['slot'],
        'valor' => 1,
      ];
      if (in_array($table, self::OCCUPANCY_TABLES, TRUE)) {
        $row += ['por_actividad' => FALSE, 'actividad' => NULL];
      }
      $rows[$table][] = $row;
    }
    // D9b: breaks, priority zones (only zones other than 1 exist), daily
    // load limits.
    foreach ($export['breaks'] as $break) {
      $rows['receso'][] = [
        'periodo' => $code($break['period']),
        'despues_del_turno' => $break['after_slot'],
        'duracion' => $break['length'],
      ];
    }
    foreach ($export['priority_zones'] as $zone) {
      $rows['zpriori'][] = [
        'clasificacion' => $code($zone['activity_type']),
        'periodo' => $code($zone['period']),
        'dia' => $zone['day'],
        'turno' => $zone['slot'],
        'valor' => $zone['zone'],
      ];
    }
    foreach ($export['load_limits'] as $limit) {
      $teacher = $limit['kind'] === 'teacher';
      $rows[$teacher ? 'higiene_para_profesor' : 'higiene_para_brigada'][] = [
        'periodo' => $code($limit['period']),
        ($teacher ? 'profesor' : 'brigada') => $code($limit['target']),
        'gasto_maximo_diario' => $limit['max_daily_load'],
      ];
    }
    return $rows;
  }

  /**
   * Converts a Y-m-d date to the legacy consecutive day number.
   *
   * A port of tiempo.date_to_number (dev/sql/tiempo/date_to_number.sql),
   * including its quirks, so that plan start days compare as in the legacy
   * engine (dias_se_solapan, turnos_se_solapan).
   */
  public static function legacyDayNumber(string $date): int {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
      throw new \InvalidArgumentException(sprintf('Not a Y-m-d date: "%s".', $date));
    }
    [, $year, $month, $day] = array_map('intval', $matches);
    $leap = ($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0;
    $month_starts = [1, 32, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335];
    $day_of_year = $month_starts[$month - 1] + $day - 1 + ($leap && $month > 2 ? 1 : 0);
    return ($year - 2000) * 365 + $day_of_year + intdiv($year - 2000, 4) + ($leap ? 0 : 1);
  }

}
