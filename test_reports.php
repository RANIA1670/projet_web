<?php
require_once 'models/Report.php';
require_once 'models/Post.php';
require_once 'config/Database.php';

echo "=== Test getOpenEnriched ===\n";
$reports = Report::getOpenEnriched(50);
echo "Total reports: " . count($reports) . "\n\n";

foreach ($reports as $report) {
    echo "Report ID: " . $report['id'] . " | Post ID: " . $report['post_id'] . " | Title: " . $report['title'] . "\n";
}

echo "\n=== Test getOpenGrouped ===\n";
$grouped = Report::getOpenGrouped(20);
echo "Total grouped: " . count($grouped) . "\n\n";

foreach ($grouped as $grp) {
    echo "Post ID: " . $grp['post_id'] . " | Count: " . $grp['report_count'] . " | Title: " . $grp['title'] . "\n";
}

echo "\n=== Test countOpen ===\n";
echo "Total open: " . Report::countOpen() . "\n";
