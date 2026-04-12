<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/equipment_bo/app/models/Reservation.php';

$id = (int) ($_GET['equipment_id'] ?? $_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid equipment_id']);
    exit;
}

$pdo = cityzen_db();
$res = new Reservation($pdo);
$busy = $res->busyRangesForEquipment($id);

echo json_encode(['busy' => $busy], JSON_UNESCAPED_UNICODE);
