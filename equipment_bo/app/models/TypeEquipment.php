<?php

declare(strict_types=1);

class TypeEquipment
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $sql = 'SELECT id, category_name, icon, daily_cost, warranty_months, default_maintenance_frequency_months
                FROM type_equipment ORDER BY category_name ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, category_name, icon, daily_cost, warranty_months, default_maintenance_frequency_months
             FROM type_equipment WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function countEquipment(int $typeId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM equipment WHERE type_id = :id');
        $stmt->execute([':id' => $typeId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array{category_name:string,icon:string,daily_cost:float,warranty_months:int,default_maintenance_frequency_months:int} $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO type_equipment (category_name, icon, daily_cost, warranty_months, default_maintenance_frequency_months)
             VALUES (:n, :i, :d, :w, :m)'
        );
        $stmt->execute([
            ':n' => $data['category_name'],
            ':i' => $data['icon'],
            ':d' => $data['daily_cost'],
            ':w' => $data['warranty_months'],
            ':m' => $data['default_maintenance_frequency_months'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array{category_name:string,icon:string,daily_cost:float,warranty_months:int,default_maintenance_frequency_months:int} $data
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE type_equipment SET category_name = :n, icon = :i, daily_cost = :d,
             warranty_months = :w, default_maintenance_frequency_months = :m WHERE id = :id'
        );
        $stmt->execute([
            ':n' => $data['category_name'],
            ':i' => $data['icon'],
            ':d' => $data['daily_cost'],
            ':w' => $data['warranty_months'],
            ':m' => $data['default_maintenance_frequency_months'],
            ':id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function reassignEquipmentToType(int $fromTypeId, int $toTypeId): void
    {
        $stmt = $this->pdo->prepare('UPDATE equipment SET type_id = :to WHERE type_id = :from');
        $stmt->execute([':to' => $toTypeId, ':from' => $fromTypeId]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM type_equipment WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
