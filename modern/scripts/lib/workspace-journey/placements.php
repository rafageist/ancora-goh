<?php

/**
 * @file
 * Reads the stored placements back from Drupal (scripts/workspace-journey.sh).
 *
 * Usage: drush php:script scripts/lib/workspace-journey/placements.php.
 *
 * Prints JSON: "count"; "e2e": the E2E placement list of dev/e2e/lib.sh
 * (template/group/teacher/room/day/slot, comma-joined in template and group
 * engine id order, with the codes of the engine ids) computed from the
 * placement ENTITIES; its md5 is the E2E digest; "rows": engine id, job and
 * the list line of every placement, in entity id order.
 */

declare(strict_types=1);

use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_scheduling\Sync\SiteRows;

$rows_service = \Drupal::service(SiteRows::class);
[$rows] = $rows_service->site();
$maps = [
  'template' => SiteRows::idsByEntity($rows['plantilla_de_actividad']),
  'group' => SiteRows::idsByEntity($rows['grupo']),
  'room' => SiteRows::idsByEntity($rows['lugar']),
];
$code = static function (?string $id): string {
  $at = $id === NULL ? FALSE : strpos($id, LegacyRows::ID_SEPARATOR);
  return $id === NULL ? '' : ($at === FALSE ? $id : substr($id, $at + 1));
};
$storage = \Drupal::entityTypeManager()->getStorage('ancora_placement');
$e2e = [];
$out = [];
foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->sort('id')->execute()) as $p) {
  $template = $maps['template'][(int) $p->get('template')->target_id] ?? '?';
  $group = $maps['group'][(int) $p->get('student_group')->target_id] ?? '?';
  $room = $p->get('room')->target_id === NULL ? NULL : ($maps['room'][(int) $p->get('room')->target_id] ?? '?');
  $teacher = $p->get('teacher')->entity;
  $line = implode('/', [
    $code($template),
    $code($group),
    $teacher ? $teacher->label() : '',
    $code($room),
    $p->get('day')->value,
    $p->get('slot')->value,
  ]);
  $e2e[$template . "\0" . $group] = $line;
  $out[] = ['engine_id' => $p->get('engine_id')->value, 'job' => (int) $p->get('job')->value, 'line' => $line];
}
ksort($e2e, SORT_STRING);
echo json_encode(['count' => count($out), 'e2e' => implode(',', $e2e), 'rows' => $out], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
