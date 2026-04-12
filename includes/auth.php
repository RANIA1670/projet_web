<?php

declare(strict_types=1);

require_once __DIR__ . '/users_store.php';

function cityzen_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function cityzen_agent_credentials(): array
{
    return [
        'user' => getenv('CITYZEN_AGENT_USER') ?: 'agent',
        'pass' => getenv('CITYZEN_AGENT_PASS') ?: 'cityzen',
    ];
}

function cityzen_apply_session_user(array $row): void
{
    cityzen_session_start();
    $_SESSION['cityzen_user'] = [
        'id' => (int) ($row['id'] ?? 0),
        'username' => (string) ($row['username'] ?? ''),
        'full_name' => (string) ($row['full_name'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'birth_date' => (string) ($row['birth_date'] ?? ''),
        'postal_code' => (string) ($row['postal_code'] ?? ''),
        'city' => (string) ($row['city'] ?? ''),
        'phone' => (string) ($row['phone'] ?? ''),
        'profile_photo' => (string) ($row['profile_photo'] ?? ''),
        'role' => (string) ($row['role'] ?? 'user'),
        'blocked' => (int) ($row['blocked'] ?? 0),
    ];
    unset($_SESSION['cityzen_agent']);
}

function cityzen_is_logged_in(): bool
{
    cityzen_session_start();
    $u = $_SESSION['cityzen_user'] ?? null;

    return is_array($u) && ($u['username'] ?? '') !== '';
}

function cityzen_is_agent(): bool
{
    cityzen_session_start();

    if (($_SESSION['cityzen_agent'] ?? false) === true) {
        return true;
    }

    return ($_SESSION['cityzen_user']['role'] ?? '') === 'admin';
}

function cityzen_authenticate_result(string $user, string $pass): array
{
    cityzen_session_start();
    $row = cityzen_find_user_by_login($user);

    if ($row !== null && cityzen_is_user_blocked($row)) {
        return ['ok' => false, 'error' => 'Votre compte est bloque. Contactez un administrateur.'];
    }

    if ($row !== null && cityzen_verify_password($row, $pass)) {
        cityzen_apply_session_user($row);

        return ['ok' => true];
    }

    $c = cityzen_agent_credentials();
    if (hash_equals($c['user'], $user) && hash_equals($c['pass'], $pass)) {
        cityzen_apply_session_user([
            'id' => 0,
            'username' => $c['user'],
            'full_name' => '',
            'email' => '',
            'role' => 'admin',
            'blocked' => 0,
        ]);

        return ['ok' => true];
    }

    return ['ok' => false, 'error' => 'Identifiants incorrects.'];
}

function cityzen_authenticate(string $user, string $pass): bool
{
    $result = cityzen_authenticate_result($user, $pass);

    return (bool) ($result['ok'] ?? false);
}

/** @deprecated Utiliser cityzen_authenticate ; conserve la compatibilite avec les pages existantes */
function cityzen_agent_login(string $user, string $pass): bool
{
    return cityzen_authenticate($user, $pass);
}

function cityzen_agent_logout(): void
{
    cityzen_session_start();
    unset($_SESSION['cityzen_user'], $_SESSION['cityzen_agent']);
}

function cityzen_csrf_token(): string
{
    cityzen_session_start();
    if (empty($_SESSION['cityzen_csrf'])) {
        $_SESSION['cityzen_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['cityzen_csrf'];
}

function cityzen_csrf_validate(?string $token): bool
{
    cityzen_session_start();
    $expected = (string) ($_SESSION['cityzen_csrf'] ?? '');

    return $expected !== '' && is_string($token) && hash_equals($expected, $token);
}

function cityzen_user_initials(): string
{
    cityzen_session_start();
    $name = trim((string) ($_SESSION['cityzen_user']['full_name'] ?? ''));
    if ($name === '') {
        $name = (string) ($_SESSION['cityzen_user']['username'] ?? '');
    }
    if ($name !== '') {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        if (count($parts) >= 2) {
            $first = mb_substr((string) $parts[0], 0, 1);
            $second = mb_substr((string) $parts[1], 0, 1);

            return mb_strtoupper($first . $second);
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    global $cityzen;

    return (string) ($cityzen['user']['initials'] ?? '??');
}

function cityzen_user_avatar_url(): ?string
{
    cityzen_session_start();
    $raw = trim((string) ($_SESSION['cityzen_user']['profile_photo'] ?? ''));
    if ($raw === '') {
        return null;
    }

    if (!str_starts_with($raw, '/')) {
        return null;
    }

    return cityzen_asset(ltrim($raw, '/'));
}

/**
 * @param  array<int, array{key: string, label: string, url: string}>  $items
 * @return array<int, array{key: string, label: string, url: string}>
 */
function cityzen_public_nav_items(array $items): array
{
    if (cityzen_is_agent()) {
        return $items;
    }

    return array_values(array_filter(
        $items,
        static fn (array $i): bool => ($i['key'] ?? '') !== 'back-office'
    ));
}

function cityzen_post_login_redirect(string $role, string $next): string
{
    $next = cityzen_safe_next($next);
    if ($role === 'admin') {
        return $next;
    }

    $dash = cityzen_asset('admin/dashboard.php');
    if ($next === $dash || str_contains($next, '/admin/dashboard.php')) {
        return cityzen_asset('index.php');
    }

    return $next;
}

/**
 * @param  array<int, array{key: string, label: string, url: string}>  $items
 * @return array<int, array{key: string, label: string, url: string}>
 */
function cityzen_full_public_nav(array $items): array
{
    $nav = cityzen_public_nav_items($items);
    if (cityzen_is_logged_in()) {
        $nav[] = ['key' => 'settings', 'label' => 'Parametres', 'url' => '/admin/settings.php'];
        $nav[] = ['key' => 'logout', 'label' => 'Deconnexion', 'url' => '/admin/logout.php'];
    } else {
        $nav[] = ['key' => 'register', 'label' => 'Creer un compte', 'url' => '/register.php'];
        $nav[] = ['key' => 'agent-login', 'label' => 'Connexion', 'url' => '/admin/login.php'];
    }

    return $nav;
}

function cityzen_safe_next(string $raw): string
{
    $fallback = cityzen_asset('admin/dashboard.php');
    $raw = rawurldecode(trim($raw));

    if ($raw === '') {
        return $fallback;
    }

    if ($raw[0] !== '/' || str_starts_with($raw, '//')) {
        return $fallback;
    }

    $base = cityzen_base_path();

    if ($base !== '' && $raw !== $base && !str_starts_with($raw, $base . '/')) {
        return $fallback;
    }

    if (str_contains($raw, "\0") || str_contains($raw, "\r") || str_contains($raw, "\n")) {
        return $fallback;
    }

    return $raw;
}

function cityzen_login_url(string $next = ''): string
{
    $login = cityzen_asset('admin/login.php');
    if ($next === '') {
        return $login;
    }

    return $login . '?next=' . rawurlencode($next);
}

function cityzen_require_agent(): void
{
    cityzen_session_start();

    if (cityzen_is_agent()) {
        return;
    }

    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $path = strtok($uri, '?') ?: '';
    $next = cityzen_safe_next($path);

    header('Location: ' . cityzen_login_url($next), true, 302);
    exit;
}
