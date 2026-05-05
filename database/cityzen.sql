-- CityZen - Smart Intervention Management
-- Database Schema

CREATE DATABASE IF NOT EXISTS cityzen_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cityzen_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('citoyen','technicien','admin') DEFAULT 'citoyen',
    telephone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    icone VARCHAR(50) DEFAULT 'fa-exclamation-circle',
    couleur VARCHAR(20) DEFAULT '#2C3E50'
) ENGINE=InnoDB;

-- Signalements Table
CREATE TABLE IF NOT EXISTS signalements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    titre VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    categorie_id INT,
    adresse VARCHAR(255) NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    priorite ENUM('faible','moyenne','haute','urgente') DEFAULT 'moyenne',
    statut ENUM('nouveau','en_attente','en_cours','resolu','ferme') DEFAULT 'nouveau',
    image VARCHAR(255),
    date_incident DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Interventions Table
CREATE TABLE IF NOT EXISTS interventions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    signalement_id INT,
    technicien_id INT,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    statut ENUM('planifiee','en_cours','terminee','annulee') DEFAULT 'planifiee',
    date_planifiee DATE,
    date_debut DATETIME,
    date_fin DATETIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (signalement_id) REFERENCES signalements(id) ON DELETE CASCADE,
    FOREIGN KEY (technicien_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Suivi Interventions Table
CREATE TABLE IF NOT EXISTS suivi_interventions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intervention_id INT NOT NULL,
    statut VARCHAR(100),
    commentaire TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (intervention_id) REFERENCES interventions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Contacts Table
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    sujet VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    statut ENUM('non_lu','lu','traite') DEFAULT 'non_lu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    titre VARCHAR(255) NOT NULL,
    message TEXT,
    signalement_id INT,
    intervention_id INT,
    lue TINYINT(1) DEFAULT 0,
    read_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (signalement_id) REFERENCES signalements(id) ON DELETE SET NULL,
    FOREIGN KEY (intervention_id) REFERENCES interventions(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Demandes Intervention Table
CREATE TABLE IF NOT EXISTS demandes_intervention (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    signalement_id INT,
    nom_demandeur VARCHAR(150) NOT NULL,
    email_demandeur VARCHAR(150) NOT NULL,
    telephone VARCHAR(20),
    type_intervention VARCHAR(100),
    description TEXT NOT NULL,
    urgence ENUM('normal','urgent','tres_urgent') DEFAULT 'normal',
    statut ENUM('en_attente','acceptee','refusee','en_cours','terminee') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (signalement_id) REFERENCES signalements(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Insert default categories
INSERT INTO categories (nom, icone, couleur) VALUES
('Voirie & Routes', 'fa-road', '#E67E22'),
('Éclairage Public', 'fa-lightbulb', '#F1C40F'),
('Espaces Verts', 'fa-leaf', '#27AE60'),
('Déchets & Propreté', 'fa-trash', '#8E44AD'),
('Eau & Assainissement', 'fa-tint', '#2980B9'),
('Sécurité', 'fa-shield-alt', '#E74C3C'),
('Bâtiments Publics', 'fa-building', '#2C3E50'),
('Transports', 'fa-bus', '#16A085'),
('Mobilier Urbain', 'fa-bench', '#D35400'),
('Autre', 'fa-question-circle', '#7F8C8D');

-- Insert demo admin user (password: Admin@123)
INSERT INTO users (nom, prenom, email, password, role, telephone) VALUES
('Admin', 'CityZen', 'admin@cityzen.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+212600000001'),
('Technicien', 'Demo', 'tech@cityzen.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technicien', '+212600000002'),
('Alami', 'Ahmed', 'ahmed.alami@cityzen.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technicien', '+212611223344'),
('Benjelloun', 'Sara', 'sara.ben@cityzen.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technicien', '+212622334455'),
('Tazi', 'Karim', 'karim.tazi@cityzen.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technicien', '+212633445566'),
('Martin', 'Jean', 'jean.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'citoyen', '+212600000003');

-- Insert demo signalements
INSERT INTO signalements (user_id, titre, description, categorie_id, adresse, priorite, statut, date_incident) VALUES
(3, 'Nid de poule dangereux avenue principale', 'Un nid de poule de grande taille sur l''avenue principale crée des risques d''accident pour les véhicules et les deux-roues.', 1, 'Avenue Mohammed V, Casablanca', 'haute', 'en_cours', '2026-04-15'),
(3, 'Lampadaire en panne rue de la paix', 'Plusieurs lampadaires sont hors service depuis plus d''une semaine, créant une zone d''insécurité la nuit.', 2, 'Rue de la Paix, Casablanca', 'moyenne', 'en_attente', '2026-04-18'),
(3, 'Dépôt sauvage de déchets', 'Un important dépôt de déchets sauvages s''est formé près du parc municipal, attirant des nuisibles.', 4, 'Parc Al Fida, Casablanca', 'haute', 'nouveau', '2026-04-20'),
(3, 'Fuite d''eau importante', 'Une fuite d''eau important sur la voie publique gaspille des ressources et crée des problèmes de circulation.', 5, 'Boulevard Zerktouni, Casablanca', 'urgente', 'en_cours', '2026-04-19'),
(3, 'Banc public vandalisé', 'Le banc public du square est complètement vandalisé et représente un danger pour les usagers.', 9, 'Square Hassan II, Casablanca', 'faible', 'resolu', '2026-04-10');

-- Insert demo interventions
INSERT INTO interventions (signalement_id, technicien_id, titre, description, statut, date_planifiee) VALUES
(1, 2, 'Réparation nid de poule avenue principale', 'Intervention urgente pour colmater le nid de poule et sécuriser la zone.', 'en_cours', '2026-04-22'),
(4, 2, 'Réparation fuite eau boulevard Zerktouni', 'Intervention plomberie pour réparer la fuite et remettre en état la chaussée.', 'planifiee', '2026-04-23');

-- Insert suivi
INSERT INTO suivi_interventions (intervention_id, statut, commentaire, created_by) VALUES
(1, 'Démarrage', 'L''équipe est arrivée sur site. Évaluation en cours.', 2),
(1, 'En cours', 'Les travaux de colmatage ont commencé. Matériaux en place.', 2);
