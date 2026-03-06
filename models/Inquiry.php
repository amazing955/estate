<?php
// models/Inquiry.php
require_once 'Model.php';

class Inquiry extends Model {
    public function __construct() {
        parent::__construct();
    }

    public function create($property_id, $client_id, $message) {
        $sql = "INSERT INTO inquiries (property_id, client_id, message) VALUES (:property_id, :client_id, :message)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':property_id' => $property_id,
            ':client_id' => $client_id,
            ':message' => $message
        ]);
    }

    public function findByProperty($property_id) {
        $sql = "SELECT i.*, u.username as client_name FROM inquiries i
                JOIN users u ON i.client_id = u.id
                WHERE i.property_id = :property_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':property_id' => $property_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByClient($client_id) {
        $sql = "SELECT * FROM inquiries WHERE client_id = :client_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':client_id' => $client_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function all() {
        $sql = "SELECT i.*, u.username as client_name, p.title as property_title FROM inquiries i
                JOIN users u ON i.client_id = u.id
                JOIN properties p ON i.property_id = p.id";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT i.*, u.username as client_name, p.title as property_title, p.location, p.price
                FROM inquiries i
                JOIN users u ON i.client_id = u.id
                JOIN properties p ON i.property_id = p.id
                WHERE i.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteById($id) {
        $sql = "DELETE FROM inquiries WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id'=>$id]);
    }

    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }

}
