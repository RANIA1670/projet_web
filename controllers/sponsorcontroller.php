<?php
require_once __DIR__ . '/../models/Sponsor.php';

class SponsorController {
    private $sponsorModel;

    public function __construct() {
        $this->sponsorModel = new Sponsor();
    }

    public function index() {
        $sponsors = $this->sponsorModel->getAll();
        require_once __DIR__ . '/../views/sponsors/index.php';
    }

    public function create() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->sponsorModel->name = $_POST['name'];
            $this->sponsorModel->logo = $_POST['logo'];
            $this->sponsorModel->website = $_POST['website'];
            $this->sponsorModel->event_id = $_POST['event_id'];
            
            if($this->sponsorModel->create()) {
                header("Location: index.php?controller=sponsor&action=index");
                exit();
            }
        }
        require_once __DIR__ . '/../views/sponsors/create.php';
    }
}
?>