<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\DashboardModel;
use App\Models\UserModel;

final class UserCardController extends Controller
{
    private UserModel $userModel;
    private DashboardModel $dashboardModel;

    public function __construct(?UserModel $userModel = null, ?DashboardModel $dashboardModel = null)
    {
        $this->userModel = $userModel ?? new UserModel();
        $this->dashboardModel = $dashboardModel ?? new DashboardModel();
    }

    public function show(): void
    {
        cityzen_session_start();

        if (!cityzen_is_logged_in()) {
            $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? cityzen_asset('controller/user_card.php'));
            $this->redirect(cityzen_login_url(cityzen_safe_next($requestUri)));
        }

        $state = 'ok';
        if (!cityzen_is_agent()) {
            http_response_code(403);
            $state = 'forbidden';
            View::render('admin/user_card', [
                'cityzen' => $this->dashboardModel->data(),
                'scanState' => $state,
                'scannedUser' => null,
            ]);
            return;
        }

        $token = trim((string) ($_GET['t'] ?? ''));
        $scannedUser = $this->userModel->getByQrToken($token);
        if ($scannedUser === null) {
            http_response_code(404);
            $state = 'not_found';
        }

        View::render('admin/user_card', [
            'cityzen' => $this->dashboardModel->data(),
            'scanState' => $state,
            'scannedUser' => $scannedUser,
        ]);
    }
}
