<?php
/**
 * CityZen - Smart Intervention Management
 * Configuration principale
 */

// Paramètres de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'cityzen_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Paramètres de l'application
define('APP_NAME', 'CityZen');
define('APP_TAGLINE', 'Smart Intervention Management');
define('APP_URL', 'http://localhost/projet_web');
define('APP_VERSION', '1.0.0');

// Chemins
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOAD_URL', APP_URL . '/public/uploads/');

// Session
define('SESSION_NAME', 'cityzen_session');
define('SESSION_LIFETIME', 7200);

// Upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Fuseau horaire
date_default_timezone_set('Africa/Casablanca');

// Mode debug (passer à false en production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
