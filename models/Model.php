<?php
// models/Model.php
require_once __DIR__ . '/../config/database.php';

class Model {
    protected $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
}
