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
            'SELECT id, equipment_id, user_id, extension_of_id, start_date, end_date, purpose, usage_purpose, status, rejection_reason, returned_at
             FROM reservation WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reservation (equipment_id, user_id, extension_of_id, start_date, end_date, purpose, usage_purpose, status)
             VALUES (:e, :u, :ext, :sd, :ed, :p, :up, :st)'
        );
        $stmt->execute([
            ':e'   => $data['equipment_id'],
            ':u'   => $data['user_id'] ?? null,
            ':ext' => $data['extension_of_id'] ?? null,
            ':sd'  => $data['start_date'],
            ':ed'  => $data['end_date'],
            ':p'   => $data['purpose'] ?? null,
            ':up'  => $data['usage_purpose'] ?? null,
            ':st'  => $data['status'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Prochaine fin de blocage active (pending/approved) ; null = aucune plage future. */
    public function latestBlockingEnd(int $equipmentId): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT MAX(end_date) AS mx FROM reservation
             WHERE equipment_id = :eq AND status IN (\'pending\',\'approved\') AND end_date > NOW()'
        );
        $stmt->execute([':eq' => $equipmentId]);
        $mx = $stmt->fetchColumn();
        if ($mx === false || $mx === null) {
            return null;
        }

        return (string) $mx;
    }

    /**
     * Plages déjà bloquées (pending / approved) pour calendrier citoyen.
     *
     * @return list<array{start:string,end:string}>
     */
    public function busyRangesForEquipment(int $equipmentId): array
    {
        $sql = 'SELECT start_date, end_date FROM reservation
                WHERE equipment_id = :eq
                  AND status IN (\'pending\', \'approved\')
                ORDER BY start_date ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':eq' => $equipmentId]);
        $out = [];
        while ($row = $stmt->fetch()) {
            $out[] = [
                'start' => (string) $row['start_date'],
                'end'   => (string) $row['end_date'],
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(int $userId): array
    {
        $sql = 'SELECT r.id, r.equipment_id, r.extension_of_id, r.start_date, r.end_date, r.purpose, r.usage_purpose,
                       r.status, r.rejection_reason, r.created_at, e.name AS equipment_name
                FROM reservation r
                INNER JOIN equipment e ON e.id = r.equipment_id
                WHERE r.user_id = :u
                ORDER BY r.start_date DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':u' => $userId]);

        return $stmt->fetchAll();
    }

    public function cancelByUser(int $reservationId, int $userId): bool
    {
        $row = $this->find($reservationId);
        if ($row === null || (int) ($row['user_id'] ?? 0) !== $userId) {
            return false;
        }
        $st = (string) ($row['status'] ?? '');
        if ($st === 'pending') {
            $stmt = $this->pdo->prepare(
                'UPDATE reservation SET status = \'cancelled\' WHERE id = :id AND user_id = :u AND status = \'pending\''
            );
            $stmt->execute([':id' => $reservationId, ':u' => $userId]);

            return $stmt->rowCount() > 0;
        }
        if ($st === 'approved') {
            $stmt = $this->pdo->prepare(
                'UPDATE reservation SET status = \'cancelled\' WHERE id = :id AND user_id = :u AND status = \'approved\' AND start_date > NOW()'
            );
            $stmt->execute([':id' => $reservationId, ':u' => $userId]);
            if ($stmt->rowCount() === 0) {
                return false;
            }
            $eqId = (int) $row['equipment_id'];
            $cnt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM reservation WHERE equipment_id = :e AND status IN (\'pending\',\'approved\')'
            );
            $cnt->execute([':e' => $eqId]);
            if ((int) $cnt->fetchColumn() === 0) {
                $this->pdo->prepare('UPDATE equipment SET status = \'available\' WHERE id = :e')->execute([':e' => $eqId]);
            }

            return true;
        }

        return false;
    }

    /**
     * Demande de prolongation : nouvelle réservation en pending après la fin de la réservation approuvée parente.
     */
    public function createExtensionRequest(int $parentId, int $userId, string $newEndDatetime): ?int
    {
        $parent = $this->find($parentId);
        if ($parent === null
            || (int) ($parent['user_id'] ?? 0) !== $userId
            || ($parent['status'] ?? '') !== 'approved'
        ) {
            return null;
        }
        if (strtotime((string) $parent['end_date']) <= time()) {
            return null;
        }
        $eqId = (int) $parent['equipment_id'];
        $parentEndTs = strtotime((string) $parent['end_date']);
        $newEndTs = strtotime($newEndDatetime);
        if ($parentEndTs === false || $newEndTs === false || $newEndTs <= $parentEndTs) {
            return null;
        }
        $start = (string) $parent['end_date'];
        $end = date('Y-m-d H:i:s', $newEndTs);
        if ($this->hasOverlap($eqId, $start, $end, null)) {
            return null;
        }

        return $this->create([
            'equipment_id'     => $eqId,
            'user_id'          => $userId,
            'extension_of_id'  => $parentId,
            'start_date'       => $start,
            'end_date'         => $end,
            'purpose'          => 'Prolongation (demande citoyenne)',
            'usage_purpose'    => null,
            'status'           => 'pending',
        ]);
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
