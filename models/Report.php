<?php
/**
 * Signalements de modération.
 */
require_once __DIR__ . '/../config/Database.php';

class Report
{
    private static function db(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    public static function ensureTable(): void
    {
        try {
            self::db()->exec(
                "CREATE TABLE IF NOT EXISTS reports (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    reporter_user_id INT NOT NULL,
                    reason VARCHAR(255) NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'open',
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_reports_post (post_id),
                    INDEX idx_reports_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Throwable $e) {
            error_log('Report::ensureTable ' . $e->getMessage());
        }
    }

    public static function create(int $postId, int $reporterUserId, string $reason): bool
    {
        self::ensureTable();
        $reason = trim($reason);
        if ($postId <= 0 || $reporterUserId <= 0 || $reason === '') {
            return false;
        }
        try {
            $stmt = self::db()->prepare(
                "INSERT INTO reports (post_id, reporter_user_id, reason) VALUES (:post, :reporter, :reason)"
            );
            $success = $stmt->execute([
                ':post' => $postId,
                ':reporter' => $reporterUserId,
                ':reason' => $reason,
            ]);

            if ($success) {
                // Check report count for this post
                $countStmt = self::db()->prepare("SELECT COUNT(*) FROM reports WHERE post_id = :post_id AND status = 'open'");
                $countStmt->execute([':post_id' => $postId]);
                $count = (int)$countStmt->fetchColumn();

                if ($count >= 5) {
                    $hideStmt = self::db()->prepare("UPDATE posts SET status = 'Masqué' WHERE id = :id");
                    $hideStmt->execute([':id' => $postId]);
                }
            }
            return $success;
        } catch (Throwable $e) {
            error_log('Report::create ' . $e->getMessage());
            return false;
        }
    }

    public static function getOpenGrouped(int $limit = 50): array
    {
        self::ensureTable();
        try {
            $limit = max(1, min(200, $limit));
            $sql = "SELECT r.post_id,
                           p.title,
                           p.status AS post_status,
                           COUNT(r.id) as report_count,
                           MAX(r.created_at) as last_report_date,
                           SUBSTRING_INDEX(GROUP_CONCAT(r.reason ORDER BY r.created_at DESC SEPARATOR '||'), '||', 1) as last_reason
                    FROM reports r
                    JOIN posts p ON p.id = r.post_id
                    WHERE r.status = 'open'
                    GROUP BY r.post_id, p.title, p.status
                    ORDER BY report_count DESC, last_report_date DESC
                    LIMIT {$limit}";
            $stmt = self::db()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('Report::getOpenGrouped ' . $e->getMessage());
            return [];
        }
    }

    public static function resolve(int $reportId): bool
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare("UPDATE reports SET status = 'resolved' WHERE id = :id");
            return $stmt->execute([':id' => $reportId]);
        } catch (Throwable $e) {
            error_log('Report::resolve ' . $e->getMessage());
            return false;
        }
    }

    public static function rejectReportsForPost(int $postId): bool
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare("UPDATE reports SET status = 'resolved' WHERE post_id = :post_id");
            return $stmt->execute([':post_id' => $postId]);
        } catch (Throwable $e) {
            error_log('Report::rejectReportsForPost ' . $e->getMessage());
            return false;
        }
    }

    public static function deleteReportsForPost(int $postId): bool
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare("DELETE FROM reports WHERE post_id = :post_id");
            return $stmt->execute([':post_id' => $postId]);
        } catch (Throwable $e) {
            error_log('Report::deleteReportsForPost ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retourne les signalements enrichis avec tous les détails du post et auteur
     */
    public static function getOpenEnriched(int $limit = 50): array
    {
        self::ensureTable();
        try {
            $limit = max(1, min(200, $limit));
            $sql = "SELECT r.id,
                           r.post_id,
                           r.reason,
                           r.created_at,
                           r.reporter_user_id,
                           p.title,
                           p.content,
                           p.status AS post_status,
                           p.user_id as author_id,
                           p.view_count,
                           (SELECT COUNT(*) FROM reports WHERE post_id = r.post_id AND status = 'open') as total_report_count
                    FROM reports r
                    JOIN posts p ON p.id = r.post_id
                    WHERE r.status = 'open'
                    ORDER BY r.post_id DESC, r.created_at DESC
                    LIMIT {$limit}";
            $stmt = self::db()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('Report::getOpenEnriched ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retourne les raisons les plus signalées
     */
    public static function getTopReasons(int $limit = 10): array
    {
        self::ensureTable();
        try {
            $limit = max(1, min(50, $limit));
            $sql = "SELECT reason,
                           COUNT(*) as count
                    FROM reports
                    WHERE status = 'open'
                    GROUP BY reason
                    ORDER BY count DESC
                    LIMIT {$limit}";
            $stmt = self::db()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('Report::getTopReasons ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retourne tous les signalements pour un post donné
     */
    public static function getReportsByPostId(int $postId): array
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare(
                "SELECT id, reason, reporter_user_id, created_at, status
                 FROM reports
                 WHERE post_id = :post_id
                 ORDER BY created_at DESC"
            );
            $stmt->execute([':post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('Report::getReportsByPostId ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Masque un post (au lieu de le supprimer)
     */
    public static function hidePost(int $postId): bool
    {
        try {
            $stmt = self::db()->prepare("UPDATE posts SET status = 'Masqué' WHERE id = :id");
            return $stmt->execute([':id' => $postId]);
        } catch (Throwable $e) {
            error_log('Report::hidePost ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Résout tous les signalements pour un post donné
     */
    public static function resolveAllReportsForPost(int $postId): bool
    {
        try {
            $stmt = self::db()->prepare("UPDATE reports SET status = 'resolved' WHERE post_id = :post_id AND status = 'open'");
            return $stmt->execute([':post_id' => $postId]);
        } catch (Throwable $e) {
            error_log('Report::resolveAllReportsForPost ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Compte les signalements ouverts
     */
    public static function countOpen(): int
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare("SELECT COUNT(*) FROM reports WHERE status = 'open'");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('Report::countOpen ' . $e->getMessage());
            return 0;
        }
    }
}

