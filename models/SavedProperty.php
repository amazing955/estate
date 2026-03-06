<?php
// models/SavedProperty.php
require_once 'Model.php';

class SavedProperty extends Model {
    public function __construct() {
        parent::__construct();
        // ensure table exists (migration fallback)
        $sql = "CREATE TABLE IF NOT EXISTS saved_properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            client_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB";
        $this->conn->exec($sql);
    }

    public function save($property_id, $client_id) {
        $sql = "INSERT INTO saved_properties (property_id, client_id) VALUES (:property_id, :client_id)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':property_id'=>$property_id, ':client_id'=>$client_id]);
    }

    public function unsave($property_id, $client_id) {
        $sql = "DELETE FROM saved_properties WHERE property_id = :property_id AND client_id = :client_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':property_id'=>$property_id, ':client_id'=>$client_id]);
    }

    public function findByClient($client_id) {
        // only return saved properties that are still available
        $sql = "SELECT sp.*, p.title, p.location FROM saved_properties sp
                JOIN properties p ON sp.property_id = p.id
                WHERE sp.client_id = :client_id AND p.status = 'Available'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':client_id' => $client_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isSaved($property_id, $client_id) {
        $sql = "SELECT COUNT(*) FROM saved_properties WHERE property_id = :property_id AND client_id = :client_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':property_id'=>$property_id, ':client_id'=>$client_id]);
        return $stmt->fetchColumn() > 0;
    }
}
