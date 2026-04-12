<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

cityzen_require_agent();

require_once __DIR__ . '/../equipment_bo/app/config/config.php';
require_once APP_PATH . '/bootstrap/post_actions.php';

try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        $rt = (string) ($_GET['route'] ?? '');
        if ($rt === 'reports' && isset($_GET['export']) && $_GET['export'] === 'csv') {
            (new ReportController($pdo))->exportCsv();
            exit;
        }
        if ($rt === 'reports' && !empty($_GET['print'])) {
            (new ReportController($pdo))->printMonth();
            exit;
        }
    }

    bo_handle_post($pdo);

    $route = (string) ($_REQUEST['route'] ?? 'dashboard');
    switch ($route) {
        case 'equipment':
            (new EquipmentManageController($pdo))->index();
            break;
        case 'types':
            (new TypesManageController($pdo))->index();
            break;
        case 'reservations':
            (new ReservationManageController($pdo))->index();
            break;
        case 'reports':
            (new ReportController($pdo))->index();
            break;
        case 'dashboard':
        default:
            (new DashboardController($pdo))->index();
    }
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Erreur</title></head><body>';
    echo '<h1>Erreur application</h1>';
    echo '<p><strong>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</strong></p>';
    echo '<p>Import SQL : <code>database/equipment_tables.sql</code> (après <code>database/cityzen.sql</code>).</p>';
    echo '<pre style="overflow:auto">' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '</body></html>';
    exit;
}
