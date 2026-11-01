<?php

/**
 * @file
 * The browser CI fixture (#141): drush php:script, on a DISPOSABLE site.
 *
 * Run as "drush php:script scripts/lib/browser/fixture.php".
 *
 * Builds the workspace fixture (WorkspaceFixtureTrait, in the tests of
 * ancora_scheduling) for the "planner" account and a copy for "other"
 * (another owner, for the forged-id checks), and prints the ids the browser
 * tests need as JSON.
 * Synthetic records only.
 */

declare(strict_types=1);

use Drupal\Tests\ancora_scheduling\Traits\WorkspaceFixtureTrait;

require_once DRUPAL_ROOT . '/modules/custom/ancora_scheduling/tests/src/Traits/WorkspaceFixtureTrait.php';

$users = \Drupal::entityTypeManager()->getStorage('user');
$uid = static fn (string $name): int => (int) current($users->loadByProperties(['name' => $name]))->id();
$builder = new class() {
  use WorkspaceFixtureTrait {
    workspaceFixture as public;

  }
};
$r = $builder->workspaceFixture($uid('planner'));
$o = $builder->workspaceFixture($uid('other'), 'O');
$ids = static fn (array $records, array $keys): array => array_map(static fn (string $k): int => (int) $records[$k]->id(), array_combine($keys, $keys));
echo json_encode([
  'planner' => $ids($r, [
    'plan:A', 'plan:B', 'plan:U', 'period:A', 'period:A2', 'period:U', 'TA', 'TB', 'TC', 'L1', 'L2',
    'GA1', 'GA2', 'GA3', 'A1', 'A2', 'A3', 'A4', 'U1', 'U2',
  ]),
  'other' => $ids($o, ['plan:A', 'period:A', 'TA', 'A1']),
]), "\n";
