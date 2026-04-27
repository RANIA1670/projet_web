<?php

declare(strict_types=1);

namespace App\Models;

final class DashboardModel
{
    private array $data;
    private array $userStats;
    private array $recentReports;
    private array $districts;
    private array $weeklyReports;
    private array $weeklySummary;
    private string $appName;
    private string $cityName;
    private string $currentDate;
    private array $stats;

    public function __construct()
    {
        global $cityzen;
        $this->data = is_array($cityzen) ? $cityzen : [];
        $this->userStats = [];
        $this->recentReports = $this->data['recent_reports'] ?? [];
        $this->districts = $this->data['districts'] ?? [];
        $this->weeklyReports = $this->data['weekly_reports'] ?? [];
        $this->weeklySummary = $this->data['weekly_summary'] ?? [];
        $this->appName = $this->data['app_name'] ?? 'CityZen';
        $this->cityName = $this->data['city_name'] ?? '';
        $this->currentDate = $this->data['current_date'] ?? '';
        $this->stats = $this->data['stats'] ?? [];
    }

    public function __destruct()
    {
        // Nettoyage
    }

    // Getters
    public function getData(): array { return $this->data; }
    public function getUserStats(): array { return $this->userStats; }
    public function getRecentReports(): array { return $this->recentReports; }
    public function getDistricts(): array { return $this->districts; }
    public function getWeeklyReports(): array { return $this->weeklyReports; }
    public function getWeeklySummary(): array { return $this->weeklySummary; }
    public function getAppName(): string { return $this->appName; }
    public function getCityName(): string { return $this->cityName; }
    public function getCurrentDate(): string { return $this->currentDate; }
    public function getStats(): array { return $this->stats; }

    // Setters
    public function setData(array $data): void { $this->data = $data; }
    public function setUserStats(array $userStats): void { $this->userStats = $userStats; }
    public function setRecentReports(array $recentReports): void { $this->recentReports = $recentReports; }
    public function setDistricts(array $districts): void { $this->districts = $districts; }
    public function setWeeklyReports(array $weeklyReports): void { $this->weeklyReports = $weeklyReports; }
    public function setWeeklySummary(array $weeklySummary): void { $this->weeklySummary = $weeklySummary; }
    public function setAppName(string $appName): void { $this->appName = $appName; }
    public function setCityName(string $cityName): void { $this->cityName = $cityName; }
    public function setCurrentDate(string $currentDate): void { $this->currentDate = $currentDate; }
    public function setStats(array $stats): void { $this->stats = $stats; }

    public function data(): array
    {
        return $this->data;
    }
}