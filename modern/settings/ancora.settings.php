<?php

/**
 * @file
 * Environment-driven Drupal settings shared by every Áncora site.
 *
 * A site's settings.php sets $ancora_env_prefix and includes this file. The
 * file lives outside the web root, holds no secret and no host-specific
 * value: everything comes from environment variables, and secrets come from
 * files named by *_FILE variables (for example a systemd credential, a
 * PHP-FPM pool env[] entry pointing to a 0640 file, or a CI temporary file).
 *
 * Variables (shown with the default prefix ANCORA_):
 * - ANCORA_ENVIRONMENT: production (default), development or testing.
 * - ANCORA_DB_HOST: host name, IP or Unix socket directory (localhost).
 * - ANCORA_DB_PORT: port (5432).
 * - ANCORA_DB_NAME, ANCORA_DB_USER: required.
 * - ANCORA_DB_PASSWORD_FILE: file holding the password; unset for peer or
 *   trust authentication over a Unix socket.
 * - ANCORA_HASH_SALT_FILE: required; file holding a random salt.
 * - ANCORA_TRUSTED_HOSTS: comma-separated host names; required in
 *   production.
 * - ANCORA_CONFIG_SYNC_DIR: configuration sync directory (../config/sync).
 * - ANCORA_PRIVATE_PATH: private files directory (../private).
 * - ANCORA_TMP_PATH: temporary directory (the system default).
 * - ANCORA_REVERSE_PROXY_ADDRESSES: comma-separated proxy IPs, for example
 *   127.0.0.1 behind a local Caddy.
 *
 * - ANCORA_DB_OWNER_USER, ANCORA_DB_OWNER_PASSWORD_FILE: the role that owns
 *   the schema. Used only on the command line with ANCORA_DB_AS_OWNER=1
 *   (install, database updates, configuration import); never by the web
 *   server, which always connects with the runtime role ANCORA_DB_USER.
 * - ANCORA_EXPECT_SITE_PATH: on the command line, the site directory the
 *   operator means (for example sites/org-alpha); any other site stops.
 *
 * The site fails closed: a missing database name, user or hash salt (or, in
 * production, missing trusted hosts) stops the request instead of falling
 * back to the interactive installer.
 *
 * Organization sites (issue #72) include this file from
 * ancora.org.settings.php with two more variables:
 * - $ancora_site_values: values read from the site's own settings file
 *   outside the web root. They take precedence over the environment.
 * - $ancora_site_only: names that must come from $ancora_site_values. The
 *   environment of the web server is never consulted for them, so an
 *   organization site can never pick up the control site's database, salt or
 *   files.
 * - $ancora_site_base_path: the site's base path, for example /o/alpha. The
 *   control site has ''.
 *
 * @see docs/modernization/drupal-foundation.md
 * @see docs/modernization/drupal-multisite.md
 */

if (!isset($ancora_env_prefix)) {
  $ancora_env_prefix = 'ANCORA_';
}
if (!isset($ancora_site_values)) {
  $ancora_site_values = [];
}
if (!isset($ancora_site_only)) {
  $ancora_site_only = [];
}
if (!isset($ancora_site_base_path)) {
  $ancora_site_base_path = '';
}

// Only the site's front controller may serve a web request, and only for
// paths under the site's base path. The control site never serves /o/...: an
// unknown organization falls back to sites/default in Drupal's site discovery,
// and this is where that fallback ends with a 404 instead of the control
// site. Command-line requests (Drush) are checked against the site directory
// the operator expects.
if (PHP_SAPI === 'cli') {
  $ancora_expected = getenv('ANCORA_EXPECT_SITE_PATH');
  if ($ancora_expected !== FALSE && $ancora_expected !== '' && $ancora_expected !== $site_path) {
    throw new \RuntimeException(sprintf('Áncora settings: this command resolved %s, but ANCORA_EXPECT_SITE_PATH is %s.', $site_path, $ancora_expected));
  }
}
else {
  $ancora_script = $_SERVER['SCRIPT_NAME'] ?? '';
  $ancora_path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  $ancora_decoded = preg_replace('@/+@', '/', rawurldecode($ancora_path));
  $ancora_routed = $ancora_site_base_path === ''
    ? $ancora_script === '/index.php' && preg_match('@^/o(/|$)@i', $ancora_decoded) !== 1
    : $ancora_script === $ancora_site_base_path . '/index.php' && str_starts_with($ancora_path, $ancora_site_base_path . '/');
  if (!$ancora_routed) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    print "Not found\n";
    exit;
  }
  unset($ancora_script, $ancora_path, $ancora_decoded, $ancora_routed);
}

$ancora_env = static function (string $name, ?string $default = NULL) use ($ancora_env_prefix, $ancora_site_values, $ancora_site_only): ?string {
  if (isset($ancora_site_values[$name]) && $ancora_site_values[$name] !== '') {
    return $ancora_site_values[$name];
  }
  if (in_array($name, $ancora_site_only, TRUE)) {
    return $default;
  }
  $value = getenv($ancora_env_prefix . $name);
  return ($value === FALSE || $value === '') ? $default : $value;
};

