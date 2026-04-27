<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\DashboardModel;
use App\Models\UserModel;

final class SettingsController extends Controller
{
    private UserModel $userModel;
    private DashboardModel $dashboardModel;

    public function __construct(?UserModel $userModel = null, ?DashboardModel $dashboardModel = null)
    {
        $this->userModel = $userModel ?? new UserModel();
        $this->dashboardModel = $dashboardModel ?? new DashboardModel();
    }

    public function index(): void
    {
        cityzen_session_start();
        if (!cityzen_is_logged_in()) {
            $this->redirect(cityzen_login_url(cityzen_asset('controller/settings.php')));
        }

        $flashKey = 'cityzen_settings_flash';
        $baseUrl = cityzen_asset('controller/settings.php');
        $sessionUserId = (int) ($_SESSION['cityzen_user']['id'] ?? 0);

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
                $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'Jeton de securite invalide. Rechargez la page.'];
                $this->redirect($baseUrl, 303);
            }

            if ($sessionUserId < 1) {
                $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'Compte session introuvable.'];
                $this->redirect($baseUrl, 303);
            }

            if (isset($_POST['save_profile'])) {
                $errors = [];
                
                $fullName = trim((string) ($_POST['full_name'] ?? ''));
                $email = trim((string) ($_POST['email'] ?? ''));
                $birthDate = trim((string) ($_POST['birth_date'] ?? ''));
                $postalCode = trim((string) ($_POST['postal_code'] ?? ''));
                $city = trim((string) ($_POST['city'] ?? ''));
                $phone = trim((string) ($_POST['phone'] ?? ''));

                if ($fullName === '') {
                    $errors['full_name'] = 'Le nom complet est obligatoire.';
                } elseif (!preg_match('/^[\p{L}\p{M}][\p{L}\p{M}\s\'\-]{1,119}$/u', $fullName)) {
                    $errors['full_name'] = 'Nom invalide (lettres, espaces, tiret, apostrophe).';
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
                    $errors['email'] = 'Email invalide.';
                }

                if ($birthDate !== '') {
                    $dateParts = explode('-', $birthDate);
                    if (count($dateParts) !== 3 || !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
                        $errors['birth_date'] = 'Date de naissance invalide.';
                    } elseif (new DateTime($birthDate) > new DateTime()) {
                        $errors['birth_date'] = 'La date de naissance ne peut pas être dans le futur.';
                    } elseif (new DateTime($birthDate) < new DateTime('1900-01-01')) {
                        $errors['birth_date'] = 'La date de naissance est trop ancienne.';
                    }
                }

                if ($postalCode !== '') {
                    if (!preg_match('/^[0-9]{5}$/', $postalCode) && !preg_match('/^[A-Z]{1,2}[0-9R][0-9A-Z]? [0-9][A-Z]{2}$/i', $postalCode)) {
                        $errors['postal_code'] = 'Code postal invalide (format français ou britannique).';
                    }
                }

                if ($city !== '') {
                    if (!preg_match('/^[\p{L}\p{M}][\p{L}\p{M}\s\'\-]{1,119}$/u', $city)) {
                        $errors['city'] = 'Nom de ville invalide.';
                    }
                }

                if ($phone !== '') {
                    $phoneClean = preg_replace('/[\s\-\.\(\)]+/', '', $phone);
                    if (!preg_match('/^\+?[0-9]{10,15}$/', $phoneClean)) {
                        $errors['phone'] = 'Numéro de téléphone invalide.';
                    }
                }

                if ($errors !== []) {
                    $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'Corrigez les champs en erreur.'];
                    $_SESSION['form_errors'] = $errors;
                    $_SESSION['form_old'] = [
                        'full_name' => $fullName,
                        'email' => $email,
                        'birth_date' => $birthDate,
                        'postal_code' => $postalCode,
                        'city' => $city,
                        'phone' => $phone,
                    ];
                    $this->redirect($baseUrl, 303);
                }

                $photoPath = null;
                if (isset($_FILES['profile_photo']) && is_array($_FILES['profile_photo'])) {
                    $upload = $this->storeProfilePhoto($_FILES['profile_photo'], $sessionUserId);
                    if (($upload['ok'] ?? false) !== true) {
                        $_SESSION[$flashKey] = ['type' => 'error', 'msg' => (string) ($upload['error'] ?? 'Upload impossible.')];
                        $this->redirect($baseUrl, 303);
                    }
                    $photoPath = isset($upload['path']) && is_string($upload['path']) ? $upload['path'] : null;
                }

                $res = $this->userModel->updateProfile(
                    $sessionUserId,
                    [
                        'full_name' => $fullName,
                        'email' => $email,
                        'birth_date' => $birthDate,
                        'postal_code' => $postalCode,
                        'city' => $city,
                        'phone' => $phone,
                    ],
                    $photoPath
                );

                if (($res['ok'] ?? false) === true && isset($res['user']) && is_array($res['user'])) {
                    cityzen_apply_session_user($res['user']);
                    $_SESSION[$flashKey] = ['type' => 'success', 'msg' => 'Informations mises a jour.'];
                } else {
                    $_SESSION[$flashKey] = ['type' => 'error', 'msg' => (string) ($res['error'] ?? 'Mise a jour impossible.')];
                }

                $this->redirect($baseUrl, 303);
            }

            if (isset($_POST['change_password'])) {
                $errors = [];
                
                $currentPassword = (string) ($_POST['current_password'] ?? '');
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if ($currentPassword === '') {
                    $errors['current_password'] = 'Le mot de passe actuel est obligatoire.';
                }

                if (mb_strlen($newPassword) < 8) {
                    $errors['new_password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
                } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
                    $errors['new_password'] = 'Le mot de passe doit contenir au moins 1 lettre et 1 chiffre.';
                }

                if ($confirmPassword === '') {
                    $errors['confirm_password'] = 'La confirmation du mot de passe est obligatoire.';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
                }

                if ($errors !== []) {
                    $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'Corrigez les champs en erreur.'];
                    $_SESSION['password_errors'] = $errors;
                    $this->redirect($baseUrl, 303);
                }

                $res = $this->userModel->changePassword($sessionUserId, $currentPassword, $newPassword);
                if (($res['ok'] ?? false) === true && isset($res['user']) && is_array($res['user'])) {
                    cityzen_apply_session_user($res['user']);
                    $_SESSION[$flashKey] = ['type' => 'success', 'msg' => 'Mot de passe modifie.'];
                } else {
                    $_SESSION[$flashKey] = ['type' => 'error', 'msg' => (string) ($res['error'] ?? 'Changement du mot de passe impossible.')];
                }

                $this->redirect($baseUrl, 303);
            }
        }

        $flash = $_SESSION[$flashKey] ?? null;
        unset($_SESSION[$flashKey]);

        $currentUser = $sessionUserId > 0 ? $this->userModel->getById($sessionUserId) : null;
        if ($currentUser === null) {
            cityzen_agent_logout();
            $this->redirect(cityzen_login_url($baseUrl));
        }

        $data = $this->dashboardModel->data();
        $settingsMenu = cityzen_is_agent()
            ? ($data['admin_menu'] ?? [])
            : [
                ['key' => 'home', 'label' => 'Accueil', 'url' => '/index.php'],
                ['key' => 'settings', 'label' => 'Parametres', 'url' => '/controller/settings.php'],
                ['key' => 'logout', 'label' => 'Deconnexion', 'url' => '/controller/logout.php'],
            ];
        $qrProfile = $this->userModel->qrProfile($sessionUserId);

        View::render('admin/settings', [
            'cityzen' => $data,
            'flash' => $flash,
            'currentUser' => $currentUser,
            'settingsMenu' => $settingsMenu,
            'qrProfile' => $qrProfile,
        ]);
    }

    private function storeProfilePhoto(array $file, int $userId): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload de la photo echoue.'];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'error' => 'Fichier invalide.'];
        }

        if ($size < 1 || $size > 5 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'La photo doit faire moins de 5 Mo.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string) finfo_file($finfo, $tmp) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => '',
        };
        if ($ext === '') {
            return ['ok' => false, 'error' => 'Formats acceptes: JPG, PNG, WEBP.'];
        }

        $uploadDir = __DIR__ . '/../../storage/uploads/profile';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            return ['ok' => false, 'error' => 'Impossible de creer le dossier d\'upload.'];
        }

        $filename = 'u' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $target = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmp, $target)) {
            return ['ok' => false, 'error' => 'Impossible d\'enregistrer la photo.'];
        }

        return ['ok' => true, 'path' => '/storage/uploads/profile/' . $filename];
    }
}
