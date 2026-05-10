<?php

declare(strict_types=1);

/**
 * Module interventions & signalements (intégration branche_fatma — routeur MVC interne).
 */

header('Content-Type: text/html; charset=UTF-8');

require_once dirname(__DIR__) . '/core/Bootstrap.php';

App\Core\Bootstrap::init();

require_once dirname(__DIR__) . '/core/auth.php';
require_once dirname(__DIR__) . '/core/layout.php';
require_once dirname(__DIR__) . '/core/data.php';

cityzen_session_start();

require_once __DIR__ . '/config/bootstrap.php';

require_once APP_PATH . 'core/Database.php';
require_once APP_PATH . 'core/Model.php';
require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'core/Router.php';

$router = new Router();
require_once APP_PATH . 'routes.php';

$requestPath = $_SERVER['REQUEST_URI'] ?? '/';
$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$pathOnly = parse_url($requestPath, PHP_URL_PATH);
if (!is_string($pathOnly) || $pathOnly === '') {
    $pathOnly = '/';
}

$scriptPrefix = dirname(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
$scriptPrefix = rtrim($scriptPrefix, '/');
if ($scriptPrefix !== '' && $scriptPrefix !== '.' && str_starts_with($pathOnly, $scriptPrefix)) {
    $pathOnly = substr($pathOnly, strlen($scriptPrefix)) ?: '/';
}

$projPath = parse_url(APP_URL, PHP_URL_PATH);
$projPath = is_string($projPath) ? rtrim($projPath, '/') : '';
if ($projPath !== '' && str_starts_with($pathOnly, $projPath)) {
    $pathOnly = substr($pathOnly, strlen($projPath)) ?: '/';
}

$pathOnly = '/' . ltrim($pathOnly, '/');
if ($pathOnly === '//') {
    $pathOnly = '/';
}

if (str_starts_with($pathOnly, '/backoffice')) {
    cityzen_require_agent();
}

$router->dispatch($pathOnly, $httpMethod);
