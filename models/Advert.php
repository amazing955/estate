<?php
// models/Advert.php
require_once 'Model.php';

class Advert extends Model {
    public function __construct() {
        parent::__construct();
        // Ensure approval columns exist
        $this->conn->exec("ALTER TABLE adverts ADD COLUMN IF NOT EXISTS user_id INT NULL");
        $this->conn->exec("ALTER TABLE adverts ADD COLUMN IF NOT EXISTS is_approved INT DEFAULT 0");
        $this->conn->exec("ALTER TABLE adverts ADD COLUMN IF NOT EXISTS approved_by INT NULL");
        $this->conn->exec("ALTER TABLE adverts ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) NULL");
    }

    public function create($title, $image_path, $link, $position, $expiry_date, $user_id = null, $telephone = null) {
        $sql = "INSERT INTO adverts (title, image_path, link, position, expiry_date, user_id, is_approved, telephone) 
                VALUES (:title, :image_path, :link, :position, :expiry_date, :user_id, 0, :telephone)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':image_path' => $image_path,
            ':link' => $link,
            ':position' => $position,
            ':expiry_date' => $expiry_date,
            ':user_id' => $user_id,
            ':telephone' => $telephone
        ]);
    }

    /**
     * Get all active approved adverts (not expired)
     */
    public function allActive() {
        $sql = "SELECT * FROM adverts WHERE is_approved = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) ORDER BY created_at DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all pending (unapproved) adverts
     */
    public function getPending() {
        $sql = "SELECT a.*, u.username FROM adverts a LEFT JOIN users u ON a.user_id = u.id WHERE a.is_approved = 0 ORDER BY a.created_at DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all adverts (admin view)
     */
    public function all() {
        $sql = "SELECT a.*, u.username FROM adverts a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update expiry date for an advert
     */
    public function updateExpiryDate($id, $expiry_date) {
        $sql = "UPDATE adverts SET expiry_date = :expiry_date WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':expiry_date' => $expiry_date]);
    }

    /**
     * Approve an advert, optionally updating expiry date
     */
    public function approve($id, $admin_id, $expiry_date = null) {
        if ($expiry_date !== null) {
            $sql = "UPDATE adverts SET is_approved = 1, approved_by = :admin_id, expiry_date = :expiry_date WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':id' => $id, ':admin_id' => $admin_id, ':expiry_date' => $expiry_date]);
        } else {
            $sql = "UPDATE adverts SET is_approved = 1, approved_by = :admin_id WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':id' => $id, ':admin_id' => $admin_id]);
        }
    }

    /**
     * Reject an advert (delete it)
     */
    public function reject($id) {
        return $this->delete($id);
    }

    public function delete($id) {
        // remove record and optionally cleanup file path
        $sql = "SELECT image_path FROM adverts WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['image_path'])) {
            $file = __DIR__ . '/../' . $row['image_path'];
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        $sql = "DELETE FROM adverts WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id'=>$id]);
    }
}
