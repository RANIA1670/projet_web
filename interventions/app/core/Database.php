<?php

declare(strict_types=1);

/**
 * Point d'accès DB : réutilise la connexion CityZen (model/db.php).
 */

require_once dirname(__DIR__, 3) . '/model/db.php';

class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = cityzen_db();
        }

        return self::$instance;
    }
}
