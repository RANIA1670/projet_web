<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Helpers\MailService;
use App\Models\UserModel;

final class AuthController extends Controller
{
    private const REGISTER_QR_SESSION_TOKEN = 'cityzen_register_qr_token';
    private const REGISTER_QR_SESSION_VALIDATED = 'cityzen_register_qr_validated';
    private const REGISTER_QR_SESSION_VALIDATED_AT = 'cityzen_register_qr_validated_at';
    private const REGISTER_QR_VALIDATION_TTL_SECONDS = 900;

    private UserModel $userModel;
    private MailService $mailService;

    public function __construct(?UserModel $userModel = null, ?MailService $mailService = null)
    {
        $this->userModel = $userModel ?? new UserModel();
        $this->mailService = $mailService ?? new MailService();
    }

    public function login(): void
    {
        $error = '';
        $errors = [];
        $next = \cityzen_safe_next((string) ($_GET['next'] ?? ''));
        $old = ['user' => ''];

        if (\cityzen_is_logged_in()) {
            if (\cityzen_is_agent()) {
                $this->redirect(\cityzen_asset('controller/dashboard.php'));
            }
            $this->redirect(\cityzen_asset('index.php'));
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            if (!\cityzen_csrf_validate($_POST['csrf'] ?? null)) {
                $error = 'Session expiree : rechargez la page puis reessayez.';
            } else {
                $user = trim((string) ($_POST['user'] ?? ''));
                $pass = (string) ($_POST['pass'] ?? '');
                $next = \cityzen_safe_next((string) ($_POST['next'] ?? ''));
                $old['user'] = $user;

                if ($user === '') {
                    $errors['user'] = 'Saisissez votre email ou votre nom d\'utilisateur.';
                } elseif (\str_contains($user, '@') && !filter_var($user, FILTER_VALIDATE_EMAIL)) {
                    $errors['user'] = 'Format d\'email invalide.';
                } elseif (!\str_contains($user, '@') && !preg_match('/^[a-zA-Z0-9_]{3,32}$/', $user)) {
                    $errors['user'] = 'Nom d\'utilisateur invalide (3-32, lettres/chiffres/underscore).';
                }

                if ($pass === '') {
                    $errors['pass'] = 'Saisissez votre mot de passe.';
                }

                if ($errors !== []) {
                    $error = 'Corrigez les champs en erreur.';
                    View::render('auth/login', [
                        'error' => $error,
                        'errors' => $errors,
                        'old' => $old,
                        'next' => $next,
                    ]);
                    return;
                }

                $auth = \cityzen_authenticate_result($user, $pass);
                if (($auth['ok'] ?? false) === true) {
                    $role = (string) ($_SESSION['cityzen_user']['role'] ?? 'user');
                    $target = \cityzen_post_login_redirect($role, $next);
                    $this->redirect($target);
                }

                $error = (string) ($auth['error'] ?? 'Identifiants incorrects.');
            }
        }

        View::render('auth/login', [
            'error' => $error,
            'errors' => $errors,
            'old' => $old,
            'next' => $next,
        ]);
    }

