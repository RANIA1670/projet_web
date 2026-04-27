<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\DashboardModel;

final class PublicController extends Controller
{
    private DashboardModel $dashboardModel;

    public function __construct(?DashboardModel $dashboardModel = null)
    {
        $this->dashboardModel = $dashboardModel ?? new DashboardModel();
    }

    public function home(): void
    {
        View::render('public/home', [
            'cityzen' => $this->dashboardModel->data(),
        ]);
    }

    public function portal(): void
    {
        View::render('public/portal', [
            'cityzen' => $this->dashboardModel->data(),
        ]);
    }
}
