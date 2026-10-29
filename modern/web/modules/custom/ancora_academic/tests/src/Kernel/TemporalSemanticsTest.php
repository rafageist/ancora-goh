<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\PeriodInputs;
use PHPUnit\Framework\Attributes\Group;

/**
 * The calendar of a placement, from Drupal data only (#133, phase 1).
 *
 * The Drupal side of docs/modernization/temporal-semantics.md, with the same
 * numbers as characterization case 151 (the legacy real-date formula as
 * evidence). occurrences() is the SPECIFICATION of how a placement becomes
 * half-open intervals [start, end) on calendar dates; it is test code here,
 * not a service.
 */
#[Group('ancora')]
final class TemporalSemanticsTest extends AcademicKernelTestBase {

  /**
   * A dated plan with one period, its breaks and periodicity.
   */
  private function plan(string $code, ?string $starts, ?string $ends, array $period): array {
    $plan = $this->newRecord('ancora_plan', [
      'label' => "Plan $code",
      'code' => $code,
      'starts_on' => $starts,
      'ends_on' => $ends,
    ]);
    $plan->save();
    $p = $this->newRecord('ancora_period', $period + [
      'plan' => $plan->id(),
      'label' => "Period $code",
      'code' => "PER$code",
    ]);
    $this->assertSame([], $this->violationFields($p));
    $p->save();
    foreach ($period['breaks'] ?? [] as $after => $length) {
      $this->newRecord('ancora_break', [
        'plan' => $plan->id(),
        'period' => $p->id(),
        'after_slot' => $after,
        'length' => $length,
      ])->save();
    }
    return [$plan, $p];
  }

  /**
   * Hours (a decimal with 2 places) as exact hundredths of an hour.
   */
  private static function centi(float|string $hours): int {
    return (int) round((float) $hours * 100);
  }

  /**
   * The specification: the occurrences of a placement.
   *
   * One interval per booked slot and repetition (case 151): the date is the
   * plan's first day + (periodicity day - 1) + (period day - 1); the slot runs
   * [slotStart, slotStart + slot length) in hundredths of an hour, wall-clock
   * time of the site. NULL when the calendar is unknown: no first day, or no
   * periodicity day.
   *
   * @return list<array{0: string, 1: int, 2: int}>|null
   *   Date (Y-m-d), start and end (hundredths of an hour), sorted.
   */
  private function occurrences(Plan $plan, Period $period, int $day, int $slot, int $duration): ?array {
    $first = $plan->getStartDate();
    $repetitions = array_map('intval', array_column($period->get('plan_days')->getValue(), 'value'));
    if ($first === NULL || $repetitions === []) {
      return NULL;
    }
    $inputs = $this->container->get(PeriodInputs::class);
    $out = [];
    foreach ($repetitions as $repetition) {
      $date = (new \DateTimeImmutable($first . ' 00:00:00', new \DateTimeZone('UTC')))->modify(sprintf('+%d days', $repetition - 1 + $day - 1))->format('Y-m-d');
      for ($s = $slot; $s < $slot + $duration; $s++) {
        $start = self::centi($inputs->slotStart($period, $s));
        $out[] = [$date, $start, $start + self::centi($period->getSlotDuration())];
      }
    }
    sort($out);
    return $out;
  }

  /**
   * The common parts of two occurrence lists.
   *
   * Same date, a.start < b.end and b.start < a.end (half-open: touching
   * intervals do not overlap).
   */
  private static function overlaps(array $a, array $b): array {
    $out = [];
    foreach ($a as [$date, $start, $end]) {
      foreach ($b as [$d2, $s2, $e2]) {
        if ($date === $d2 && $start < $e2 && $s2 < $end) {
          $out[] = sprintf('%s %d-%d', $date, max($start, $s2), min($end, $e2));
        }
      }
    }
    return $out;
  }

