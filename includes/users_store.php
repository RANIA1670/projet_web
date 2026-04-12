<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * @return array{id: int, username: string, full_name: string, email: string, birth_date: string, postal_code: string, city: string, phone: string, profile_photo: string, password_hash: string, role: string, blocked: int, created_at: string, updated_at: string}
 */
function cityzen_user_row_normalize(array $row): array
{
    $created = $row['created_at'] ?? '';
    if ($created instanceof \DateTimeInterface) {
        $created = $created->format('Y-m-d H:i:s');
    }

    $updated = $row['updated_at'] ?? '';
    if ($updated instanceof \DateTimeInterface) {
        $updated = $updated->format('Y-m-d H:i:s');
    }

    $birthDate = $row['birth_date'] ?? '';
    if ($birthDate instanceof \DateTimeInterface) {
        $birthDate = $birthDate->format('Y-m-d');
    }

    return [
        'id' => (int) ($row['id'] ?? 0),
        'username' => (string) ($row['username'] ?? ''),
        'full_name' => (string) ($row['full_name'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'birth_date' => (string) $birthDate,
        'postal_code' => (string) ($row['postal_code'] ?? ''),
        'city' => (string) ($row['city'] ?? ''),
        'phone' => (string) ($row['phone'] ?? ''),
        'profile_photo' => (string) ($row['profile_photo'] ?? ''),
        'password_hash' => (string) ($row['password_hash'] ?? ''),
        'role' => (string) ($row['role'] ?? 'user'),
        'blocked' => (int) ($row['blocked'] ?? 0) === 1 ? 1 : 0,
        'created_at' => (string) $created,
        'updated_at' => (string) $updated,
    ];
}

function cityzen_user_select_sql(): string
{
    return 'id, username, full_name, email, birth_date, postal_code, city, phone, profile_photo, password_hash, role, blocked, created_at, updated_at';
}

/**
 * @return array{users: list<array{id: int, username: string, full_name: string, email: string, birth_date: string, postal_code: string, city: string, phone: string, profile_photo: string, password_hash: string, role: string, blocked: int, created_at: string, updated_at: string}>}
 */
function cityzen_users_load(): array
{
    $pdo = cityzen_db();
    $stmt = $pdo->query('SELECT ' . cityzen_user_select_sql() . ' FROM users ORDER BY id ASC');
    $rows = $stmt->fetchAll();
    $users = [];
    foreach ($rows as $row) {
        $users[] = cityzen_user_row_normalize($row);
    }

    return ['users' => $users];
}

function cityzen_find_user_by_username(string $username): ?array
{
    $username = trim($username);
    if ($username === '') {
        return null;
    }

    $pdo = cityzen_db();
    $stmt = $pdo->prepare('SELECT ' . cityzen_user_select_sql() . ' FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    return $row === false ? null : cityzen_user_row_normalize($row);
}

function cityzen_find_user_by_email(string $email): ?array
{
    $email = mb_strtolower(trim($email));
    if ($email === '') {
        return null;
    }

    $pdo = cityzen_db();
    $stmt = $pdo->prepare('SELECT ' . cityzen_user_select_sql() . ' FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    return $row === false ? null : cityzen_user_row_normalize($row);
}

function cityzen_find_user_by_login(string $login): ?array
{
    $login = trim($login);
    if ($login === '') {
        return null;
    }

    if (str_contains($login, '@')) {
        return cityzen_find_user_by_email($login);
    }

    return cityzen_find_user_by_username($login);
}

function cityzen_build_username_from_email(string $email): string
{
    $base = trim((string) preg_replace('/[^a-z0-9_]+/i', '_', explode('@', $email)[0] ?? 'user'));
    $base = trim($base, '_');
    if ($base === '') {
        $base = 'user';
    }
    $base = mb_substr($base, 0, 24);
    if ($base === '') {
        $base = 'user';
    }

    $candidate = $base;
    $i = 1;
    while (cityzen_find_user_by_username($candidate) !== null) {
        $suffix = '_' . $i;
        $limit = max(1, 32 - mb_strlen($suffix));
        $candidate = mb_substr($base, 0, $limit) . $suffix;
        $i++;
    }

    return $candidate;
}

/** Compatibilite legacy */
function cityzen_register_user(string $username, string $password): array
{
    if (str_contains($username, '@')) {
        return cityzen_register_user_with_email($username, $password, '');
    }

    $username = trim($username);
    if ($username === '' || mb_strlen($username) < 3 || mb_strlen($username) > 32) {
        return ['ok' => false, 'error' => 'Identifiant : 3 a 32 caracteres.'];
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/u', $username)) {
        return ['ok' => false, 'error' => 'Identifiant : lettres, chiffres et underscore uniquement.'];
    }

    if (mb_strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Mot de passe : au moins 8 caracteres.'];
    }

    if (cityzen_find_user_by_username($username) !== null) {
        return ['ok' => false, 'error' => 'Ce nom d\'utilisateur est deja pris.'];
    }

    $pdo = cityzen_db();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, password_hash, role, blocked, created_at) VALUES (?, ?, ?, 0, UTC_TIMESTAMP())'
        );
        $stmt->execute([$username, $hash, 'user']);
    } catch (PDOException) {
        return ['ok' => false, 'error' => 'Enregistrement impossible. Reessayez.'];
    }

    $created = cityzen_find_user_by_username($username);
    if ($created === null) {
        return ['ok' => false, 'error' => 'Erreur apres inscription.'];
    }

    return ['ok' => true, 'user' => $created];
}

function cityzen_register_user_with_email(string $email, string $password, string $fullName): array
{
    $email = mb_strtolower(trim($email));
    $fullName = trim($fullName);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        return ['ok' => false, 'error' => 'Email invalide.'];
    }

    if ($fullName !== '' && mb_strlen($fullName) > 120) {
        return ['ok' => false, 'error' => 'Nom complet trop long.'];
    }

    if (mb_strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Mot de passe : au moins 8 caracteres.'];
    }

    if (cityzen_find_user_by_email($email) !== null) {
        return ['ok' => false, 'error' => 'Cet email est deja utilise.'];
    }

    $username = cityzen_build_username_from_email($email);
    $pdo = cityzen_db();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, full_name, email, password_hash, role, blocked, created_at) VALUES (?, ?, ?, ?, ?, 0, UTC_TIMESTAMP())'
        );
        $stmt->execute([$username, $fullName !== '' ? $fullName : null, $email, $hash, 'user']);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            return ['ok' => false, 'error' => 'Cet email est deja utilise.'];
        }
        return ['ok' => false, 'error' => 'Enregistrement impossible. Reessayez.'];
    }

    $created = cityzen_find_user_by_email($email);
    if ($created === null) {
        return ['ok' => false, 'error' => 'Erreur apres inscription.'];
    }

    return ['ok' => true, 'user' => $created];
}

