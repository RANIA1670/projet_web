-- Module équipement CityZen — à importer APRÈS database/cityzen.sql
-- Les réservations référencent users(id) (comptes applicatifs : username, rôle).
-- user_id peut être NULL si aucun compte n’est associé.

USE cityzen;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reservation;
DROP TABLE IF EXISTS equipment_issue;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS type_equipment;
SET FOREIGN_KEY_CHECKS = 1;

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

-- Réservations de démo : user_id NULL tant qu’aucun compte n’existe (sinon mettre à jour manuellement).
INSERT INTO reservation (equipment_id, user_id, start_date, end_date, purpose, status, rejection_reason, returned_at) VALUES
(1, NULL, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY), 'Travaux voirie — tranchée', 'pending', NULL, NULL),
(4, NULL, DATE_ADD(NOW(), INTERVAL 8 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY), 'Tournée inspection', 'pending', NULL, NULL),
(5, NULL, DATE_ADD(NOW(), INTERVAL -10 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY), 'Mairie — conseil municipal', 'returned', NULL, DATE_ADD(NOW(), INTERVAL -7 DAY)),
(7, NULL, DATE_ADD(NOW(), INTERVAL -30 DAY), DATE_ADD(NOW(), INTERVAL -28 DAY), 'Surveillance événement', 'returned', NULL, DATE_ADD(NOW(), INTERVAL -26 DAY)),
(8, NULL, DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY), 'Test en retard simulé', 'approved', NULL, NULL),
(7, NULL, DATE_ADD(NOW(), INTERVAL -60 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY), 'Surveillance — retard restitution', 'returned', NULL, DATE_ADD(NOW(), INTERVAL -55 DAY));
