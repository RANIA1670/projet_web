<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\MailDeliveryStatsRepository;

final class ApiController
{
    private DashboardController $dashboardController;
    private MailDeliveryStatsRepository $mailStatsRepository;

    public function __construct(?DashboardController $dashboardController = null, ?MailDeliveryStatsRepository $mailStatsRepository = null)
    {
        $this->dashboardController = $dashboardController ?? new DashboardController();
        $this->mailStatsRepository = $mailStatsRepository ?? new MailDeliveryStatsRepository();
    }

    public function dashboard(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $data = $this->dashboardController->apiDashboard();

        echo json_encode(array_merge($data, [
            'mailing' => [
                'summary' => $this->mailStatsRepository->getSummary(),
                'recent_logs' => $this->mailStatsRepository->recentLogs(25),
            ],
        ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function reports(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $query = isset($_GET['q']) ? mb_strtolower(trim((string) $_GET['q'])) : '';
        $result = $this->dashboardController->apiReports($query);

        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}