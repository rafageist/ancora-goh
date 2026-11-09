<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Reads current scope demands using the same legacy rows as the engine sync.
 */
final class RunActivities {

  use StringTranslationTrait;

  public function __construct(
    private readonly AcademicExport $export,
    private readonly LegacyRows $legacyRows,
    private readonly TimetableReader $timetable,
  ) {}

  /**
   * Implements each join and predicate of EngineClient::METRICS demand CTE.
   *
   * The returned pairs retain engine ids so a placement can be subtracted
   * without comparing names. Labels and assignments come from those rows.
   */
  public static function demands(array $rows, JobScope $scope): array {
    [$specialty, $level, $period] = $scope->toParameters();
    $periods = array_column($rows['periodo'], NULL, 'id');
    $subjects = array_column($rows['asignatura'], NULL, 'id');
    $types = array_column($rows['clasificacion_de_actividad'], 'descripcion', 'id');
    $groups = $rows['grupo'];
    $breakdowns = array_column($rows['desglose'], NULL, 'id');
    $teachers = array_column($rows['profesor'], 'nombre', 'id');
    $rooms = array_column($rows['lugar'], 'nombre', 'id');
    $restricted = [];
    foreach ($rows['profesor_para_grupo'] as $row) {
      $restricted[$row['profesor_para_actividad']][$row['grupo']] = TRUE;
    }
    $assigned_teachers = [];
    foreach ($rows['profesor_para_actividad'] as $row) {
      $assigned_teachers[$row['plantilla']][] = $row;
    }
    $assigned_rooms = [];
    foreach ($rows['lugar_para_actividad'] as $row) {
      $assigned_rooms[$row['plantilla']][] = $row['lugar'];
    }
    $demands = [];
    foreach ($rows['plantilla_de_actividad'] as $template) {
      $breakdown = $breakdowns[$template['desglose']] ?? NULL;
      $subject = $breakdown === NULL ? NULL : ($subjects[$breakdown['asignatura']] ?? NULL);
      $scope_period = $breakdown === NULL ? NULL : ($periods[$breakdown['periodo']] ?? NULL);
      if ($subject === NULL || $scope_period === NULL || $subject['especialidad'] !== $specialty || $subject['nivel'] !== $level || $breakdown['periodo'] !== $period) {
        continue;
      }
      foreach ($groups as $group) {
        if ($group['clasificacion'] !== $template['clasificacion'] || $group['especialidad'] !== $subject['especialidad'] || $group['nivel'] !== $subject['nivel'] || $group['planificacion'] !== $scope_period['planificacion']) {
          continue;
        }
        $teacher_names = [];
        foreach ($assigned_teachers[$template['id']] ?? [] as $assignment) {
          if (isset($restricted[$assignment['id']]) && !isset($restricted[$assignment['id']][$group['id']])) {
            continue;
          }
          $teacher_names[] = $teachers[$assignment['profesor']] ?? $assignment['profesor'];
        }
        $room_names = [];
        foreach ($assigned_rooms[$template['id']] ?? [] as $room) {
          $room_names[] = $rooms[$room] ?? $room;
        }
        $key = $template['id'] . "\x1F" . $group['id'];
        $demands[$key] = [
          'template_id' => $template['id'],
          'group_id' => $group['id'],
          'subject' => $subject['descripcion'],
          'template' => $subject['descripcion'] . ' #' . $template['orden'],
          'group' => $group['descripcion'],
          'type' => $types[$template['clasificacion']] ?? $template['clasificacion'],
          'teachers' => array_values(array_unique($teacher_names)),
          'rooms' => array_values(array_unique($room_names)),
        ];
      }
    }
    ksort($demands, SORT_STRING);
    return $demands;
  }

  /**
   * Removes the same distinct (template, group) pairs as METRICS unplaced.
   */
  public static function unplaced(array $demands, array $placements): array {
    $placed = [];
    foreach ($placements as $placement) {
      if ($placement['template_id'] !== NULL && $placement['group_id'] !== NULL) {
        $placed[(int) $placement['template_id'] . ':' . (int) $placement['group_id']] = TRUE;
      }
    }
    foreach ($demands as $key => $demand) {
      $pair = (int) explode(LegacyRows::ID_SEPARATOR, $demand['template_id'], 2)[0]
        . ':' . (int) explode(LegacyRows::ID_SEPARATOR, $demand['group_id'], 2)[0];
      if (isset($placed[$pair])) {
        unset($demands[$key]);
      }
    }
    return $demands;
  }

