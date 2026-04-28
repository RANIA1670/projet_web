<?php

declare(strict_types=1);

class EquipmentIssue
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param array{equipment_id:int,user_id:?int,issue_type:string,description:string,photo_path:?string} $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO equipment_issue (equipment_id, user_id, issue_type, photo_path, description, status)
             VALUES (:e, :u, :it, :ph, :d, \'open\')'
        );
        $stmt->execute([
            ':e'  => $data['equipment_id'],
            ':u'  => $data['user_id'],
            ':it' => $data['issue_type'],
            ':ph' => $data['photo_path'] ?? null,
            ':d'  => $data['description'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForAdmin(string $statusFilter = 'open'): array
    {
        $sql = 'SELECT i.id, i.equipment_id, i.user_id, i.issue_type, i.photo_path, i.description, i.status, i.created_at,
                       e.name AS equipment_name, u.username AS user_name
                FROM equipment_issue i
                INNER JOIN equipment e ON e.id = i.equipment_id
                LEFT JOIN users u ON u.id = i.user_id
                WHERE 1=1';
        $params = [];
        if ($statusFilter === 'open') {
            $sql .= ' AND i.status = \'open\'';
        } elseif ($statusFilter === 'acknowledged') {
            $sql .= ' AND i.status = \'acknowledged\'';
        } elseif ($statusFilter === 'resolved') {
            $sql .= ' AND i.status = \'resolved\'';
        } elseif ($statusFilter !== 'all') {
            $sql .= ' AND i.status = :st';
            $params[':st'] = $statusFilter;
        }
        $sql .= ' ORDER BY i.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['open', 'acknowledged', 'resolved'], true)) {
            return false;
        }
        $stmt = $this->pdo->prepare('UPDATE equipment_issue SET status = :s WHERE id = :id');
        $stmt->execute([':s' => $status, ':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
