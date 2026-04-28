<?php
/**
 * Configuration MVC équipement — intégration CityZen (PDO via cityzen_db).
 */
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('PROJECT_ROOT', dirname(__DIR__, 4));
define('BASE_PATH', dirname(__DIR__, 2));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('DATABASE_PATH', APP_PATH . '/database');

if (!function_exists('cityzen_asset')) {
    require_once PROJECT_ROOT . '/core/layout.php';
}

require_once PROJECT_ROOT . '/model/db.php';

try {
    $pdo = cityzen_db();
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Connexion base de données impossible : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

define('PUBLIC_WEB_PATH', cityzen_asset('assets/equipment'));

require_once APP_PATH . '/helpers/url.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/DashboardStats.php';
require_once APP_PATH . '/models/TypeEquipment.php';
require_once APP_PATH . '/models/Equipment.php';
require_once APP_PATH . '/models/Reservation.php';
require_once APP_PATH . '/controllers/DashboardController.php';
require_once APP_PATH . '/controllers/EquipmentManageController.php';
require_once APP_PATH . '/controllers/TypesManageController.php';
require_once APP_PATH . '/controllers/ReservationManageController.php';
require_once APP_PATH . '/controllers/ReportController.php';