function cityzen_verify_password(array $user, string $password): bool
{
    $hash = (string) ($user['password_hash'] ?? '');

    return $hash !== '' && password_verify($password, $hash);
}

function cityzen_is_user_blocked(array $user): bool
{
    return (int) ($user['blocked'] ?? 0) === 1;
}

function cityzen_users_like_escape(string $q): string
{
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
}

function cityzen_user_get_by_id(int $id): ?array
{
    if ($id < 1) {
        return null;
    }

    $pdo = cityzen_db();
    $stmt = $pdo->prepare('SELECT ' . cityzen_user_select_sql() . ' FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row === false ? null : cityzen_user_row_normalize($row);
}

function cityzen_users_list_paginated(array $opts): array
{
    $page = max(1, (int) ($opts['page'] ?? 1));
    $perPage = min(100, max(5, (int) ($opts['per_page'] ?? 10)));
    $sortKey = (string) ($opts['sort'] ?? 'id');
    $dirRaw = strtoupper((string) ($opts['dir'] ?? 'DESC'));
    $dir = $dirRaw === 'ASC' ? 'ASC' : 'DESC';

    $sortCol = match ($sortKey) {
        'username' => 'username',
        'role' => 'role',
        'created_at' => 'created_at',
        default => 'id',
    };

    $q = trim((string) ($opts['q'] ?? ''));
    $pdo = cityzen_db();

    $whereSql = '';
    $bind = [];
    if ($q !== '') {
        $whereSql = 'WHERE (u.username LIKE ? ESCAPE \'\\\\\' OR u.email LIKE ? ESCAPE \'\\\\\' OR u.full_name LIKE ? ESCAPE \'\\\\\')';
        $like = '%' . cityzen_users_like_escape($q) . '%';
        $bind = [$like, $like, $like];
    }

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM users u ' . $whereSql);
    $countStmt->execute($bind);
    $total = (int) $countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $orderSql = 'u.' . $sortCol . ' ' . $dir;
    $listSql = 'SELECT u.' . str_replace(', ', ', u.', cityzen_user_select_sql()) . ' FROM users u '
        . $whereSql
        . ' ORDER BY ' . $orderSql
        . ' LIMIT ' . $perPage . ' OFFSET ' . $offset;

    $listStmt = $pdo->prepare($listSql);
    $listStmt->execute($bind);
    $rows = [];
    foreach ($listStmt->fetchAll() as $row) {
        $rows[] = cityzen_user_row_normalize($row);
    }

    return [
        'rows' => $rows,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'sort' => $sortCol,
        'dir' => $dir,
        'q' => $q,
    ];
}

function cityzen_users_export_rows(string $q, string $sortKey, string $dir, int $max = 500): array
{
    $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
    $sortCol = match ($sortKey) {
        'username' => 'username',
        'role' => 'role',
        'created_at' => 'created_at',
        default => 'id',
    };

    $pdo = cityzen_db();
    $whereSql = '';
    $bind = [];
    $q = trim($q);
    if ($q !== '') {
        $whereSql = 'WHERE (u.username LIKE ? ESCAPE \'\\\\\' OR u.email LIKE ? ESCAPE \'\\\\\' OR u.full_name LIKE ? ESCAPE \'\\\\\')';
        $like = '%' . cityzen_users_like_escape($q) . '%';
        $bind = [$like, $like, $like];
    }

    $listSql = 'SELECT u.' . str_replace(', ', ', u.', cityzen_user_select_sql()) . ' FROM users u '
        . $whereSql
        . ' ORDER BY u.' . $sortCol . ' ' . $dir
        . ' LIMIT ' . max(1, min(2000, $max));

    $stmt = $pdo->prepare($listSql);
    $stmt->execute($bind);
    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        $out[] = cityzen_user_row_normalize($row);
    }

    return $out;
}

