<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/layout.php';
require_once dirname(__DIR__, 3) . '/model/db.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/Equipment.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/Reservation.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/EquipmentIssue.php';

$pdo = cityzen_db();
$equipment = new Equipment($pdo);
$reservations = new Reservation($pdo);
$issues = new EquipmentIssue($pdo);

$id = (int) ($_GET['id'] ?? 0);
$row = $id > 0 ? $equipment->findWithType($id) : null;
if ($row === null) {
    header('Location: ' . cityzen_asset('equipment/index.php'), true, 302);
    exit;
}

$issueFlash = '';
$issueFlashType = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['report_issue'])) {
    cityzen_require_citizen_login();
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $issueFlash = 'Session expirée. Rechargez la page.';
        $issueFlashType = 'err';
    } else {
        $itype = (string) ($_POST['issue_type'] ?? '');
        $allowedIt = ['not_working', 'damaged', 'lost'];
        $desc = trim((string) ($_POST['description'] ?? ''));
        if (!in_array($itype, $allowedIt, true) || $desc === '' || mb_strlen($desc) > 4000) {
            $issueFlash = 'Vérifiez le type de panne et la description.';
            $issueFlashType = 'err';
        } else {
            $photoPath = null;
            if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'] ?? '')) {
                $f = $_FILES['photo'];
                $okMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $mime = mime_content_type($f['tmp_name']);
                if (!isset($okMime[$mime]) || ($f['size'] ?? 0) > 2_000_000) {
                    $issueFlash = 'Photo : JPEG, PNG ou Webp, max 2 Mo.';
                    $issueFlashType = 'err';
                } else {
                    $dir = dirname(__DIR__, 3) . '/storage/equipment_issues';
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $name = bin2hex(random_bytes(16)) . '.' . $okMime[$mime];
                    $dest = $dir . '/' . $name;
                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        $photoPath = 'storage/equipment_issues/' . $name;
                    }
                }
            }
            if ($issueFlash === '') {
                $issues->create([
                    'equipment_id' => $id,
                    'user_id'      => cityzen_current_user_id(),
                    'issue_type'   => $itype,
                    'description'  => $desc,
                    'photo_path'   => $photoPath,
                ]);
                $issueFlash = 'Signalement enregistré. Un agent en prendra connaissance.';
                $issueFlashType = 'ok';
            }
        }
    }
}

$st = (string) $row['status'];
$badgeClass = match ($st) {
    'available' => 'eq-badge--available',
    'reserved' => 'eq-badge--reserved',
    'maintenance' => 'eq-badge--maintenance',
    default => 'eq-badge--out',
};
$stLabel = match ($st) {
    'available' => 'Disponible',
    'reserved' => 'En utilisation',
    'maintenance' => 'En maintenance',
    'out_of_service' => 'Hors service',
    default => $st,
};

$busy = $reservations->busyRangesForEquipment($id);
$nextEnd = $reservations->latestBlockingEnd($id);
$canRequest = $st === 'available';

$lat = $row['latitude'] ?? null;
$lng = $row['longitude'] ?? null;
$hasMap = $lat !== null && $lat !== '' && $lng !== null && $lng !== '';

$eq_title = (string) $row['name'] . ' — Équipement';
$eq_nav_active = 'equipment';
$eq_extra_css = $hasMap ? ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'] : [];
