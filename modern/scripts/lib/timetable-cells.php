<?php

/**
 * @file
 * Lists the placements of a timetable page (scripts/r1-journey.sh).
 *
 * Usage: php timetable-cells.php <page.html> <final|draft>
 *
 * Prints one line per placement found in a timetable of that kind:
 * day/slot/group code/teacher code/room code/group/teacher/room. Only
 * placements inside the grid cells (data-day, data-slot) are listed.
 */

declare(strict_types=1);

[, $file, $kind] = $argv + [NULL, NULL, NULL];
if (!is_string($file) || !in_array($kind, ['final', 'draft'], TRUE)) {
  fwrite(STDERR, "usage: timetable-cells.php <page.html> <final|draft>\n");
  exit(2);
}
$document = new DOMDocument();
libxml_use_internal_errors(TRUE);
$document->loadHTML((string) file_get_contents($file));
$xpath = new DOMXPath($document);
$text = static fn (DOMNode $node, string $class): string => trim((string) $xpath->evaluate("string(.//span[contains(concat(' ', @class, ' '), ' $class ')])", $node));
foreach ($xpath->query("//*[@data-timetable='$kind']//td[@data-day]") as $cell) {
  foreach ($xpath->query(".//div[contains(concat(' ', @class, ' '), ' ancora-placement ')]", $cell) as $placement) {
    echo implode('/', [
      $cell->getAttribute('data-day'),
      $cell->getAttribute('data-slot'),
      $placement->getAttribute('data-group'),
      $placement->getAttribute('data-teacher'),
      $placement->getAttribute('data-room'),
      $text($placement, 'ancora-placement__group'),
      $text($placement, 'ancora-placement__teacher'),
      $text($placement, 'ancora-placement__room'),
    ]), "\n";
  }
}