  /**
   * Builds the existing job page's current-data activity section.
   */
  public function build(Job $job, Plan $plan, Period $period): array {
    if (!in_array($job->state, [
      JobState::Partial, JobState::Cancelled, JobState::Failed,
      JobState::TimedOut, JobState::Succeeded,
    ], TRUE)) {
      return [];
    }
    if ($job->state === JobState::Cancelled && !in_array($job->result['kept'] ?? NULL, ['partial', 'placements'], TRUE)) {
      return [];
    }
    if ($job->state === JobState::Succeeded && (!isset($job->result['overflow']) || (int) $job->result['overflow'] <= 0)) {
      return [];
    }
    $export = $this->export->export($plan);
    $rows = $this->legacyRows->build($export, ScopeMapper::creator($plan), TRUE);
    $demands = self::demands($rows, $job->scope);
    $stale = !isset($job->result['input_hash']) || $job->result['input_hash'] !== ScopeMapper::hash($export);
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-scheduling-job__activities'],
        'role' => 'region',
        'aria-label' => $this->t('Activities in this scope'),
      ],
      'heading' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('Activities in this scope')],
    ];
    if ($stale) {
      $build['stale'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('This list is computed from current academic data, which changed after this run or has no recorded input snapshot.'),
      ];
    }
    if (in_array($job->state, [JobState::Failed, JobState::TimedOut], TRUE)) {
      $build['intro'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->formatPlural(count($demands),
          'This run kept no placements. This is the scope’s 1 current activity; the list does not show which activities would have failed placement.',
          'This run kept no placements. These are the scope’s @count current activities; the list does not show which activities would have failed placement.'),
      ];
      $build['table'] = $this->table($demands);
      return $build;
    }
    $scope = NULL;
    foreach ($this->timetable->read($plan, $period)['scopes'] as $candidate) {
      if ($candidate['source']->specialtyId === $job->source->specialtyId && $candidate['source']->levelId === $job->source->levelId) {
        $scope = $candidate;
        break;
      }
    }
    if ($scope === NULL || $scope['job']?->id !== $job->id || $scope['error'] !== NULL) {
      $build['unavailable'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('The placements kept by this run are no longer available to compare with the current activities.'),
      ];
      return $build;
    }
    if ($job->state === JobState::Succeeded) {
      $slots_per_day = (int) $period->get('slots_per_day')->value;
      $overflow = array_values(array_filter($scope['placements'], static fn (array $placement): bool => $placement['slot'] + $placement['slots'] - 1 > $slots_per_day));
      $build['intro'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Placements extending beyond the final daily slot:'),
      ];
      if (!$stale && count($overflow) !== (int) $job->result['overflow']) {
        $build['mismatch'] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('The current overflow list does not match the recorded count; review the run before relying on it.'),
        ];
      }
      $build['table'] = $this->table(array_map(static fn (array $placement): array => [
        'subject' => $placement['subject'],
        'template' => $placement['activity'],
        'group' => $placement['group'],
        'type' => $placement['activity_type'],
        'teachers' => [$placement['teacher']],
        'rooms' => [$placement['room']],
      ], $overflow));
      return $build;
    }
    $unplaced = self::unplaced($demands, $scope['placements']);
    $build['intro'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->formatPlural(count($unplaced),
        '1 activity has no retained placement. The engine does not record an individual reason.',
        '@count activities have no retained placement. The engine does not record individual reasons.'),
    ];
    if (!$stale && isset($job->result['unplaced']) && (int) $job->result['unplaced'] !== count($unplaced)) {
      $build['mismatch'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('The current list does not match the recorded unplaced count; review the run before relying on it.'),
      ];
    }
    $build['table'] = $this->table($unplaced);
    return $build;
  }

  /**
   * Renders labels from trusted legacy export rows with Drupal escaping.
   */
  private function table(array $activities): array {
    $table = [
      '#type' => 'table',
      '#header' => [
        $this->t('Subject'), $this->t('Activity template'), $this->t('Student group'),
        $this->t('Activity type'), $this->t('Assigned teachers'), $this->t('Assigned rooms'),
      ],
      '#attributes' => ['class' => ['ancora-scheduling-job__activity-list']],
      '#empty' => $this->t('No activities to list.'),
    ];
    foreach (array_values($activities) as $index => $activity) {
      $table[$index] = [
        'subject' => ['#plain_text' => $activity['subject']],
        'template' => ['#plain_text' => $activity['template']],
        'group' => ['#plain_text' => $activity['group']],
        'type' => ['#plain_text' => $activity['type']],
        'teachers' => ['#plain_text' => implode(', ', array_filter($activity['teachers']))],
        'rooms' => ['#plain_text' => implode(', ', array_filter($activity['rooms']))],
      ];
    }
    return $table;
  }

}
