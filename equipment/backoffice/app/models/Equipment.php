<?php

declare(strict_types=1);

class Equipment
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
    public function allWithType(?int $typeId = null, ?string $status = null, ?string $location = null): array
    {
        $sql = 'SELECT e.id, e.name, e.status, e.location, e.type_id, e.price_per_day, e.last_maintenance, e.latitude, e.longitude,
                       t.category_name AS type_category_name, t.icon AS type_icon, t.daily_cost AS type_daily_cost
                FROM equipment e
                INNER JOIN type_equipment t ON t.id = e.type_id
                WHERE 1=1';
        $params = [];
        if ($typeId !== null && $typeId > 0) {
            $sql .= ' AND e.type_id = :tid';
            $params[':tid'] = $typeId;
        }
        if ($status !== null && $status !== '') {
            $sql .= ' AND e.status = :st';
            $params[':st'] = $status;
        }
        if ($location !== null && $location !== '') {
            $sql .= ' AND e.location LIKE :loc';
            $params[':loc'] = '%' . $location . '%';
        }
        $sql .= ' ORDER BY t.category_name ASC, e.name ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, status, location, type_id, price_per_day, last_maintenance, latitude, longitude FROM equipment WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithType(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id, e.name, e.status, e.location, e.type_id, e.price_per_day, e.last_maintenance, e.latitude, e.longitude,
                    t.category_name AS type_category_name, t.icon AS type_icon, t.daily_cost AS type_daily_cost
             FROM equipment e
             INNER JOIN type_equipment t ON t.id = e.type_id
             WHERE e.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array{name:string,status:string,location:string,type_id:int,price_per_day?:float,last_maintenance?:?string,latitude?:?float,longitude?:?float} $data
     */
    public function create(array $data): int
    {
        $pricePerDay = max(0, (float) ($data['price_per_day'] ?? 0));
        $stmt = $this->pdo->prepare(
            'INSERT INTO equipment (name, status, location, type_id, price_per_day, last_maintenance, latitude, longitude)
             VALUES (:n, :s, :l, :t, :ppd, :lm, :lat, :lng)'
        );
        $stmt->execute([
            ':n'   => $data['name'],
            ':s'   => $data['status'],
            ':l'   => $data['location'],
            ':t'   => $data['type_id'],
            ':ppd' => $pricePerDay,
            ':lm'  => $data['last_maintenance'] ?? null,
            ':lat' => $data['latitude'] ?? null,
            ':lng' => $data['longitude'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array{name:string,status:string,location:string,type_id:int,price_per_day?:float,last_maintenance?:?string,latitude?:?float,longitude?:?float} $data
     */
    public function update(int $id, array $data): bool
    {
        $pricePerDay = max(0, (float) ($data['price_per_day'] ?? 0));
        $stmt = $this->pdo->prepare(
            'UPDATE equipment SET name = :n, status = :s, location = :l, type_id = :t, price_per_day = :ppd,
             last_maintenance = :lm, latitude = :lat, longitude = :lng WHERE id = :id'
        );
        $stmt->execute([
            ':n'   => $data['name'],
            ':s'   => $data['status'],
            ':l'   => $data['location'],
            ':t'   => $data['type_id'],
            ':ppd' => $pricePerDay,
            ':lm'  => $data['last_maintenance'] ?? null,
            ':lat' => $data['latitude'] ?? null,
            ':lng' => $data['longitude'] ?? null,
            ':id'  => $id,
        ]);
        return true;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM equipment WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function bulkSetStatus(array $ids, string $status): int
    {
        $allowed = ['available', 'reserved', 'maintenance', 'out_of_service'];
        if (!in_array($status, $allowed, true) || $ids === []) {
            return 0;
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("UPDATE equipment SET status = ? WHERE id IN ($placeholders)");
        $stmt->execute(array_merge([$status], $ids));
        return $stmt->rowCount();
    }

    public function setStatus(int $id, string $status): bool
    {
        return $this->bulkSetStatus([$id], $status) > 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function maintenanceOverdue(int $months = 6): array
    {
        $m = max(1, min(120, $months));
        $sql = "SELECT e.*, t.category_name AS type_category_name
                FROM equipment e
                INNER JOIN type_equipment t ON t.id = e.type_id
                WHERE e.last_maintenance IS NULL
                   OR e.last_maintenance < DATE_SUB(CURDATE(), INTERVAL {$m} MONTH)
                ORDER BY e.last_maintenance IS NULL DESC, e.last_maintenance ASC";
        return $this->pdo->query($sql)->fetchAll();
    }
}
