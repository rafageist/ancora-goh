<?php

declare(strict_types=1);

namespace Ancora\Parity;

use PgSql\Connection;

/**
 * Drives the legacy application over HTTP.
 */
final class LegacyDriver {

  /**
   * Legacy id of every record entered so far, by scenario key.
   *
   * @var array<string, string>
   */
  private array $ids = [];

  /**
   * Cookie jar file of each logged-in user.
   *
   * @var array<string, string>
   */
  private array $jars = [];

  /**
   * The second of the last row entered into each table.
   *
   * @var array<string, int>
   */
  private array $lastSecond = [];

  /**
   * Generated ids of each table, in entry order.
   *
   * @var array<string, list<string>>
   */
  private array $created = [];

  /**
   * Number of failed steps.
   */
  public int $failures = 0;

  public function __construct(
    private readonly Scenario $scenario,
    private readonly string $base,
    private readonly Connection $db,
    private readonly array $credentials,
    private readonly string $out,
  ) {}

  /**
   * Runs the scenario.
   */
  public function run(): void {
    $this->users();
    foreach ($this->scenario->records as $key => $record) {
      $this->enter($key, $record);
    }
    foreach ($this->scenario->visibility() as $check) {
      $this->visibility($check);
    }
    $this->checkIdOrder();
    foreach ($this->scenario->runs() as $run) {
      $this->generate($run);
    }
    file_put_contents($this->out . '/legacy-ids.json', json_encode($this->ids, JSON_PRETTY_PRINT));
  }

  /**
   * Runs the characterization steps of the scenario (legacy only).
   *
   * They record handler behaviour that the data entry screens never show,
   * after the databases were compared (they change data):
   * - foreign_update: a user sends set_<Class> for a record of another
   *   creador; the generated handler updates by id, and its fdm hook sets
   *   "creador" to the session user (control/data/set/set_Profesor.php,
   *   control/data/fdms/fdm_Profesor_creador.php);
   * - foreign_delete: a user sends del_<Class> for a record of another
   *   creador; the handler deletes by id (control/data/del/del_Lugar.php),
   *   and the foreign keys cascade;
   * - explorer_list: a list event of the timetable explorer, which does not
   *   filter by creador (control/custom/custom_getui_ExploradorList*.php).
   *
   * Each step prints a CHAR line and fails when the legacy application does
   * not behave as the scenario records.
   *
   * @param array<string, string> $users
   *   Legacy user id => scenario user key.
   */
  public function characterize(array $users): void {
    $this->ids = json_decode((string) file_get_contents($this->out . '/legacy-ids.json'), TRUE, 8, JSON_THROW_ON_ERROR);
    $classes = [
      'teacher' => ['Profesor', 'profesor', 'nombre'],
      'room' => ['Lugar', 'lugar', 'nombre'],
    ];
    foreach ($this->scenario->data['characterize'] ?? [] as $step) {
      $tag = $step['ref'] ?? '';
      if ($step['kind'] === 'explorer_list') {
        $body = $this->event($step['as'], $step['event'], [], "{$step['event']} as {$step['as']}", TRUE);
        foreach ($step['shows'] as $ref) {
          $shown = str_contains($body, htmlspecialchars((string) $this->scenario->records[$ref]['label']));
          printf("CHAR  %s %s: %s %s %s\n", $tag, $step['event'], $step['as'], $shown ? 'sees' : 'does not see', $ref);
          if (!$shown) {
            $this->failures++;
          }
        }
        continue;
      }
      if ($step['kind'] === 'breakdown_post') {
        $this->characterizeBreakdownPost($step);
        continue;
      }
      if ($step['kind'] === 'event') {
        $this->characterizeEvent($step, $users);
        continue;
      }
      $record = $this->scenario->records[$step['record']];
      [$class, $table, $name] = $classes[$record['type']];
      $id = $this->ids[$step['record']];
      $what = "{$step['kind']} {$step['record']} as {$step['as']}";
      if ($step['kind'] === 'foreign_update') {
        $this->event($step['as'], "set_$class", [
          "field_{$class}_id" => $id,
          "field_{$class}_$name" => $step['label'],
        ], $what, TRUE);
        $row = pg_fetch_assoc(pg_query_params($this->db, "SELECT $name, creador FROM $table WHERE id = $1", [$id]));
        $observed = [$name => $row[$name] ?? NULL, 'creador' => $users[$row['creador'] ?? ''] ?? '?'];
      }
      else {
        $this->event($step['as'], "del_$class", ["field_{$class}_id" => $id], $what, TRUE);
        $observed = ['deleted' => $this->value("SELECT count(*) FROM $table WHERE id = $1", [$id]) === '0'];
      }
      $as_recorded = $observed == $step['legacy_expect'];
      printf("CHAR  %s %s (owner %s): %s, %s\n", $tag, $what, $this->scenario->ownerOf($step['record']), json_encode($observed), $as_recorded ? 'as recorded' : 'NOT as recorded');
      if (!$as_recorded) {
        $this->failures++;
      }
    }
  }

