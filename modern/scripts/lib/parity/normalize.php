<?php

/**
 * @file
 * Normalizer of the differential parity harness (scripts/parity-diff.sh).
 *
 * Reads the legacy academic tables of one database (the legacy application's
 * database, or a site's engine database after the Drupal sync) and prints
 * them as canonical JSON in which every generated id is replaced by a stable
 * key:
 * - a row that is a scenario record gets the record's key, found through its
 *   natural key (owner or plan, and name; see Scenario::naturalKey());
 * - rows the legacy triggers or handlers create get a key derived from the
 *   keys they reference (a breakdown: "<subject>@<period>"; a plan day:
 *   "<period>@<day>");
 * - "creador" becomes the scenario user key, through the users map the
 *   driver wrote (legacy or engine user id => user key);
 * - a row that matches no scenario record keeps a visible "?..." key.
 *
 * Nothing is ignored silently: every column of the 17 tables is printed,
 * except the generated ids themselves. The placements of each scenario run
 * are summarised by scope: tst.metrics(), a digest of the normalized
 * placements and the legacy E2E digest (dev/e2e/lib.sh, e2e_placements).
 *
 * Usage:
 *
 * @code
 * php normalize.php <scenario.json> <database> <users.json> > canonical.json
 * @endcode
 */

declare(strict_types=1);

use Ancora\Parity\Normalizer;
use Ancora\Parity\Scenario;
use PgSql\Connection;

require __DIR__ . '/src/Scenario.php';
require __DIR__ . '/src/Normalizer.php';

[, $scenario_file, $database, $users_file] = $argv + array_fill(0, 4, NULL);
if ($users_file === NULL) {
  fwrite(STDERR, "usage: normalize.php <scenario.json> <database> <users.json>\n");
  exit(2);
}

$db = pg_connect('dbname=' . $database);
if (!$db instanceof Connection) {
  fwrite(STDERR, "cannot connect to $database\n");
  exit(1);
}
$users = json_decode((string) file_get_contents($users_file), TRUE, 8, JSON_THROW_ON_ERROR);
$normalizer = new Normalizer(Scenario::load($scenario_file), $db, $users);
echo json_encode($normalizer->canonical(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
