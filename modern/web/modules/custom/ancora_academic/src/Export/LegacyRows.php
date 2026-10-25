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
 * Legacy identifiers are derived from the codes, optionally prefixed:
 * - plan, period, activity type, specialty, level, cohort, group, subject,
 *   room, teacher: the code;
 * - activity template: subject code + sequence ("A1"), with "@" + period code
 *   appended when the export has more than one period;
 * - subject breakdown (desglose): subject code + "@" + period code;
 * - period day (periodicidad): period code + "@" + day;
 * - teacher assignment: template ID + "@" + teacher code.
 *
 * The legacy schema creates a desglose row for every subject and period by
 * trigger (nuevo_desglose_cuando_nueva_asignatura / _nuevo_periodo). The rows
 * here contain those desglose rows explicitly, with the legacy defaults; a
 * loader that keeps the triggers must reuse the trigger rows instead.
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
   * Builds the legacy rows of an export.
   *
   * @param array<string, mixed> $export
   *   The result of AcademicExport::export().
   * @param string $creator
   *   The legacy user ID that owns the records ("creador").
   * @param string $prefix
   *   A prefix for every legacy ID, to keep several plans apart.
   *
   * @return array<string, list<array<string, scalar|null>>>
   *   Rows keyed by legacy table name, in foreign key order.
   */
  public function build(array $export, string $creator = 'admin-superuser', string $prefix = ''): array {
    if (($export['schema'] ?? NULL) !== AcademicExport::SCHEMA) {
      throw new \InvalidArgumentException('Unsupported export schema.');
    }
    $id = static fn (?string $code): ?string => $code === NULL ? NULL : $prefix . $code;
    $codes = [];
    $coded = [
      'periods', 'activity_types', 'specialties', 'levels', 'cohorts',
      'student_groups', 'subjects', 'rooms', 'teachers',
    ];
    foreach ($coded as $list) {
      foreach ($export[$list] as $record) {
        $codes[$record['key']] = $record['code'];
      }
    }
    $code = static fn (?string $key): ?string => $key === NULL ? NULL : $id($codes[$key] ?? NULL);
    $plan = $export['plan'];
    $plan_id = $id($plan['code']);
    $several_periods = count($export['periods']) > 1;

    $rows = array_fill_keys([
      'planificacion', 'periodo', 'periodicidad', 'clasificacion_de_actividad',
      'especialidad', 'nivel', 'brigada', 'grupo', 'brigada_de_grupo',
      'asignatura', 'desglose', 'plantilla_de_actividad', 'profesor',
      'profesor_para_actividad', 'profesor_para_grupo', 'lugar',
      'lugar_para_actividad',
    ], []);

    $rows['planificacion'][] = [
      'id' => $plan_id,
      'descripcion' => $plan['label'],
      'fecha_inicial' => $plan['starts_on'] === NULL ? 0 : self::legacyDayNumber($plan['starts_on']),
      'cant_dias' => $plan['duration_days'],
      'activa' => $plan['active'],
      'creador' => $creator,
    ];
    foreach ($export['periods'] as $period) {
      $rows['periodo'][] = [
        'id' => $id($period['code']),
        'descripcion' => $period['label'],
        'cant_dias' => $period['days'],
        'cant_turnos' => $period['slots_per_day'],
        'hora_inicial' => $period['start_hour'],
        'duracion_del_turno' => $period['slot_duration'],
        'planificacion' => $plan_id,
      ];
      foreach ($period['plan_days'] as $day) {
        $rows['periodicidad'][] = [
          'id' => $id($period['code'] . '@' . $day),
          'periodo' => $id($period['code']),
          'dia' => $day,
        ];
      }
    }
    foreach ($export['activity_types'] as $type) {
      $rows['clasificacion_de_actividad'][] = [
        'id' => $id($type['code']),
        'descripcion' => $type['label'],
        'duracion' => $type['duration_slots'],
        'creador' => $creator,
        'prioridad' => $type['priority'],
        'gasto' => $type['cost'],
      ];
    }
    foreach (['specialties' => 'especialidad', 'levels' => 'nivel'] as $list => $table) {
      foreach ($export[$list] as $record) {
        $rows[$table][] = ['id' => $id($record['code']), 'descripcion' => $record['label'], 'creador' => $creator];
      }
    }
    foreach ($export['cohorts'] as $cohort) {
      $rows['brigada'][] = [
        'id' => $id($cohort['code']),
        'descripcion' => $cohort['label'],
        'especialidad' => $code($cohort['specialty']),
        'nivel' => $code($cohort['level']),
        'matricula' => $cohort['enrollment'],
        'creador' => $creator,
      ];
    }
    foreach ($export['student_groups'] as $group) {
      $rows['grupo'][] = [
        'id' => $id($group['code']),
        'descripcion' => $group['label'],
        'clasificacion' => $code($group['activity_type']),
        'especialidad' => $code($group['specialty']),
        'planificacion' => $plan_id,
        'nivel' => $code($group['level']),
      ];
      foreach ($group['cohorts'] as $cohort_key) {
        $rows['brigada_de_grupo'][] = ['brigada' => $code($cohort_key), 'grupo' => $id($group['code'])];
      }
    }
    foreach ($export['subjects'] as $subject) {
      $rows['asignatura'][] = [
        'id' => $id($subject['code']),
        'descripcion' => $subject['label'],
        'especialidad' => $code($subject['specialty']),
        'nivel' => $code($subject['level']),
        'creador' => $creator,
      ];
      foreach ($export['periods'] as $period) {
        $rows['desglose'][] = [
          'id' => $id($subject['code'] . '@' . $period['code']),
          'asignatura' => $id($subject['code']),
          'periodo' => $id($period['code']),
        ] + self::BREAKDOWN_DEFAULTS;
      }
    }

    $template_ids = [];
    foreach ($export['activity_templates'] as $template) {
      $template_id = $id($template['subject_code'] . $template['sequence'] . ($several_periods ? '@' . $template['period_code'] : ''));
      $template_ids[$template['key']] = $template_id;
      $rows['plantilla_de_actividad'][] = [
        'id' => $template_id,
        'desglose' => $id($template['subject_code'] . '@' . $template['period_code']),
        'orden' => $template['sequence'],
        'clasificacion' => $code($template['activity_type']),
        'exige_profesor' => $template['requires_teacher'],
        'exige_lugar' => $template['requires_room'],
      ];
    }
    foreach ($export['teachers'] as $teacher) {
      $rows['profesor'][] = ['id' => $id($teacher['code']), 'nombre' => $teacher['label'], 'creador' => $creator];
    }
    foreach ($export['teacher_assignments'] as $assignment) {
      $assignment_id = $template_ids[$assignment['template']] . '@' . $assignment['teacher_code'];
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
        'id' => $id($room['code']),
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
