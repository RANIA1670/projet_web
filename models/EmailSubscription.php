<?php
/**
 * Abonnements email pour recevoir les nouvelles réponses d'un post.
 */
require_once __DIR__ . '/../config/Database.php';

class EmailSubscription
{
    private static function db(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    public static function ensureTable(): void
    {
        try {
            self::db()->exec(
                "CREATE TABLE IF NOT EXISTS post_email_subscriptions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    email VARCHAR(190) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_post_email (post_id, email),
                    INDEX idx_post_email_subscriptions_post (post_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Throwable $e) {
            error_log('EmailSubscription::ensureTable ' . $e->getMessage());
        }
    }

    public static function subscribe(int $postId, string $email): bool
    {
        self::ensureTable();
        $email = trim($email);
        if ($postId <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        try {
            $stmt = self::db()->prepare(
                "INSERT INTO post_email_subscriptions (post_id, email, is_active)
                 VALUES (:post_id, :email, 1)
                 ON DUPLICATE KEY UPDATE is_active = 1, updated_at = CURRENT_TIMESTAMP"
            );
            return $stmt->execute([
                ':post_id' => $postId,
                ':email' => $email,
            ]);
        } catch (Throwable $e) {
            error_log('EmailSubscription::subscribe ' . $e->getMessage());
            return false;
        }
    }

    public static function listActiveEmailsByPostId(int $postId): array
    {
        self::ensureTable();
        try {
            $stmt = self::db()->prepare(
                "SELECT email FROM post_email_subscriptions
                 WHERE post_id = :post_id AND is_active = 1"
            );
            $stmt->execute([':post_id' => $postId]);
            return array_values(array_filter(array_map(
                static fn($row) => (string)($row['email'] ?? ''),
                $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []
            )));
        } catch (Throwable $e) {
            error_log('EmailSubscription::listActiveEmailsByPostId ' . $e->getMessage());
            return [];
        }
    }

    public static function notifyNewReply(int $postId, string $postTitle, string $replyPreview): void
    {
        $emails = self::listActiveEmailsByPostId($postId);
        if (empty($emails)) {
            return;
        }

        $subject = 'Nouvelle réponse sur votre post - CityZen';
        $baseUrl = (isset($_SERVER['HTTP_HOST']) ? ('http://' . $_SERVER['HTTP_HOST']) : 'http://localhost')
            . '/web%20mardi/index.php?page=post&id=' . $postId;

        $safeTitle = trim($postTitle) !== '' ? $postTitle : ('Post #' . $postId);
        $safePreview = trim($replyPreview) !== '' ? $replyPreview : 'Une nouvelle réponse a été publiée.';

        $message = "Bonjour,\n\n"
            . "Vous avez reçu une nouvelle réponse sur votre post : \"" . $safeTitle . "\".\n\n"
            . "Aperçu : " . $safePreview . "\n\n"
            . "Voir le post : " . $baseUrl . "\n\n"
            . "Cordialement,\nCityZen Forum";

        $headers = "From: no-reply@cityzen.local\r\n"
            . "Reply-To: no-reply@cityzen.local\r\n"
            . "X-Mailer: PHP/" . phpversion();

        foreach ($emails as $email) {
            try {
                @mail($email, $subject, $message, $headers);
            } catch (Throwable $e) {
                error_log('EmailSubscription::notifyNewReply mail ' . $e->getMessage());
            }
        }
    }

    public static function countActive(): int
    {
        self::ensureTable();
        try {
            return (int)self::db()->query(
                "SELECT COUNT(*) FROM post_email_subscriptions WHERE is_active = 1"
            )->fetchColumn();
        } catch (Throwable $e) {
            error_log('EmailSubscription::countActive ' . $e->getMessage());
            return 0;
        }
    }

    public static function listRecent(int $limit = 10): array
    {
        self::ensureTable();
        try {
            $limit = max(1, min(50, $limit));
            $stmt = self::db()->query(
                "SELECT post_id, email, created_at
                 FROM post_email_subscriptions
                 WHERE is_active = 1
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('EmailSubscription::listRecent ' . $e->getMessage());
            return [];
        }
    }
}