  /**
   * Enters the restricted cells of one object in one period.
   *
   * The period grid is the legacy editor: the browser opens
   * get_form_edit_Periodo_restricciones and posts the whole grid to
   * custom_set_Restricciones_Periodo ("1"/"0" per slot, "," between slots,
   * ";" between days: ancoragoh_client_forms_periodo.js
   * guardarRestricciones()). The other kinds had no editor (A-4): their
   * rows go in through the generated handlers add_RestriccionDe<Class>,
   * one per cell, with valor 1 and por_actividad false, the values of a
   * manual restriction.
   */
  private function restrictions(string $key, array $r): void {
    $as = $r['as'];
    $period = $this->scenario->records[$r['period']];
    $cells = [];
    foreach ($r['cells'] as [$day, $slot]) {
      $cells["$day:$slot"] = TRUE;
    }
    if ($r['kind'] === 'period') {
      $this->event($as, 'get_form_edit_Periodo_restricciones', ['id' => $this->ids[$r['period']]], "restriction form of {$r['period']}", TRUE);
      $days = [];
      for ($day = 1; $day <= $period['days']; $day++) {
        $slots = [];
        for ($slot = 1; $slot <= $period['slots_per_day']; $slot++) {
          $slots[] = isset($cells["$day:$slot"]) ? '1' : '0';
        }
        $days[] = implode(',', $slots);
      }
      $this->event($as, 'custom_set_Restricciones_Periodo', [
        'field_Periodo_id' => $this->ids[$r['period']],
        'field_Restricciones' => implode(';', $days),
      ], "custom_set_Restricciones_Periodo $key");
      $this->ids[$key] = $this->ids[$r['period']];
      return;
    }
    $classes = [
      'activity_type' => ['ClasifAct', 'clasificacion', FALSE],
      'subject' => ['Asignatura', 'asignatura', FALSE],
      'cohort' => ['Brigada', 'brigada', TRUE],
      'teacher' => ['Profesor', 'profesor', TRUE],
      'room' => ['Lugar', 'lugar', TRUE],
    ];
    [$class, $column, $occupancy] = $classes[$r['kind']];
    if (!isset($this->ids[$r['target']])) {
      $this->fail("$key: skipped, target {$r['target']} was not entered");
      return;
    }
    foreach ($r['cells'] as [$day, $slot]) {
      $fields = [
        "field_RestriccionDe{$class}_$column" => $this->ids[$r['target']],
        "field_RestriccionDe{$class}_periodo" => $this->ids[$r['period']],
        "field_RestriccionDe{$class}_dia" => $day,
        "field_RestriccionDe{$class}_turno" => $slot,
        "field_RestriccionDe{$class}_valor" => 1,
      ];
      if ($occupancy) {
        $fields["field_RestriccionDe{$class}_por_actividad"] = 'false';
      }
      $this->event($as, "add_RestriccionDe$class", $fields, "add_RestriccionDe$class $key $day.$slot", TRUE);
    }
    echo "ok    add_RestriccionDe$class $key (" . count($r['cells']) . " cells)\n";
    $this->ids[$key] = $this->ids[$r['target']];
  }

