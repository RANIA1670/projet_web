<?php
header('Content-Type: text/html; charset=UTF-8');
/**
 * CityZen - Front Controller (index.php)
 * Point d'entrée unique de l'application
 */

// Chargement de la configuration
require_once __DIR__ . '/../config/config.php';

// Chargement des classes core
require_once APP_PATH . 'core/Database.php';
require_once APP_PATH . 'core/Model.php';
require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'core/Router.php';

// Démarrage de la session
ini_set('session.name', SESSION_NAME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_start();

// Instanciation du routeur
$router = new Router();

// Chargement des routes
require_once APP_PATH . 'routes.php';

// Dispatch de la requête
$uri        = $_SERVER['REQUEST_URI'] ?? '/';
$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($uri, $httpMethod);
