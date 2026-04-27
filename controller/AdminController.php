<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;

final class AdminController extends Controller
{
    private DashboardController $dashboardController;

    public function __construct(?DashboardController $dashboardController = null)
    {
        $this->dashboardController = $dashboardController ?? new DashboardController();
    }

    public function dashboard(): void
    {
        cityzen_require_agent();

        View::render('admin/dashboard', [
            'cityzen' => $this->dashboardController->getData(),
            'userStats' => $this->dashboardController->getUserStats(),
        ]);
    }
}