<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * @return array{id: int, username: string, password_hash: string, role: string, created_at: string}
 */
function cityzen_user_row_normalize(array $row): array
{
    $created = $row['created_at'] ?? '';
    if ($created instanceof \DateTimeInterface) {
        $created = $created->format('Y-m-d H:i:s');
    }

    return [
        'id' => (int) ($row['id'] ?? 0),
        'username' => (string) ($row['username'] ?? ''),
        'password_hash' => (string) ($row['password_hash'] ?? ''),
        'role' => (string) ($row['role'] ?? 'user'),
        'created_at' => (string) $created,
    ];
}

/**
 * @return array{users: list<array{id: int, username: string, password_hash: string, role: string, created_at: string}>}
 */
function cityzen_users_load(): array
{
    $pdo = cityzen_db();
    $stmt = $pdo->query(
        'SELECT id, username, password_hash, role, created_at FROM users ORDER BY id ASC'
    );
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
    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash, role, created_at FROM users WHERE username = ? LIMIT 1'
    );
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    return $row === false ? null : cityzen_user_row_normalize($row);
}

/** Inscription publique : toujours compte citoyen (`user`). Le role admin se regle dans le tableau de bord. */
function cityzen_register_user(string $username, string $password): array
{
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
            'INSERT INTO users (username, password_hash, role, created_at) VALUES (?, ?, ?, UTC_TIMESTAMP())'
        );
        $stmt->execute([$username, $hash, 'user']);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate')) {
            return ['ok' => false, 'error' => 'Ce nom d\'utilisateur est deja pris.'];
        }

        return ['ok' => false, 'error' => 'Enregistrement impossible. Reessayez.'];
    }

    $created = cityzen_find_user_by_username($username);
    if ($created === null) {
        return ['ok' => false, 'error' => 'Erreur apres inscription.'];
    }

    return ['ok' => true, 'user' => $created];
}

/**
 * @return array{ok: bool, error?: string, user?: array{id: int, username: string, password_hash: string, role: string, created_at: string}}
 */
function cityzen_update_user_role(int $userId, string $newRole): array
{
    if ($newRole !== 'user' && $newRole !== 'admin') {
        return ['ok' => false, 'error' => 'Role invalide.'];
    }

    $pdo = cityzen_db();
    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash, role, created_at FROM users WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$userId]);
    $current = $stmt->fetch();

    if ($current === false) {
        return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
    }

    $current = cityzen_user_row_normalize($current);

    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

    if ($current['role'] === 'admin' && $newRole === 'user' && $adminCount <= 1) {
        return ['ok' => false, 'error' => 'Il doit rester au moins un administrateur.'];
    }

    $upd = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
    $upd->execute([$newRole, $userId]);

    $stmt->execute([$userId]);
    $after = $stmt->fetch();
    if ($after === false) {
        return ['ok' => false, 'error' => 'Erreur apres mise a jour.'];
    }

    return ['ok' => true, 'user' => cityzen_user_row_normalize($after)];
}

function cityzen_verify_password(array $user, string $password): bool
{
    $hash = (string) ($user['password_hash'] ?? '');

    return $hash !== '' && password_verify($password, $hash);
}
