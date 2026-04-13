<?php
require_once __DIR__ . '/../config/database.php';

class Sponsor {
    private $conn;
    private $table = "sponsors";

    public $id;
    public $name;
    public $logo;
    public $website;
    public $event_id;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEvent($event_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE event_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$event_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " SET name=:name, logo=:logo, website=:website, event_id=:event_id";
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->logo = htmlspecialchars(strip_tags($this->logo));
        $this->website = htmlspecialchars(strip_tags($this->website));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":logo", $this->logo);
        $stmt->bindParam(":website", $this->website);
        $stmt->bindParam(":event_id", $this->event_id);
        
        return $stmt->execute();
    }
}
?>