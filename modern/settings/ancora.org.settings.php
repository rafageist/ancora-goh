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

unset($ancora_match, $ancora_sites_dir, $ancora_site_file, $ancora_site_values, $ancora_site_only, $ancora_name, $ancora_site_slug);
