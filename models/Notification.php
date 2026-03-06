<?php
// models/Notification.php
require_once 'Model.php';

class Notification extends Model {
    public function __construct() {
        parent::__construct();
        // ensure column exists
        $this->conn->exec("ALTER TABLE notifications ADD COLUMN IF NOT EXISTS property_id INT NULL");
    }

    public function create($user_id, $message, $property_id = null) {
        $sql = "INSERT INTO notifications (user_id, message, property_id) VALUES (:user_id, :message, :property_id)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':message' => $message,
            ':property_id' => $property_id
        ]);
    }

    public function findByUser($user_id) {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markRead($id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
