<?php

/**
 * @file
 * Round trip of the workspace mode through the real worker (issue #95).
 *
 * Run by scripts/parity-diff.sh --workspace with ANCORA_ENGINE_MODE=workspace
 * and the site's engine worker role. For every timetable request of the
 * scenario, in order, a new job with the same scope, options, source and
 * requester is queued and the worker runs it once
 * (Worker::runOnce(): workspace, generar(), publication as placement
 * entities). After each job the placement ENTITIES of its scope are read
 * back and written in the natural-key form of wsproof.outcome() (template,
 * group, teacher, room, day, slot, creator, and "+" with the other resources
 * when the placement has any, D9d), so workspace-compare.php can compare
 * them placement by placement with the legacy oracle. Every job after the
 * first loads the placements and resource uses the earlier jobs published
 * (continuation, "resource_uses_loaded").
 *
 * Usage (drush php:script):
 *
 * @code
 * drush php:script scripts/lib/parity/workspace-roundtrip.php -- <scenario.json> <out dir>
 * @endcode
 *
 * Writes <out dir>/roundtrip.json.
 */

declare(strict_types=1);

use Drupal\ancora_scheduling\ScopeMapper;

// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
[$rt_scenario_file, $rt_out] = $extra + [NULL, NULL];
$rt_scenario = json_decode((string) file_get_contents($rt_scenario_file), TRUE, 64, JSON_THROW_ON_ERROR);
$etm = \Drupal::entityTypeManager();
$jobs = \Drupal::service('ancora_engine.job_repository');
$worker = \Drupal::service('ancora_engine.worker');
if (!\Drupal::service('ancora_engine.settings')->isWorkspace()) {
  throw new \RuntimeException('Run with ANCORA_ENGINE_MODE=workspace.');
}
$u = static fn ($uid): string => '{U:' . ScopeMapper::creatorOf((int) $uid) . '}';
$plan_key = static fn ($plan): string => $u($plan->getCreatorId()) . '/' . $plan->label();
$owned = static fn ($record): string => $record === NULL ? '(none)' : $u($record->getCreatorId()) . '/' . $record->label();

// Scenario key of a record by type and code (code defaults to the key).
$rt_codes = [];
foreach ($rt_scenario['records'] as $r) {
  $rt_codes[$r['type']][$r['code'] ?? $r['key']] ??= $r['key'];
}
$rt_key = static fn (string $type, $record): string => $record === NULL ? '(none)' : ($rt_codes[$type][$record->getCode()] ?? '?' . $record->getCode());

// Scenario key of a template by subject key, period key and sequence.
$rt_template = static function (string $subject, string $period, int $sequence) use ($rt_scenario): string {
  foreach ($rt_scenario['records'] as $r) {
    if ($r['type'] === 'activity_template' && $r['subject'] === $subject && $r['period'] === $period && (int) $r['sequence'] === $sequence) {
      return $r['key'];
    }
  }
  return '?';
};

$originals = array_column(\Drupal::database()->query('SELECT id FROM {ancora_engine_job} ORDER BY id')->fetchAll(), 'id');
$out = ['runs' => []];
foreach ($rt_scenario['runs'] as $i => $run) {
  $original = $jobs->load((int) $originals[$i]);
  $id = $jobs->enqueue($original->scope, $original->options, $original->timeLimit, $original->requestedBy, NULL, $original->source);
  $worker->runOnce('parity-roundtrip');
  $job = $jobs->load($id);
  \Drupal::service('entity.memory_cache')->deleteAll();
  $storage = $etm->getStorage('ancora_placement');
  $ids = $storage->getQuery()->accessCheck(FALSE)
    ->condition('period', $original->source->periodId)
    ->condition('template.entity:ancora_activity_template.subject.entity:ancora_subject.specialty', $original->source->specialtyId)
    ->condition('template.entity:ancora_activity_template.subject.entity:ancora_subject.level', $original->source->levelId)
    ->execute();
  $lines = [];
  $e2e = [];
  foreach ($storage->loadMultiple($ids) as $p) {
    $template = $p->getTemplate();
    $subject = $template->getSubject();
    $period = $template->getPeriod();
    $group = $p->get('student_group')->entity;
    $lines[] = $u($subject->getCreatorId()) . '/' . $subject->label() . '@' . $plan_key($period->get('plan')->entity) . '/' . $period->label() . '#' . $template->getSequence()
      . '|' . $plan_key($group->get('plan')->entity) . '/' . $group->label()
      . '|' . $owned($p->get('teacher')->entity) . '|' . $owned($p->get('room')->entity)
      . '@' . $p->get('day')->value . '.' . $p->get('slot')->value . '|' . $u($p->getCreatorId())
      . ($p->get('other_resources')->isEmpty() ? '' : '+' . implode(',', array_map($owned, $p->get('other_resources')->referencedEntities())));
    // The E2E list as Normalizer::runs() builds it: scenario keys of
    // template, group and room, the teacher's label, day, slot.
    $tkey = $rt_template($rt_codes['subject'][$subject->getCode()] ?? '', $rt_codes['period'][$period->getCode()] ?? '', (int) $template->getSequence());
    $gkey = $rt_key('student_group', $group);
    $teacher = $p->get('teacher')->entity;
    $e2e[$tkey . "\0" . $gkey] = implode('/', [
      $tkey,
      $gkey,
      $teacher ? $teacher->label() : '(none)',
      $rt_key('room', $p->get('room')->entity),
      $p->get('day')->value,
      $p->get('slot')->value,
    ]);
  }
  ksort($e2e, SORT_STRING);
  sort($lines, SORT_STRING);
  $out['runs'][] = [
    'key' => $run['key'],
    'job' => $id,
    'state' => $job->state->value,
    'error' => $job->error,
    'publication' => $job->result['input']['publication'] ?? NULL,
    'placements_loaded' => $job->result['input']['placements_loaded'] ?? NULL,
    'resource_uses_loaded' => $job->result['input']['resource_uses_loaded'] ?? NULL,
    'digest' => md5(implode(',', $e2e)),
    'placements' => $lines,
  ];
  printf("ok    round trip %s: job %d %s, published %s, %d placements of the scope in Drupal\n", $run['key'], $id, $job->state->value, json_encode($job->result['input']['publication'] ?? NULL), count($lines));
}
$out['total_placements'] = (int) $etm->getStorage('ancora_placement')->getQuery()->accessCheck(FALSE)->count()->execute();
file_put_contents("$rt_out/roundtrip.json", json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
