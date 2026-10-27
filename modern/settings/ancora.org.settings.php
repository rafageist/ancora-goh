<?php

/**
 * @file
 * Settings of an organization site, web/sites/org-<slug>/ (issue #72).
 *
 * Every organization site directory holds the same small settings.php (a copy
 * of settings/org-site.settings.php written by scripts/ancora-site.sh), which
 * includes this file. The slug is taken from the site directory that Drupal's
 * site discovery selected, never from the request.
 *
 * The site's values come from its own settings file outside the web root:
 * $ANCORA_SITES_DIR/<slug>/site.ini (mode 0640), written by the provisioning
 * tool. The database, the hash salt, the private and temporary directories
 * and the owner credentials are read only from that file. Values that are
 * the same for every site (environment, database host and port, trusted
 * hosts, reverse proxy, configuration sync directory) may come from the
 * environment, as for the control site.
 *
 * A site whose STATUS is not "active" answers 503 to web requests without
 * bootstrapping Drupal (provisioning in progress, suspended or retired).
 *
 * @see docs/modernization/drupal-multisite.md
 */

if (!preg_match('@^sites/org-([a-z][a-z0-9-]{1,30})$@', $site_path, $ancora_match)) {
  throw new \RuntimeException('Áncora settings: an organization site must live in sites/org-<slug>.');
}
$ancora_site_slug = $ancora_match[1];

$ancora_sites_dir = getenv('ANCORA_SITES_DIR');
if ($ancora_sites_dir === FALSE || $ancora_sites_dir === '' || !is_dir($ancora_sites_dir)) {
  throw new \RuntimeException('Áncora settings: ANCORA_SITES_DIR must name the directory of the per-site settings.');
}
$ancora_site_file = $ancora_sites_dir . '/' . $ancora_site_slug . '/site.ini';
$ancora_site_values = is_file($ancora_site_file) && is_readable($ancora_site_file)
  ? parse_ini_file($ancora_site_file, FALSE, INI_SCANNER_RAW)
  : FALSE;
if (!is_array($ancora_site_values) || ($ancora_site_values['SLUG'] ?? NULL) !== $ancora_site_slug) {
  throw new \RuntimeException(sprintf('Áncora settings: the site file of %s is missing, unreadable or for another site.', $ancora_site_slug));
}

// Read by the shared settings file (the analyser cannot see across require).
// phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
$ancora_env_prefix = 'ANCORA_';
$ancora_site_base_path = '/o/' . $ancora_site_slug;
$ancora_site_only = [
  'DB_NAME',
  'DB_USER',
  'DB_PASSWORD_FILE',
  'DB_OWNER_USER',
  'DB_OWNER_PASSWORD_FILE',
  'HASH_SALT_FILE',
  'PRIVATE_PATH',
  'TMP_PATH',
];
// phpcs:enable
foreach ($ancora_site_only as $ancora_name) {
  if (in_array($ancora_name, ['DB_OWNER_USER', 'DB_OWNER_PASSWORD_FILE', 'DB_PASSWORD_FILE'], TRUE)) {
    continue;
  }
  if (($ancora_site_values[$ancora_name] ?? '') === '') {
    throw new \RuntimeException(sprintf('Áncora settings: %s is missing from the site file of %s.', $ancora_name, $ancora_site_slug));
  }
}

require __DIR__ . '/ancora.settings.php';

// Checked after the request guard of the shared file, so a request that was
// not routed to this site gets the same 404 whatever the site's status.
if (PHP_SAPI !== 'cli' && ($ancora_site_values['STATUS'] ?? '') !== 'active') {
  http_response_code(503);
  header('Content-Type: text/plain; charset=utf-8');
  header('Cache-Control: no-store');
  header('Retry-After: 3600');
  print "This site is not available.\n";
  exit;
}

// Per-site public files (served at /o/<slug>/sites/org-<slug>/files/).
$settings['file_public_path'] = $site_path . '/files';

// The session cookie name already depends on the base path (Drupal hashes
// host and base path); the path attribute keeps the browser from sending it
// to the control site or to another organization.
ini_set('session.cookie_path', $ancora_site_base_path . '/');

// The site name is an override, so config/sync stays identical on every site
// and config:import never reverts it.
if (($ancora_site_values['SITE_NAME'] ?? '') !== '') {
  $config['system.site']['name'] = $ancora_site_values['SITE_NAME'];
}

// The site's scheduling engine, provisioned with the site by
// scripts/ancora-site.sh: the engine database tt_<slug>_engine (#71,
// ENGINE_MODE two_database, the default of a site file written before #96)
// or the engine schema of the site's own database with job workspaces (#96,
// ENGINE_MODE workspace). The mode, database name, role and password file
// come only from the site file; the engine module's environment fallback is
// never used for an organization, so a site can never reach another site's
// engine. Only the server address and the limits may be shared (environment).
$settings['ancora_engine'] = ['database' => []];
if (($ancora_site_values['ENGINE_DB_NAME'] ?? '') !== '' && ($ancora_site_values['ENGINE_DB_USER'] ?? '') !== '') {
  $ancora_shared = static function (string $name, string $fallback = ''): string {
    $value = getenv('ANCORA_ENGINE_' . $name);
    if ($value === FALSE || $value === '') {
      $value = getenv('ANCORA_' . $name);
    }
    return ($value === FALSE || $value === '') ? $fallback : $value;
  };
  $settings['ancora_engine']['database'] = [
    'host' => $ancora_shared('DB_HOST', 'localhost'),
    'port' => (int) $ancora_shared('DB_PORT', '5432'),
    'database' => $ancora_site_values['ENGINE_DB_NAME'],
    'username' => $ancora_site_values['ENGINE_DB_USER'],
    'password_file' => $ancora_site_values['ENGINE_DB_PASSWORD_FILE'] ?? '',
    'sslmode' => (string) getenv('ANCORA_ENGINE_DB_SSLMODE'),
  ];
  $settings['ancora_engine']['mode'] = ($ancora_site_values['ENGINE_MODE'] ?? '') === 'workspace' ? 'workspace' : 'two_database';
  $ancora_limits = [
    'time_limit',
    'max_time_limit',
    'heartbeat_interval',
    'stale_after',
    'max_attempts',
    'cancel_grace',
    'workspace_retention',
  ];
  foreach ($ancora_limits as $ancora_name) {
    $ancora_value = getenv('ANCORA_ENGINE_' . strtoupper($ancora_name));
    if ($ancora_value !== FALSE && ctype_digit($ancora_value)) {
      $settings['ancora_engine'][$ancora_name] = (int) $ancora_value;
    }
  }
  unset($ancora_shared, $ancora_value, $ancora_limits);
}

unset($ancora_match, $ancora_sites_dir, $ancora_site_file, $ancora_site_values, $ancora_site_only, $ancora_name, $ancora_site_slug);
