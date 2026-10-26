<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\ScopeMapper;
use Psr\Log\LoggerInterface;

/**
 * The timetable read model of one plan and period.
 *
 * For every specialty and level the plan's jobs ran on, it finds the latest
 * job that changed the scope's placements (JobRepository::changedPlacements)
 * and reads the placements the engine database holds for that scope, with
 * explicit read-only SQL (EngineClient::placements()). The placements are a
 * final timetable only when that job succeeded; after a partial or a
 * soft-cancelled run they are a draft. A rolled-back run changed nothing, so
 * it does not hide the final timetable before it.
 *
 * Engine ids are mapped back to the plan's records through the same numbered
 * ids LegacyRows gives them (LegacyRows::engineId()). An id that no longer
 * maps (the record was deleted after the run) is shown as its code, the part
 * after the separator.
 */
final class TimetableReader {

  public function __construct(
    private readonly JobRepository $jobs,
    private readonly EngineClient $engine,
    private readonly EngineSettings $settings,
    private readonly AcademicExport $export,
    private readonly LegacyRows $legacyRows,
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * Reads the timetable of a plan and period.
   *
   * @return array
   *   - hash: the input snapshot hash of the plan's current data;
   *   - groups, teachers, rooms: code => label, for the filters;
   *   - scopes: list of scope arrays, each with specialty and level labels,
   *     the job whose placements are shown ("job", NULL when none), "final"
   *     (bool), "stale" (the data changed since the run), "active" (a queued
   *     or running job, or NULL), "placements" (mapped placements) and
   *     "error" (NULL, or a message without details).
   */
  public function read(Plan $plan, Period $period): array {
    $export = $this->export->export($plan);
    $hash = ScopeMapper::hash($export);
    $index = $this->index($export, $this->legacyRows->build($export, ScopeMapper::creator($plan), TRUE));

    $by_scope = [];
    foreach ($this->jobs->forSource((int) $plan->id(), (int) $period->id()) as $job) {
      $by_scope[$job->source->specialtyId . ':' . $job->source->levelId][] = $job;
    }
    $scopes = [];
    foreach ($by_scope as $jobs) {
      $active = NULL;
      $shown = NULL;
      foreach ($jobs as $job) {
        if ($active === NULL && !$job->state->isTerminal()) {
          $active = $job;
        }
        if ($shown === NULL && JobRepository::changedPlacements($job)) {
          $shown = $job;
        }
      }
      $scope_job = $shown ?? $jobs[array_key_first($jobs)];
      $scope = [
        'specialty' => $this->label($index['specialties'], $scope_job->scope->specialty),
        'level' => $this->label($index['levels'], $scope_job->scope->level),
        'job' => $shown,
        'final' => $shown !== NULL && $shown->state === JobState::Succeeded,
        'stale' => $shown !== NULL && ($shown->result['input_hash'] ?? NULL) !== $hash,
        'active' => $active,
        'placements' => [],
        'error' => NULL,
      ];
      if ($shown !== NULL) {
        try {
          $scope['placements'] = $this->placements($shown, $index);
        }
        catch (\RuntimeException $e) {
          $this->logger->error('Timetable of plan @plan: the engine database could not be read: @message', [
            '@plan' => $plan->id(),
            '@message' => $e->getMessage(),
          ]);
          $scope['error'] = 'unreachable';
        }
      }
      $scopes[] = $scope;
    }
    usort($scopes, static fn (array $a, array $b): int => strcmp($a['specialty'] . "\0" . $a['level'], $b['specialty'] . "\0" . $b['level']));

    return [
      'hash' => $hash,
      'groups' => $index['group_labels'],
      'teachers' => $index['teacher_labels'],
      'rooms' => $index['room_labels'],
      'scopes' => $scopes,
    ];
  }

  /**
   * The engine placements of a job's scope, mapped to the plan's records.
   */
  private function placements(Job $job, array $index): array {
    if (!$this->settings->isConfigured()) {
      throw new \RuntimeException('The engine database of this site is not configured.');
    }
    $placements = [];
    foreach ($this->engine->placements($job->scope) as $row) {
      $template = $index['templates'][$row['plantilla']] ?? NULL;
      $placements[] = [
        'day' => $row['dia'],
        'slot' => $row['turno'],
        'slots' => $row['duracion'],
        'activity' => $template['label'] ?? self::strip($row['plantilla']),
        'subject' => $template['subject'] ?? '',
        'group' => $index['groups'][$row['grupo']] ?? self::strip($row['grupo']),
        'group_code' => self::strip($row['grupo']),
        'teacher' => $row['profesor'] === NULL ? '' : ($index['teachers'][$row['profesor']] ?? self::strip($row['profesor'])),
        'teacher_code' => $row['profesor'] === NULL ? '' : self::strip($row['profesor']),
        'room' => $row['lugar'] === NULL ? '' : ($index['rooms'][$row['lugar']] ?? self::strip($row['lugar'])),
        'room_code' => $row['lugar'] === NULL ? '' : self::strip($row['lugar']),
      ];
    }
    return $placements;
  }

  /**
   * Engine id => label maps, built from the same rows the sync writes.
   */
  private function index(array $export, array $rows): array {
    $subjects = array_column($export['subjects'], 'label', 'key');
    $index = ['templates' => []];
    foreach ($export['activity_templates'] as $i => $template) {
      $subject = $subjects[$template['subject']] ?? $template['subject_code'];
      $index['templates'][$rows['plantilla_de_actividad'][$i]['id']] = [
        'label' => $subject . ' #' . $template['sequence'],
        'subject' => $subject,
      ];
    }
    $pairs = [
      'groups' => ['student_groups', 'grupo'],
      'teachers' => ['teachers', 'profesor'],
      'rooms' => ['rooms', 'lugar'],
      'specialties' => ['specialties', 'especialidad'],
      'levels' => ['levels', 'nivel'],
    ];
    foreach ($pairs as $name => [$list, $table]) {
      $index[$name] = [];
      foreach ($export[$list] as $i => $record) {
        $index[$name][$rows[$table][$i]['id']] = $record['label'];
      }
    }
    foreach (['group' => 'student_groups', 'teacher' => 'teachers', 'room' => 'rooms'] as $name => $list) {
      $index[$name . '_labels'] = array_column($export[$list], 'label', 'code');
    }
    return $index;
  }

  /**
   * A label by engine id, or the code when it no longer maps.
   */
  private function label(array $labels, string $id): string {
    return $labels[$id] ?? self::strip($id);
  }

  /**
   * The code of a numbered engine id: the part after the separator.
   */
  private static function strip(string $id): string {
    $at = strpos($id, LegacyRows::ID_SEPARATOR);
    return $at === FALSE ? $id : substr($id, $at + strlen(LegacyRows::ID_SEPARATOR));
  }

}
