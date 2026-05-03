<?php
/**
 * Classe Database - Configuration et connexion PDO
 * Gère la connexion à la base de données MySQL
 */

class Database
{
    private static $instance = null;
    private $pdo;

    // Paramètres de connexion
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'furum';
    private const DB_USER = 'root';
    private const DB_PASSWORD = '';
    private const DB_CHARSET = 'utf8mb4';

    /**
     * Constructeur privé - Singleton
     */
    private function __construct()
    {
        try {
            $dsn = 'mysql:host=' . self::DB_HOST . ';dbname=' . self::DB_NAME . ';charset=' . self::DB_CHARSET;
            $this->pdo = new PDO($dsn, self::DB_USER, self::DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }

    /**
     * Récupère l'instance unique de la base de données (Singleton)
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne la connexion PDO
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prépare et exécute une requête
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres à lier
     * @return PDOStatement
     */
    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Évite la duplication d'instances
     */
    private function __clone() {}

    public function __wakeup() {}
}
