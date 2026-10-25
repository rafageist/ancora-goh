<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora\Functional;

use Drupal\Core\Database\Database;
use Drupal\Tests\BrowserTestBase;

/**
 * Base class for Áncora Functional tests.
 *
 * When ANCORA_TEST_DB_SOCKET_DIR is set, the PostgreSQL host of SIMPLETEST_DB
 * is replaced by that Unix socket directory (see AncoraKernelTestBase).
 */
abstract class AncoraBrowserTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'ancora_theme';

  /**
   * {@inheritdoc}
   */
  protected function changeDatabasePrefix(): void {
    $socket_dir = getenv('ANCORA_TEST_DB_SOCKET_DIR');
    $db_url = getenv('SIMPLETEST_DB');
    if (!is_string($socket_dir) || $socket_dir === '' || !is_string($db_url) || $db_url === '') {
      parent::changeDatabasePrefix();
      return;
    }
    // Register the connection with the socket host, then let the parent
    // derive the prefixed test connection from it. The parent reads
    // SIMPLETEST_DB only when it is set, so it is hidden for that call.
    Database::removeConnection('default');
    $info = Database::convertDbUrlToConnectionInfo($db_url);
    $info['host'] = $socket_dir;
    Database::addConnectionInfo('default', 'default', $info);
    putenv('SIMPLETEST_DB');
    try {
      parent::changeDatabasePrefix();
    }
    finally {
      putenv('SIMPLETEST_DB=' . $db_url);
    }
  }

}
