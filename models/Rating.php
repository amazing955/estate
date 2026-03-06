<?php
// models/Rating.php
require_once 'Model.php';

class Rating extends Model {
    public function __construct() {
        parent::__construct();
        // ensure table exists
        $sql = "CREATE TABLE IF NOT EXISTS ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            client_id INT NOT NULL,
            rating INT NOT NULL,
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB";
        $this->conn->exec($sql);
    }

    public function add($property_id, $client_id, $rating, $comment='') {
        $sql = "INSERT INTO ratings (property_id, client_id, rating, comment) 
                VALUES (:property_id, :client_id, :rating, :comment)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':property_id' => $property_id,
            ':client_id' => $client_id,
            ':rating' => $rating,
            ':comment' => $comment
        ]);
    }

    public function findByProperty($property_id) {
        $sql = "SELECT r.*, u.username FROM ratings r JOIN users u ON r.client_id = u.id WHERE r.property_id = :property_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':property_id'=>$property_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageRating($property_id) {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count FROM ratings WHERE property_id = :property_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':property_id'=>$property_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function hasRated($property_id, $client_id) {
        $sql = "SELECT id FROM ratings WHERE property_id = :property_id AND client_id = :client_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':property_id'=>$property_id, ':client_id'=>$client_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}
