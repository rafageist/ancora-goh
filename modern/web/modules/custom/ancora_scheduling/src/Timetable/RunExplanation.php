<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobState;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Explains a completed run using only its persisted job row.
 */
final class RunExplanation {

  use StringTranslationTrait;

  /**
   * Builds the explanation shown above the job details.
   */
  public function build(Job $job): array {
    $result = $job->result ?? [];
    $lines = [];
    $causes = [];

    switch ($job->state) {
      case JobState::Succeeded:
        $lines[] = $this->t('The run succeeded and kept a final timetable.');
        if (($overflow = self::count($result, 'overflow')) !== NULL && $overflow > 0) {
          $lines[] = $this->formatPlural($overflow,
            '1 activity extends past the last slot of the day. This is documented legacy scheduling behaviour.',
            '@count activities extend past the last slot of the day. This is documented legacy scheduling behaviour.');
        }
        break;

      case JobState::Partial:
        $lines[] = $this->t('The run kept a draft timetable.');
        $unplaced = self::count($result, 'unplaced');
        $demands = self::count($result, 'demands');
        if ($unplaced !== NULL && $demands !== NULL) {
          $lines[] = $this->t('@unplaced of @demands activities could not be placed. The engine does not record why.', [
            '@unplaced' => $unplaced,
            '@demands' => $demands,
          ]);
        }
        else {
          $lines[] = $this->t('The job does not record how many activities could not be placed or why.');
        }
        break;

      case JobState::Cancelled:
        $lines[] = $this->t('The run was cancelled.');
        $lines[] = match ($result['kept'] ?? NULL) {
          'partial' => $this->t('The placements from this run were kept as a draft timetable.'),
          'placements' => $this->t('The placements from this run were kept, but the run is not marked final.'),
          'none' => $this->t('Work from this run was discarded; the previous timetable is unchanged.'),
          default => $this->t('The job record does not say whether this run kept placements.'),
        };
        break;

      case JobState::TimedOut:
        $lines[] = $this->t('The run exceeded its time limit. Work from this run was rolled back; the previous timetable is unchanged.');
        break;

      case JobState::Failed:
        $lines[] = $this->t('The run stopped with an engine error. Work from this run was rolled back; the previous timetable is unchanged.');
        if ($job->error !== NULL && preg_match('/^\[([A-Z0-9]{5})\]/', $job->error, $matches)) {
          $lines[] = $this->t('The recorded SQLSTATE is @code.', ['@code' => $matches[1]]);
        }
        $causes = $this->possibleCauses($job->error);
        if ($causes === []) {
          $lines[] = $job->error === NULL || $job->error === ''
            ? $this->t('No error detail was recorded, so no documented possible cause is available.')
            : $this->t('No documented possible cause is available for this recorded error.');
        }
        break;

      default:
        throw new \InvalidArgumentException('Only completed jobs have an outcome explanation.');
    }

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-scheduling-job__explanation'],
        'role' => 'region',
        'aria-label' => $this->t('What happened'),
      ],
      'heading' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('What happened')],
    ];
    foreach ($lines as $index => $line) {
      $build['line_' . $index] = ['#type' => 'html_tag', '#tag' => 'p', '#value' => $line];
    }
    if ($causes !== []) {
      $build['possible_heading'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t('Documented possible causes in the legacy engine'),
      ];
      $build['possible_causes'] = ['#theme' => 'item_list', '#items' => $causes];
      $build['uncertainty'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('The job record does not identify which, if any, of these possible causes applied to this run.'),
      ];
    }
    return $build;
  }

  /**
   * A metric is shown only when it is an actual non-negative job result count.
   */
  private static function count(array $result, string $key): ?int {
    return isset($result[$key]) && is_int($result[$key]) && $result[$key] >= 0 ? $result[$key] : NULL;
  }

  /**
   * Historical catalogue in next-functional-priorities.md §2.2.
   *
   * Every entry names its case, quirk or source; none diagnoses this job.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   Documented possible causes of the recorded error.
   */
  private function possibleCauses(?string $error): array {
    if ($error === NULL) {
      return [];
    }
    if (str_contains($error, 'broke an engine invariant')) {
      return [$this->t('In two-database mode, collisions or overflow may break an engine invariant and roll back the run (D12; EngineClient.php).')];
    }
    if (!preg_match('/^\[([A-Z0-9]{5})\]/', $error, $matches)) {
      return [];
    }
    return match ($matches[1]) {
      '2202E' => [
        $this->t('A multi-slot activity may overflow the day before dia_minimo_impacto() fails (Q-39; case 127 F1/F2).'),
        $this->t('A brigade restriction from another period may be outside this period’s size (engine-dependency-closure.md §10, Quirk 3).'),
        $this->t('A subject in the scope may lack a template in this period (engine-dependency-closure.md §10, Quirk 2).'),
      ],
      '23502' => [$this->t('A template may not require a teacher or a room (Q-05; case 123).')],
      '23505' => [$this->t('An other resource may be under contention, or manually restricted at the chosen cell (Q-31/Q-32, D-05; cases 083 and 142).')],
      '22004' => [$this->t('A period may have periodicity but no start hour (case 130).')],
      'P0001' => preg_match('/^\[P0001\] generar: activity template .+ has no orden/', $error)
        ? [$this->t('An activity template may have no order (generar.sql:207).')]
        : [],
      '42804', '42703' => [$this->t('Historical inputs may trigger Q-28 or Q-29; Drupal does not provide those inputs (cases 070–072 and 100).')],
      default => [],
    };
  }

}
