<?php
// config.php
// Configuration PDO pour la connexion à la base de données

// Modifier ces valeurs selon votre environnement
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'cityzen_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('DB_CHARSET', 'utf8mb4');

/**
 * Retourne une instance PDO configurée.
 *
 * @return PDO
 */
function getPDO(): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // En production, éviter d'exposer l'erreur complète.
        die('Erreur de connexion à la base de données : ' . $e->getMessage());
    }
}