    public function register(): void
    {
        $error = '';
        $errors = [];
        $old = ['full_name' => '', 'email' => ''];

        if (\cityzen_is_logged_in()) {
            if (\cityzen_is_agent()) {
                $this->redirect(\cityzen_asset('controller/dashboard.php'));
            }
            $this->redirect(\cityzen_asset('index.php'));
        }

        $qrGate = $this->registerQrGatePayload();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            if (!\cityzen_csrf_validate($_POST['csrf'] ?? null)) {
                $error = 'Session expiree : rechargez la page puis reessayez.';
            } else {
                $fullName = trim((string) ($_POST['full_name'] ?? ''));
                $email = trim((string) ($_POST['email'] ?? ''));
                $pass = (string) ($_POST['pass'] ?? '');
                $pass2 = (string) ($_POST['pass2'] ?? '');
                $old['full_name'] = $fullName;
                $old['email'] = $email;

                if (!$this->isRegisterQrValidated()) {
                    $errors['qr'] = 'Scannez le QR code de validation avant de creer votre compte.';
                }

                if ($fullName === '') {
                    $errors['full_name'] = 'Le nom complet est obligatoire.';
                } elseif (!preg_match('/^[\\p{L}\\p{M}][\\p{L}\\p{M}\\s\'\\-]{1,119}$/u', $fullName)) {
                    $errors['full_name'] = 'Nom invalide (lettres, espaces, tiret, apostrophe).';
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
                    $errors['email'] = 'Email invalide.';
                }

                if (mb_strlen($pass) < 8) {
                    $errors['pass'] = 'Le mot de passe doit contenir au moins 8 caracteres.';
                } elseif (!preg_match('/[A-Za-z]/', $pass) || !preg_match('/\\d/', $pass)) {
                    $errors['pass'] = 'Le mot de passe doit contenir au moins 1 lettre et 1 chiffre.';
                }

                if ($pass !== $pass2) {
                    $errors['pass2'] = 'Les mots de passe ne correspondent pas.';
                }

                if ($errors !== []) {
                    $error = 'Corrigez les champs en erreur.';
                    View::render('auth/register', [
                        'error' => $error,
                        'errors' => $errors,
                        'old' => $old,
                        'qrGate' => $this->registerQrGatePayload(),
                    ]);
                    return;
                }

                $result = $this->userModel->register($email, $pass, $fullName);
                if (($result['ok'] ?? false) === true && isset($result['user']) && is_array($result['user'])) {
                    $this->sendWelcomeEmail($email, $fullName);
                    $this->resetRegisterQrGate();
                    \cityzen_apply_session_user($result['user']);
                    $this->redirect(\cityzen_asset('index.php'));
                } else {
                    $error = (string) ($result['error'] ?? 'Inscription refusee.');
                }
            }
        }

