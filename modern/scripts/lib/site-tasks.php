<?php

/**
 * @file
 * Site tasks of the operator tool (issue #72), run with drush php:script.
 *
 * Usage (scripts/ancora-site.sh calls it; it is not meant to be run alone):
 *
 * @code
 * drush --uri=<site URI> php:script scripts/lib/site-tasks.php -- <task>
 * @endcode
 *
 * Tasks:
 * - runtime-tables: create now every table that Drupal would otherwise create
 *   lazily on first use (cache bins, flood, queue, batch, semaphore, cache
 *   tags, key/value stores). Run as the owner role after install and after
 *   every update, because the runtime role has no CREATE on the schema.
 * - admin-blocked: block the first administrator (uid 1) and remove its
 *   password, so the account cannot log in until the operator issues a
 *   one-time login link (admin-link). Prints the account name.
 * - admin-state: prints "<name> <status> <password set: yes|no>" for uid 1.
 * - maintenance-on, maintenance-off: Drupal maintenance mode.
 * - end-sessions: delete every session of the site.
 *
 * Migration to the single-database layout (#96, ancora-site.sh
 * migrate-single-db and rollback-single-db):
 * - jobs-idle: exit status 1 while a job is queued or running.
 * - job-max: the highest job id.
 * - checksums [max job id]: JSON, row count and md5 of every table of every
 *   content entity type except the placements, of the configuration and of
 *   the job history up to the given id.
 * - placements-check, placements-import: the engine database's placements
 *   as placement entities (TwoDatabaseImport): a report, or the import in
 *   one transaction. Exit status 1 when a placement cannot be imported.
 * - placements-foreign: the number of placement entities that are not rows
 *   of the engine database.
 * - placements-delete: every placement entity (Entity API, one transaction).
 * - engine-check: the engine is reachable with the site's settings.
 */

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Queue\DatabaseQueue;

// Set by drush php:script.
// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
$ancora_task = $extra[0] ?? '';

