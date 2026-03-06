<?php
// models/Sale.php
require_once 'Model.php';

class Sale extends Model {
    public function __construct() {
        parent::__construct();
        // ensure buyer_id nullable if existing schema did not allow it
        $this->conn->exec("ALTER TABLE sales MODIFY COLUMN buyer_id INT NULL");
    }

    public function record($property_id, $buyer_id, $seller_id, $price) {
        $sql = "INSERT INTO sales (property_id, buyer_id, seller_id, price) VALUES (:property_id, :buyer_id, :seller_id, :price)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':property_id' => $property_id,
            ':buyer_id' => $buyer_id,
            ':seller_id' => $seller_id,
            ':price' => $price
        ]);
    }
}
