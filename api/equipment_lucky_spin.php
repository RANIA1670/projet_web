<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . '/core/layout.php';
require_once dirname(__DIR__) . '/model/db.php';
require_once dirname(__DIR__) . '/equipment/backoffice/app/models/LuckySpin.php';

if (!cityzen_is_logged_in() || cityzen_current_user_id() <= 0) {
    http_response_code(401);
    echo json_encode(['error' => 'auth_required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$uid = cityzen_current_user_id();
$pdo = cityzen_db();
$spin = new LuckySpin($pdo);

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($method === 'POST') {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        http_response_code(403);
        echo json_encode(['error' => 'csrf_invalid'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = $spin->spinToday($uid);
    echo json_encode([
        'ok' => true,
        'can_spin' => false,
        'already_spun' => (bool) ($result['already_spun'] ?? false),
        'outcome' => (string) ($result['outcome'] ?? 'no_win'),
        'code' => (string) ($result['code'] ?? ''),
        'discount_percent' => (int) ($result['discount_percent'] ?? 0),
        'valid_until' => (string) ($result['valid_until'] ?? ''),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$today = $spin->todaySpinForUser($uid);
echo json_encode([
    'ok' => true,
    'can_spin' => $today === null,
    'already_spun' => $today !== null,
    'outcome' => (string) ($today['outcome'] ?? ''),
    'code' => (string) ($today['code'] ?? ''),
    'discount_percent' => (int) ($today['discount_percent'] ?? 0),
    'valid_until' => (string) ($today['valid_until'] ?? ''),
], JSON_UNESCAPED_UNICODE);
