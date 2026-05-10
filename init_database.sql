-- Base de données Forum CityZen — schéma aligné sur models/*.php
-- Base MySQL : furum (identique à DB_NAME dans config/Database.php)
-- Si la base existe déjà avec des tables incomplètes : exécutez d’abord config/align_furum_schema.sql
-- Import : phpMyAdmin → onglet SQL → coller / exécuter

CREATE DATABASE IF NOT EXISTS furum
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE furum;

-- ---------------------------------------------------------------------------
-- Utilisateurs (FK pour posts, replies, likes)
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

-- ---------------------------------------------------------------------------
-- Posts (models/Post.php)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    content LONGTEXT NOT NULL,
    view_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_posts_user (user_id),
    INDEX idx_posts_created (created_at),
    FULLTEXT INDEX ft_posts_title_content (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Réponses (models/Reply.php)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_replies_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_replies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_replies_post (post_id),
    INDEX idx_replies_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Likes unifiés (models/Like.php : post_id OU reply_id, l’autre à NULL)
-- Remplace likes_posts / likes_replies si vous aviez l’ancien script.
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
-- Données minimales pour que les INSERT du forum réussissent (FK user_id)
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO users (id, username, email, password) VALUES
(1, 'admin', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT IGNORE INTO posts (id, user_id, title, content, view_count) VALUES
(1, 1, 'Bienvenue sur le Forum', 'Ceci est le premier post du forum. N\'hésitez pas à partager vos idées et vos questions ici.', 0),
(2, 1, 'Guide d\'utilisation du forum', 'Voici quelques règles à respecter pour une bonne utilisation du forum : soyez respectueux, évitez le spam, et contribuez de manière constructive.', 0);
