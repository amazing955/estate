<?php
// models/Tour.php
require_once 'Model.php';

class Tour extends Model {
    public function __construct() {
        parent::__construct();
        $sql = "CREATE TABLE IF NOT EXISTS tours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            client_id INT NOT NULL,
            tour_date DATETIME NOT NULL,
            phone VARCHAR(50) NULL,
            email VARCHAR(100) NULL,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB";
        // ensure columns exist for older installs
        $this->conn->exec("ALTER TABLE tours ADD COLUMN IF NOT EXISTS phone VARCHAR(50) NULL");
        $this->conn->exec("ALTER TABLE tours ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL");
        $this->conn->exec($sql);
    }

    public function add($property_id, $client_id, $tour_date, $phone = null, $email = null, $message='') {
        $sql = "INSERT INTO tours (property_id, client_id, tour_date, phone, email, message) 
                VALUES (:property_id, :client_id, :tour_date, :phone, :email, :message)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':property_id' => $property_id,
            ':client_id' => $client_id,
            ':tour_date' => $tour_date,
            ':phone' => $phone,
            ':email' => $email,
            ':message' => $message
        ]);
    }

    public function findByProperty($property_id) {
        $sql = "SELECT t.*, u.username FROM tours t JOIN users u ON t.client_id = u.id WHERE t.property_id = :property_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':property_id'=>$property_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT t.*, u.username FROM tours t JOIN users u ON t.client_id = u.id WHERE t.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }
}
