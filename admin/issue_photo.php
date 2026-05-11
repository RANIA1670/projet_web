<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Bootstrap.php';

App\Core\Bootstrap::init();

cityzen_require_agent();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('Not found');
}

$pdo = cityzen_db();
$stmt = $pdo->prepare('SELECT photo_path FROM equipment_issue WHERE id = :id');
$stmt->execute([':id' => $id]);
$path = $stmt->fetchColumn();
if ($path === false || $path === null || $path === '') {
    http_response_code(404);
    exit('Not found');
}

$rel = (string) $path;
if (!preg_match('#^storage/equipment_issues/[a-f0-9]{32}\\.(jpg|png|webp)$#', $rel)) {
    http_response_code(404);
    exit('Not found');
}

$baseDir = realpath(dirname(__DIR__) . '/storage/equipment_issues');
if ($baseDir === false) {
    http_response_code(500);
    exit;
}

$full = dirname(__DIR__) . '/' . $rel;
$fullReal = realpath($full);
if ($fullReal === false || !str_starts_with($fullReal, $baseDir)) {
    http_response_code(404);
    exit('Not found');
}

$mime = mime_content_type($fullReal) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($fullReal));
readfile($fullReal);
exit;