  /**
   * Posts set_Desglose with raw values and records the stored row.
   *
   * The legacy form posts the two day counts as free text and the
   * checkboxes as 1/0 (forms_desglose.js:10-19); set_Desglose.php calls one
   * setter per posted field in order, and each setter runs its own UPDATE
   * (model/data/Desglose.php, PartModel::sqlValue()). The step records the
   * HTTP status and the row afterwards. "as" may be another user than the
   * breakdown's owner: the handler updates by id.
   *
   * @param array<string, mixed> $step
   *   The step: record (a breakdown record), as, post (column => raw
   *   value), legacy_expect, ref.
   */
  private function characterizeBreakdownPost(array $step): void {
    $id = $this->ids[$step['record']];
    $row = pg_fetch_assoc(pg_query_params($this->db, 'SELECT asignatura, periodo FROM desglose WHERE id = $1', [$id]));
    $fields = [
      'app_event_set_Desglose' => 'yes',
      'field_Desglose_id' => $id,
      'field_Desglose_asignatura' => $row['asignatura'],
      'field_Desglose_periodo' => $row['periodo'],
    ];
    foreach ($step['post'] as $column => $value) {
      $fields["field_Desglose_$column"] = (string) $value;
    }
    $this->login($step['as']);
    [$status] = $this->rawPost($step['as'], $this->base . '?execute', $fields);
    $after = pg_fetch_assoc(pg_query_params($this->db, 'SELECT min_dias, max_dias, respetar_orden, mismo_dia FROM desglose WHERE id = $1', [$id]));
    $observed = ['http' => $status] + $after;
    $as_recorded = $observed == $step['legacy_expect'];
    $what = sprintf('set_Desglose %s as %s %s', $step['record'], $step['as'], json_encode($step['post']));
    printf("CHAR  %s %s (owner %s): %s, %s\n", $step['ref'] ?? '', $what, $this->scenario->ownerOf($step['record']), json_encode($observed), $as_recorded ? 'as recorded' : 'NOT as recorded');
    if (!$as_recorded) {
      $this->failures++;
    }
  }

  /**
   * Sends one legacy event and records the result of a query.
   *
   * A value "@KEY" in the fields or the query parameters is the legacy id of
   * scenario record KEY. The query result (rows of text values; legacy user
   * ids in a column "creador" become user keys) is compared with
   * legacy_expect, and the HTTP status is recorded as "http".
   *
   * @param array<string, mixed> $step
   *   The step: as, event, fields, query, params, legacy_expect, ref.
   * @param array<string, string> $users
   *   Legacy user id => scenario user key.
   */
  private function characterizeEvent(array $step, array $users): void {
    $id = fn (mixed $v): mixed => is_string($v) && str_starts_with($v, '@') ? ($this->ids[substr($v, 1)] ?? $v) : $v;
    $fields = ["app_event_{$step['event']}" => 'yes'] + array_map($id, $step['fields'] ?? []);
    $this->login($step['as']);
    [$status] = $this->rawPost($step['as'], $this->base . '?execute', $fields);
    $rows = pg_fetch_all(pg_query_params($this->db, $step['query'], array_map($id, $step['params'] ?? []))) ?: [];
    foreach ($rows as &$row) {
      if (isset($row['creador'])) {
        $row['creador'] = $users[$row['creador']] ?? '?';
      }
    }
    unset($row);
    $observed = ['http' => $status, 'rows' => $rows];
    $as_recorded = $observed == $step['legacy_expect'];
    printf("CHAR  %s %s as %s: %s, %s\n", $step['ref'] ?? '', $step['label'] ?? $step['event'], $step['as'], json_encode($observed), $as_recorded ? 'as recorded' : 'NOT as recorded');
    if (!$as_recorded) {
      $this->failures++;
    }
  }

