<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora\Kernel;

use Drupal\Core\Database\Database;
use Drupal\KernelTests\KernelTestBase;

/**
 * Base class for Áncora Kernel tests.
 *
 * Drupal's SIMPLETEST_DB URL cannot name a Unix socket directory as the
 * PostgreSQL host, and the disposable test cluster (with-ephemeral-pg.sh)
 * listens on a socket only. When ANCORA_TEST_DB_SOCKET_DIR is set, the host of
 * the parsed SIMPLETEST_DB connection is replaced by that directory; nothing
 * else changes.
 */
abstract class AncoraKernelTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getDatabaseConnectionInfo() {
    $connection_info = parent::getDatabaseConnectionInfo();
    $socket_dir = getenv('ANCORA_TEST_DB_SOCKET_DIR');
    if (is_string($socket_dir) && $socket_dir !== '') {
      foreach ($connection_info as $target => $info) {
        $connection_info[$target]['host'] = $socket_dir;
      }
      // The parent registered the original connection for the test runner.
      $original = Database::getConnectionInfo('simpletest_original_default');
      if ($original) {
        Database::removeConnection('simpletest_original_default');
        foreach ($original as $target => $info) {
          $info['host'] = $socket_dir;
          Database::addConnectionInfo('simpletest_original_default', $target, $info);
        }
      }
    }
    return $connection_info;
  }

}
