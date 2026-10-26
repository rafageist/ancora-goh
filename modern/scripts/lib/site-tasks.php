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
 */

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Database\Database;
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

  default:
    throw new \InvalidArgumentException('site-tasks.php: unknown task ' . $ancora_task);
}
