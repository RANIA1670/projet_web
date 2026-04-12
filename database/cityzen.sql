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
  password_hash VARCHAR(255) NOT NULL COMMENT 'Hash PHP password_hash',
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
