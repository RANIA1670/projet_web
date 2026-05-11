<?php

declare(strict_types=1);

/**
 * Pont PDO vers la base CityZen (même instance que cityzen_db()).
 * Module forum — branche amine (tables posts, replies, likes, …).
 */
final class Database
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return cityzen_db();
    }

    public function prepare(string $sql): \PDOStatement
    {
        return $this->getConnection()->prepare($sql);
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
    }
}
