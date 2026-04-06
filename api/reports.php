<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/data.php';

$query = isset($_GET['q']) ? mb_strtolower(trim((string) $_GET['q'])) : '';

$reports = array_values(array_filter(
    $cityzen['recent_reports'],
    static function (array $report) use ($query): bool {
        if ($query === '') {
            return true;
        }

        $haystack = mb_strtolower(implode(' ', [
            $report['title'],
            $report['meta'],
            $report['status'],
            $report['category'],
            $report['district'],
        ]));

        return str_contains($haystack, $query);
    }
));

echo json_encode([
    'query' => $query,
    'count' => count($reports),
    'reports' => $reports,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
