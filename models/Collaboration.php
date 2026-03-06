<?php
// models/Collaboration.php
require_once 'Model.php';

class Collaboration extends Model {
    public function __construct() {
        parent::__construct();
        $sql = "CREATE TABLE IF NOT EXISTS collaborations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            broker_id INT NOT NULL,
            owner_id INT NOT NULL,
            status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (broker_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB";
        $this->conn->exec($sql);
    }

    public function request($broker_id, $owner_id) {
        // avoid duplicate pending/accepted
        $sql = "SELECT COUNT(*) FROM collaborations WHERE broker_id = :broker AND owner_id = :owner AND status IN ('pending','accepted')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':broker'=>$broker_id, ':owner'=>$owner_id]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }
        $sql = "INSERT INTO collaborations (broker_id, owner_id) VALUES (:broker, :owner)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':broker'=>$broker_id, ':owner'=>$owner_id]);
    }

    public function respond($id, $status) {
        if (!in_array($status, ['accepted','rejected'])) return false;
        $sql = "UPDATE collaborations SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':status'=>$status, ':id'=>$id]);
    }

    public function findPendingForOwner($owner_id) {
        $sql = "SELECT c.*, u.username AS broker_name FROM collaborations c
                JOIN users u ON c.broker_id = u.id
                WHERE c.owner_id = :owner AND c.status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':owner'=>$owner_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAcceptedForBroker($broker_id) {
        $sql = "SELECT c.*, u.username AS owner_name FROM collaborations c
                JOIN users u ON c.owner_id = u.id
                WHERE c.broker_id = :broker AND c.status = 'accepted'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':broker'=>$broker_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPendingForBroker($broker_id) {
        $sql = "SELECT c.*, u.username AS owner_name FROM collaborations c
                JOIN users u ON c.owner_id = u.id
                WHERE c.broker_id = :broker AND c.status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':broker'=>$broker_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM collaborations WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}