        View::render('auth/register', [
            'error' => $error,
            'errors' => $errors,
            'old' => $old,
            'qrGate' => $qrGate,
        ]);
    }

    public function forgotPassword(): void
    {
        $error = '';
        $success = '';
        $errors = [];
        $old = ['email' => ''];

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            if (!\cityzen_csrf_validate($_POST['csrf'] ?? null)) {
                $error = 'Session expiree : rechargez la page puis reessayez.';
            } else {
                $email = trim((string) ($_POST['email'] ?? ''));
                $pass = (string) ($_POST['pass'] ?? '');
                $pass2 = (string) ($_POST['pass2'] ?? '');
                $old['email'] = $email;

                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
                    $errors['email'] = 'Email invalide.';
                }

                if (mb_strlen($pass) < 8) {
                    $errors['pass'] = 'Le mot de passe doit contenir au moins 8 caracteres.';
                } elseif (!preg_match('/[A-Za-z]/', $pass) || !preg_match('/\\d/', $pass)) {
                    $errors['pass'] = 'Le mot de passe doit contenir au moins 1 lettre et 1 chiffre.';
                }

                if ($pass !== $pass2) {
                    $errors['pass2'] = 'Les mots de passe ne correspondent pas.';
                }

                if ($errors !== []) {
                    $error = 'Corrigez les champs en erreur.';
                    View::render('auth/forgot_password', [
                        'error' => $error,
                        'errors' => $errors,
                        'old' => $old,
                        'success' => $success,
                    ]);
                    return;
                } else {
                    $res = $this->userModel->resetPasswordByEmail($email, $pass);
                    if (($res['ok'] ?? false) === true) {
                        $success = 'Mot de passe modifie. Vous pouvez maintenant vous connecter.';
                        $mail = $this->sendPasswordResetNotificationEmail($email);
                        $mailError = (string) ($mail['error'] ?? '');
                    if (($mail['ok'] ?? false) !== true && !\str_contains($mailError, 'SMTP desactive')) {
                            $success .= ' Notification email non envoyee.';
                        }
                    } else {
                        $error = (string) ($res['error'] ?? 'Reinitialisation impossible.');
                    }
                }
            }
        }

        View::render('auth/forgot_password', [
            'error' => $error,
            'errors' => $errors,
            'old' => $old,
            'success' => $success,
        ]);
    }

    public function logout(): void
    {
        \cityzen_agent_logout();
        $this->redirect(\cityzen_asset('index.php'));
    }

    /**
     * @return array{ok: bool, error: string}
     */
    private function sendWelcomeEmail(string $email, string $fullName): array
    {
        $name = trim($fullName) !== '' ? trim($fullName) : 'Utilisateur';
        $subject = 'Bienvenue sur CityZen';
        $html = '<h2>Bienvenue ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h2>'
            . '<p>Votre compte a ete cree avec succes.</p>'
            . '<p>Vous pouvez maintenant utiliser les services CityZen.</p>';
        $text = 'Bienvenue ' . $name . '. Votre compte CityZen a ete cree avec succes.';

        return $this->mailService->send($email, $subject, $html, $text);
    }

    /**
     * @return array{ok: bool, error: string}
     */
    private function sendPasswordResetNotificationEmail(string $email): array
    {
        $subject = 'Mot de passe modifie - CityZen';
        $html = "<p>Votre mot de passe CityZen vient d'etre modifie.</p>"
            . "<p>Si vous n'etes pas a l'origine de cette action, contactez rapidement l'administrateur.</p>";
        $text = "Votre mot de passe CityZen vient d'etre modifie. Si ce n'est pas vous, contactez l'administrateur.";

        return $this->mailService->send($email, $subject, $html, $text);
    }

    /**
     * @return array{scan_url: string, image_url: string, validated: bool, validation_error: string}
     */
    private function registerQrGatePayload(): array
    {
        $token = $this->registerQrGateToken();
        $validationError = '';
        $qrFromQuery = trim((string) ($_GET['qr'] ?? ''));
        if ($qrFromQuery !== '') {
            if (hash_equals($token, strtolower($qrFromQuery))) {
                $_SESSION[self::REGISTER_QR_SESSION_VALIDATED] = true;
                $_SESSION[self::REGISTER_QR_SESSION_VALIDATED_AT] = gmdate('c');
            } else {
                $validationError = 'QR invalide ou expire. Rechargez la page puis scannez a nouveau.';
            }
        }

        $validated = $this->isRegisterQrValidated();
        $scanPath = cityzen_asset('register.php') . '?' . http_build_query(['qr' => $token]);
        $scanUrl = \cityzen_absolute_url($scanPath);

        return [
            'scan_url' => $scanUrl,
            'image_url' => $this->qrImageUrlFor($scanUrl),
            'validated' => $validated,
            'validation_error' => $validationError,
        ];
    }

    private function registerQrGateToken(): string
    {
        \cityzen_session_start();

        $token = strtolower((string) ($_SESSION[self::REGISTER_QR_SESSION_TOKEN] ?? ''));
        if (preg_match('/^[a-f0-9]{32}$/', $token) !== 1) {
            $token = bin2hex(random_bytes(16));
            $_SESSION[self::REGISTER_QR_SESSION_TOKEN] = $token;
            $_SESSION[self::REGISTER_QR_SESSION_VALIDATED] = false;
            unset($_SESSION[self::REGISTER_QR_SESSION_VALIDATED_AT]);
        }

        return $token;
    }

    private function isRegisterQrValidated(): bool
    {
        \cityzen_session_start();
        if (($_SESSION[self::REGISTER_QR_SESSION_VALIDATED] ?? false) !== true) {
            return false;
        }

        $validatedAtRaw = (string) ($_SESSION[self::REGISTER_QR_SESSION_VALIDATED_AT] ?? '');
        $validatedAt = strtotime($validatedAtRaw);
        if ($validatedAt === false) {
            $_SESSION[self::REGISTER_QR_SESSION_VALIDATED] = false;
            unset($_SESSION[self::REGISTER_QR_SESSION_VALIDATED_AT]);
            return false;
        }

        if ((time() - $validatedAt) > self::REGISTER_QR_VALIDATION_TTL_SECONDS) {
            $_SESSION[self::REGISTER_QR_SESSION_VALIDATED] = false;
            unset($_SESSION[self::REGISTER_QR_SESSION_VALIDATED_AT]);
            return false;
        }

        return true;
    }

    private function resetRegisterQrGate(): void
    {
        \cityzen_session_start();
        unset(
            $_SESSION[self::REGISTER_QR_SESSION_TOKEN],
            $_SESSION[self::REGISTER_QR_SESSION_VALIDATED],
            $_SESSION[self::REGISTER_QR_SESSION_VALIDATED_AT]
        );
    }

    private function qrImageUrlFor(string $targetUrl): string
    {
        $base = 'https://api.qrserver.com/v1/create-qr-code/';

        return $base . '?' . http_build_query([
            'size' => '280x280',
            'format' => 'png',
            'margin' => 12,
            'data' => $targetUrl,
        ]);
    }
}
