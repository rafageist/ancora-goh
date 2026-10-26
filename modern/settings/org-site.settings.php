<?php

/**
 * @file
 * Settings of an Áncora organization site (issue #72).
 *
 * The provisioning tool (scripts/ancora-site.sh) copies this file to
 * web/sites/org-<slug>/settings.php when it provisions the site. It holds no
 * secret and no site-specific value: the slug comes from the site directory,
 * and everything else from the site's settings file outside the web root
 * (see ancora.org.settings.php).
 *
 * @see docs/modernization/drupal-multisite.md
 */

require dirname($app_root) . '/settings/ancora.org.settings.php';
