<?php

declare(strict_types=1);

/**
 * Connexion PDO partagée avec le reste de CityZen (base `cityzen`, XAMPP).
 */
require_once dirname(__DIR__, 2) . '/model/db.php';

final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
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
