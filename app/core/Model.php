<?php
/**
 * CityZen - Base Model
 * Toutes les méthodes ORM communes
 */

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` ORDER BY `$orderBy` $direction");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function find(array $conditions): array
    {
        $where = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $where[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }
        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE $whereStr");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}`");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function countWhere(array $conditions): int
    {
        $where = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $where[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }
        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE $whereStr");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function insert(array $data): int
    {
        $columns = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        $params = [];
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` ($columns) VALUES ($placeholders)");
        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($data)));
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET $set WHERE `{$this->primaryKey}` = :id");
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function paginate(int $page = 1, int $perPage = ITEMS_PER_PAGE, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` ORDER BY `$orderBy` $direction LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
