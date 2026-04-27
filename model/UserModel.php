<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use PDO;

final class UserModel
{
    public function register(string $email, string $password, string $fullName): array
    {
        return cityzen_register_user_with_email($email, $password, $fullName);
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
        } catch (\Throwable) {
            return null;
        }
    }

    public function resetPasswordByEmail(string $email, string $newPassword): array
    {
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            return ['ok' => false, 'error' => 'Email invalide.'];
        }

        if (mb_strlen($newPassword) < 8) {
            return ['ok' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caracteres.'];
        }

        $user = $this->findByEmail($email);
        if ($user === null) {
            return ['ok' => false, 'error' => 'Aucun compte ne correspond a cet email.'];
        }

        try {
            $pdo = cityzen_db();
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int) $user['id']]);

            $after = $this->getById((int) $user['id']);
            if ($after === null) {
                return ['ok' => false, 'error' => 'Erreur apres reinitialisation.'];
            }

            return ['ok' => true, 'user' => $after];
        } catch (\Throwable) {
            return ['ok' => false, 'error' => 'Reinitialisation impossible.'];
        }
    }

    public function updateProfile(int $id, array $profile, ?string $profilePhotoPath = null): array
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
        if (!$this->validateBirthDate($birthDate)) {
            return ['ok' => false, 'error' => 'Date de naissance invalide.'];
        }
        if ($postalCode !== '' && preg_match('/^[a-zA-Z0-9\\-\\s]{3,20}$/', $postalCode) !== 1) {
            return ['ok' => false, 'error' => 'Code postal invalide.'];
        }
        if ($city !== '' && mb_strlen($city) > 120) {
            return ['ok' => false, 'error' => 'Ville trop longue.'];
        }
        if ($phone !== '' && preg_match('/^[0-9+().\\-\\s]{6,30}$/', $phone) !== 1) {
            return ['ok' => false, 'error' => 'Numero de telephone invalide.'];
        }

        $current = $this->getById($id);
        if ($current === null) {
            return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
        }

        try {
            $pdo = cityzen_db();
            $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
            $dup->execute([$email, $id]);
            if ($dup->fetch() !== false) {
                return ['ok' => false, 'error' => 'Cet email est deja utilise.'];
            }

            $finalPhoto = $current['profile_photo'];
            if (is_string($profilePhotoPath) && $profilePhotoPath !== '') {
                $finalPhoto = $profilePhotoPath;
            }

            $upd = $pdo->prepare(
                'UPDATE users SET full_name = ?, email = ?, birth_date = ?, postal_code = ?, city = ?, phone = ?, profile_photo = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?'
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

            $user = $this->getById($id);
            if ($user === null) {
                return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
            }

            return ['ok' => true, 'user' => $user];
        } catch (\Throwable) {
            return ['ok' => false, 'error' => 'Mise a jour impossible.'];
        }
    }

    public function changePassword(int $id, string $currentPassword, string $newPassword): array
    {
        $user = $this->getById($id);
        if ($user === null) {
            return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
        }

        $hash = (string) ($user['password_hash'] ?? '');
        if ($hash === '' || !password_verify($currentPassword, $hash)) {
            return ['ok' => false, 'error' => 'Mot de passe actuel incorrect.'];
        }

        if (mb_strlen($newPassword) < 8) {
            return ['ok' => false, 'error' => 'Le nouveau mot de passe doit contenir au moins 8 caracteres.'];
        }

        try {
            $pdo = cityzen_db();
            $upd = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?');
            $upd->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);

            $after = $this->getById($id);
            if ($after === null) {
                return ['ok' => false, 'error' => 'Erreur apres changement du mot de passe.'];
            }

            return ['ok' => true, 'user' => $after];
        } catch (\Throwable) {
            return ['ok' => false, 'error' => 'Changement du mot de passe impossible.'];
        }
    }

    public function qrProfile(int $id): array
    {
        $user = $this->ensureUserQrToken($id);
        if ($user === null) {
            return ['ok' => false, 'error' => 'Utilisateur introuvable.'];
        }

        $targetPath = cityzen_asset('controller/user_card.php') . '?' . http_build_query(['t' => $user['qr_token']]);
        $targetUrl = cityzen_absolute_url($targetPath);

        return [
            'ok' => true,
            'token' => (string) $user['qr_token'],
            'target_path' => $targetPath,
            'target_url' => $targetUrl,
            'image_url' => $this->qrApiImageUrl($targetUrl),
        ];
    }

    public function getByQrToken(string $token): ?array
    {
        $token = strtolower(trim($token));
        if ($token === '' || preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return null;
        }

        try {
            $pdo = cityzen_db();
            $stmt = $pdo->prepare('SELECT ' . $this->userSelectSql() . ' FROM users WHERE qr_token = ? LIMIT 1');
            $stmt->execute([$token]);
            $row = $stmt->fetch();

            return is_array($row) ? $this->normalizeUserRow($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function stats(): array
    {
        try {
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
        } catch (\Throwable) {
            return ['total' => 0, 'admins' => 0, 'users' => 0, 'blocked' => 0];
        }
    }

    private function ensureUserQrToken(int $id): ?array
    {
        $user = $this->getById($id);
        if ($user === null) {
            return null;
        }

        $existing = trim((string) ($user['qr_token'] ?? ''));
        if ($existing !== '' && preg_match('/^[a-f0-9]{64}$/', $existing) === 1) {
            return $user;
        }

        try {
            $pdo = cityzen_db();
            $token = $this->generateUniqueQrToken($pdo);
            $upd = $pdo->prepare('UPDATE users SET qr_token = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?');
            $upd->execute([$token, $id]);

            return $this->getById($id);
        } catch (\Throwable) {
            return null;
        }
    }

    private function generateUniqueQrToken(PDO $pdo): string
    {
        do {
            $token = bin2hex(random_bytes(32));
            $check = $pdo->prepare('SELECT id FROM users WHERE qr_token = ? LIMIT 1');
            $check->execute([$token]);
        } while ($check->fetch() !== false);

        return $token;
    }

    private function qrApiImageUrl(string $targetUrl, int $size = 280): string
    {
        $finalSize = max(120, min(600, $size));
        $base = rtrim((string) (getenv('CITYZEN_QR_API_BASE') ?: 'https://api.qrserver.com/v1/create-qr-code/'), '?&');

        return $base . '?' . http_build_query([
            'size' => $finalSize . 'x' . $finalSize,
            'format' => 'png',
            'margin' => 12,
            'data' => $targetUrl,
        ]);
    }

    private function findByEmail(string $email): ?array
    {
        try {
            $pdo = cityzen_db();
            $stmt = $pdo->prepare('SELECT ' . $this->userSelectSql() . ' FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $row = $stmt->fetch();

            return is_array($row) ? $this->normalizeUserRow($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function validateBirthDate(string $birthDate): bool
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

    private function userSelectSql(): string
    {
        return 'id, username, full_name, email, birth_date, postal_code, city, phone, profile_photo, qr_token, password_hash, role, blocked, created_at, updated_at';
    }

    private function normalizeUserRow(array $row): array
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
            'qr_token' => (string) ($row['qr_token'] ?? ''),
            'password_hash' => (string) ($row['password_hash'] ?? ''),
            'role' => (string) ($row['role'] ?? 'user'),
            'blocked' => (int) ($row['blocked'] ?? 0) === 1 ? 1 : 0,
            'created_at' => (string) $created,
            'updated_at' => (string) $updated,
        ];
    }
}