switch ($ancora_task) {
  case 'runtime-tables':
    $connection = Database::getConnection();
    $tables = static fn (): array => $connection->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")->fetchCol();
    $before = $tables();
    // Every core storage that creates its table on first use has a protected
    // ensureTableExists() (ensureBinExists() for cache bins); calling it now
    // is exactly what the first use would do, only as the owner role.
    $ensure = static function (object $storage, string $method = 'ensureTableExists'): void {
      (new \ReflectionMethod($storage, $method))->invoke($storage);
    };
    $container = \Drupal::getContainer();
    // Every bin, whatever its configured backend: the chained fast backends
    // keep their consistent copy in the database. Some bins are deliberately
    // not tagged (cache.file_parsing), so the service files of core and of
    // every installed module are read as well.
    $bins = array_values($container->getParameter('cache_bins'));
    $service_files = [DRUPAL_ROOT . '/core/core.services.yml'];
    foreach (\Drupal::moduleHandler()->getModuleList() as $name => $extension) {
      $service_files[] = DRUPAL_ROOT . '/' . $extension->getPath() . "/$name.services.yml";
    }
    foreach (array_filter($service_files, 'is_file') as $file) {
      foreach (Yaml::decode(file_get_contents($file))['services'] ?? [] as $definition) {
        $factory = $definition['factory'] ?? NULL;
        $memory = in_array('cache.bin.memory', array_column($definition['tags'] ?? [], 'name'), TRUE);
        if (!$memory && in_array($factory, [['@cache_factory', 'get'], 'cache_factory:get'], TRUE) && is_string($definition['arguments'][0] ?? NULL)) {
          $bins[] = $definition['arguments'][0];
        }
      }
    }
    $database_cache = $container->get('cache.backend.database');
    foreach (array_unique($bins) as $bin) {
      $ensure($database_cache->get($bin), 'ensureBinExists');
    }
    $storages = [
      'cache_tags.invalidator.checksum',
      'flood',
      'batch.storage',
      'lock',
      'lock.persistent',
      'session_handler.storage',
      'router.dumper',
    ];
    foreach ($storages as $service_id) {
      $service = $container->get($service_id);
      // Some of these services are lazy proxies; the table belongs to the
      // real object behind them.
      if (method_exists($service, 'lazyLoadItself')) {
        $service = (new \ReflectionMethod($service, 'lazyLoadItself'))->invoke($service);
      }
      $ensure($service);
    }
    $ensure(\Drupal::keyValue('ancora_probe'));
    $ensure(\Drupal::keyValueExpirable('ancora_probe'));
    $ensure(new DatabaseQueue('ancora_probe', $connection));
    $created = array_values(array_diff($tables(), $before));
    print 'runtime tables: ' . ($created === [] ? 'all present' : 'created ' . implode(', ', $created)) . "\n";
    break;

  case 'admin-blocked':
    $connection = Database::getConnection();
    $connection->update('users_field_data')
      ->fields(['status' => 0, 'pass' => NULL])
      ->condition('uid', 1)
      ->execute();
    \Drupal::entityTypeManager()->getStorage('user')->resetCache([1]);
    if ($connection->schema()->tableExists('sessions')) {
      $connection->delete('sessions')->condition('uid', 1)->execute();
    }
    print $connection->select('users_field_data', 'u')->fields('u', ['name'])->condition('uid', 1)->execute()->fetchField() . "\n";
    break;

  case 'admin-state':
    $row = Database::getConnection()->select('users_field_data', 'u')
      ->fields('u', ['name', 'status', 'pass'])
      ->condition('uid', 1)
      ->execute()
      ->fetchAssoc();
    printf("%s %s %s\n", $row['name'], $row['status'] ? 'active' : 'blocked', ($row['pass'] ?? '') === '' ? 'no-password' : 'password');
    break;

  case 'maintenance-on':
  case 'maintenance-off':
    \Drupal::state()->set('system.maintenance_mode', $ancora_task === 'maintenance-on');
    // The page cache answers before the maintenance mode check runs.
    \Drupal::cache('page')->deleteAll();
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['rendered']);
    print $ancora_task . "\n";
    break;

  case 'end-sessions':
    $connection = Database::getConnection();
    $count = $connection->schema()->tableExists('sessions') ? $connection->delete('sessions')->execute() : 0;
    print "sessions ended: $count\n";
    break;

  case 'jobs-idle':
    // Exit status 1 while a job of the site is queued or running.
    $busy = (int) Database::getConnection()->select('ancora_engine_job', 'j')
      ->condition('state', ['queued', 'running'], 'IN')
      ->countQuery()->execute()->fetchField();
    print "jobs queued or running: $busy\n";
    if ($busy > 0) {
      exit(1);
    }
    break;

  case 'job-max':
    print (int) Database::getConnection()->query('SELECT coalesce(max(id), 0) FROM {ancora_engine_job}')->fetchField() . "\n";
    break;

  case 'checksums':
    // Row count and md5 of every table of every content entity type except
    // the placements, of the configuration and of the job history up to the
    // given job id: the records a migration must leave untouched (#96).
    $connection = Database::getConnection();
    $max = (int) ($extra[1] ?? PHP_INT_MAX);
    $entity_type_manager = \Drupal::entityTypeManager();
    $tables = ['config', 'users_data'];
    foreach ($entity_type_manager->getDefinitions() as $definition) {
      if (!$definition instanceof ContentEntityTypeInterface || $definition->id() === 'ancora_placement') {
        continue;
      }
      $storage = $entity_type_manager->getStorage($definition->id());
      if ($storage instanceof SqlContentEntityStorage) {
        $tables = array_merge($tables, $storage->getTableMapping()->getTableNames());
      }
    }
    $tables = array_unique($tables);
    sort($tables);
    $sums = [];
    foreach ($tables as $table) {
      if ($connection->schema()->tableExists($table)) {
        $row = $connection->query("SELECT count(*) AS n, md5(coalesce(string_agg(x::text, E'\\n' ORDER BY x::text), '')) AS h FROM {" . $table . '} x')->fetchAssoc();
        $sums[$table] = $row['n'] . ' ' . $row['h'];
      }
    }
    $row = $connection->query("SELECT count(*) AS n, md5(coalesce(string_agg(x::text, E'\\n' ORDER BY x.id), '')) AS h FROM {ancora_engine_job} x WHERE x.id <= :max", [':max' => $max])->fetchAssoc();
    $sums["ancora_engine_job (id <= $max)"] = $row['n'] . ' ' . $row['h'];
    print json_encode($sums, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    break;

  case 'placements-check':
  case 'placements-import':
    // The two-database placements as placement entities (#96): a report
    // (check), or the import in one transaction (import).
    $import = \Drupal::service('ancora_scheduling.two_database_import');
    $read = $import->read();
    $plan = $import->plan($read);
    $report = [
      'rows' => count($read['rows']),
      'other_resources' => $read['resources'],
      'leftover_cancel_requests' => $read['cancel_requests'],
      'jobs' => array_count_values(array_map('strval', array_column($plan['placements'], 'job'))),
      'problems' => $plan['problems'],
    ];
    if ($ancora_task === 'placements-import' && $plan['problems'] === []) {
      $fail_after = getenv('ANCORA_MIGRATION_TEST_FAIL_AFTER');
      $report += $import->import($plan, ctype_digit((string) $fail_after) ? (int) $fail_after : NULL);
    }
    print json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    if ($plan['problems'] !== []) {
      exit(1);
    }
    break;

  case 'placements-foreign':
    // Placement entities that are not rows of the engine database: made by a
    // workspace run after the migration.
    $import = \Drupal::service('ancora_scheduling.two_database_import');
    print $import->foreign($import->read()) . "\n";
    break;

  case 'placements-delete':
    // Rollback of a migration: every placement entity, through the Entity
    // API, in one transaction.
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_placement');
    $transaction = Database::getConnection()->startTransaction();
    $ids = $storage->getQuery()->accessCheck(FALSE)->execute();
    foreach (array_chunk($ids, 100) as $chunk) {
      $storage->delete($storage->loadMultiple($chunk));
    }
    unset($transaction);
    print 'placements deleted: ' . count($ids) . "\n";
    break;

  case 'engine-check':
    // The site's engine is reachable with the site's own settings: the
    // worker role's cleanup in the workspace mode, a read in the other.
    $settings = \Drupal::service('ancora_engine.settings');
    if ($settings->isWorkspace()) {
      \Drupal::service('ancora_engine.workspace_client')->cleanup();
    }
    else {
      $client = \Drupal::service('ancora_engine.engine_client');
      $conn = $client->connectReadOnly();
      $client->rows($conn, 'SELECT count(*) FROM actividad');
      pg_close($conn);
    }
    $database = $settings->database();
    print 'engine reachable: ' . $settings->mode() . ' ' . $database['database'] . ' as ' . $database['username'] . "\n";
    break;

  default:
    throw new \InvalidArgumentException('site-tasks.php: unknown task ' . $ancora_task);
}
