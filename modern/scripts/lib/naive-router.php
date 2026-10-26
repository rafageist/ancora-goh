<?php

/**
 * @file
 * A deliberately careless front-controller mapping, for tests only (#72).
 *
 * The multisite verification runs it with the PHP built-in server to prove
 * that the Drupal side refuses cross-site requests on its own, without the
 * filtering of the Caddy site block: every /o/<anything>/ request is handed
 * to the front controller as /o/<anything>/index.php, whether or not such a
 * site exists, and everything else as /index.php.
 *
 * @code
 * cd web && php -S 127.0.0.1:<port> ../scripts/lib/naive-router.php
 * @endcode
 */

$ancora_path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$ancora_script = preg_match('@^/o/([^/]+)/@', $ancora_path, $ancora_match) ? '/o/' . $ancora_match[1] . '/index.php' : '/index.php';
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $ancora_script;
$_SERVER['SCRIPT_FILENAME'] = getcwd() . '/index.php';
unset($ancora_path, $ancora_script, $ancora_match);
require getcwd() . '/index.php';
