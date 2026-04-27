-- Base CityZen : module utilisateurs
-- Import : phpMyAdmin > Importer ce fichier, ou en ligne de commande :
--   mysql -u root < database/cityzen.sql

CREATE DATABASE IF NOT EXISTS cityzen
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cityzen;

-- Comptes applicatifs (inscription publique + roles admin depuis le tableau de bord)
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(32) NOT NULL COMMENT 'Identifiant unique (lettres, chiffres, underscore)',
  full_name VARCHAR(120) NULL,
  email VARCHAR(190) NULL,
  birth_date DATE NULL,
  postal_code VARCHAR(20) NULL,
  city VARCHAR(120) NULL,
  phone VARCHAR(30) NULL,
  profile_photo VARCHAR(255) NULL,
  qr_token CHAR(64) NULL,
  password_hash VARCHAR(255) NOT NULL COMMENT 'Hash PHP password_hash',
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  blocked TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_qr_token (qr_token),
  KEY idx_users_role (role),
  KEY idx_users_blocked (blocked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
