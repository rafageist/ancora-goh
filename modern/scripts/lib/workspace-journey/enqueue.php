<?php

/**
 * @file
 * Queues a timetable request with a job source (scripts/workspace-journey.sh).
 *
 * Usage: drush php:script scripts/lib/workspace-journey/enqueue.php -- \
 *   <owner name> <plan code> <period code> <specialty code> <level code>
 *   [time limit]
 *
 * Queues the same job the request form queues (scope, source, default
 * options, the requester) and prints its id.
 */

declare(strict_types=1);

use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_scheduling\ScopeMapper;

// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
[$owner_name, $plan_code, $period_code, $specialty_code, $level_code, $limit] = $extra + array_fill(0, 6, NULL);
$etm = \Drupal::entityTypeManager();
$owner = current($etm->getStorage('user')->loadByProperties(['name' => $owner_name]));
$one = static function (string $type, array $properties) use ($etm) {
  $found = $etm->getStorage($type)->loadByProperties($properties);
  if (count($found) !== 1) {
    throw new \RuntimeException("no single $type for " . json_encode($properties));
  }
  return current($found);
};
$plan = $one('ancora_plan', ['code' => $plan_code, 'uid' => $owner->id()]);
$records = [
  $plan,
  $one('ancora_period', ['code' => $period_code, 'plan' => $plan->id()]),
  $one('ancora_specialty', ['code' => $specialty_code, 'uid' => $owner->id()]),
  $one('ancora_level', ['code' => $level_code, 'uid' => $owner->id()]),
];
$settings = \Drupal::service('ancora_engine.settings');
echo \Drupal::service('ancora_engine.job_repository')->enqueue(
  ScopeMapper::scope(...$records),
  new JobOptions(),
  $limit === NULL ? $settings->timeLimit() : (int) $limit,
  (int) $owner->id(),
  NULL,
  ScopeMapper::source(...$records),
), "\n";
