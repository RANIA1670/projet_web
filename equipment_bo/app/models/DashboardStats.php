<?php

declare(strict_types=1);

class DashboardStats
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function totalEquipment(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM equipment')->fetchColumn();
    }

    public function countMaintenance(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'maintenance'");
        return (int) $stmt->fetchColumn();
    }

    public function countPendingReservations(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM reservation WHERE status = 'pending'");
        return (int) $stmt->fetchColumn();
    }

    /** Équipements avec réservation active aujourd'hui (approuvée ou en attente chevauchant la date) */
    public function countReservedToday(): int
    {
        $sql = "SELECT COUNT(DISTINCT equipment_id) FROM reservation
                WHERE status IN ('approved', 'pending')
                  AND DATE(start_date) <= CURDATE()
                  AND DATE(end_date) >= CURDATE()";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    /**
     * @return array<int, array{category_name: string, icon: string, request_count: int}>
     */
    public function mostRequestedTypes(): array
    {
        $sql = 'SELECT t.category_name, t.icon, COUNT(r.id) AS request_count
                FROM type_equipment t
                LEFT JOIN equipment e ON e.type_id = t.id
                LEFT JOIN reservation r ON r.equipment_id = e.id
                GROUP BY t.id, t.category_name, t.icon
                ORDER BY request_count DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<int, array{location: string, equipment_count: int, reservation_hits: int}>
     */
    public function usageByLocation(): array
    {
        $sql = 'SELECT e.location,
                       COUNT(DISTINCT e.id) AS equipment_count,
                       COUNT(r.id) AS reservation_hits
                FROM equipment e
                LEFT JOIN reservation r ON r.equipment_id = e.id
                    AND r.start_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                WHERE e.location != \'\'
                GROUP BY e.location
                ORDER BY reservation_hits DESC, equipment_count DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function lateReturns(): array
    {
        $sql = 'SELECT r.id, r.equipment_id, r.user_id, r.start_date, r.end_date, r.status, r.returned_at,
                       e.name AS equipment_name, u.username AS user_name
                FROM reservation r
                INNER JOIN equipment e ON e.id = r.equipment_id
                LEFT JOIN users u ON u.id = r.user_id
                WHERE (r.status = \'approved\' AND r.end_date < NOW())
                   OR (r.status = \'returned\' AND r.returned_at IS NOT NULL AND r.returned_at > r.end_date)
                ORDER BY r.end_date ASC';
        return $this->pdo->query($sql)->fetchAll();
    }
}
