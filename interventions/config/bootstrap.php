<?php

declare(strict_types=1);

/**
 * Config module interventions/signalements (branche_fatma).
 */

require_once dirname(__DIR__, 2) . '/core/layout.php';

define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', PUBLIC_PATH . 'uploads' . DIRECTORY_SEPARATOR);

$origin = cityzen_request_origin();
$assetPath = cityzen_asset('interventions');
define('APP_URL', rtrim($origin, '/') . $assetPath);
define(
    'UPLOAD_URL',
    rtrim($origin, '/') . cityzen_asset('interventions/public/uploads') . '/'
);

$dbHost = getenv('CITYZEN_DB_HOST') ?: '127.0.0.1';
$dbName = getenv('CITYZEN_DB_NAME') ?: (getenv('DB_NAME') ?: 'cityzen');
$dbUser = getenv('CITYZEN_DB_USER') ?: 'root';
$dbPassEnv = getenv('CITYZEN_DB_PASS');
$dbPass = $dbPassEnv === false ? '' : (string) $dbPassEnv;

define('DB_HOST', $dbHost);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'CityZen');
define('APP_TAGLINE', 'Interventions et signalements');
define('APP_VERSION', '1.0.0');

define('ITEMS_PER_PAGE', 10);
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

date_default_timezone_set('Africa/Tunis');

define('DEBUG_MODE', false);
