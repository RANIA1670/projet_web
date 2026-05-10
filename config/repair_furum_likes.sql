-- Ajouter la table `likes` si elle manque (base furum, models/Like.php)

USE furum;

CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NULL DEFAULT NULL,
    reply_id INT NULL DEFAULT NULL,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_likes_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_likes_reply FOREIGN KEY (reply_id) REFERENCES replies(id) ON DELETE CASCADE,
    INDEX idx_likes_post_user (post_id, user_id),
    INDEX idx_likes_reply_user (reply_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
