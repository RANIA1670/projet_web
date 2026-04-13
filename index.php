<?php
// index.php - Routeur principal pour la gestion d'événements
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'event';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch($controller) {
    case 'event':
        require_once __DIR__ . '/controllers/EventController.php';
        $controllerInstance = new EventController();
        switch($action) {
            case 'index': $controllerInstance->index(); break;
            case 'show': $controllerInstance->show($id); break;
            case 'create': $controllerInstance->create(); break;
            case 'edit': $controllerInstance->edit($id); break;
            case 'delete': $controllerInstance->delete($id); break;
            default: $controllerInstance->index();
        }
        break;
    case 'sponsor':
        require_once __DIR__ . '/controllers/SponsorController.php';
        $controllerInstance = new SponsorController();
        switch($action) {
            case 'index': $controllerInstance->index(); break;
            case 'create': $controllerInstance->create(); break;
            default: $controllerInstance->index();
        }
        break;
    case 'participation':
        require_once __DIR__ . '/controllers/ParticipationController.php';
        $controllerInstance = new ParticipationController();
        switch($action) {
            case 'index': $controllerInstance->index(); break;
            case 'register': $controllerInstance->register(); break;
            default: $controllerInstance->index();
        }
        break;
    default:
        echo "<h1>404 - Page non trouvée</h1>";
        echo "<p>Le controller '$controller' n'existe pas.</p>";
}
?>