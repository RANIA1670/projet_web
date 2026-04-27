<?php

declare(strict_types=1);

namespace App\Models;

final class UserStoreModel
{
    private \PDO $pdo;
    private array $user;
    private array $users;
    private int $totalCount;
    private string $selectSql;

    public function __construct()
    {
        $this->pdo = cityzen_db();
        $this->user = [];
        $this->users = [];
        $this->totalCount = 0;
        $this->selectSql = 'id, username, full_name, email, birth_date, postal_code, city, phone, profile_photo, password_hash, role, blocked, created_at, updated_at';
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    // Getters
    public function getPdo(): \PDO { return $this->pdo; }
    public function getUser(): array { return $this->user; }
    public function getUsers(): array { return $this->users; }
    public function getTotalCount(): int { return $this->totalCount; }
    public function getSelectSql(): string { return $this->selectSql; }

    // Setters
    public function setPdo(\PDO $pdo): void { $this->pdo = $pdo; }
    public function setUser(array $user): void { $this->user = $user; }
    public function setUsers(array $users): void { $this->users = $users; }
    public function setTotalCount(int $totalCount): void { $this->totalCount = $totalCount; }
    public function setSelectSql(string $selectSql): void { $this->selectSql = $selectSql; }
}