  /**
   * Opens the edit form of a breakdown as the browser reaches it.
   *
   * The subject's breakdown browser (get_browser_DesgloseDetalles,
   * desglose_detalles.tpl:8) lists one link per period of the plan, which
   * calls showForEdit(<desglose id>); the form (get_form_edit_desglose,
   * form_desglose.tpl) carries the id, subject and period as hidden fields
   * and the current options.
   *
   * @return array<string, string>|null
   *   id, asignatura, periodo, min_dias, max_dias; NULL after a FAIL.
   */
  private function breakdownForm(string $key, array $r): ?array {
    $as = $r['as'];
    $browser = $this->event($as, 'get_browser_DesgloseDetalles', [
      'idasig' => $this->ids[$r['subject']],
      'idplanificacion' => $this->ids[$this->scenario->planOf($key)],
    ], "breakdown browser of {$r['subject']}", TRUE);
    preg_match_all("/forms\\.desglose\\.showForEdit\\('([^']+)'\\);[^>]*><img[^>]*>([^<]*)/", $browser, $links, PREG_SET_ORDER);
    $id = NULL;
    foreach ($links as [, $desglose, $label]) {
      if (trim($label) === $this->scenario->records[$r['period']]['label']) {
        $id = $desglose;
      }
    }
    if ($id === NULL) {
      $this->fail("$key: the breakdown browser has no link for period {$r['period']}");
      return NULL;
    }
    $html = $this->event($as, 'get_form_edit_desglose', ['id' => $id], "breakdown form of $key", TRUE);
    $form = ['id' => $id];
    $inputs = [
      'asignatura' => 'edtAsigDesglose',
      'periodo' => 'edtPeriodoDesglose',
      'min_dias' => 'edtMinDias',
      'max_dias' => 'edtMaxDias',
    ];
    foreach ($inputs as $field => $input) {
      if (!preg_match('/id="' . $input . '"\s+value\s*=\s*"([^"]*)"/', $html, $m)) {
        $this->fail("$key: the breakdown form has no $input");
        return NULL;
      }
      $form[$field] = trim($m[1]);
    }
    if ($form['asignatura'] !== $this->ids[$r['subject']] || $form['periodo'] !== $this->ids[$r['period']]) {
      $this->fail("$key: the breakdown form shows another subject or period");
      return NULL;
    }
    return $form;
  }

  /**
   * The set_Desglose fields the browser client posts.
   *
   * @param array<string, string> $form
   *   The values of the edit form (breakdownForm()).
   * @param array<string, string> $options
   *   The options as posted: min_dias, max_dias, respetar_orden, mismo_dia.
   *
   * @return array<string, string>
   *   The POST fields (ancoragoh.server.desgloses.actualizar).
   */
  private function breakdownPost(array $form, array $options): array {
    $fields = [
      'field_Desglose_id' => $form['id'],
      'field_Desglose_asignatura' => $form['asignatura'],
      'field_Desglose_periodo' => $form['periodo'],
    ];
    foreach ($options as $column => $value) {
      $fields["field_Desglose_$column"] = $value;
    }
    return $fields;
  }