function cityzen_delete_user(int $id, int $currentUserId): array
{
    if ($id < 1) {
        return ['ok' => false, 'error' => 'Identifiant invalide.'];
    }

    if ($id === $currentUserId) {
        return ['ok' => false, 'error' => 'Vous ne pouvez pas supprimer votre propre compte.'];
    }

    $user = cityzen_user_get_by_id($id);
    if ($user === null) {
        return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
    }

    $pdo = cityzen_db();
    if ($user['role'] === 'admin') {
        $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($adminCount <= 1) {
            return ['ok' => false, 'error' => 'Impossible de supprimer le dernier administrateur.'];
        }
    }

    $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $del->execute([$id]);

    return ['ok' => true];
}

function cityzen_update_user_admin(int $id, string $role, bool $blocked): array
{
    if ($id < 1) {
        return ['ok' => false, 'error' => 'Identifiant invalide.'];
    }

    if ($role !== 'user' && $role !== 'admin') {
        return ['ok' => false, 'error' => 'Role invalide.'];
    }

    $pdo = cityzen_db();
    $current = cityzen_user_get_by_id($id);
    if ($current === null) {
        return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
    }

    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ($current['role'] === 'admin' && $role === 'user' && $adminCount <= 1) {
        return ['ok' => false, 'error' => 'Il doit rester au moins un administrateur.'];
    }

    $activeAdminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND blocked = 0")->fetchColumn();
    if ($current['role'] === 'admin' && !$current['blocked'] && $blocked && $activeAdminCount <= 1) {
        return ['ok' => false, 'error' => 'Impossible de bloquer le dernier administrateur actif.'];
    }

    $upd = $pdo->prepare('UPDATE users SET role = ?, blocked = ? WHERE id = ?');
    $upd->execute([$role, $blocked ? 1 : 0, $id]);

    $after = cityzen_user_get_by_id($id);
    if ($after === null) {
        return ['ok' => false, 'error' => 'Erreur apres mise a jour.'];
    }

    return ['ok' => true, 'user' => $after];
}

function cityzen_user_stats(): array
{
    $pdo = cityzen_db();
    $row = $pdo->query(
        "SELECT COUNT(*) AS total,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admins,
                SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) AS users_count,
                SUM(CASE WHEN blocked = 1 THEN 1 ELSE 0 END) AS blocked_count
         FROM users"
    )->fetch();

    return [
        'total' => (int) ($row['total'] ?? 0),
        'admins' => (int) ($row['admins'] ?? 0),
        'users' => (int) ($row['users_count'] ?? 0),
        'blocked' => (int) ($row['blocked_count'] ?? 0),
    ];
}

