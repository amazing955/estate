<?php
// models/PropertyVideo.php
require_once 'Model.php';

class PropertyVideo extends Model {
    public function __construct() {
        parent::__construct();
    }

    public function add($property_id, $path) {
        $sql = "INSERT INTO property_videos (property_id, video_path) VALUES (:property_id, :video_path)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':property_id' => $property_id,
            ':video_path' => $path
        ]);
    }

    public function findByProperty($property_id) {
        $sql = "SELECT * FROM property_videos WHERE property_id = :property_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':property_id' => $property_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
