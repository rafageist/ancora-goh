<?php

/**
 * @file
 * Site discovery for the Áncora organization sites (issue #72).
 *
 * The control site is sites/default and serves "/". Each organization site
 * lives in sites/org-<slug> and serves "/o/<slug>/". Drupal core resolves a
 * subdirectory site from the script path: the web server runs the front
 * controller as /o/<slug>/index.php (web/o/<slug> is a symbolic link to the
 * web root, created by scripts/ancora-site.sh when the site is ready), so
 * DrupalKernel::findSitePath() looks up the key "<port>.<host>.o.<slug>".
 *
 * This file is code, not a registry: it maps the key of the current request
 * to sites/org-<slug>, whatever the host. A site exists only when its
 * directory and its settings.php exist; Drupal checks that before it uses the
 * mapping. An unknown slug therefore falls back to sites/default, and the
 * request guard in settings/ancora.settings.php answers 404 there, because
 * the control site serves only /index.php for paths outside /o/. The
 * organization's own settings likewise refuse any request whose script is
 * not /o/<slug>/index.php.
 *
 * DrupalKernel::findSitePath() defines $sites, $http_host and $script_name
 * before it includes this file. Drush builds the same request from --uri.
 *
 * @see docs/modernization/drupal-multisite.md
 * @see \Drupal\Core\DrupalKernel::findSitePath()
 */

if (isset($script_name, $http_host) && preg_match('@^/o/([a-z][a-z0-9-]{1,30})/index\.php$@', $script_name, $ancora_match)) {
  $ancora_host = implode('.', array_reverse(explode(':', rtrim($http_host, '.'))));
  $sites[$ancora_host . '.o.' . $ancora_match[1]] = 'org-' . $ancora_match[1];
  unset($ancora_host);
}
unset($ancora_match);
