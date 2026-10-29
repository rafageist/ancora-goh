<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\PeriodInputs;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Read-only warnings: one teacher or room booked twice at the same time.
 *
 * The scheduler checks a teacher or a room within one period only (case
 * 150), so the same owner's teacher or room (owner catalogues, D1) can be
 * booked in two periods, of two plans or of one, whose real dates overlap.
 * This service finds those overlaps from Drupal data only. It needs no
 * worker, no engine SQL and no legacy change, and it never blocks a move or
 * a run.
 *
 * The temporal model is docs/modernization/temporal-semantics.md (#133
 * phase 1, case 151, Kernel TemporalSemanticsTest):
 * - a placement on period day d, first slot s, n slots long, in a period
 *   with repetitions P (plan_days) of a plan whose first day is F, happens
 *   for every p in P and every slot t in s..s+n-1 on the date F + (p - 1) +
 *   (d - 1), in [slotStart(t), slotStart(t) + slot length), breaks included,
 *   in exact hundredths of an hour of the site's wall-clock time;
 * - two occurrences overlap when they share a date and a.start < b.end and
 *   b.start < a.end; touching intervals do not;
 * - a period's calendar is UNKNOWN when its plan has no first day, it has
 *   no repetition day or its slot length is 0: its placements are not
 *   evaluated, which is never reported as "no conflict";
 * - occurrences after the plan's last day count, and are marked (maintainer
 *   decision on #133); repetitions closer than the period's length give
 *   both occurrences.
 *
 * The placements are those the timetables show (TimetableReader: the final
 * or draft placements of each scope, never those of a running job), in
 * either engine mode. Only the plans of ONE owner are compared, and only
 * for a user who may view that owner's plan; another plan is named only if
 * that user may view it.
 */
final class CrossPlanConflictDetector {

