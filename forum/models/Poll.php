<?php
/**
 * Sondages structurés par post.
 */
require_once __DIR__ . '/../config/Database.php';

class Poll
{
    private static function db(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    public static function ensureTables(): void
    {
        try {
            $db = self::db();
            $db->exec(
                "CREATE TABLE IF NOT EXISTS poll_questions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    question VARCHAR(255) NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_poll_post (post_id),
                    INDEX idx_poll_post (post_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $db->exec(
                "CREATE TABLE IF NOT EXISTS poll_options (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    poll_id INT NOT NULL,
                    option_text VARCHAR(255) NOT NULL,
                    position INT NOT NULL DEFAULT 0,
                    INDEX idx_poll_options_poll (poll_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $db->exec(
                "CREATE TABLE IF NOT EXISTS poll_votes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    poll_id INT NOT NULL,
                    option_id INT NOT NULL,
                    user_id INT NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_poll_vote_user (poll_id, user_id),
                    INDEX idx_poll_votes_poll (poll_id),
                    INDEX idx_poll_votes_option (option_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Throwable $e) {
            error_log('Poll::ensureTables ' . $e->getMessage());
        }
    }

    public static function createForPost(int $postId, string $question, array $options): bool
    {
        self::ensureTables();
        $question = trim($question);
        $options = array_values(array_filter(array_map(static fn($x) => trim((string)$x), $options), static fn($x) => $x !== ''));
        if ($question === '' || count($options) < 2) {
            return false;
        }
        try {
            $db = self::db();
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO poll_questions (post_id, question) VALUES (:post, :question)");
            $stmt->execute([':post' => $postId, ':question' => $question]);
            $pollId = (int)$db->lastInsertId();

            $optStmt = $db->prepare("INSERT INTO poll_options (poll_id, option_text, position) VALUES (:poll, :text, :position)");
            foreach ($options as $i => $opt) {
                $optStmt->execute([':poll' => $pollId, ':text' => $opt, ':position' => $i + 1]);
            }
            $db->commit();
            return true;
        } catch (Throwable $e) {
            if (self::db()->inTransaction()) {
                self::db()->rollBack();
            }
            error_log('Poll::createForPost ' . $e->getMessage());
            return false;
        }
    }

    public static function getByPostId(int $postId): ?array
    {
        self::ensureTables();
        try {
            $db = self::db();
            $q = $db->prepare("SELECT id, post_id, question FROM poll_questions WHERE post_id = :p LIMIT 1");
            $q->execute([':p' => $postId]);
            $poll = $q->fetch(PDO::FETCH_ASSOC);
            if (!$poll) {
                return null;
            }
            $pollId = (int)$poll['id'];
            $opts = $db->prepare(
                "SELECT o.id, o.option_text, o.position, COUNT(v.id) AS vote_count
                 FROM poll_options o
                 LEFT JOIN poll_votes v ON v.option_id = o.id
                 WHERE o.poll_id = :poll
                 GROUP BY o.id, o.option_text, o.position
                 ORDER BY o.position ASC, o.id ASC"
            );
            $opts->execute([':poll' => $pollId]);
            $options = $opts->fetchAll(PDO::FETCH_ASSOC);
            $total = 0;
            foreach ($options as $o) {
                $total += (int)$o['vote_count'];
            }
            $poll['options'] = $options;
            $poll['total_votes'] = $total;
            return $poll;
        } catch (Throwable $e) {
            error_log('Poll::getByPostId ' . $e->getMessage());
            return null;
        }
    }

    public static function getUserVoteOptionId(int $pollId, int $userId): int
    {
        self::ensureTables();
        try {
            $stmt = self::db()->prepare("SELECT option_id FROM poll_votes WHERE poll_id = :poll AND user_id = :u LIMIT 1");
            $stmt->execute([':poll' => $pollId, ':u' => $userId]);
            return (int)($stmt->fetchColumn() ?: 0);
        } catch (Throwable $e) {
            error_log('Poll::getUserVoteOptionId ' . $e->getMessage());
            return 0;
        }
    }

    public static function vote(int $pollId, int $optionId, int $userId): bool
    {
        self::ensureTables();
        try {
            $db = self::db();
            $check = $db->prepare("SELECT COUNT(*) FROM poll_options WHERE id = :option AND poll_id = :poll");
            $check->execute([':option' => $optionId, ':poll' => $pollId]);
            if ((int)$check->fetchColumn() <= 0) {
                return false;
            }
            $sql = "INSERT INTO poll_votes (poll_id, option_id, user_id)
                    VALUES (:poll, :option, :user)
                    ON DUPLICATE KEY UPDATE option_id = VALUES(option_id), created_at = CURRENT_TIMESTAMP";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':poll' => $pollId,
                ':option' => $optionId,
                ':user' => $userId,
            ]);
        } catch (Throwable $e) {
            error_log('Poll::vote ' . $e->getMessage());
            return false;
        }
    }
}

