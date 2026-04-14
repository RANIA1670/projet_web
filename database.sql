-- Base de données pour le forum CityZen
-- Créer cette base de données dans phpMyAdmin ou via la ligne de commande

CREATE DATABASE IF NOT EXISTS cityzen_forum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cityzen_forum;

-- Table des posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    author VARCHAR(100) NOT NULL,
    author_email VARCHAR(255),
    status ENUM('Publié', 'En révision', 'Brouillon', 'Archivé') DEFAULT 'Brouillon',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    likes INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des replies
CREATE TABLE replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    content TEXT NOT NULL,
    author VARCHAR(100) NOT NULL,
    author_email VARCHAR(255),
    status ENUM('Validé', 'Rejeté', 'En attente') DEFAULT 'En attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des utilisateurs (pour l'authentification future)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderator', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion de données de test
INSERT INTO posts (title, content, author, status) VALUES
('Bienvenue sur le forum CityZen', 'Ceci est le premier post de bienvenue sur notre forum communautaire.', 'Admin', 'Publié'),
('Présentation de la nouvelle fonctionnalité', 'Nous sommes heureux d''annoncer le lancement de notre nouvelle fonctionnalité de messagerie intégrée.', 'Amine B.', 'Publié'),
('Question sur l''intégration PDO', 'J''ai des difficultés à intégrer PDO dans mon projet. Quelqu''un peut m''aider ?', 'Lea M.', 'En révision'),
('Problème avec les formulaires', 'Mon formulaire ne s''envoie pas correctement. Voici le code...', 'Sofia R.', 'Publié');

INSERT INTO replies (post_id, content, author, status) VALUES
(3, 'Voici un exemple d''utilisation de PDO...', 'Marc L.', 'Validé'),
(4, 'Vérifie que ton action pointe vers le bon fichier PHP.', 'Yasmine F.', 'Rejeté'),
(2, 'Excellente initiative ! J''attendais cette fonctionnalité.', 'Oumeima I.', 'En attente');

INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@cityzen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password: password
('moderator', 'mod@cityzen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderator'), -- password: password
('user', 'user@cityzen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'); -- password: password