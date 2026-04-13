<?php
require_once __DIR__ . '/../models/Participation.php';

class ParticipationController {
    private $participationModel;

    public function __construct() {
        $this->participationModel = new Participation();
    }

    public function index() {
        $participations = $this->participationModel->getAll();
        require_once __DIR__ . '/../views/participations/index.php';
    }

    public function register() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->participationModel->user_name = $_POST['user_name'];
            $this->participationModel->user_email = $_POST['user_email'];
            $this->participationModel->event_id = $_POST['event_id'];
            
            if($this->participationModel->register()) {
                header("Location: index.php?controller=participation&action=index");
                exit();
            }
        }
        require_once __DIR__ . '/../views/participations/register.php';
    }
}
?>