  /**
   * POSTs form fields with the user's cookie jar; any HTTP status.
   *
   * @return array{0: int, 1: string}
   *   The HTTP status and the body.
   */
  private function rawPost(string $as, string $url, array $fields): array {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
      CURLOPT_POST => TRUE,
      CURLOPT_POSTFIELDS => http_build_query(array_map('strval', $fields)),
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_COOKIEFILE => $this->jars[$as],
      CURLOPT_COOKIEJAR => $this->jars[$as],
      CURLOPT_TIMEOUT => 30,
    ]);
    $body = curl_exec($curl);
    return [(int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE), is_string($body) ? $body : ''];
  }

  /**
   * Checks that the generated ids of every table sort in entry order.
   *
   * The generator, generar(), reads several tables in id order (for example
   * the groups of a template), so the legacy result depends on the lexical
   * order of the generated ids. A replica id is the table name, the time as
   * d/m/y h:i:s and time(), then random characters
   * (lib/basic/string/StringUtils.php, getReplica()), so rows entered in
   * different seconds sort in entry order, and rows entered in the same
   * second in random order. The driver enters
   * the rows of one table in different seconds (see add()); this check
   * catches the clock wrapping (12-hour "h", day of month first) during a
   * run, which would reorder them.
   */
  private function checkIdOrder(): void {
    foreach ($this->created as $table => $ids) {
      $sorted = $ids;
      sort($sorted, SORT_STRING);
      if ($sorted !== $ids) {
        $this->fail("the generated ids of $table do not sort in entry order (the clock wrapped); run the scenario again");
      }
    }
  }

  /**
   * Logs every user in and maps the legacy user ids to the user keys.
   *
   * The users and their role were created by scripts/parity-diff.sh: the
   * seeded administrator with bin/setpassword.php, the others with
   * bin/adduser.php, which assigns no role. The generated handler for roles
   * (control/data/add/add_RolDeUsuario.php) fails with a fatal error (the
   * class RolDeUsuario does not exist), so the harness inserts the role row
   * "superusuario" with SQL, the only seeded role with a permission.
   */
  private function users(): void {
    $map = [];
    foreach ($this->scenario->users as $key => $user) {
      $id = $this->value('SELECT id FROM seguridad.usuario WHERE nick = $1', [$this->credentials[$key]['nick']]);
      $map[$id] = $key;
      $this->login($key);
    }
    file_put_contents($this->out . '/legacy-users.json', json_encode($map, JSON_PRETTY_PRINT));
  }

  /**
   * Enters one record with the events the browser client sends.
   */
  private function enter(string $key, array $r): void {
    $as = $r['as'];
    foreach (Scenario::TYPES[$r['type']] as $field => $target) {
      foreach ((array) ($r[$field] ?? []) as $ref) {
        if (!isset($this->ids[$ref])) {
          $this->fail("{$r['type']} $key: skipped, $field $ref was not entered");
          return;
        }
      }
    }
    $id = fn (string $ref): string => $this->ids[$ref];
    switch ($r['type']) {
      case 'plan':
        // ancoragoh.server.planificaciones.add: dates as d/m/Y; the fdm hook
        // turns them into fecha_inicial and cant_dias. "activa" is not sent.
        $start = new \DateTimeImmutable($r['starts_on']);
        $end = $start->modify('+' . ($r['duration_days'] - 1) . ' days');
        $this->add($key, 'planificacion', $as, 'add_Planificacion', [
          'field_Planificacion_descripcion' => $r['label'],
          'field_Planificacion_fecha_inicial' => $start->format('d/m/Y'),
          'edtFechaFinal' => $end->format('d/m/Y'),
        ]);
        break;

      case 'period':
        // ancoragoh.server.periodos.add sends cant_dias as hora_inicial
        // (www/js/app/server/ancoragoh_server_periodos.js, quirk Q-20), and
        // the form gives the slot length in minutes (h*60+m,
        // view/custom/form_periodo.tpl; inventory A-1). Both kept as is.
        $this->add($key, 'periodo', $as, 'add_Periodo', [
          'field_Periodo_descripcion' => $r['label'],
          'field_Periodo_cant_dias' => $r['days'],
          'field_Periodo_cant_turnos' => $r['slots_per_day'],
          'field_Periodo_hora_inicial' => $r['days'],
          'field_Periodo_duracion_del_turno' => (int) round($r['slot_duration'] * 60),
          'field_Periodo_planificacion' => $id($r['plan']),
        ]);
        // updatePeriodicidades: one 1/0 flag per day of the plan, from day 1.
        $flags = array_fill(1, max($r['plan_days']), '0');
        foreach ($r['plan_days'] as $day) {
          $flags[$day] = '1';
        }
        $this->event($as, 'custom_set_Periodicidades', [
          'field_Periodo_id' => $this->ids[$key],
          'field_Periodicidades' => implode(',', $flags),
        ], "periodicidad of $key");
        break;

      case 'activity_type':
        $this->add($key, 'clasificacion_de_actividad', $as, 'add_ClasificacionDeActividad', [
          'field_ClasificacionDeActividad_descripcion' => $r['label'],
          'field_ClasificacionDeActividad_duracion' => $r['duration_slots'],
          'field_ClasificacionDeActividad_gasto' => $r['cost'],
          'field_ClasificacionDeActividad_prioridad' => $r['priority'],
        ]);
        break;

      case 'specialty':
        $this->add($key, 'especialidad', $as, 'add_Especialidad', ['field_Especialidad_descripcion' => $r['label']]);
        break;

      case 'level':
        $this->add($key, 'nivel', $as, 'add_Nivel', ['field_Nivel_descripcion' => $r['label']]);
        break;

      case 'cohort':
        $this->add($key, 'brigada', $as, 'add_Brigada', [
          'field_Brigada_descripcion' => $r['label'],
          'field_Brigada_matricula' => $r['enrollment'],
          'field_Brigada_especialidad' => $id($r['specialty']),
          'field_Brigada_nivel' => $id($r['level']),
        ]);
        break;

      case 'student_group':
        $this->add($key, 'grupo', $as, 'add_Grupo', [
          'field_Grupo_descripcion' => $r['label'],
          'field_Grupo_clasificacion' => $id($r['activity_type']),
          'field_Grupo_especialidad' => $id($r['specialty']),
          'field_Grupo_nivel' => $id($r['level']),
          'field_Grupo_planificacion' => $id($r['plan']),
        ]);
        // ancoragoh.server.grupos.moverBrigadaDeGrupo.
        foreach ($r['cohorts'] as $cohort) {
          $this->event($as, 'custom_MoverBrigadaDeGrupo', ['brigada' => $id($cohort), 'grupo' => $this->ids[$key]], "cohort $cohort into $key");
        }
        break;

      case 'subject':
        $this->add($key, 'asignatura', $as, 'add_Asignatura', [
          'field_Asignatura_descripcion' => $r['label'],
          'field_Asignatura_especialidad' => $id($r['specialty']),
          'field_Asignatura_nivel' => $id($r['level']),
        ]);
        break;

      case 'activity_template':
        // The breakdown row (desglose) was created by the legacy triggers.
        $desglose = $this->value('SELECT id FROM desglose WHERE asignatura = $1 AND periodo = $2', [
          $id($r['subject']),
          $id($r['period']),
        ]);
        if ($desglose === NULL) {
          $this->fail("$key: no desglose row for its subject and period");
          return;
        }
        $this->add($key, 'plantilla_de_actividad', $as, 'add_PlantillaDeActividad', [
          'field_PlantillaDeActividad_desglose' => $desglose,
          'field_PlantillaDeActividad_orden' => $r['sequence'],
          'field_PlantillaDeActividad_exige_profesor' => ($r['requires_teacher'] ?? TRUE) ? 'true' : 'false',
          'field_PlantillaDeActividad_exige_lugar' => ($r['requires_room'] ?? TRUE) ? 'true' : 'false',
          'field_PlantillaDeActividad_clasificacion' => $id($r['activity_type']),
        ]);
        break;

      case 'teacher':
        $this->add($key, 'profesor', $as, 'add_Profesor', ['field_Profesor_nombre' => $r['label']]);
        break;

      case 'teacher_assignment':
        // ancoragoh.server.recursos.agregarProfesorParaActividad (priority 1),
        // actualizaPrioridadPxact, then actualizarProfesorParaGrupo per group.
        $this->add($key, 'profesor_para_actividad', $as, 'add_ProfesorParaActividad', [
          'field_ProfesorParaActividad_profesor' => $id($r['teacher']),
          'field_ProfesorParaActividad_plantilla' => $id($r['template']),
          'field_ProfesorParaActividad_prioridad' => 1,
        ]);
        if (($r['priority'] ?? 1) !== 1) {
          $this->event($as, 'set_prioridad_pxact', ['pxact' => $this->ids[$key], 'prioridad' => $r['priority']], "priority of $key");
        }
        foreach ($r['groups'] as $group) {
          $this->event($as, 'custom_ActualizaProfesorParaGrupo', ['pxact' => $this->ids[$key], 'grupo' => $id($group)], "group $group of $key");
        }
        break;

      case 'room':
        $this->add($key, 'lugar', $as, 'add_Lugar', [
          'field_Lugar_nombre' => $r['label'],
          'field_Lugar_capacidad' => $r['capacity'],
        ]);
        break;

      case 'breakdown':
        // The breakdown row was created by the legacy triggers; the user
        // opens it from the subject's breakdown browser and saves the form
        // (forms_desglose.js modificar(), ancoragoh_server_desgloses.js).
        $form = $this->breakdownForm($key, $r);
        if ($form === NULL) {
          return;
        }
        $this->event($as, 'set_Desglose', $this->breakdownPost($form, [
          'min_dias' => (string) $r['min_days'],
          'max_dias' => (string) $r['max_days'],
          'respetar_orden' => $r['respect_order'] ? '1' : '0',
          'mismo_dia' => $r['same_day'] ? '1' : '0',
        ]), "set_Desglose $key");
        $this->ids[$key] = $form['id'];
        break;

      case 'time_restriction':
        $this->restrictions($key, $r);
        break;

      case 'room_assignment':
        // agregarLugarParaActividad (priority 1), then actualizaPrioridadLxact.
        $fields = [
          'field_LugarParaActividad_lugar' => $id($r['room']),
          'field_LugarParaActividad_plantilla' => $id($r['template']),
        ];
        $this->event($as, 'add_LugarParaActividad', $fields + [
          'field_LugarParaActividad_prioridad' => 1,
        ], "add $key");
        if (($r['priority'] ?? 1) !== 1) {
          $this->event($as, 'set_LugarParaActividad', $fields + ['field_LugarParaActividad_prioridad' => $r['priority']], "priority of $key");
        }
        break;
    }
  }

  /**
   * Sends an add event and records the id of the one new row.
   */
  private function add(string $key, string $table, string $as, string $event, array $fields): void {
    // One row per table per second: replica ids then sort in entry order,
    // as when a person enters them (see checkIdOrder()).
    $last = $this->lastSecond[$table] ?? 0;
    while (time() <= $last) {
      usleep(50000);
    }
    $before = $this->column("SELECT id FROM $table");
    $this->event($as, $event, $fields, "$event $key");
    $new = array_values(array_diff($this->column("SELECT id FROM $table"), $before));
    if (count($new) !== 1) {
      $this->fail("$event $key: expected one new $table row, found " . count($new));
      return;
    }
    $this->ids[$key] = $new[0];
    $this->created[$table][] = $new[0];
    $this->lastSecond[$table] = time();
  }

  /**
   * Runs one visibility check through the legacy list events.
   *
   * Each type is checked with the list the data entry screens use; every
   * one of them filters by "creador = $_SESSION['user']".
   */
  private function visibility(array $check): void {
    $events = [
      'plan' => ['get_minibrowser_planificaciones', []],
      'activity_type' => ['get_minibrowser_clasifact', []],
      'specialty' => ['get_minibrowser_especialidades', []],
      'level' => ['get_minibrowser_niveles', []],
      'subject' => ['get_minibrowser_asignaturas', []],
      'teacher' => ['get_minibrowser_profesores', []],
      'room' => ['get_minibrowser_lugares', []],
    ];
    [$event, $fields] = $events[$check['type']];
    $body = $this->event($check['as'], $event, $fields, "list {$check['type']} as {$check['as']}", TRUE);
    foreach (['visible' => TRUE, 'hidden' => FALSE] as $list => $expected) {
      foreach ($check[$list] ?? [] as $ref) {
        $label = htmlspecialchars((string) $this->scenario->records[$ref]['label']);
        $shown = str_contains($body, $label);
        printf("VIS   %s %s %s %s: %s\n", $check['as'], $check['type'], $ref, $shown ? 'listed' : 'not listed', $shown === $expected ? 'as expected' : 'UNEXPECTED');
        if ($shown !== $expected) {
          $this->failures++;
        }
      }
    }
  }

  /**
   * Runs generar() through control/custom/generar.php.
   */
  private function generate(array $run): void {
    $o = $run['options'];
    $bool = static fn (bool $v): string => $v ? 'true' : 'false';
    $this->event($run['as'], 'generar', [
      'esp' => $this->ids[$run['specialty']],
      'nivel' => $this->ids[$run['level']],
      'periodo' => $this->ids[$run['period']],
      'forma' => $o['forma'],
      'parche' => $bool($o['parche']),
      'compactar' => $o['compactar'],
      'parche1' => $bool($o['parche1']),
      'buscalugar' => $bool($o['buscalugar']),
    ], "generar {$run['key']}", FALSE, 600);
  }

  /**
   * Logs a user in (www/index.php login form).
   */
  private function login(string $key): void {
    if (isset($this->jars[$key])) {
      return;
    }
    $this->jars[$key] = $this->out . "/legacy-$key.jar";
    $body = $this->post($key, $this->base, [
      'btnLogin' => 'Entrar',
      'edtUser' => $this->credentials[$key]['nick'],
      'edtPass' => $this->credentials[$key]['password'],
    ], 30);
    if (str_contains($body, 'access_denied') || str_contains($body, 'name="edtUser"')) {
      $this->fail("login of $key");
    }
  }

  /**
   * Sends one event as a user; returns the response body.
   */
  private function event(string $as, string $event, array $fields, string $what, bool $quiet = FALSE, int $timeout = 30): string {
    $this->login($as);
    $body = $this->post($as, $this->base . '?execute', ["app_event_$event" => 'yes'] + $fields, $timeout);
    if (str_contains($body, 'access_denied') || str_contains($body, 'message-session-expired')) {
      $this->fail("$what (refused)");
    }
    elseif (!$quiet) {
      echo "ok    $what\n";
    }
    return $body;
  }

  /**
   * POSTs form fields with the user's cookie jar.
   */
  private function post(string $as, string $url, array $fields, int $timeout): string {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
      CURLOPT_POST => TRUE,
      CURLOPT_POSTFIELDS => http_build_query(array_map('strval', $fields)),
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_COOKIEFILE => $this->jars[$as],
      CURLOPT_COOKIEJAR => $this->jars[$as],
      CURLOPT_TIMEOUT => $timeout,
    ]);
    $body = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    if (!is_string($body) || $status !== 200) {
      $this->fail("POST $url: HTTP $status");
      return '';
    }
    return $body;
  }

  /**
   * One value of a query, or NULL.
   */
  private function value(string $sql, array $params = []): ?string {
    $result = pg_query_params($this->db, $sql, $params);
    $row = pg_fetch_row($result);
    return $row === FALSE ? NULL : $row[0];
  }

  /**
   * The first column of a query.
   *
   * @return list<string>
   *   The values.
   */
  private function column(string $sql): array {
    return pg_fetch_all_columns(pg_query($this->db, $sql), 0);
  }

  /**
   * Reports a failed step.
   */
  private function fail(string $what): void {
    $this->failures++;
    echo "FAIL  $what\n";
  }

}
