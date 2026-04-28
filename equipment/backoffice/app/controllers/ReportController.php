<?php

declare(strict_types=1);

class ReportController
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
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));
        $year = max(2000, min(2100, $year));
        $month = max(1, min(12, $month));

        $forecast = $this->equipment->maintenanceOverdue(6);
        $violations = $this->reservations->usersWithLateReturns(1);

        ob_start();
        require VIEW_PATH . '/reports/index.php';
        $content = ob_get_clean();
        $activeRoute = 'reports';
        require VIEW_PATH . '/layouts/main.php';
    }

    public function exportCsv(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));
        $rows = $this->reservations->forMonthExport($year, $month);

        $fn = sprintf('reservations_%04d_%02d.csv', $year, $month);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fn . '"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id', 'start_date', 'end_date', 'status', 'equipment', 'location', 'type', 'user', 'email', 'purpose'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['start_date'],
                $r['end_date'],
                $r['status'],
                $r['equipment_name'],
                $r['location'],
                $r['type_name'],
                $r['user_name'] ?? '',
                $r['user_email'] ?? '',
                $r['purpose'] ?? '',
            ], ';');
        }
        fclose($out);
        exit;
    }

    public function printMonth(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));
        $rows = $this->reservations->forMonthExport($year, $month);
        require VIEW_PATH . '/reports/print_month.php';
        exit;
    }
}
