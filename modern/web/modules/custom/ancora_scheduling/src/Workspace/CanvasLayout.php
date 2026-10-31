<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Workspace;

/**
 * The time grid of the planning workspace canvas (#141): pure layout.
 *
 * One row per slot, and one row per break between two slots (a break after
 * the last slot of the day is not drawn). A placement is ONE block from the
 * row of its first slot to the row of its last slot, so a multi-slot
 * activity across a break covers the break row too, and the block says
 * where its break is. Blocks that overlap in a day get lanes, so none hides
 * another (the "all activities" view of a period has many per cell).
 *
 * Times come from a callback (PeriodInputs::slotStart(), breaks included),
 * so this class needs no service and is tested on its own.
 */
final class CanvasLayout {

  /**
   * Lays out a period's placements.
   *
   * @param int $days
   *   The period's days.
   * @param int $slots
   *   The period's slots per day.
   * @param float $slot_length
   *   The slot length in hours.
   * @param array<int, float> $breaks
   *   Break length in hours by the slot it follows.
   * @param callable(int): float $start
   *   The start hour of a slot, breaks included.
   * @param list<array> $placements
   *   TimetableReader placements (day, slot, slots, ...).
   *
   * @return array
   *   - rows: list of rows, each with type ("slot" or "break"), the grid
   *     line it starts on (1-based, after the day header row), and for a
   *     slot its number, start and end, for a break its start and end;
   *   - days: list of days, each with number, lanes and blocks; a block is
   *     the placement plus row_start and row_end (grid lines), lane,
   *     first and last slot, start and end ("HH:MM"), breaks (list of
   *     start, end and the block-relative row), and overflow (TRUE when it
   *     runs past the last slot of the day).
   */
  public static function build(int $days, int $slots, float $slot_length, array $breaks, callable $start, array $placements): array {
    $days = max(1, $days);
    $slots = max(1, $slots);
    // Line 1 is the day header; the first slot starts on line 2.
    $line = 2;
    $rows = [];
    $slot_line = [];
    for ($s = 1; $s <= $slots; $s++) {
      $begin = $start($s);
      $slot_line[$s] = $line;
      $rows[] = [
        'type' => 'slot',
        'line' => $line++,
        'number' => $s,
        'start' => self::hour($begin),
        'end' => self::hour($begin + $slot_length),
      ];
      if ($s < $slots && isset($breaks[$s]) && $breaks[$s] > 0) {
        $rows[] = [
          'type' => 'break',
          'line' => $line++,
          'after' => $s,
          'start' => self::hour($begin + $slot_length),
          'end' => self::hour($begin + $slot_length + $breaks[$s]),
        ];
      }
    }

    $by_day = array_fill(1, $days, []);
    foreach ($placements as $p) {
      $day = (int) $p['day'];
      $first = (int) $p['slot'];
      if ($day < 1 || $day > $days || $first < 1 || $first > $slots) {
        continue;
      }
      $length = max(1, (int) ($p['slots'] ?? 1));
      $last = min($slots, $first + $length - 1);
      $block_breaks = [];
      for ($s = $first; $s < $last; $s++) {
        if (isset($breaks[$s]) && $breaks[$s] > 0) {
          $begin = $start($s) + $slot_length;
          $block_breaks[] = [
            'start' => self::hour($begin),
            'end' => self::hour($begin + $breaks[$s]),
            // The break's row inside the block's own rows (1-based).
            'row' => $slot_line[$s] + 1 - $slot_line[$first] + 1,
          ];
        }
      }
      $by_day[$day][] = $p + [
        'first_slot' => $first,
        'last_slot' => $last,
        'row_start' => $slot_line[$first],
        'row_end' => $slot_line[$last] + 1,
        'start' => self::hour($start($first)),
        'end' => self::hour($start($last) + $slot_length),
        'breaks' => $block_breaks,
        'overflow' => $first + $length - 1 > $slots,
      ];
    }

    $out = [];
    foreach ($by_day as $day => $blocks) {
      // By start, the longest first, then by id: a stable order.
      usort($blocks, static fn (array $a, array $b): int => [$a['row_start'], $b['row_end'], $a['placement_id'] ?? 0]
        <=> [$b['row_start'], $a['row_end'], $b['placement_id'] ?? 0]);
      // Lanes: the first lane whose last block ended before this one starts.
      $lane_end = [];
      foreach ($blocks as &$block) {
        $lane = 1;
        while (isset($lane_end[$lane]) && $lane_end[$lane] > $block['row_start']) {
          $lane++;
        }
        $lane_end[$lane] = $block['row_end'];
        $block['lane'] = $lane;
      }
      unset($block);
      $out[] = [
        'number' => $day,
        'lanes' => max(1, count($lane_end)),
        'blocks' => $blocks,
      ];
    }
    return ['rows' => $rows, 'days' => $out, 'lines' => $line];
  }

  /**
   * Hours as "HH:MM".
   */
  public static function hour(float $hours): string {
    $minutes = (int) round($hours * 60);
    return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
  }

}
