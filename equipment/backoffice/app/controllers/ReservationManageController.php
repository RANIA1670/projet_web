<?php

declare(strict_types=1);

class ReservationManageController
{
    /** @var PDO */
    private $pdo;
    /** @var Reservation */
    private $reservations;
    /** @var Equipment */
    private $equipment;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->reservations = new Reservation($pdo);
        $this->equipment = new Equipment($pdo);
    }

    public function index(string $message = '', string $messageType = ''): void
    {
        $pending = $this->reservations->pendingWithDetails();
        $history = $this->reservations->activeAndHistory();

        ob_start();
        require VIEW_PATH . '/reservations/manage.php';
        $content = ob_get_clean();
        $activeRoute = 'reservations';
        require VIEW_PATH . '/layouts/main.php';
    }
}
