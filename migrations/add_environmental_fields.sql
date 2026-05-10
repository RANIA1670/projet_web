-- =====================================================================
-- Migration : Ajout de colonnes pour la corrélation environnementale
-- =====================================================================
-- Exécutez ce script pour ajouter les colonnes manquantes à la table posts
-- 
-- Colonnes ajoutées :
-- - latitude : Latitude du lieu mentionné (float, nullable)
-- - longitude : Longitude du lieu mentionné (float, nullable)
-- - weather_current : Condition météo actuelle au moment de la création (varchar)
-- - ai_tag : Tag IA détecté dans le contenu (varchar, nullable)
-- - display_address : Adresse normalisée depuis Nominatim (varchar)
-- - metadata_env : JSON stockant les données complètes (json, nullable)
-- =====================================================================

USE furum;

-- Ajout des colonnes si elles n'existent pas
ALTER TABLE posts 
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) NULL DEFAULT NULL AFTER content,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) NULL DEFAULT NULL AFTER latitude,
ADD COLUMN IF NOT EXISTS weather_current VARCHAR(50) NULL DEFAULT NULL AFTER longitude,
ADD COLUMN IF NOT EXISTS ai_tag VARCHAR(100) NULL DEFAULT NULL AFTER weather_current,
ADD COLUMN IF NOT EXISTS display_address VARCHAR(255) NULL DEFAULT NULL AFTER ai_tag,
ADD COLUMN IF NOT EXISTS metadata_env JSON NULL DEFAULT NULL AFTER display_address;

-- Créer des index pour améliorer les performances
ALTER TABLE posts 
ADD INDEX IF NOT EXISTS idx_posts_weather_tag (weather_current, ai_tag),
ADD INDEX IF NOT EXISTS idx_posts_coordinates (latitude, longitude),
ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'Actif' AFTER metadata_env;

-- Données de test : Ajouter un post avec données météo/environnementales
-- (optionnel, pour les tests)
-- INSERT INTO posts (user_id, title, content, latitude, longitude, weather_current, ai_tag, display_address, status)
-- VALUES (1, 'Test Inondation', 'L\'eau monte dangereusement...', 48.8566, 2.3522, 'Rain', 'Inondation', 'Paris, France', 'Alerte Climatique');

-- Afficher la structure mise à jour
DESCRIBE posts;
