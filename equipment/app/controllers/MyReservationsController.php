<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/layout.php';
require_once dirname(__DIR__, 3) . '/model/db.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/Reservation.php';

cityzen_require_citizen_login();

$pdo = cityzen_db();
$reservations = new Reservation($pdo);
$uid = cityzen_current_user_id();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['cancel_reservation'])) {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $err = 'Jeton invalide.';
    } else {
        $rid = (int) ($_POST['reservation_id'] ?? 0);
        if ($reservations->cancelByUser($rid, $uid)) {
            header('Location: ' . cityzen_asset('equipment/my-reservations.php?msg=' . rawurlencode('Réservation annulée.') . '&type=ok'), true, 302);
            exit;
        }
        $err = 'Annulation impossible.';
    }
}

$list = $reservations->listForUser($uid);

$msg = (string) ($_GET['msg'] ?? '');
$type = (string) ($_GET['type'] ?? '');

$eq_title = 'Mes réservations';
$eq_nav_active = 'my-reservations';

$purposeLabels = [
    'event' => 'Événement',
    'repair' => 'Réparation',
    'inspection' => 'Inspection',
];

$statusLabels = [
    'pending' => 'En attente',
    'approved' => 'Approuvée',
    'rejected' => 'Refusée',
    'returned' => 'Terminée',
    'no_show' => 'Non présentation',
    'cancelled' => 'Annulée',
];
