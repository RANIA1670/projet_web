<?php

declare(strict_types=1);

class EquipmentManageController
{
    /** @var PDO */
    private $pdo;
    /** @var Equipment */
    private $equipment;
    /** @var TypeEquipment */
    private $types;
    /** @var Reservation */
    private $reservations;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->equipment = new Equipment($pdo);
        $this->types = new TypeEquipment($pdo);
        $this->reservations = new Reservation($pdo);
    }

    public function index(string $message = '', string $messageType = ''): void
    {
        $typeId = !empty($_GET['filter_type']) ? (int) $_GET['filter_type'] : null;
        $status = isset($_GET['filter_status']) && $_GET['filter_status'] !== '' ? (string) $_GET['filter_status'] : null;
        $location = isset($_GET['filter_location']) && $_GET['filter_location'] !== '' ? trim((string) $_GET['filter_location']) : null;

        $typeId = $typeId !== null && $typeId > 0 ? $typeId : null;

        $rows = $this->equipment->allWithType($typeId, $status, $location);
        $typeList = $this->types->all();
        $equipmentIds = array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $rows);
        $reservationsByEquipment = $this->reservations->groupedByEquipment($equipmentIds);

        ob_start();
        require VIEW_PATH . '/equipment/manage.php';
        $content = ob_get_clean();
        $activeRoute = 'equipment';
        require VIEW_PATH . '/layouts/main.php';
    }
}