  /**
   * Day numbers and plan length, as the legacy form computes them.
   */
  public function testDayNumbersAndPlanLength(): void {
    for ($d = new \DateTimeImmutable('2000-01-01'); $d < new \DateTimeImmutable('2032-01-01'); $d = $d->modify('+37 days')) {
      $this->assertSame((int) $d->diff(new \DateTimeImmutable('1999-12-31'))->days, LegacyRows::legacyDayNumber($d->format('Y-m-d')), $d->format('Y-m-d'));
    }
    [$plan] = $this->plan('A', '2026-09-07', '2026-10-04', [
      'days' => 5,
      'slots_per_day' => 3,
      'start_hour' => 8,
      'slot_duration' => 1.5,
    ]);
    $this->assertSame(28, $plan->getDurationDays(), 'the last day minus the first + 1, as cant_dias');
    [$undated] = $this->plan('U', NULL, NULL, [
      'days' => 5,
      'slots_per_day' => 3,
      'start_hour' => 8,
      'slot_duration' => 1.5,
    ]);
    $this->assertNull($undated->getStartDate());
    $this->assertSame(30, $undated->getDurationDays(), 'without dates the length is only the default, not a fact');
  }

  /**
   * What the engine receives: periodicity rows, and 0 for a missing date.
   */
  public function testExportOfTheCalendar(): void {
    [$plan] = $this->plan('A', '2026-09-07', '2026-10-04', [
      'days' => 5,
      'slots_per_day' => 3,
      'start_hour' => 8,
      'slot_duration' => 1.5,
      'plan_days' => [1, 8, 27],
    ]);
    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan), 'u1');
    $this->assertSame(LegacyRows::legacyDayNumber('2026-09-07'), $rows['planificacion'][0]['fecha_inicial']);
    $this->assertSame([1, 8, 27], array_column($rows['periodicidad'], 'dia'));
    $this->assertSame([5, 90], [$rows['periodo'][0]['hora_inicial'], $rows['periodo'][0]['duracion_del_turno']], 'Q-20: the number of days as the start hour; the slot length in minutes');
    [$undated] = $this->plan('U', NULL, NULL, [
      'days' => 5,
      'slots_per_day' => 3,
      'start_hour' => 8,
      'slot_duration' => 1.5,
      'plan_days' => [1],
    ]);
    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($undated), 'u1');
    $this->assertSame(0, $rows['planificacion'][0]['fecha_inicial'], 'no date: 0, which legacy reads as 1999-12-31 (case 151)');
    $this->assertNull($this->occurrences($undated, $this->byCode('ancora_period', 'PERU', $undated), 1, 1, 1), 'Drupal treats it as unknown');
  }

  /**
   * Slot times with breaks: the numbers of case 151, in hours.
   */
  public function testSlotTimes(): void {
    [, $period] = $this->plan('A', '2026-09-07', '2026-10-04', [
      'days' => 5,
      'slots_per_day' => 3,
      'start_hour' => 8,
      'slot_duration' => 1.5,
      'plan_days' => [1],
      'breaks' => [1 => 0.5],
    ]);
    $inputs = $this->container->get(PeriodInputs::class);
    $this->assertSame([
      800,
      1000,
      1150,
    ], array_map(fn (int $s): int => self::centi($inputs->slotStart($period, $s)), [1, 2, 3]), 'minutes 480, 600, 690 in case 151');
    // Hundredths of an hour are exact: 0.33-hour slots touch, never overlap.
    [, $thin] = $this->plan('T', '2026-09-07', '2026-10-04', [
      'days' => 5,
      'slots_per_day' => 3,
      'start_hour' => 8,
      'slot_duration' => 0.33,
      'plan_days' => [1],
    ]);
    $starts = array_map(fn (int $s): int => self::centi($inputs->slotStart($thin, $s)), [1, 2, 3]);
    $this->assertSame([800, 833, 866], $starts);
    $this->assertSame($starts[1], $starts[0] + self::centi($thin->getSlotDuration()), 'slot 1 ends exactly where slot 2 starts');
  }

  /**
   * The specification on the #133 fixture.
   *
   * Overlap, adjacency, disjoint dates, different slot lengths, breaks,
   * durations and unknown calendars.
   */
  public function testOccurrencesAndOverlaps(): void {
    // A: Monday 2026-09-07, weekly on plan days 1 and 8; 08:00, 1.5 h slots,
    // a 0.5 h break after slot 1. B: Monday 2026-09-14 (A's second week);
    // 08:30, 1 h slots. C: November, no common date.
    [$a, $pa] = $this->plan('A', '2026-09-07', '2026-09-20', [
      'days' => 5,
      'slots_per_day' => 4,
      'start_hour' => 8,
      'slot_duration' => 1.5,
      'plan_days' => [1, 8],
      'breaks' => [1 => 0.5],
    ]);
    [$b, $pb] = $this->plan('B', '2026-09-14', '2026-09-18', [
      'days' => 5,
      'slots_per_day' => 6,
      'start_hour' => 8.5,
      'slot_duration' => 1,
      'plan_days' => [1],
    ]);
    [$c, $pc] = $this->plan('C', '2026-11-02', '2026-11-06', [
      'days' => 5,
      'slots_per_day' => 6,
      'start_hour' => 8.5,
      'slot_duration' => 1,
      'plan_days' => [1],
    ]);

    // A: Tuesday (period day 2), slot 2 = [10:00, 11:30) in weeks 1 and 2.
    $a2 = $this->occurrences($a, $pa, 2, 2, 1);
    $this->assertSame([['2026-09-08', 1000, 1150], ['2026-09-15', 1000, 1150]], $a2);
    // B: Tuesday 2026-09-15, slot 2 = [09:30, 10:30): overlaps A week 2 only.
    $b2 = $this->occurrences($b, $pb, 2, 2, 1);
    $this->assertSame(['2026-09-15 1000-1050'], self::overlaps($a2, $b2), 'partial overlap of different slot lengths, one date only');
    // Adjacency: A slot 1 ends 09:30, B slot 2 starts 09:30: no overlap.
    $this->assertSame([], self::overlaps($this->occurrences($a, $pa, 2, 1, 1), $b2), 'touching intervals do not overlap');
    // The break: A slot 1 [08:00, 09:30) and A slot 2 [10:00, 11:30) leave
    // 09:30-10:00 free; B slot 2 [09:30, 10:30) shares only 10:00-10:30.
    // A 2-slot activity is two intervals, the break not booked.
    $this->assertSame([
      ['2026-09-08', 800, 950],
      ['2026-09-08', 1000, 1150],
      ['2026-09-15', 800, 950],
      ['2026-09-15', 1000, 1150],
    ], $this->occurrences($a, $pa, 2, 1, 2));
    // Same period day, other weekday of B: no common date.
    $this->assertSame([], self::overlaps($a2, $this->occurrences($b, $pb, 3, 2, 1)));
    // Disjoint plan dates.
    $this->assertSame([], self::overlaps($a2, $this->occurrences($c, $pc, 2, 2, 1)));
    // Unknown calendars: no first day, or no periodicity day.
    [$u, $pu] = $this->plan('U', NULL, NULL, [
      'days' => 5,
      'slots_per_day' => 4,
      'start_hour' => 8,
      'slot_duration' => 1,
      'plan_days' => [1],
    ]);
    $this->assertNull($this->occurrences($u, $pu, 2, 2, 1));
    [$n, $pn] = $this->plan('N', '2026-09-14', '2026-09-18', [
      'days' => 5,
      'slots_per_day' => 4,
      'start_hour' => 8,
      'slot_duration' => 1,
    ]);
    $this->assertNull($this->occurrences($n, $pn, 2, 2, 1));
    // A repetition that passes the plan's last day is still dated (legacy
    // does not clip): plan A ends 2026-09-20; a periodicity on plan day 12
    // puts period day 5 on 2026-09-22.
    $pa->set('plan_days', [1, 8, 12])->save();
    $this->assertSame(['2026-09-11', '2026-09-18', '2026-09-22'], array_column($this->occurrences($a, $pa, 5, 2, 1), 0), "plan day 12 + 4 = day 16, after the plan's last day (2026-09-20)");
  }

}
