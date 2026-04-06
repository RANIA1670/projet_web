<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/data.php';

echo json_encode([
    'app' => $cityzen['app_name'],
    'city' => $cityzen['city_name'],
    'date' => $cityzen['current_date'],
    'stats' => $cityzen['stats'],
    'districts' => $cityzen['districts'],
    'recent_reports' => $cityzen['recent_reports'],
    'weekly_reports' => $cityzen['weekly_reports'],
    'weekly_summary' => $cityzen['weekly_summary'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
