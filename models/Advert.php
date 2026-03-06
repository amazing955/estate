<?php
// models/Advert.php
require_once 'Model.php';

class Advert extends Model {
    public function __construct() {
        parent::__construct();
    }

    public function create($title, $image_path, $link, $position, $expiry_date) {
        $sql = "INSERT INTO adverts (title, image_path, link, position, expiry_date) 
                VALUES (:title, :image_path, :link, :position, :expiry_date)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':image_path' => $image_path,
            ':link' => $link,
            ':position' => $position,
            ':expiry_date' => $expiry_date
        ]);
    }

    public function allActive() {
        $sql = "SELECT * FROM adverts WHERE expiry_date IS NULL OR expiry_date >= CURDATE()";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
