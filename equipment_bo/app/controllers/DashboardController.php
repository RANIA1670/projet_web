<?php

declare(strict_types=1);

class DashboardController
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        $stats = new DashboardStats($this->pdo);
        $kpis = [
            'total_equipment'   => $stats->totalEquipment(),
            'reserved_today'    => $stats->countReservedToday(),
            'maintenance'       => $stats->countMaintenance(),
            'pending'           => $stats->countPendingReservations(),
        ];
        $chartTypes = $stats->mostRequestedTypes();
        $heatmap = $stats->usageByLocation();
        $lateReturns = $stats->lateReturns();

        ob_start();
        require VIEW_PATH . '/dashboard/index.php';
        $content = ob_get_clean();
        $activeRoute = 'dashboard';
        require VIEW_PATH . '/layouts/main.php';
    }
}
