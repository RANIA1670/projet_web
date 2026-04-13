<?php
require_once __DIR__ . '/../config/database.php';

class Participation {
    private $conn;
    private $table = "participations";

    public $id;
    public $user_name;
    public $user_email;
    public $event_id;
    public $registered_at;

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

    public function register() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_name=:user_name, user_email=:user_email, event_id=:event_id";
        $stmt = $this->conn->prepare($query);
        
        $this->user_name = htmlspecialchars(strip_tags($this->user_name));
        $this->user_email = htmlspecialchars(strip_tags($this->user_email));
        
        $stmt->bindParam(":user_name", $this->user_name);
        $stmt->bindParam(":user_email", $this->user_email);
        $stmt->bindParam(":event_id", $this->event_id);
        
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id]);
    }
}
?>