<?php

declare(strict_types=1);

class TypesManageController
{
    /** @var PDO */
    private $pdo;
    /** @var TypeEquipment */
    private $types;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->types = new TypeEquipment($pdo);
    }

    public function index(string $message = '', string $messageType = ''): void
    {
        $rows = $this->types->all();
        $deleteId = isset($_GET['delete_type']) ? (int) $_GET['delete_type'] : 0;
        $deleteRow = $deleteId > 0 ? $this->types->find($deleteId) : null;
        $deleteCount = $deleteRow ? $this->types->countEquipment($deleteId) : 0;

        ob_start();
        require VIEW_PATH . '/types/manage.php';
        $content = ob_get_clean();
        $activeRoute = 'types';
        require VIEW_PATH . '/layouts/main.php';
    }
}
