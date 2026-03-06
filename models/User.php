<?php
// models/User.php
require_once 'Model.php';

class User extends Model {
    public function __construct() {
        parent::__construct();
        // ensure profile_pic column exists
        $this->conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(255) NULL");
        // broker approval flag
        $this->conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS broker_approved TINYINT(1) DEFAULT 0");
    }

    public function create($username, $email, $passwordHash, $role) {
        $sql = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $passwordHash,
            ':role' => $role
        ]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function all() {
        $sql = "SELECT * FROM users";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id'=>$id]);
    }

    public function updateRole($id, $role) {
        $sql = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':role'=>$role, ':id'=>$id]);
    }

    public function setBrokerApproved($id, $approved) {
        $sql = "UPDATE users SET broker_approved = :ap WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':ap'=>$approved?1:0, ':id'=>$id]);
    }

    public function isBrokerApproved($id) {
        $sql = "SELECT broker_approved FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$id]);
        return $stmt->fetchColumn() == 1;
    }

    public function updateProfile($id, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $params[':id'] = $id;
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // return last insert id after create
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }
}