$ancora_require = static function (string $name) use ($ancora_env, $ancora_env_prefix): string {
  $value = $ancora_env($name);
  if ($value === NULL) {
    throw new \RuntimeException(sprintf('Áncora settings: the environment variable %s%s is required.', $ancora_env_prefix, $name));
  }
  return $value;
};

// Reads a secret from the file named by <NAME>_FILE. Returns NULL when the
// variable is unset; refuses an unreadable or empty file.
$ancora_secret = static function (string $name) use ($ancora_env, $ancora_env_prefix): ?string {
  $path = $ancora_env($name . '_FILE');
  if ($path === NULL) {
    return NULL;
  }
  $value = is_file($path) && is_readable($path) ? file_get_contents($path) : FALSE;
  if ($value === FALSE || trim($value) === '') {
    throw new \RuntimeException(sprintf('Áncora settings: the file named by %s%s_FILE is missing, unreadable or empty.', $ancora_env_prefix, $name));
  }
  return rtrim($value, "\r\n");
};

$ancora_list = static function (?string $value): array {
  return $value === NULL ? [] : array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'));
};

$ancora_environment = $ancora_env('ENVIRONMENT', 'production');
if (!in_array($ancora_environment, ['production', 'development', 'testing'], TRUE)) {
  throw new \RuntimeException(sprintf('Áncora settings: %sENVIRONMENT must be production, development or testing.', $ancora_env_prefix));
}
$settings['ancora_environment'] = $ancora_environment;

// Database: PostgreSQL only (the scheduling engine is PostgreSQL, #71).
$databases['default']['default'] = [
  'driver' => 'pgsql',
  'namespace' => 'Drupal\\pgsql\\Driver\\Database\\pgsql',
  'autoload' => 'core/modules/pgsql/src/Driver/Database/pgsql/',
  'host' => $ancora_env('DB_HOST', 'localhost'),
  'port' => $ancora_env('DB_PORT', '5432'),
  'database' => $ancora_require('DB_NAME'),
  'username' => $ancora_require('DB_USER'),
  'password' => $ancora_secret('DB_PASSWORD') ?? '',
  'prefix' => '',
];
// Schema changes (install, updatedb, config:import) run as the owner role, on
// the command line only. The web server never reads the owner's secret.
if (PHP_SAPI === 'cli' && getenv('ANCORA_DB_AS_OWNER') === '1') {
  $databases['default']['default']['username'] = $ancora_require('DB_OWNER_USER');
  $databases['default']['default']['password'] = $ancora_secret('DB_OWNER_PASSWORD') ?? '';
}

$settings['hash_salt'] = $ancora_secret('HASH_SALT') ?? '';
if ($settings['hash_salt'] === '') {
  throw new \RuntimeException(sprintf('Áncora settings: %sHASH_SALT_FILE is required.', $ancora_env_prefix));
}

// Host header protection. Each name is matched literally (anchored, escaped).
$settings['trusted_host_patterns'] = array_map(
  static fn (string $host): string => '^' . preg_quote($host, '/') . '$',
  $ancora_list($ancora_env('TRUSTED_HOSTS')),
);
if ($ancora_environment === 'production' && $settings['trusted_host_patterns'] === []) {
  throw new \RuntimeException(sprintf('Áncora settings: %sTRUSTED_HOSTS is required in production.', $ancora_env_prefix));
}

// Configuration and files live outside the web root.
$ancora_project_root = dirname($app_root);
$settings['config_sync_directory'] = $ancora_env('CONFIG_SYNC_DIR', $ancora_project_root . '/config/sync');
$settings['file_private_path'] = $ancora_env('PRIVATE_PATH', $ancora_project_root . '/private');
if ($ancora_env('TMP_PATH') !== NULL) {
  $settings['file_temp_path'] = $ancora_env('TMP_PATH');
}

$ancora_proxies = $ancora_list($ancora_env('REVERSE_PROXY_ADDRESSES'));
if ($ancora_proxies !== []) {
  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_addresses'] = $ancora_proxies;
}

// Security defaults. Nothing here is relaxed in production; development
// relaxations belong in settings.local.php, which is never committed.
$settings['update_free_access'] = FALSE;
$settings['allow_authorize_operations'] = FALSE;
$settings['file_scan_ignore_directories'] = ['node_modules', 'bower_components'];
$settings['entity_update_batch_size'] = 50;
$settings['entity_update_backup'] = TRUE;
if ($ancora_environment === 'production') {
  ini_set('display_errors', '0');
  ini_set('display_startup_errors', '0');
  $config['system.logging']['error_level'] = 'hide';
}
else {
  $config['system.logging']['error_level'] = 'verbose';
}

if (file_exists($app_root . '/' . $site_path . '/services.yml')) {
  $settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
}

unset($ancora_env, $ancora_require, $ancora_secret, $ancora_list, $ancora_proxies, $ancora_project_root, $ancora_expected);
