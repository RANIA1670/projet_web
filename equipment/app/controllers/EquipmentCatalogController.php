<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/model/db.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/Equipment.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/TypeEquipment.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/Reservation.php';

$pdo = cityzen_db();
$equipment = new Equipment($pdo);
$types = new TypeEquipment($pdo);
$reservations = new Reservation($pdo);

$typeId = isset($_GET['type']) ? (int) $_GET['type'] : null;
if ($typeId !== null && $typeId <= 0) {
    $typeId = null;
}

$rows = $equipment->allWithType($typeId, null, null);
$typeList = $types->all();

$eq_title = 'Équipement municipal';
$eq_nav_active = 'equipment';
