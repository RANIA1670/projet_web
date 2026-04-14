-- =============================================================================
-- CityZen — INSTALLATION COMPLÈTE DE LA BASE (tous les modules)
-- =============================================================================
-- Ce fichier recrée la base `cityzen` depuis zéro (DESTRUCTIF : supprime l’ancienne base).
--
-- Contenu :
--   • Utilisateurs (users)
--   • Module équipement (type_equipment, equipment, reservation, equipment_issue)
--   • Module forum (forum_categories, forum_posts, forum_replies)
--   • Module événements (events, sponsors, event_sponsors, event_participations)
--   • Module interventions (signalements, interventions, intervention_feedback)
--
-- Prérequis : MySQL / MariaDB 10.2+ (CHECK sur intervention_feedback : MySQL 8.0.16+)
--
-- Import en ligne de commande :
--   mysql -u root -p < database/cityzen_install_complete.sql
--
-- Sous XAMPP (mot de passe root vide souvent) :
--   /Applications/XAMPP/xamppfiles/bin/mysql -u root < database/cityzen_install_complete.sql
--
-- Compte de démo après import :
--   username : admin
--   password : cityzen
--   (à changer en production ; le mot de passe est un hash bcrypt ci-dessous)
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS cityzen;
CREATE DATABASE cityzen
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cityzen;

