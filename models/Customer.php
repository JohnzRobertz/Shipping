<?php
class Customer {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function getAllCustomers() {
        $sql = "SELECT * FROM customers ORDER BY name ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching customers: " . $e->getMessage());
            return [];
        }
    }

    public function getCustomerById($id) {
        $sql = "SELECT * FROM customers WHERE id = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching customer by ID: " . $e->getMessage());
            return null;
        }
    }

    public function getCustomerByCode($code) {
        $sql = "SELECT * FROM customers WHERE code = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching customer by code: " . $e->getMessage());
            return null;
        }
    }

    public function createCustomer($data) {
        $sql = "INSERT INTO customers (name, code, email, phone, address, tax_id, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['code'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['tax_id'] ?? null
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating customer: " . $e->getMessage());
            return false;
        }
    }

    public function updateCustomer($id, $data) {
        $sql = "UPDATE customers SET 
                name = ?, 
                code = ?,
                email = ?, 
                phone = ?, 
                address = ?,
                tax_id = ?,
                updated_at = NOW() 
                WHERE id = ?";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['code'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['tax_id'] ?? null,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating customer: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCustomer($id) {
        $sql = "DELETE FROM customers WHERE id = ?";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting customer: " . $e->getMessage());
            return false;
        }
    }
}
?>

