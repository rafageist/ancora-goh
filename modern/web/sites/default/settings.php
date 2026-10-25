<?php

/**
 * @file
 * Settings of the default Áncora site.
 *
 * This file holds no secret and no host-specific value. Every setting is read
 * from environment variables by the shared ../settings/ancora.settings.php,
 * which keeps the same code usable by additional sites (sites/<name>/) if the
 * multisite tenant model is chosen (#72): such a site sets its own
 * $ancora_env_prefix before including the shared file.
 *
 * Local development overrides go in settings.local.php next to this file. It
 * is git-ignored and must never be deployed to production.
 *
 * @see docs/modernization/drupal-foundation.md
 */

// Read by the shared settings file (the analyser cannot see across require).
// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
$ancora_env_prefix = 'ANCORA_';
require dirname($app_root) . '/settings/ancora.settings.php';

if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
