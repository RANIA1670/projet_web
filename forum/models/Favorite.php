<?php
/**
 * Gestion des favoris utilisateur (posts).
 */
require_once __DIR__ . '/../config/Database.php';

class Favorite
{
    private static function db(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    public static function ensureTable(): void
    {
        try {
            self::db()->exec(
                "CREATE TABLE IF NOT EXISTS favorites (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    post_id INT NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_favorites_user_post (user_id, post_id),
                    INDEX idx_favorites_user (user_id),
                    INDEX idx_favorites_post (post_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Throwable $e) {
            error_log('Favorite::ensureTable ' . $e->getMessage());
        }
    }

    public static function exists(int $userId, int $postId): bool
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :u AND post_id = :p");
            $stmt->execute([':u' => $userId, ':p' => $postId]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (Throwable $e) {
            error_log('Favorite::exists ' . $e->getMessage());
            return false;
        }
    }

    public static function toggle(int $userId, int $postId): bool
    {
        self::ensureTable();
        try {
            if (self::exists($userId, $postId)) {
                $stmt = self::db()->prepare("DELETE FROM favorites WHERE user_id = :u AND post_id = :p");
                return $stmt->execute([':u' => $userId, ':p' => $postId]);
            }
            $stmt = self::db()->prepare("INSERT INTO favorites (user_id, post_id) VALUES (:u, :p)");
            return $stmt->execute([':u' => $userId, ':p' => $postId]);
        } catch (Throwable $e) {
            error_log('Favorite::toggle ' . $e->getMessage());
            return false;
        }
    }

    public static function countByPostId(int $postId): int
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare("SELECT COUNT(*) FROM favorites WHERE post_id = :p");
            $stmt->execute([':p' => $postId]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('Favorite::countByPostId ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Returns all post_ids favorited by a given user.
     */
    public static function findPostIdsByUserId(int $userId): array
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare(
                "SELECT post_id FROM favorites WHERE user_id = :u ORDER BY created_at DESC"
            );
            $stmt->execute([':u' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            error_log('Favorite::findPostIdsByUserId ' . $e->getMessage());
            return [];
        }
    }
}

