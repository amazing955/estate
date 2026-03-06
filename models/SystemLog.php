<?php
// models/SystemLog.php
require_once 'Model.php';

class SystemLog extends Model {
    public function __construct() {
        parent::__construct();
        $sql = "CREATE TABLE IF NOT EXISTS system_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB";
        $this->conn->exec($sql);
    }

    // purge logs older than 30 days
    private function purgeOld() {
        $sql = "DELETE FROM system_logs WHERE created_at < (NOW() - INTERVAL 30 DAY)";
        $this->conn->exec($sql);
    }

    public function add($type, $message) {
        // remove old entries before adding
        $this->purgeOld();
        $sql = "INSERT INTO system_logs (type, message) VALUES (:type, :message)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':type' => $type, ':message' => $message]);
    }

    public function all() {
        // clean first
        $this->purgeOld();
        $sql = "SELECT * FROM system_logs ORDER BY created_at DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
