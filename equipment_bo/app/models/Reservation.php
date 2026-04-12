<?php

declare(strict_types=1);

class Reservation
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
    public function allWithEquipment(): array
    {
        $sql = 'SELECT r.id, r.equipment_id, r.user_id, r.start_date, r.end_date, r.purpose, r.status,
                       r.rejection_reason, r.returned_at, r.notify_email_sent, r.created_at,
                       e.name AS equipment_name,
                       t.category_name AS type_category_name,
                       u.username AS user_name
                FROM reservation r
                INNER JOIN equipment e ON e.id = r.equipment_id
                INNER JOIN type_equipment t ON t.id = e.type_id
                LEFT JOIN users u ON u.id = r.user_id
                ORDER BY r.start_date DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pendingWithDetails(): array
    {
        $sql = 'SELECT r.id, r.equipment_id, r.user_id, r.start_date, r.end_date, r.purpose, r.status,
                       e.name AS equipment_name,
                       t.category_name AS type_category_name,
                       u.username AS user_name, \'\' AS user_email
                FROM reservation r
                INNER JOIN equipment e ON e.id = r.equipment_id
                INNER JOIN type_equipment t ON t.id = e.type_id
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.status = \'pending\'
                ORDER BY r.id ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * Approved / rejected / returned / no_show (not pending)
     * @return array<int, array<string, mixed>>
     */
    public function activeAndHistory(): array
    {
        $sql = 'SELECT r.id, r.equipment_id, r.user_id, r.start_date, r.end_date, r.purpose, r.status,
                       r.rejection_reason, r.returned_at,
                       e.name AS equipment_name,
                       t.category_name AS type_category_name,
                       u.username AS user_name
                FROM reservation r
                INNER JOIN equipment e ON e.id = r.equipment_id
                INNER JOIN type_equipment t ON t.id = e.type_id
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.status != \'pending\'
                ORDER BY r.start_date DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forMonthExport(int $year, int $month): array
    {
        $sql = 'SELECT r.id, r.start_date, r.end_date, r.status, r.purpose, r.rejection_reason, r.returned_at,
                       e.name AS equipment_name, e.location,
                       t.category_name AS type_name,
                       u.username AS user_name, \'\' AS user_email
                FROM reservation r
                INNER JOIN equipment e ON e.id = r.equipment_id
                INNER JOIN type_equipment t ON t.id = e.type_id
                LEFT JOIN users u ON u.id = r.user_id
                WHERE YEAR(r.start_date) = :y AND MONTH(r.start_date) = :m
                ORDER BY r.start_date ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':y' => $year, ':m' => $month]);
        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array{id:int,full_name:string,late_count:int}>
     */
    public function usersWithLateReturns(int $minLate = 1): array
    {
        $sql = 'SELECT u.id, u.username AS full_name, COUNT(*) AS late_count
                FROM reservation r
                INNER JOIN users u ON u.id = r.user_id
                WHERE r.status = \'returned\'
                  AND r.returned_at IS NOT NULL
                  AND r.returned_at > r.end_date
                GROUP BY u.id, u.username
                HAVING late_count >= :minc
                ORDER BY late_count DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':minc', $minLate, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function hasOverlap(int $equipmentId, string $startDate, string $endDate, ?int $excludeReservationId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM reservation
                WHERE equipment_id = :eq
                  AND status IN (\'pending\', \'approved\')
                  AND start_date < :end_date
                  AND end_date > :start_date';
        $params = [
            ':eq'         => $equipmentId,
            ':start_date' => $startDate,
            ':end_date'   => $endDate,
        ];
        if ($excludeReservationId !== null) {
            $sql .= ' AND id <> :ex';
            $params[':ex'] = $excludeReservationId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, equipment_id, user_id, start_date, end_date, purpose, status, rejection_reason, returned_at
             FROM reservation WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reservation (equipment_id, user_id, start_date, end_date, purpose, status)
             VALUES (:e, :u, :sd, :ed, :p, :st)'
        );
        $stmt->execute([
            ':e'  => $data['equipment_id'],
            ':u'  => $data['user_id'] ?? null,
            ':sd' => $data['start_date'],
            ':ed' => $data['end_date'],
            ':p'  => $data['purpose'] ?? null,
            ':st' => $data['status'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function approve(int $id, bool $markEmailSent): bool
    {
        $this->pdo->beginTransaction();
        try {
            $r = $this->find($id);
            if ($r === null || ($r['status'] ?? '') !== 'pending') {
                $this->pdo->rollBack();
                return false;
            }
            $eqId = (int) $r['equipment_id'];
            $stmt = $this->pdo->prepare(
                'UPDATE reservation SET status = \'approved\', notify_email_sent = :n WHERE id = :id AND status = \'pending\''
            );
            $stmt->execute([':n' => $markEmailSent ? 1 : 0, ':id' => $id]);
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }
            $this->pdo->prepare("UPDATE equipment SET status = 'reserved' WHERE id = :e")->execute([':e' => $eqId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function reject(int $id, string $reason): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reservation SET status = \'rejected\', rejection_reason = :r WHERE id = :id AND status = \'pending\''
        );
        $stmt->execute([':r' => $reason, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function markReturned(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            $row = $this->find($id);
            if ($row === null || ($row['status'] ?? '') !== 'approved') {
                $this->pdo->rollBack();
                return false;
            }
            $eqId = (int) $row['equipment_id'];
            $stmt = $this->pdo->prepare(
                'UPDATE reservation SET status = \'returned\', returned_at = NOW() WHERE id = :id'
            );
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }
            $this->pdo->prepare("UPDATE equipment SET status = 'available' WHERE id = :e")->execute([':e' => $eqId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function markNoShow(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            $row = $this->find($id);
            if ($row === null || ($row['status'] ?? '') !== 'approved') {
                $this->pdo->rollBack();
                return false;
            }
            $eqId = (int) $row['equipment_id'];
            $stmt = $this->pdo->prepare("UPDATE reservation SET status = 'no_show' WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }
            $this->pdo->prepare("UPDATE equipment SET status = 'available' WHERE id = :e")->execute([':e' => $eqId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
