<?php

declare(strict_types=1);

// Front controller of the access-request portal. Only this file and the
// static assets in public/ are reachable; see deploy/caddy/timetables.caddy.

use Ancora\Portal\Config;
use Ancora\Portal\Http\App;
use Ancora\Portal\Http\Request;
use Ancora\Portal\Log\StderrLogger;
use Ancora\Portal\View\Renderer;

// PHP built-in server (tests, staging): let it serve existing static files
if (
    PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH))
    && basename((string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH)) !== 'index.php'
) {
    return false;
}

ini_set('display_errors', '0');
require dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(Config::fromEnvironment(), new StderrLogger(null, PHP_SAPI === 'fpm-fcgi'), new Renderer(dirname(__DIR__) . '/templates'));
$app->handle(Request::fromGlobals())->send();
