<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_academic\Entity\Level;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Specialty;
use Drupal\ancora_engine\Engine\CheckRunnerInterface;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\Engine\WorkspaceClient;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\WorkspaceSync;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use PgSql\Connection as PgConnection;
use Psr\Log\LoggerInterface;

/**
 * Runs the destination checks of placement moves in workspaces (#130).
 *
 * Registered as "ancora_engine.check_runner"; the worker calls it at the
 * start of each cycle, workspace mode only. For one queued check:
 *
 * 1. a workspace is opened under a key of its own range
 *    (CHECK_KEY_BASE + check id, attempt 1: generation job ids never reach
 *    it), and the whole site is loaded with the unchanged WorkspaceSync,
 *    WITHOUT the closure prune (loadForCheck()): every activity of the
 *    period, of every specialty and level, books its cells through the
 *    legacy RULEs;
 * 2. in one transaction that is rolled back, the placement's row is
 *    deleted (its occupancy goes, ON DELETE CASCADE) and every cell of the
 *    period is evaluated with the engine's own restriction functions, the
 *    check of characterization case 150: for every slot of the duration,
 *    inside the period, no restriction or booking of the period, the
 *    activity type, the subject, the group's cohorts, its teacher, its room
 *    and its other resources (valor_restriccion_*). posible_inicio() is not
 *    used: it never reads a template's only teacher or room (6.2). The
 *    daily load limits (es_higienico_*) and the subject's day range
 *    (rango(), when the breakdown respects the order) are warnings: the
 *    database does not refuse them, generar() avoids them;
 * 3. the workspace is dropped; the grid is stored with the site input
 *    version the workspace was loaded from.
 *
 * A check writes only its own row. It is not a generation job, takes no
 * generation lock and is never blocked by a queued generation job; the
 * commit refuses it when the input changed or a job is queued or running.
 *
 * What a check cannot see: other plans whose dates overlap. The legacy
 * restriction rows are per period, so a teacher or room shared with another
 * plan's period on the same dates is not seen (case 150). The UI says so.
 */
final class PlacementCheckRunner implements CheckRunnerInterface {

  /**
   * Workspace keys of checks: this + the check id (12 digits at most).
   */
  public const CHECK_KEY_BASE = 900000000000;

  /**
   * Seconds after which a running check is considered lost.
   */
  public const LOST_AFTER = 120;

  /**
   * The statement timeout of a check, in seconds.
   */
  public const TIME_LIMIT = 60;

  /**
   * The grid: one row per cell of the period, with the reasons and warnings.
   */
  private const GRID = <<<'SQL'
    SELECT c.d AS day, u.t AS slot,
      (u.t + i.dur - 1 > i.slots) AS overflow,
      EXISTS (SELECT 1 FROM generate_series(0, i.dur - 1) s WHERE u.t + s <= i.slots AND valor_restriccion_periodo(i.per, c.d, u.t + s) > 0) AS period,
      EXISTS (SELECT 1 FROM generate_series(0, i.dur - 1) s WHERE u.t + s <= i.slots AND valor_restriccion_clasif_act(i.clasif, i.per, c.d, u.t + s) > 0) AS activity_type,
      EXISTS (SELECT 1 FROM generate_series(0, i.dur - 1) s WHERE u.t + s <= i.slots AND valor_restriccion_asignatura(i.asig, i.per, c.d, u.t + s) > 0) AS subject,
      EXISTS (SELECT 1 FROM generate_series(0, i.dur - 1) s, unnest(dame_lista_id_brigadas_de_grupo($2::varchar)) b
               WHERE u.t + s <= i.slots AND valor_restriccion_brigada(b, i.per, c.d, u.t + s) > 0) AS cohort,
      ($3::varchar IS NOT NULL AND EXISTS (SELECT 1 FROM generate_series(0, i.dur - 1) s
               WHERE u.t + s <= i.slots AND valor_restriccion_profesor($3::varchar, i.per, c.d, u.t + s) > 0)) AS teacher,
      ($4::varchar IS NOT NULL AND EXISTS (SELECT 1 FROM generate_series(0, i.dur - 1) s
               WHERE u.t + s <= i.slots AND valor_restriccion_lugar($4::varchar, i.per, c.d, u.t + s) > 0)) AS room,
      EXISTS (SELECT 1 FROM generate_series(0, i.dur - 1) s, jsonb_array_elements_text($5::jsonb) r
               WHERE u.t + s <= i.slots AND valor_restriccion_otro_recurso(r, i.per, c.d, u.t + s) > 0) AS resource,
      ($3::varchar IS NOT NULL AND NOT es_higienico_profesor($3::varchar, $1::varchar, c.d)) AS teacher_load,
      EXISTS (SELECT 1 FROM unnest(dame_lista_id_brigadas_de_grupo($2::varchar)) b WHERE NOT es_higienico_brigada(b, $1::varchar, c.d)) AS cohort_load
    FROM (SELECT dame_periodo_plantilla_de_actividad($1::varchar) AS per, dame_duracion_plantilla_de_actividad($1::varchar) AS dur,
                 dame_clasif_plantilla_de_actividad($1::varchar) AS clasif, dame_asignatura_plantilla_de_actividad($1::varchar) AS asig) i0
    CROSS JOIN LATERAL (SELECT i0.*, p.cant_dias AS days, p.cant_turnos AS slots FROM periodo p WHERE p.id = i0.per) i
    CROSS JOIN LATERAL generate_series(1, i.days) c(d)
    CROSS JOIN LATERAL generate_series(1, i.slots) u(t)
    ORDER BY c.d, u.t
    SQL;

