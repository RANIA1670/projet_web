<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Throwable;

final class UserStoreController
{
    private function userSelectSql(): string
    {
        return 'id, username, full_name, email, birth_date, postal_code, city, phone, profile_photo, qr_token, password_hash, role, blocked, created_at, updated_at';
    }

    private function normalizeUserRow(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'username' => (string) ($row['username'] ?? ''),
            'full_name' => (string) ($row['full_name'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'birth_date' => (string) ($row['birth_date'] ?? ''),
            'postal_code' => (string) ($row['postal_code'] ?? ''),
            'city' => (string) ($row['city'] ?? ''),
            'phone' => (string) ($row['phone'] ?? ''),
            'profile_photo' => (string) ($row['profile_photo'] ?? ''),
            'qr_token' => (string) ($row['qr_token'] ?? ''),
            'password_hash' => (string) ($row['password_hash'] ?? ''),
            'role' => (string) ($row['role'] ?? 'user'),
            'blocked' => (int) ($row['blocked'] ?? 0) === 1 ? 1 : 0,
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }

    private function likeEscape(string $q): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
    }

    public function getById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        try {
            $pdo = cityzen_db();
            $stmt = $pdo->prepare('SELECT ' . $this->userSelectSql() . ' FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            return is_array($row) ? $this->normalizeUserRow($row) : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function listPaginated(array $opts): array
    {
        $page = max(1, (int) ($opts['page'] ?? 1));
        $perPage = min(100, max(5, (int) ($opts['per_page'] ?? 10)));
        $sortKey = (string) ($opts['sort'] ?? 'id');
        $dir = strtoupper((string) ($opts['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $q = trim((string) ($opts['q'] ?? ''));

        $sortCol = match ($sortKey) {
            'username' => 'username',
            'full_name' => 'full_name',
            'role' => 'role',
            'created_at' => 'created_at',
            default => 'id',
        };

        $pdo = cityzen_db();
        $whereSql = '';
        $bind = [];
        if ($q !== '') {
            $whereSql = "WHERE (u.username LIKE ? ESCAPE '\\\\' OR u.email LIKE ? ESCAPE '\\\\' OR u.full_name LIKE ? ESCAPE '\\\\')";
            $like = '%' . $this->likeEscape($q) . '%';
            $bind = [$like, $like, $like];
        }

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM users u ' . $whereSql);
        $countStmt->execute($bind);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT u.' . str_replace(', ', ', u.', $this->userSelectSql()) . ' FROM users u '
            . $whereSql
            . ' ORDER BY u.' . $sortCol . ' ' . $dir
            . ' LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bind);

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            if (is_array($row)) {
                $rows[] = $this->normalizeUserRow($row);
            }
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

    public function exportRows(string $q, string $sortKey, string $dir, int $max = 500): array
    {
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        $sortCol = match ($sortKey) {
            'username' => 'username',
            'full_name' => 'full_name',
            'role' => 'role',
            'created_at' => 'created_at',
            default => 'id',
        };

        $q = trim($q);
        $whereSql = '';
        $bind = [];
        if ($q !== '') {
            $whereSql = "WHERE (u.username LIKE ? ESCAPE '\\\\' OR u.email LIKE ? ESCAPE '\\\\' OR u.full_name LIKE ? ESCAPE '\\\\')";
            $like = '%' . $this->likeEscape($q) . '%';
            $bind = [$like, $like, $like];
        }

        $pdo = cityzen_db();
        $sql = 'SELECT u.' . str_replace(', ', ', u.', $this->userSelectSql()) . ' FROM users u '
            . $whereSql
            . ' ORDER BY u.' . $sortCol . ' ' . $dir
            . ' LIMIT ' . max(1, min(2000, $max));
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bind);

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            if (is_array($row)) {
                $rows[] = $this->normalizeUserRow($row);
            }
        }

        return $rows;
    }

    public function delete(int $id, int $currentUserId): array
    {
        if ($id < 1) {
            return ['ok' => false, 'error' => 'Identifiant invalide.'];
        }
        if ($id === $currentUserId) {
            return ['ok' => false, 'error' => 'Vous ne pouvez pas supprimer votre propre compte.'];
        }

        $user = $this->getById($id);
        if ($user === null) {
            return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
        }

        $pdo = cityzen_db();
        if ((string) ($user['role'] ?? 'user') === 'admin') {
            $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
            if ($adminCount <= 1) {
                return ['ok' => false, 'error' => 'Impossible de supprimer le dernier administrateur.'];
            }
        }

        $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $del->execute([$id]);

        return ['ok' => true];
    }

    public function updateAdmin(int $id, string $role, bool $blocked): array
    {
        if ($id < 1) {
            return ['ok' => false, 'error' => 'Identifiant invalide.'];
        }
        if ($role !== 'user' && $role !== 'admin') {
            return ['ok' => false, 'error' => 'Role invalide.'];
        }

        $pdo = cityzen_db();
        $current = $this->getById($id);
        if ($current === null) {
            return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
        }

        $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ((string) $current['role'] === 'admin' && $role === 'user' && $adminCount <= 1) {
            return ['ok' => false, 'error' => 'Il doit rester au moins un administrateur.'];
        }

        $activeAdminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND blocked = 0")->fetchColumn();
        if ((string) $current['role'] === 'admin' && (int) $current['blocked'] === 0 && $blocked && $activeAdminCount <= 1) {
            return ['ok' => false, 'error' => 'Impossible de bloquer le dernier administrateur actif.'];
        }

        $upd = $pdo->prepare('UPDATE users SET role = ?, blocked = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?');
        $upd->execute([$role, $blocked ? 1 : 0, $id]);

        $after = $this->getById($id);
        if ($after === null) {
            return ['ok' => false, 'error' => 'Erreur apres mise a jour.'];
        }

        return ['ok' => true, 'user' => $after];
    }
}
