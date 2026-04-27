<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Helpers\UsersPageHelper;
use App\Models\DashboardModel;

final class UsersController extends Controller
{
    private UserStoreController $userStoreController;
    private DashboardModel $dashboardModel;

    public function __construct(?UserStoreController $userStoreController = null, ?DashboardModel $dashboardModel = null)
    {
        $this->userStoreController = $userStoreController ?? new UserStoreController();
        $this->dashboardModel = $dashboardModel ?? new DashboardModel();
    }

    public function index(): void
    {
        cityzen_require_agent();

        $flashKey = 'cityzen_users_flash';
        $baseUrl = cityzen_asset('controller/users.php');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $this->handlePostRequest($flashKey, $baseUrl);
        }

        $flash = $_SESSION[$flashKey] ?? null;
        unset($_SESSION[$flashKey]);

        $editId = (int) ($_GET['edit'] ?? 0);
        $qGet = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(5, min(100, (int) ($_GET['per_page'] ?? 10)));
        $sortGet = (string) ($_GET['sort'] ?? 'id');
        $dirGet = strtoupper((string) ($_GET['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $list = $this->userStoreController->listPaginated([
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sortGet,
            'dir' => $dirGet,
            'q' => $qGet,
        ]);

        $sort = (string) $list['sort'];
        $dir = (string) $list['dir'];
        $q = (string) $list['q'];
        $get = [
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
            'page' => (string) $list['page'],
            'per_page' => (string) $list['per_page'],
        ];

        $totalPages = max(1, (int) ceil($list['total'] / max(1, $list['per_page'])));
        if ($list['page'] > $totalPages && $totalPages >= 1) {
            $this->redirect(UsersPageHelper::url($baseUrl, $get, ['page' => (string) $totalPages]), 303);
        }

        $editUser = $editId > 0 ? $this->userStoreController->getById($editId) : null;

        $pdfHref = cityzen_asset('controller/users_pdf.php') . '?' . http_build_query([
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
        ]);

        View::render('admin/users', [
            'cityzen' => $this->dashboardModel->data(),
            'flash' => $flash,
            'editId' => $editId,
            'editUser' => $editUser,
            'baseUrl' => $baseUrl,
            'list' => $list,
            'sort' => $sort,
            'dir' => $dir,
            'q' => $q,
            'get' => $get,
            'totalPages' => $totalPages,
            'pdfHref' => $pdfHref,
        ]);
    }

    public function pdf(): void
    {
        cityzen_require_agent();

        $q = trim((string) ($_GET['q'] ?? ''));
        $sort = (string) ($_GET['sort'] ?? 'id');
        $dir = strtoupper((string) ($_GET['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $rows = $this->userStoreController->exportRows($q, $sort, $dir, 500);
        $data = $this->dashboardModel->data();
        $app = (string) ($data['app_name'] ?? 'CityZen');
        $city = (string) ($data['city_name'] ?? '');
        $exportedAt = date('d/m/Y H:i');

        header('Content-Type: text/html; charset=utf-8');
        View::render('admin/users_pdf', [
            'rows' => $rows,
            'app' => $app,
            'city' => $city,
            'exportedAt' => $exportedAt,
        ]);
    }

    private function handlePostRequest(string $flashKey, string $baseUrl): void
    {
        if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
            $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'Jeton de securite invalide. Rechargez la page.'];
            $this->redirect($baseUrl, 303);
        }

        if (isset($_POST['delete_user'])) {
            $uid = (int) ($_POST['user_id'] ?? 0);
            $sid = (int) ($_SESSION['cityzen_user']['id'] ?? 0);
            $res = $this->userStoreController->delete($uid, $sid);
            $_SESSION[$flashKey] = [
                'type' => ($res['ok'] ?? false) ? 'success' : 'error',
                'msg' => (string) (($res['ok'] ?? false) ? 'Utilisateur supprime.' : ($res['error'] ?? 'Suppression refusee.')),
            ];
            $this->redirect($baseUrl . '?' . UsersPageHelper::buildQuery($_GET, ['edit' => '']), 303);
        }

        if (isset($_POST['save_user'])) {
            $uid = (int) ($_POST['user_id'] ?? 0);
            $role = (string) ($_POST['role'] ?? '');
            $blocked = (string) ($_POST['blocked'] ?? '0') === '1';

            $res = $this->userStoreController->updateAdmin($uid, $role, $blocked);
            if (($res['ok'] ?? false) !== true) {
                $_SESSION[$flashKey] = ['type' => 'error', 'msg' => (string) ($res['error'] ?? 'Erreur.')];
                $this->redirect($baseUrl . '?' . UsersPageHelper::buildQuery($_GET, ['edit' => (string) $uid]), 303);
            }

            $sid = (int) ($_SESSION['cityzen_user']['id'] ?? 0);
            if ($sid === $uid && isset($res['user']) && is_array($res['user'])) {
                cityzen_apply_session_user($res['user']);
            }

            $_SESSION[$flashKey] = ['type' => 'success', 'msg' => 'Utilisateur mis a jour.'];
            $this->redirect($baseUrl . '?' . UsersPageHelper::buildQuery($_GET, ['edit' => '']), 303);
        }

        $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'Action non reconnue.'];
        $this->redirect($baseUrl, 303);
    }
}
