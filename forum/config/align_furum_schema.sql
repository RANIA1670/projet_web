-- Alignement de la base MySQL `furum` sur models/Post.php, Reply.php, Like.php
-- Erreur typique : #1054 Champ 'title' inconnu → la table posts n’a pas les bonnes colonnes.
-- Exécuter dans phpMyAdmin : sélectionner la base « furum », onglet SQL, coller ce fichier.

USE furum;

-- ---------------------------------------------------------------------------
-- Table users (obligatoire pour la clé étrangère user_id)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO users (id, username, email, password) VALUES
(1, 'admin', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ---------------------------------------------------------------------------
-- Colonnes manquantes sur `posts` (ajout seulement si absentes)
-- ---------------------------------------------------------------------------
SET @db = DATABASE();

-- title
SET @sql = (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'title') > 0,
    'SELECT ''posts.title : OK'' AS info',
    'ALTER TABLE posts ADD COLUMN title VARCHAR(150) NOT NULL DEFAULT ''Sans titre'''
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- is_featured
SET @sql = (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'is_featured') > 0,
    'SELECT ''posts.is_featured : OK'' AS info',
    'ALTER TABLE posts ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- content
SET @sql = (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'content') > 0,
    'SELECT ''posts.content : OK'' AS info',
    'ALTER TABLE posts ADD COLUMN content LONGTEXT NULL'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- view_count
SET @sql = (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'view_count') > 0,
    'SELECT ''posts.view_count : OK'' AS info',
    'ALTER TABLE posts ADD COLUMN view_count INT NOT NULL DEFAULT 0'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- created_at
SET @sql = (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'created_at') > 0,
    'SELECT ''posts.created_at : OK'' AS info',
    'ALTER TABLE posts ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- updated_at
SET @sql = (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'updated_at') > 0,
    'SELECT ''posts.updated_at : OK'' AS info',
    'ALTER TABLE posts ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user_id (si la table a été créée sans)
SET @sql = (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'user_id') > 0,
    'SELECT ''posts.user_id : OK'' AS info',
    'ALTER TABLE posts ADD COLUMN user_id INT NOT NULL DEFAULT 1'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- (Optionnel) FK posts.user_id → users.id si besoin et données cohérentes :
-- ALTER TABLE posts ADD CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- ---------------------------------------------------------------------------
-- Table likes (modèle Like.php) — ignorée si déjà présente
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- Favoris personnels
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_favorites_user_post (user_id, post_id),
    INDEX idx_favorites_user (user_id),
    INDEX idx_favorites_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Sondages et votes structurés
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS poll_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    question VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_poll_post (post_id),
    INDEX idx_poll_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    INDEX idx_poll_options_poll (poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS poll_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_poll_vote_user (poll_id, user_id),
    INDEX idx_poll_votes_poll (poll_id),
    INDEX idx_poll_votes_option (option_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Notifications temps réel (polling AJAX)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Abonnements email par post (nouvelle réponse => email)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS post_email_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    email VARCHAR(190) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_post_email (post_id, email),
    INDEX idx_post_email_subscriptions_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Signalements (modération)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    reporter_user_id INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reports_post (post_id),
    INDEX idx_reports_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
