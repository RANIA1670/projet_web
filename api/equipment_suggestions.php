<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . '/model/db.php';
require_once dirname(__DIR__) . '/equipment/backoffice/app/models/Reservation.php';

$id = (int) ($_GET['equipment_id'] ?? $_GET['id'] ?? 0);
$duration = (int) ($_GET['duration_minutes'] ?? 120);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid equipment_id']);
    exit;
}

$pdo = cityzen_db();
$res = new Reservation($pdo);
$slots = $res->suggestAvailableSlots($id, $duration, 14, 5);

echo json_encode(['slots' => $slots], JSON_UNESCAPED_UNICODE);
