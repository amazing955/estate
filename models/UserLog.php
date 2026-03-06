<?php
// models/UserLog.php
require_once 'Model.php';

class UserLog extends Model {
    public function __construct() {
        parent::__construct();
        // create table if not exists
        $sql = "CREATE TABLE IF NOT EXISTS user_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB";
        $this->conn->exec($sql);
    }

    public function add($user_id, $action) {
        $sql = "INSERT INTO user_logs (user_id, action) VALUES (:user_id, :action)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':user_id' => $user_id, ':action' => $action]);
    }

    public function findByUser($user_id) {
        $sql = "SELECT ul.*, u.username FROM user_logs ul
                JOIN users u ON ul.user_id = u.id
                WHERE ul.user_id = :user_id
                ORDER BY ul.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function all() {
        $sql = "SELECT ul.*, u.username FROM user_logs ul
                JOIN users u ON ul.user_id = u.id
                ORDER BY ul.created_at DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
