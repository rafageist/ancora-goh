<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Renders the cross-plan conflict warnings (#133): text only, read-only.
 *
 * An unknown calendar is always "Dates unknown / cross-plan conflicts not
 * evaluated", never "no conflicts"; a known one without overlap says so only
 * for the dated periods.
 */
final class CrossPlanConflictView {

  use StringTranslationTrait;

  /**
   * The most overlaps listed in one section.
   */
  public const SHOWN = 50;

  /**
   * The section of a timetable or the explorer.
   *
   * @param array $result
   *   CrossPlanConflictDetector::forPlan().
   * @param string $id
   *   The HTML id of the section.
   */
  public function section(array $result, string $id = 'ancora-cross-plan'): array {
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-cross-plan'],
        'id' => $id,
        'role' => 'region',
        'aria-labelledby' => "$id-title",
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Cross-plan conflicts'),
        '#attributes' => ['id' => "$id-title"],
      ],
      'help' => [
        '#markup' => '<p class="ancora-cross-plan__help">' . $this->t('The same teacher or room booked at the same time in two of your periods (of different plans, or of one plan) whose dates overlap. The scheduler checks each period on its own and does not see them. These are warnings only: nothing is blocked.') . '</p>',
      ],
    ];
    if (!$result['evaluated']) {
      $build['unknown'] = ['#markup' => '<p class="ancora-cross-plan__unknown"><strong>' . $this->t('Dates unknown / cross-plan conflicts not evaluated.') . '</strong> ' . $this->t('Give the plan a first day and its periods their days of the plan to evaluate them.') . '</p>'];
    }
    elseif ($result['conflicts'] === []) {
      $build['none'] = ['#markup' => '<p class="ancora-cross-plan__none">' . $this->t('No overlap found with the dated periods of your plans.') . '</p>'];
    }
    else {
      $shown = array_slice($result['conflicts'], 0, self::SHOWN);
      $build['count'] = ['#markup' => '<p>' . $this->formatPlural(count($result['conflicts']), 'One overlap:', '@count overlaps:') . '</p>'];
      $build['list'] = [
        '#theme' => 'item_list',
        '#attributes' => ['class' => ['ancora-cross-plan__list']],
        '#items' => array_map(fn (array $c): array => ['#markup' => (string) $this->conflict($c)], $shown),
      ];
      if (count($result['conflicts']) > self::SHOWN) {
        $build['more'] = ['#markup' => '<p>' . $this->t('… and @n more. Choose a teacher or a room in the explorer to see fewer at a time.', ['@n' => count($result['conflicts']) - self::SHOWN]) . '</p>'];
      }
    }
    $named = array_filter($result['unknown'], static fn (array $u): bool => $u['plan'] !== NULL);
    if ($result['evaluated'] && $result['unknown'] !== []) {
      $build['others'] = [
        '#markup' => '<p class="ancora-cross-plan__unknown">' . $this->t('Not evaluated (dates unknown): @periods.', [
          '@periods' => implode('; ', array_map(fn (array $u): string => sprintf('%s, %s (%s)', $u['plan'], $u['period'], $this->reason($u['reason'])), $named)) ?: $this->t('periods you cannot see'),
        ]) . '</p>',
      ];
    }
    return $build;
  }

  /**
   * One conflict as a sentence.
   */
  public function conflict(array $c): TranslatableMarkup {
    $dates = implode(', ', array_map(fn (array $o): string => $o['date'] . ' ' . $o['start'] . '–' . $o['end'] . ($o['after_end'] ? ' (' . $this->t("after the plan's last day") . ')' : ''), $c['occurrences']));
    $other = $c['other']['plan'] === NULL
      ? $this->t('another of your plans')
      : $this->t('@activity, @group, in plan @plan, period @period, day @day, slot @slot', [
        '@activity' => $c['other']['activity'],
        '@group' => $c['other']['group'],
        '@plan' => $c['other']['plan'],
        '@period' => $c['other']['period'],
        '@day' => $c['other']['day'],
        '@slot' => $c['other']['slot'],
      ]);
    $args = [
      '@resource' => $c['resource_label'],
      '@activity' => $c['mine']['activity'],
      '@group' => $c['mine']['group'],
      '@day' => $c['mine']['day'],
      '@slot' => $c['mine']['slot'],
      '@other' => $other,
      '@dates' => $dates,
    ];
    return $c['resource'] === 'teacher'
      ? $this->t('Teacher @resource: @activity, @group (day @day, slot @slot) and @other, on @dates.', $args)
      : $this->t('Room @resource: @activity, @group (day @day, slot @slot) and @other, on @dates.', $args);
  }

  /**
   * A short warning for a move destination.
   */
  public function cellWarning(array $conflicts): TranslatableMarkup {
    $parts = [];
    foreach ($conflicts as $c) {
      $where = $c['other']['plan'] === NULL ? (string) $this->t('another plan') : (string) $this->t('plan @plan, period @period', [
        '@plan' => $c['other']['plan'],
        '@period' => $c['other']['period'],
      ]);
      $when = implode(', ', array_map(static fn (array $o): string => $o['date'] . ' ' . $o['start'] . '–' . $o['end'], $c['occurrences']));
      $parts[] = $c['resource'] === 'teacher'
        ? (string) $this->t('teacher @r busy in @where on @when', [
          '@r' => $c['resource_label'],
          '@where' => $where,
          '@when' => $when,
        ])
        : (string) $this->t('room @r busy in @where on @when', [
          '@r' => $c['resource_label'],
          '@where' => $where,
          '@when' => $when,
        ]);
    }
    return $this->t('Cross-plan warning: @parts.', ['@parts' => implode('; ', $parts)]);
  }

  /**
   * Why a calendar is unknown.
   */
  public function reason(?string $reason): TranslatableMarkup {
    return match ($reason) {
      'no_first_day' => $this->t('the plan has no first day'),
      'no_repetition' => $this->t('the period has no days of the plan'),
      'no_slot_length' => $this->t('the period has no slot length'),
      default => $this->t('not available'),
    };
  }

}
