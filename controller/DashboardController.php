<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\DashboardModel;

final class DashboardController
{
    private DashboardModel $dashboardModel;

    public function __construct(?DashboardModel $dashboardModel = null)
    {
        $this->dashboardModel = $dashboardModel ?? new DashboardModel();
    }

    public function index(): void
    {
        $this->dashboardModel->setUserStats(cityzen_user_stats());

        View::render('admin/dashboard', [
            'cityzen' => $this->dashboardModel->getData(),
            'userStats' => $this->dashboardModel->getUserStats(),
        ]);
    }

    public function getData(): array
    {
        global $cityzen;

        return is_array($cityzen) ? $cityzen : [];
    }

    public function getUserStats(): array
    {
        return cityzen_user_stats();
    }

    public function filterReports(string $query): array
    {
        $query = mb_strtolower(trim($query));
        $data = $this->getData();
        $reports = $data['recent_reports'] ?? [];

        if ($query === '' || !is_array($reports)) {
            return is_array($reports) ? $reports : [];
        }

        return array_values(array_filter(
            $reports,
            static function (array $report) use ($query): bool {
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($report['title'] ?? ''),
                    (string) ($report['meta'] ?? ''),
                    (string) ($report['status'] ?? ''),
                    (string) ($report['category'] ?? ''),
                    (string) ($report['district'] ?? ''),
                ]));

                return str_contains($haystack, $query);
            }
        ));
    }

    public function apiDashboard(): array
    {
        return [
            'app' => $this->getData()['app_name'] ?? 'CityZen',
            'city' => $this->getData()['city_name'] ?? '',
            'date' => $this->getData()['current_date'] ?? '',
            'user_stats' => $this->getUserStats(),
            'stats' => $this->getData()['stats'] ?? [],
            'districts' => $this->getData()['districts'] ?? [],
            'recent_reports' => $this->getData()['recent_reports'] ?? [],
            'weekly_reports' => $this->getData()['weekly_reports'] ?? [],
            'weekly_summary' => $this->getData()['weekly_summary'] ?? [],
        ];
    }

    public function apiReports(string $query): array
    {
        return [
            'query' => $query,
            'count' => count($this->filterReports($query)),
            'reports' => $this->filterReports($query),
        ];
    }
}
