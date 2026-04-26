<?php
/**
 * CityZen - Database Connection (PDO Singleton)
 */

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    die('<div style="font-family:monospace;background:#fee;padding:20px;border:2px solid #e00;margin:20px">
                        <h3>Erreur de connexion à la base de données</h3>
                        <p>' . htmlspecialchars($e->getMessage()) . '</p>
                    </div>');
                } else {
                    die('Une erreur est survenue. Veuillez réessayer plus tard.');
                }
            }
        }
        return self::$instance;
    }
}
