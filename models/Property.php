<?php
// models/Property.php
require_once 'Model.php';

class Property extends Model {
    public function __construct() {
        parent::__construct();
        // ensure original_owner_id column exists for repost tracking
        $this->conn->exec("ALTER TABLE properties ADD COLUMN IF NOT EXISTS original_owner_id INT NULL");
    }

    public function create($owner_id, $title, $description, $price, $type, $location, $status = 'Available', $original_owner = null) {
        $sql = "INSERT INTO properties (owner_id, title, description, price, type, location, status, original_owner_id) VALUES
            (:owner_id, :title, :description, :price, :type, :location, :status, :original_owner)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute([
            ':owner_id' => $owner_id,
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':type' => $type,
            ':location' => $location,
            ':status' => $status,
            ':original_owner' => $original_owner
        ])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // return the id of the last inserted property
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }

    // delete a property by id
    public function delete($id) {
        // remove any broker reposts first
        $this->deleteReposts($id);
        $sql = "DELETE FROM properties WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Remove all properties that were reposted from the given original property id.
     */
    public function deleteReposts($originalPropId) {
        $sql = "DELETE FROM properties WHERE original_owner_id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $originalPropId]);
    }

    /**
     * Return reposted records for a given original property ID.
     * Each entry contains id and owner_id so controllers can notify brokers.
     */
    public function findReposts($originalPropId) {
        $sql = "SELECT id, owner_id FROM properties WHERE original_owner_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $originalPropId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE properties SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $data['id'] = $id;
        $result = $stmt->execute($data);
        // if status has been updated to Sold, remove any reposts
        if ($result && isset($data['status']) && $data['status'] === 'Sold') {
            $this->deleteReposts($id);
        }
        return $result;
    }

    public function findById($id) {
        $sql = "SELECT * FROM properties WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByOwner($owner_id) {
        $sql = "SELECT * FROM properties WHERE owner_id = :owner_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':owner_id' => $owner_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function all() {
        // return everything (used by admins/owners)
        $sql = "SELECT * FROM properties";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allAvailable() {
        $sql = "SELECT * FROM properties WHERE status = 'Available'";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($filters = []) {
        $sql = "SELECT * FROM properties WHERE status = 'Available'"; // only available to clients
        $params = [];
        if (!empty($filters['type'])) {
            $sql .= " AND type = :type";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND location LIKE :location";
            $params[':location'] = "%{$filters['location']}%";
        }
        if (!empty($filters['min_price'])) {
            $sql .= " AND price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