  /**
   * Per-request results: owner => the owner's occurrences and unknowns.
   *
   * @var array<int, array>
   */
  private array $owners = [];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly TimetableReader $reader,
    private readonly PeriodInputs $inputs,
  ) {}

  /**
   * The cache tags of every input: placements, plans, periods, breaks, runs.
   */
  public function cacheability(): CacheableMetadata {
    $cache = (new CacheableMetadata())->addCacheTags([JobRepository::CACHE_TAG]);
    foreach ([
      'ancora_placement',
      'ancora_plan',
      'ancora_period',
      'ancora_break',
      'ancora_activity_type',
      'ancora_activity_template',
      'ancora_teacher',
      'ancora_room',
    ] as $type) {
      if ($this->entityTypeManager->hasDefinition($type)) {
        $cache->addCacheTags($this->entityTypeManager->getDefinition($type)->getListCacheTags());
      }
    }
    return $cache;
  }

  /**
   * Why a period's calendar is unknown, or NULL when it is known.
   */
  public function unknownReason(Plan $plan, Period $period): ?string {
    return match (TRUE) {
      $plan->getStartDate() === NULL => 'no_first_day',
      $period->get('plan_days')->isEmpty() => 'no_repetition',
      self::centi($period->getSlotDuration()) <= 0 => 'no_slot_length',
      default => NULL,
    };
  }

  /**
   * The cross-plan conflicts of a plan's placements, for an account.
   *
   * @return array{evaluated: bool, unknown: list<array>, conflicts: list<array>}
   *   - evaluated: FALSE when none of the plan's periods has a known
   *     calendar (nothing could be evaluated);
   *   - unknown: the owner's periods whose calendar is unknown: plan label
   *     (when visible), period label, reason;
   *   - conflicts: see conflictsOf().
   */
  public function forPlan(Plan $plan, AccountInterface $account, ?Period $only = NULL): array {
    if (!$plan->access('view', $account)) {
      return ['evaluated' => FALSE, 'unknown' => [], 'conflicts' => []];
    }
    $owner = $this->owner((int) $plan->getCreatorId());
    $mine = array_filter($owner['occurrences'], static fn (array $o): bool => $o['ref']['plan'] === (int) $plan->id() && ($only === NULL || $o['ref']['period'] === (int) $only->id()));
    $evaluated = FALSE;
    foreach ($owner['periods'] as $p) {
      if ($p['plan'] === (int) $plan->id() && ($only === NULL || $p['period'] === (int) $only->id()) && $p['known']) {
        $evaluated = TRUE;
      }
    }
    return [
      'evaluated' => $evaluated,
      'unknown' => $this->visibleUnknown($owner, $account),
      'conflicts' => $this->conflictsOf($mine, $owner['index'], $account),
    ];
  }

  /**
   * The conflicts a placement would have on each cell of its period.
   *
   * The prospective destinations of a move (#130): the placement's
   * occurrences at (day, slot) against the placements of the owner's OTHER
   * periods; its own current occurrences are not counted.
   *
   * @param \Drupal\ancora_scheduling\Entity\Placement $placement
   *   The placement.
   * @param list<array{0: int, 1: int}> $cells
   *   The cells, as [day, slot] pairs.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user.
   *
   * @return array{evaluated: bool, reason: ?string, cells: array<string, list<array>>}
   *   cells: "day.slot" => conflicts (see conflictsOf()); evaluated FALSE
   *   (with the reason) when the placement's calendar is unknown.
   */
  public function forCandidates(Placement $placement, array $cells, AccountInterface $account): array {
    $plan = $placement->get('plan')->entity;
    $period = $placement->get('period')->entity;
    if (!$plan instanceof Plan || !$period instanceof Period || !$placement->access('view', $account)) {
      return ['evaluated' => FALSE, 'reason' => 'no_access', 'cells' => []];
    }
    $reason = $this->unknownReason($plan, $period);
    if ($reason !== NULL) {
      return ['evaluated' => FALSE, 'reason' => $reason, 'cells' => []];
    }
    $owner = $this->owner((int) $plan->getCreatorId());
    $type = $placement->getTemplate()?->getActivityType();
    $slots = max(1, (int) ($type?->get('duration_slots')->value ?? 1));
    $ref = [
      'plan' => (int) $plan->id(),
      'period' => (int) $period->id(),
      'key' => 'placement:' . $placement->id(),
      'teacher' => $placement->get('teacher')->target_id === NULL ? NULL : (int) $placement->get('teacher')->target_id,
      'room' => $placement->get('room')->target_id === NULL ? NULL : (int) $placement->get('room')->target_id,
      'activity' => (string) ($placement->getTemplate()?->label() ?? ''),
      'group' => (string) ($placement->get('student_group')->entity?->label() ?? ''),
    ];
    $out = [];
    foreach ($cells as [$day, $slot]) {
      $mine = $this->occurrences($plan, $period, $day, $slot, $slots, $ref + ['day' => $day, 'slot' => $slot]);
      $out["$day.$slot"] = $this->conflictsOf($mine, $owner['index'], $account);
    }
    return ['evaluated' => TRUE, 'reason' => NULL, 'cells' => $out];
  }

  /**
   * Conflicts between some occurrences and the owner's in OTHER periods.
   *
   * The index holds the owner's occurrences by resource and date, so the
   * cost is the number of occurrences sharing a resource and a date.
   *
   * @return list<array>
   *   One entry per pair of placements and resource: resource ("teacher" or
   *   "room"), resource_label, mine and other (activity, group, plan label
   *   or NULL when not visible, period label, day, slot), and occurrences
   *   (date, start and end of the common part as "HH:MM", after_end).
   */
  private function conflictsOf(array $mine, array $index, AccountInterface $account): array {
    $pairs = [];
    foreach ($mine as $a) {
      foreach (['teacher', 'room'] as $kind) {
        if ($a['ref'][$kind] === NULL) {
          continue;
        }
        foreach ($index["$kind:" . $a['ref'][$kind] . ':' . $a['date']] ?? [] as $b) {
          if ($b['ref']['period'] === $a['ref']['period'] || $b['ref']['key'] === $a['ref']['key'] || !($a['start'] < $b['end'] && $b['start'] < $a['end'])) {
            continue;
          }
          $id = $kind . '|' . $a['ref']['key'] . '|' . $b['ref']['key'];
          $pairs[$id] ??= [
            'resource' => $kind,
            'resource_id' => $a['ref'][$kind],
            'resource_label' => $this->resourceLabel($kind, $a['ref'][$kind]),
            'mine' => $this->describe($a['ref'], $account),
            'other' => $this->describe($b['ref'], $account),
            'occurrences' => [],
          ];
          $pairs[$id]['occurrences'][$a['date'] . ' ' . max($a['start'], $b['start'])] = [
            'date' => $a['date'],
            'start' => self::time(max($a['start'], $b['start'])),
            'end' => self::time(min($a['end'], $b['end'])),
            'after_end' => $a['after_end'] || $b['after_end'],
          ];
        }
      }
    }
    foreach ($pairs as &$pair) {
      ksort($pair['occurrences']);
      $pair['occurrences'] = array_values($pair['occurrences']);
    }
    unset($pair);
    return array_values($pairs);
  }

  /**
   * Every occurrence of the owner's placements, and the unknown periods.
   */
  private function owner(int $uid): array {
    if (isset($this->owners[$uid])) {
      return $this->owners[$uid];
    }
    $occurrences = [];
    $periods = [];
    $plans = $this->entityTypeManager->getStorage('ancora_plan')->loadByProperties(['uid' => $uid]);
    ksort($plans);
    foreach ($plans as $plan) {
      assert($plan instanceof Plan);
      $plan_periods = $this->entityTypeManager->getStorage('ancora_period')->loadByProperties(['plan' => $plan->id()]);
      ksort($plan_periods);
      foreach ($plan_periods as $period) {
        assert($period instanceof Period);
        $reason = $this->unknownReason($plan, $period);
        $periods[] = [
          'plan' => (int) $plan->id(),
          'period' => (int) $period->id(),
          'known' => $reason === NULL,
          'reason' => $reason,
        ];
        if ($reason !== NULL) {
          continue;
        }
        $n = 0;
        foreach ($this->reader->read($plan, $period)['scopes'] as $scope) {
          foreach ($scope['placements'] as $p) {
            $n++;
            $ref = [
              'plan' => (int) $plan->id(),
              'period' => (int) $period->id(),
              'key' => $p['placement_id'] !== NULL ? 'placement:' . $p['placement_id'] : 'row:' . $period->id() . ':' . $n,
              'teacher' => $p['teacher_id'],
              'room' => $p['room_id'],
              'activity' => (string) $p['activity'],
              'group' => (string) $p['group'],
              'day' => (int) $p['day'],
              'slot' => (int) $p['slot'],
            ];
            array_push($occurrences, ...$this->occurrences($plan, $period, (int) $p['day'], (int) $p['slot'], max(1, (int) $p['slots']), $ref));
          }
        }
      }
    }
    // By resource and date: the only occurrences that can overlap.
    $index = [];
    foreach ($occurrences as $o) {
      foreach (['teacher', 'room'] as $kind) {
        if ($o['ref'][$kind] !== NULL) {
          $index["$kind:" . $o['ref'][$kind] . ':' . $o['date']][] = $o;
        }
      }
    }
    return $this->owners[$uid] = ['occurrences' => $occurrences, 'periods' => $periods, 'index' => $index];
  }

  /**
   * The occurrences of a placement (temporal-semantics.md §2).
   */
  private function occurrences(Plan $plan, Period $period, int $day, int $slot, int $slots, array $ref): array {
    $first = new \DateTimeImmutable($plan->getStartDate() . ' 00:00:00', new \DateTimeZone('UTC'));
    $last = $plan->get('ends_on')->value;
    $length = self::centi($period->getSlotDuration());
    $out = [];
    foreach ($period->get('plan_days')->getValue() as $item) {
      $date = $first->modify(sprintf('+%d days', (int) $item['value'] - 1 + $day - 1))->format('Y-m-d');
      for ($t = $slot; $t < $slot + $slots; $t++) {
        $start = self::centi($this->inputs->slotStart($period, $t));
        $out[] = [
          'date' => $date,
          'start' => $start,
          'end' => $start + $length,
          'after_end' => $last !== NULL && $date > $last,
          'ref' => $ref,
        ];
      }
    }
    return $out;
  }

  /**
   * The owner's unknown periods, named only where the account may see them.
   */
  private function visibleUnknown(array $owner, AccountInterface $account): array {
    $out = [];
    foreach ($owner['periods'] as $p) {
      if ($p['known']) {
        continue;
      }
      $plan = $this->entityTypeManager->getStorage('ancora_plan')->load($p['plan']);
      $period = $this->entityTypeManager->getStorage('ancora_period')->load($p['period']);
      $visible = $plan !== NULL && $plan->access('view', $account);
      $out[] = [
        'plan' => $visible ? (string) $plan->label() : NULL,
        'period' => $visible ? (string) $period?->label() : NULL,
        'reason' => $p['reason'],
      ];
    }
    return $out;
  }

  /**
   * A placement reference as shown: another plan only if it may be viewed.
   */
  private function describe(array $ref, AccountInterface $account): array {
    $plan = $this->entityTypeManager->getStorage('ancora_plan')->load($ref['plan']);
    $visible = $plan !== NULL && $plan->access('view', $account);
    return [
      'plan' => $visible ? (string) $plan->label() : NULL,
      'plan_id' => $visible ? $ref['plan'] : NULL,
      'period' => $visible ? (string) ($this->entityTypeManager->getStorage('ancora_period')->load($ref['period'])?->label() ?? '') : NULL,
      'activity' => $visible ? $ref['activity'] : NULL,
      'group' => $visible ? $ref['group'] : NULL,
      'day' => $visible ? ($ref['day'] ?? NULL) : NULL,
      'slot' => $visible ? ($ref['slot'] ?? NULL) : NULL,
    ];
  }

  /**
   * The label of a teacher or room.
   */
  private function resourceLabel(string $kind, int $id): string {
    return (string) ($this->entityTypeManager->getStorage($kind === 'teacher' ? 'ancora_teacher' : 'ancora_room')->load($id)?->label() ?? '');
  }

  /**
   * Hours (2 decimal places) as exact hundredths of an hour.
   */
  public static function centi(float|string|null $hours): int {
    return (int) round((float) $hours * 100);
  }

  /**
   * Hundredths of an hour as HH:MM.
   */
  public static function time(int $centi): string {
    $minutes = (int) round($centi * 60 / 100);
    return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
  }

}