-- ─── UTILISATEURS ───────────────────────────────────────────────────────────

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(32) NOT NULL COMMENT 'Identifiant unique (lettres, chiffres, underscore)',
  full_name VARCHAR(120) NULL,
  email VARCHAR(190) NULL,
  birth_date DATE NULL,
  postal_code VARCHAR(20) NULL,
  city VARCHAR(120) NULL,
  phone VARCHAR(30) NULL,
  profile_photo VARCHAR(255) NULL,
  password_hash VARCHAR(255) NOT NULL COMMENT 'Hash PHP password_hash',
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  blocked TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role),
  KEY idx_users_blocked (blocked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mot de passe en clair : cityzen (bcrypt généré par PHP password_hash)
INSERT INTO users (username, full_name, email, password_hash, role, created_at) VALUES
('admin', 'Administrateur', NULL, '$2y$12$WC1xFMxyOkLSaqrYz0XGJ.bkA2u4ujbYueYYE.FokrbIx9g/8IHCS', 'admin', NOW());

-- ─── MODULE ÉQUIPEMENT ───────────────────────────────────────────────────────

CREATE TABLE type_equipment (
    id                                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name                        VARCHAR(150)     NOT NULL,
    icon                                 VARCHAR(64)      NOT NULL DEFAULT '📦',
    daily_cost                           DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    warranty_months                      TINYINT UNSIGNED NOT NULL DEFAULT 12,
    default_maintenance_frequency_months TINYINT UNSIGNED NOT NULL DEFAULT 6
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE equipment (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(150)    NOT NULL,
    status           ENUM('available','reserved','maintenance','out_of_service') NOT NULL DEFAULT 'available',
    location         VARCHAR(255)    NOT NULL DEFAULT '',
    type_id          INT UNSIGNED    NOT NULL,
    last_maintenance DATE            NULL,
    latitude         DECIMAL(10,8)   NULL,
    longitude        DECIMAL(11,8)   NULL,
    CONSTRAINT fk_equipment_type
        FOREIGN KEY (type_id) REFERENCES type_equipment(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservation (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id      INT UNSIGNED    NOT NULL,
    user_id           INT UNSIGNED    NULL,
    extension_of_id   INT UNSIGNED    NULL,
    start_date        DATETIME        NOT NULL,
    end_date          DATETIME        NOT NULL,
    purpose           TEXT            NULL,
    usage_purpose     ENUM('event','repair','inspection') NULL,
    status            ENUM('pending','approved','rejected','returned','no_show','cancelled') NOT NULL DEFAULT 'pending',
    rejection_reason  TEXT            NULL,
    returned_at       DATETIME        NULL,
    notify_email_sent TINYINT(1)      NOT NULL DEFAULT 0,
    created_at        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservation_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reservation_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_reservation_extension
        FOREIGN KEY (extension_of_id) REFERENCES reservation(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE equipment_issue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    issue_type ENUM('not_working','damaged','lost') NOT NULL,
    photo_path VARCHAR(255) NULL,
    description TEXT NOT NULL,
    status ENUM('open','acknowledged','resolved') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_issue_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_issue_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    KEY idx_issue_status (status),
    KEY idx_issue_equipment (equipment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO type_equipment (category_name, icon, daily_cost, warranty_months, default_maintenance_frequency_months) VALUES
('Construction & heavy', '🚜', 450.00, 24, 3),
('Utility vehicles', '🚐', 85.50, 36, 6),
('Events & AV', '🎪', 120.00, 12, 2),
('Surveillance Camera', '📷', 35.00, 24, 1);

INSERT INTO equipment (name, status, location, type_id, last_maintenance, latitude, longitude) VALUES
('Excavator CAT 320', 'available', 'Depot Nord — allée B', 1, '2025-11-20', 36.8065, 10.1815),
('Lift platform 12m', 'available', 'Depot Nord — allée A', 1, NULL, 36.8070, 10.1820),
('Renault Master van', 'maintenance', 'Atelier central', 2, '2026-01-10', 36.8500, 10.1900),
('Peugeot Partner', 'available', 'Parking services', 2, '2025-09-05', 36.7980, 10.1750),
('PA system 500W', 'available', 'Logistique événements', 3, NULL, 36.7900, 10.1700),
('Folding tent 4x4m', 'out_of_service', 'Hangar 3', 3, '2024-06-01', NULL, NULL),
('Caméra PTZ Hall A', 'available', 'Mairie — hall A', 4, '2025-08-01', 36.7990, 10.1780),
('Caméra parking', 'available', 'Mairie — parking', 4, NULL, 36.7992, 10.1785),
('Smart Bench jardin', 'available', 'Parc central', 3, NULL, 36.8020, 10.1800);

INSERT INTO reservation (equipment_id, user_id, start_date, end_date, purpose, status, rejection_reason, returned_at) VALUES
(1, 1, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY), 'Travaux voirie — tranchée', 'pending', NULL, NULL),
(4, 1, DATE_ADD(NOW(), INTERVAL 8 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY), 'Tournée inspection', 'pending', NULL, NULL),
(5, 1, DATE_ADD(NOW(), INTERVAL -10 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY), 'Mairie — conseil municipal', 'returned', NULL, DATE_ADD(NOW(), INTERVAL -7 DAY)),
(7, 1, DATE_ADD(NOW(), INTERVAL -30 DAY), DATE_ADD(NOW(), INTERVAL -28 DAY), 'Surveillance événement', 'returned', NULL, DATE_ADD(NOW(), INTERVAL -26 DAY)),
(8, 1, DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY), 'Test en retard simulé', 'approved', NULL, NULL),
(7, 1, DATE_ADD(NOW(), INTERVAL -60 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY), 'Surveillance — retard restitution', 'returned', NULL, DATE_ADD(NOW(), INTERVAL -55 DAY));

-- ─── MODULE 2 — FORUM ───────────────────────────────────────────────────────

CREATE TABLE forum_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_forum_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE forum_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  content TEXT NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_forum_posts_category (category_id),
  KEY idx_forum_posts_user (user_id),
  KEY idx_forum_posts_created (created_at),
  CONSTRAINT fk_forum_posts_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_forum_posts_category
    FOREIGN KEY (category_id) REFERENCES forum_categories (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE forum_replies (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  parent_reply_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_forum_replies_post (post_id),
  KEY idx_forum_replies_user (user_id),
  KEY idx_forum_replies_parent (parent_reply_id),
  CONSTRAINT fk_forum_replies_post
    FOREIGN KEY (post_id) REFERENCES forum_posts (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_forum_replies_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_forum_replies_parent
    FOREIGN KEY (parent_reply_id) REFERENCES forum_replies (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO forum_categories (name, description) VALUES
('Général', 'Discussions générales citoyennes'),
('Mobilité', 'Transports et voirie'),
('Environnement', 'Espaces verts et propreté');

-- ─── MODULE 3 — ÉVÉNEMENTS ───────────────────────────────────────────────────

CREATE TABLE events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME NOT NULL,
  location TEXT NULL,
  max_participants INT UNSIGNED NULL,
  created_by INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_events_start (start_datetime),
  KEY idx_events_created_by (created_by),
  CONSTRAINT fk_events_created_by
    FOREIGN KEY (created_by) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sponsors (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  logo_url VARCHAR(255) NULL,
  website VARCHAR(255) NULL,
  contribution_amount DECIMAL(12,2) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_sponsors_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_sponsors (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_id INT UNSIGNED NOT NULL,
  sponsor_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_event_sponsor (event_id, sponsor_id),
  KEY idx_event_sponsors_sponsor (sponsor_id),
  CONSTRAINT fk_event_sponsors_event
    FOREIGN KEY (event_id) REFERENCES events (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_event_sponsors_sponsor
    FOREIGN KEY (sponsor_id) REFERENCES sponsors (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_participations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  attended TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_event_user (event_id, user_id),
  KEY idx_event_participations_user (user_id),
  CONSTRAINT fk_event_participations_event
    FOREIGN KEY (event_id) REFERENCES events (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_event_participations_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sponsors (name, logo_url, website, contribution_amount) VALUES
('Partenaire Demo', NULL, 'https://example.org', 5000.00);

INSERT INTO events (title, description, start_datetime, end_datetime, location, max_participants, created_by) VALUES
('Conseil de quartier — démo', 'Événement exemple pour les développeurs.', DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY) + INTERVAL 2 HOUR, 'Mairie — salle A', 50, 1);

INSERT INTO event_sponsors (event_id, sponsor_id) VALUES (1, 1);

-- ─── MODULE 4 — INTERVENTIONS ────────────────────────────────────────────────

CREATE TABLE signalements (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  category ENUM('broken','vandalism','cleaning','electrical','other') NOT NULL DEFAULT 'other',
  status ENUM('pending','assigned','in_progress','resolved','rejected') NOT NULL DEFAULT 'pending',
  priority ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_signalements_user (user_id),
  KEY idx_signalements_status (status),
  KEY idx_signalements_priority (priority),
  CONSTRAINT fk_signalements_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE interventions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  signalement_id INT UNSIGNED NOT NULL,
  technician_id INT UNSIGNED NOT NULL,
  action_taken TEXT NULL,
  start_time DATETIME NULL,
  end_time DATETIME NULL,
  status ENUM('assigned','in_progress','completed','cancelled') NOT NULL DEFAULT 'assigned',
  PRIMARY KEY (id),
  KEY idx_interventions_signalement (signalement_id),
  KEY idx_interventions_technician (technician_id),
  CONSTRAINT fk_interventions_signalement
    FOREIGN KEY (signalement_id) REFERENCES signalements (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_interventions_technician
    FOREIGN KEY (technician_id) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE intervention_feedback (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  intervention_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NULL,
  PRIMARY KEY (id),
  KEY idx_intervention_feedback_intervention (intervention_id),
  KEY idx_intervention_feedback_user (user_id),
  CONSTRAINT fk_intervention_feedback_intervention
    FOREIGN KEY (intervention_id) REFERENCES interventions (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_intervention_feedback_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_intervention_feedback_rating
    CHECK (rating >= 1 AND rating <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO signalements (user_id, title, description, category, status, priority) VALUES
(1, 'Lampadaire démo', 'Exemple de signalement pour les tests.', 'electrical', 'pending', 'medium');

INSERT INTO interventions (signalement_id, technician_id, action_taken, status) VALUES
(1, 1, 'Prise en charge planifiée (données de démo).', 'assigned');

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Fin. Vérification rapide : SHOW TABLES;
-- =============================================================================
