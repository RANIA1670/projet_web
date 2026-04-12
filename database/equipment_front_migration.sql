-- Front office équipement : statuts annulation / prolongation, type d'usage, tickets panne
-- Exécuter sur la base `cityzen` après equipment_tables.sql

USE cityzen;

ALTER TABLE reservation
  MODIFY COLUMN status ENUM('pending','approved','rejected','returned','no_show','cancelled')
    NOT NULL DEFAULT 'pending';

ALTER TABLE reservation
  ADD COLUMN extension_of_id INT UNSIGNED NULL DEFAULT NULL AFTER user_id,
  ADD COLUMN usage_purpose ENUM('event','repair','inspection') NULL DEFAULT NULL AFTER purpose,
  ADD CONSTRAINT fk_reservation_extension
    FOREIGN KEY (extension_of_id) REFERENCES reservation(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS equipment_issue (
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
