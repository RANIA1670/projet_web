<?php
require_once __DIR__ . '/../models/Event.php';

class EventController {
    private $eventModel;

    public function __construct() {
        $this->eventModel = new Event();
    }

    public function index() {
        $events = $this->eventModel->getAll();
        require_once __DIR__ . '/../views/events/index.php';
    }

    public function show($id) {
        $event = $this->eventModel->getById($id);
        require_once __DIR__ . '/../views/events/show.php';
    }

    public function create() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->eventModel->name = $_POST['name'];
            $this->eventModel->description = $_POST['description'];
            $this->eventModel->event_date = $_POST['event_date'];
            $this->eventModel->location = $_POST['location'];
            
            if($this->eventModel->create()) {
                header("Location: index.php?controller=event&action=index");
                exit();
            }
        }
        require_once __DIR__ . '/../views/events/create.php';
    }

    public function edit($id) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->eventModel->id = $id;
            $this->eventModel->name = $_POST['name'];
            $this->eventModel->description = $_POST['description'];
            $this->eventModel->event_date = $_POST['event_date'];
            $this->eventModel->location = $_POST['location'];
            
            if($this->eventModel->update()) {
                header("Location: index.php?controller=event&action=index");
                exit();
            }
        }
        
        $event = $this->eventModel->getById($id);
        require_once __DIR__ . '/../views/events/edit.php';
    }

    public function delete($id) {
        $this->eventModel->id = $id;
        if($this->eventModel->delete()) {
            header("Location: index.php?controller=event&action=index");
        }
        exit();
    }
}
?>