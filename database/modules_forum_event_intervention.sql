-- CityZen — Modules 2 (Forum), 3 (Événements), 4 (Interventions)
-- Prérequis : base `cityzen` et table `users` (voir database/cityzen.sql)
-- Import : mysql -u root cityzen < database/modules_forum_event_intervention.sql

USE cityzen;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── MODULE 2 — FORUM ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS forum_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_forum_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forum_posts (
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

CREATE TABLE IF NOT EXISTS forum_replies (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  parent_reply_id INT UNSIGNED NULL COMMENT 'Réponse imbriquée (NULL = réponse directe au post)',
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

-- ─── MODULE 3 — ÉVÉNEMENTS ────────────────────────────────────────

CREATE TABLE IF NOT EXISTS events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME NOT NULL,
  location TEXT NULL,
  max_participants INT UNSIGNED NULL COMMENT 'NULL = pas de limite',
  created_by INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_events_start (start_datetime),
  KEY idx_events_created_by (created_by),
  CONSTRAINT fk_events_created_by
    FOREIGN KEY (created_by) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sponsors (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  logo_url VARCHAR(255) NULL,
  website VARCHAR(255) NULL,
  contribution_amount DECIMAL(12,2) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_sponsors_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_sponsors (
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

CREATE TABLE IF NOT EXISTS event_participations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  attended TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = non, 1 = présent',
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

-- ─── MODULE 4 — INTERVENTIONS / SIGNALEMENTS ──────────────────────

CREATE TABLE IF NOT EXISTS signalements (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL COMMENT 'NULL si signalement anonyme / système',
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

CREATE TABLE IF NOT EXISTS interventions (
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

CREATE TABLE IF NOT EXISTS intervention_feedback (
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

SET FOREIGN_KEY_CHECKS = 1;
