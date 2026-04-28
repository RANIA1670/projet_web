<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;

final class StatisticsController extends Controller
{
    public function statistics(): void
    {
        cityzen_require_agent();
        
        // Get filters from GET parameters
        $dateFilter = $_GET['date_filter'] ?? null;
        $categoryFilter = $_GET['category_filter'] ?? null;
        
        // Validate date filter
        $allowedDateFilters = ['today', 'week', 'month', 'year'];
        if ($dateFilter && !in_array($dateFilter, $allowedDateFilters)) {
            $dateFilter = null;
        }
        
        // Get statistics data
        $stats = cityzen_detailed_stats($dateFilter, $categoryFilter);
        
        // Get basic dashboard data
        $cityzen = [
            'city_name' => 'CityZen',
            'current_date' => date('d/m/Y H:i'),
            'admin_menu' => [
                ['key' => 'dashboard', 'label' => 'Tableau de bord', 'url' => '/controller/dashboard.php'],
                ['key' => 'users', 'label' => 'Utilisateurs', 'url' => '/controller/users.php'],
                ['key' => 'statistics', 'label' => 'Statistiques', 'url' => '/controller/statistics.php'],
                ['key' => 'settings', 'label' => 'Paramètres', 'url' => '/controller/settings.php'],
            ],
        ];
        
        View::render('admin/statistics', [
            'stats' => $stats,
            'dateFilter' => $dateFilter,
            'categoryFilter' => $categoryFilter,
            'cityzen' => $cityzen,
        ]);
    }
}
