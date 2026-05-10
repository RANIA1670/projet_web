<?php
/**
 * Notifications temps réel simples (polling).
 */
require_once __DIR__ . '/../config/Database.php';

class Notification
{
    private static function db(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    public static function ensureTable(): void
    {
        try {
            self::db()->exec(
                "CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    actor_user_id INT NULL DEFAULT NULL,
                    type VARCHAR(50) NOT NULL,
                    post_id INT NULL DEFAULT NULL,
                    reply_id INT NULL DEFAULT NULL,
                    message VARCHAR(255) NOT NULL,
                    is_read TINYINT(1) NOT NULL DEFAULT 0,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_notifications_user_read (user_id, is_read),
                    INDEX idx_notifications_created (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Throwable $e) {
            error_log('Notification::ensureTable ' . $e->getMessage());
        }
    }

    public static function create(
        int $userId,
        string $type,
        string $message,
        int $actorUserId = 0,
        int $postId = 0,
        int $replyId = 0
    ): bool {
        self::ensureTable();
        if ($userId <= 0 || trim($message) === '') {
            return false;
        }
        try {
            $stmt = self::db()->prepare(
                "INSERT INTO notifications (user_id, actor_user_id, type, post_id, reply_id, message)
                 VALUES (:user_id, :actor_user_id, :type, :post_id, :reply_id, :message)"
            );
            return $stmt->execute([
                ':user_id' => $userId,
                ':actor_user_id' => $actorUserId > 0 ? $actorUserId : null,
                ':type' => $type,
                ':post_id' => $postId > 0 ? $postId : null,
                ':reply_id' => $replyId > 0 ? $replyId : null,
                ':message' => trim($message),
            ]);
        } catch (Throwable $e) {
            error_log('Notification::create ' . $e->getMessage());
            return false;
        }
    }

    public static function fetchLatest(int $userId, int $limit = 20): array
    {
        self::ensureTable();
        try {
            $limit = max(1, min(50, $limit));
            $stmt = self::db()->prepare(
                "SELECT id, message, type, post_id, is_read, created_at
                 FROM notifications
                 WHERE user_id = :user
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            $stmt->execute([':user' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('Notification::fetchLatest ' . $e->getMessage());
            return [];
        }
    }

    public static function countUnread(int $userId): int
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user AND is_read = 0");
            $stmt->execute([':user' => $userId]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('Notification::countUnread ' . $e->getMessage());
            return 0;
        }
    }

    public static function markAllRead(int $userId): bool
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user AND is_read = 0");
            return $stmt->execute([':user' => $userId]);
        } catch (Throwable $e) {
            error_log('Notification::markAllRead ' . $e->getMessage());
            return false;
        }
    }
}