function cityzen_validate_birth_date(string $birthDate): bool
{
    if ($birthDate === '') {
        return true;
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $birthDate);
    if (!$dt || $dt->format('Y-m-d') !== $birthDate) {
        return false;
    }

    $today = new DateTimeImmutable('today');
    $minDate = $today->modify('-120 years');

    return $dt <= $today && $dt >= $minDate;
}

function cityzen_update_profile(int $id, array $profile, ?string $profilePhotoPath = null): array
{
    $fullName = trim((string) ($profile['full_name'] ?? ''));
    $email = mb_strtolower(trim((string) ($profile['email'] ?? '')));
    $birthDate = trim((string) ($profile['birth_date'] ?? ''));
    $postalCode = trim((string) ($profile['postal_code'] ?? ''));
    $city = trim((string) ($profile['city'] ?? ''));
    $phone = trim((string) ($profile['phone'] ?? ''));

    if ($fullName !== '' && mb_strlen($fullName) > 120) {
        return ['ok' => false, 'error' => 'Nom complet trop long.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        return ['ok' => false, 'error' => 'Email invalide.'];
    }
    if (!cityzen_validate_birth_date($birthDate)) {
        return ['ok' => false, 'error' => 'Date de naissance invalide.'];
    }
    if ($postalCode !== '' && (!preg_match('/^[a-zA-Z0-9\\-\\s]{3,20}$/', $postalCode))) {
        return ['ok' => false, 'error' => 'Code postal invalide.'];
    }
    if ($city !== '' && mb_strlen($city) > 120) {
        return ['ok' => false, 'error' => 'Ville trop longue.'];
    }
    if ($phone !== '' && (!preg_match('/^[0-9+().\\-\\s]{6,30}$/', $phone))) {
        return ['ok' => false, 'error' => 'Numero de telephone invalide.'];
    }

    $pdo = cityzen_db();
    $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $dup->execute([$email, $id]);
    if ($dup->fetch() !== false) {
        return ['ok' => false, 'error' => 'Cet email est deja utilise.'];
    }

    $current = cityzen_user_get_by_id($id);
    if ($current === null) {
        return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
    }

    $finalPhoto = $current['profile_photo'];
    if (is_string($profilePhotoPath) && $profilePhotoPath !== '') {
        $finalPhoto = $profilePhotoPath;
    }

    $upd = $pdo->prepare(
        'UPDATE users SET full_name = ?, email = ?, birth_date = ?, postal_code = ?, city = ?, phone = ?, profile_photo = ? WHERE id = ?'
    );
    $upd->execute([
        $fullName !== '' ? $fullName : null,
        $email,
        $birthDate !== '' ? $birthDate : null,
        $postalCode !== '' ? $postalCode : null,
        $city !== '' ? $city : null,
        $phone !== '' ? $phone : null,
        $finalPhoto !== '' ? $finalPhoto : null,
        $id,
    ]);

    $user = cityzen_user_get_by_id($id);
    if ($user === null) {
        return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
    }

    return ['ok' => true, 'user' => $user];
}

function cityzen_change_user_password(int $id, string $currentPassword, string $newPassword): array
{
    $user = cityzen_user_get_by_id($id);
    if ($user === null) {
        return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
    }

    if (!cityzen_verify_password($user, $currentPassword)) {
        return ['ok' => false, 'error' => 'Mot de passe actuel incorrect.'];
    }

    if (mb_strlen($newPassword) < 8) {
        return ['ok' => false, 'error' => 'Le nouveau mot de passe doit contenir au moins 8 caracteres.'];
    }

    $pdo = cityzen_db();
    $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $upd->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);

    $after = cityzen_user_get_by_id($id);
    if ($after === null) {
        return ['ok' => false, 'error' => 'Erreur apres changement du mot de passe.'];
    }

    return ['ok' => true, 'user' => $after];
}

function cityzen_reset_user_password_by_email(string $email, string $newPassword): array
{
    $email = mb_strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Email invalide.'];
    }

    if (mb_strlen($newPassword) < 8) {
        return ['ok' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caracteres.'];
    }

    $user = cityzen_find_user_by_email($email);
    if ($user === null) {
        return ['ok' => false, 'error' => 'Aucun compte ne correspond a cet email.'];
    }

    $pdo = cityzen_db();
    $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $upd->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int) $user['id']]);

    $after = cityzen_user_get_by_id((int) $user['id']);
    if ($after === null) {
        return ['ok' => false, 'error' => 'Erreur apres reinitialisation.'];
    }

    return ['ok' => true, 'user' => $after];
}