  /**
   * The subject's day range, when its breakdown respects the order.
   */
  private const RANGE = <<<'SQL'
    SELECT d.respetar_orden AS ordered, (x.r).ini AS ini, (x.r).fin AS fin
      FROM desglose d CROSS JOIN LATERAL (SELECT rango($1::varchar, $2::varchar, d.min_dias, d.max_dias) AS r) x
     WHERE d.id = dame_desglose_de_plantilla($1::varchar)
    SQL;

  /**
   * The reasons that refuse a cell, in the order they are shown.
   */
  public const REASONS = ['overflow', 'period', 'activity_type', 'subject', 'cohort', 'teacher', 'room', 'resource'];

  /**
   * The warnings of a cell.
   */
  public const WARNINGS = ['teacher_load', 'cohort_load', 'order'];

  public function __construct(
    private readonly Connection $database,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly WorkspaceClient $workspace,
    private readonly EngineClient $engine,
    private readonly WorkspaceSync $input,
    private readonly TimeInterface $time,
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function runPending(int $max): int {
    $now = $this->time->getCurrentTime();
    $this->database->update('ancora_placement_check')
      ->fields(['state' => 'failed', 'error' => 'The worker stopped during the check. Check again.', 'changed' => $now])
      ->condition('state', 'running')
      ->condition('changed', $now - self::LOST_AFTER, '<')
      ->execute();
    $done = 0;
    while ($done < $max && ($id = $this->claim()) !== NULL) {
      $this->run($id);
      $done++;
    }
    return $done;
  }

  /**
   * Claims the oldest queued check, or returns NULL.
   */
  private function claim(): ?int {
    $id = $this->database->query("UPDATE {ancora_placement_check} SET state = 'running', changed = :now
      WHERE id = (SELECT id FROM {ancora_placement_check} WHERE state = 'queued' ORDER BY id LIMIT 1 FOR UPDATE SKIP LOCKED)
      AND state = 'queued' RETURNING id", [':now' => $this->time->getCurrentTime()])->fetchField();
    return $id === FALSE ? NULL : (int) $id;
  }

  /**
   * Runs one claimed check and records its outcome.
   */
  public function run(int $id): void {
    $row = $this->database->select('ancora_placement_check', 'c')->fields('c')->condition('id', $id)->execute()->fetchAssoc();
    try {
      $storage = $this->entityTypeManager->getStorage('ancora_placement');
      $storage->resetCache([(int) $row['placement_id']]);
      $placement = $storage->load((int) $row['placement_id']);
      if (!$placement instanceof Placement) {
        throw new \RuntimeException('The placement no longer exists.');
      }
      $job = $this->job($id, $placement, (int) $row['requested_by']);
      $signature = PlacementModification::signature($placement);
      [$version, $result] = $this->workspace->withWorkspace($job, function (PgConnection $conn) use ($job, $placement): array {
        $loaded = $this->input->loadForCheck($job, $conn, $this->engine);
        return [(string) $loaded['site_version'], $this->grid($conn, $placement)];
      });
      $this->database->update('ancora_placement_check')
        ->fields([
          'state' => 'done',
          'version' => $version,
          'signature' => $signature,
          'result' => json_encode($result, JSON_THROW_ON_ERROR),
          'changed' => $this->time->getCurrentTime(),
        ])
        ->condition('id', $id)
        ->execute();
    }
    catch (\Throwable $e) {
      $this->logger->warning('Placement check @id failed: @message', ['@id' => $id, '@message' => $e->getMessage()]);
      $this->database->update('ancora_placement_check')
        ->fields([
          'state' => 'failed',
          'error' => mb_substr(EngineClient::summary($e->getMessage()), 0, 1000),
          'changed' => $this->time->getCurrentTime(),
        ])
        ->condition('id', $id)
        ->execute();
    }
  }

  /**
   * A job value for the check's workspace and input (never stored).
   */
  private function job(int $id, Placement $placement, int $requestedBy): Job {
    $template = $placement->getTemplate();
    $subject = $template?->getSubject();
    $plan = $placement->get('plan')->entity;
    $period = $placement->get('period')->entity;
    $specialty = $subject?->get('specialty')->entity;
    $level = $subject?->get('level')->entity;
    if (!$plan instanceof Plan || !$period instanceof Period || !$specialty instanceof Specialty || !$level instanceof Level) {
      throw new \RuntimeException('The plan, period, specialty or level of the placement no longer exists.');
    }
    $now = $this->time->getCurrentTime();
    return new Job(
      self::CHECK_KEY_BASE + $id, '', ScopeMapper::scope($plan, $period, $specialty, $level), new JobOptions(), self::TIME_LIMIT,
      JobState::Running, FALSE, NULL, $requestedBy, NULL, NULL, $now, $now, $now, NULL, $now, NULL, 1, NULL, NULL,
      ScopeMapper::source($plan, $period, $specialty, $level),
    );
  }

  /**
   * Evaluates every cell of the period with the placement's row removed.
   */
  private function grid(PgConnection $conn, Placement $placement): array {
    $engine_id = $placement->getEngineId();
    $rows = $this->engine->rows($conn, "SELECT plantilla, grupo, profesor, lugar, dia, turno,
        (SELECT coalesce(json_agg(u.otro_recurso ORDER BY u.ctid), '[]'::json) FROM otros_recursos_usados u WHERE u.actividad = a.id) AS recursos
      FROM actividad a WHERE id = $1", [$engine_id]);
    if ($rows === []) {
      throw new \RuntimeException('The placement is not in the loaded input.');
    }
    $a = $rows[0];
    $this->engine->affected($conn, 'BEGIN');
    try {
      $this->engine->affected($conn, 'DELETE FROM actividad WHERE id = $1', [$engine_id]);
      $cells = $this->engine->rows($conn, self::GRID, [
        $a['plantilla'],
        $a['grupo'],
        $a['profesor'],
        $a['lugar'],
        (string) $a['recursos'],
      ]);
      $range = NULL;
      $this->engine->affected($conn, 'SAVEPOINT range');
      try {
        $range = $this->engine->rows($conn, self::RANGE, [$a['plantilla'], $a['grupo']])[0] ?? NULL;
      }
      catch (\RuntimeException) {
        $this->engine->affected($conn, 'ROLLBACK TO SAVEPOINT range');
      }
    }
    finally {
      @pg_query($conn, 'ROLLBACK');
    }
    $flag = static fn ($v): bool => $v === TRUE || $v === 't';
    $ordered = $range !== NULL && $flag($range['ordered']);
    $out = [];
    foreach ($cells as $cell) {
      $day = (int) $cell['day'];
      $slot = (int) $cell['slot'];
      $reasons = array_values(array_filter(self::REASONS, static fn (string $r): bool => $flag($cell[$r])));
      $warnings = array_values(array_filter(['teacher_load', 'cohort_load'], static fn (string $w): bool => $flag($cell[$w])));
      if ($ordered && ($day < (int) $range['ini'] || $day > (int) $range['fin'])) {
        $warnings[] = 'order';
      }
      $out[] = ['day' => $day, 'slot' => $slot, 'ok' => $reasons === [], 'reasons' => $reasons, 'warnings' => $warnings];
    }
    return [
      'from' => ['day' => (int) $a['dia'], 'slot' => (int) $a['turno']],
      'engine_id' => $engine_id,
      'order_range' => $range === NULL ? NULL : [
        'ordered' => $ordered,
        'ini' => (int) $range['ini'],
        'fin' => (int) $range['fin'],
      ],
      'cells' => $out,
    ];
  }

}
