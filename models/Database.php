<?php
// ================================================
//  FICHIER  : models/Database.php
//  RÔLE     : Classe de connexion PDO (Singleton)
//             Une seule connexion partagée dans tout
//             le projet — PDO obligatoire selon l'énoncé
// ================================================

class Database
{
    private static string $host   = 'localhost';
    private static string $dbname = 'cityzen_events';
    private static string $user   = 'root';
    private static string $pass   = '';

    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = 'mysql:host=' . self::$host
                     . ';dbname='   . self::$dbname
                     . ';charset=utf8mb4';

                self::$instance = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die('<div style="font-family:Arial;padding:20px;background:#fde;border:1px solid red;">'
                  . '<strong>Erreur de connexion PDO :</strong> ' . $e->getMessage()
                  . '</div>');
            }
        }

        return self::$instance;
    }
}
