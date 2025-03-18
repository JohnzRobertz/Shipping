<?php
require_once 'debug.php';
require_once 'lib/UlidGenerator.php';
require_once 'models/BaseModel.php';

class Customer extends BaseModel {
    protected $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        parent::__construct(); // Call the parent constructor
        // ตรวจสอบและสร้างตารางที่จำเป็น
        $this->createCustomerTablesIfNotExist();
    }
    
    public function getAllCustomers() {
        try {
            $sql = "SELECT * FROM customers ORDER BY name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all customers: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCustomerById($id) {
        try {
            // Debug: แสดง ID ที่ส่งมา
            error_log("getCustomerById: Looking for customer with ID: " . $id);
        
            $sql = "SELECT * FROM customers WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
            // Debug: แสดงผลลัพธ์
            if ($customer) {
                error_log("getCustomerById: Found customer: " . print_r($customer, true));
            } else {
                error_log("getCustomerById: No customer found with ID: " . $id);
            }
        
            return $customer;
        } catch (PDOException $e) {
            error_log("Error getting customer by ID: " . $e->getMessage());
            return false;
        }
    }
    
    public function getCustomerByCode($code) {
        try {
            $sql = "SELECT * FROM customers WHERE code = :code";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting customer by code: " . $e->getMessage());
            return false;
        }
    }
    
    public function createCustomer($data) {
        try {
            // Debug log
            error_log("Creating customer with data: " . print_r($data, true));
            
            // ตรวจสอบว่ามีรหัสลูกค้านี้อยู่แล้วหรือไม่
            if (!empty($data['code'])) {
                $existingCustomer = $this->getCustomerByCode($data['code']);
                if ($existingCustomer) {
                    error_log("Customer code already exists: " . $data['code']);
                    return false; // มีรหัสลูกค้านี้อยู่แล้ว
                }
            }
            
            // สร้าง ULID สำหรับ customer ID
            $id = UlidGenerator::generate();
            error_log("Generated ULID: " . $id);
            
            // Prepare data for insertion
            $customerData = [
                'id' => $id,
                'code' => $data['code'] ?? '',
                'name' => $data['name'] ?? '',
                'phone' => $data['phone'] ?? '',
                'email' => $data['email'] ?? '',
                'address' => $data['address'] ?? '',
                'tax_id' => $data['tax_id'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'created_by' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null
            ];

            // Debug log
            error_log("Prepared customer data: " . print_r($customerData, true));
            
            $sql = "INSERT INTO customers (
                    id, code, name, phone, email, address, tax_id, 
                    created_at, updated_at, created_by
                ) VALUES (
                    :id, :code, :name, :phone, :email, :address, :tax_id,
                    :created_at, :updated_at, :created_by
                )";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($customerData as $key => $value) {
                $type = is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR;
                $stmt->bindValue(":$key", $value, $type);
            }
            
            // Execute and check result
            $result = $stmt->execute();
            
            if ($result) {
                error_log("Customer created successfully with ID: " . $id);
                return $id;
            } else {
                error_log("Failed to create customer. Error info: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("Error creating customer: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    public function updateCustomer($data) {
        try {
            // ตรวจสอบว่ามี id หรือไม่
            if (!isset($data['id'])) {
                error_log("updateCustomer: No ID provided");
                return false;
            }
            
            $id = $data['id'];
            
            // Debug: แสดงข้อมูลที่จะอัปเดต
            error_log("updateCustomer: Updating customer with ID: " . $id);
            error_log("updateCustomer: Data: " . print_r($data, true));
            
            // ตรวจสอบว่ามีรหัสลูกค้านี้อยู่แล้วหรือไม่ (ถ้ามีการเปลี่ยนรหัสลูกค้า)
            if (isset($data['code']) && !empty($data['code'])) {
                $existingCustomer = $this->getCustomerByCode($data['code']);
                if ($existingCustomer && $existingCustomer['id'] != $id) {
                    error_log("updateCustomer: Code already exists for another customer");
                    return false; // มีรหัสลูกค้านี้อยู่แล้ว (และไม่ใช่ลูกค้าคนเดิม)
                }
            }
            
            // สร้าง SQL สำหรับอัพเดทข้อมูล - ใช้เฉพาะฟิลด์ที่มีในตาราง
            $sql = "UPDATE customers SET 
                    name = :name, 
                    code = :code, 
                    phone = :phone, 
                    email = :email, 
                    address = :address, 
                    tax_id = :tax_id,
                    updated_by = :updated_by,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':name', $data['name'] ?? '');
            $stmt->bindValue(':code', $data['code'] ?? '');
            $stmt->bindValue(':phone', $data['phone'] ?? '');
            $stmt->bindValue(':email', $data['email'] ?? '');
            $stmt->bindValue(':address', $data['address'] ?? '');
            $stmt->bindValue(':tax_id', $data['tax_id'] ?? '');
            $stmt->bindValue(':updated_by', $this->getCurrentUserId());
            
            $result = $stmt->execute();
            
            // Debug: แสดงผลลัพธ์การอัปเดท
            if ($result) {
                error_log("updateCustomer: Customer updated successfully");
            } else {
                error_log("updateCustomer: Failed to update customer");
                error_log("updateCustomer: Error info: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating customer: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteCustomer($id) {
        try {
            // Debug: แสดง ID ที่จะลบ
            error_log("deleteCustomer: Attempting to delete customer with ID: " . $id);
            
            $sql = "DELETE FROM customers WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            $result = $stmt->execute();
            
            // Debug: แสดงผลลัพธ์
            if ($result) {
                error_log("deleteCustomer: Customer deleted successfully");
                error_log("deleteCustomer: Rows affected: " . $stmt->rowCount());
            } else {
                error_log("deleteCustomer: Failed to delete customer");
                error_log("deleteCustomer: Error info: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting customer: " . $e->getMessage());
            return false;
        }
    }
    
    public function searchCustomers($search, $limit = 10) {
        try {
            $search = "%$search%";
            $sql = "SELECT * FROM customers 
                    WHERE name LIKE :search OR code LIKE :search OR email LIKE :search 
                    ORDER BY name ASC LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':search', $search);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching customers: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalCustomers() {
        try {
            $sql = "SELECT COUNT(*) as total FROM customers";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting total customers: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getCustomerStatistics() {
        try {
            // Get total customers
            $totalCustomers = $this->getTotalCustomers();
            
            // Get new customers in the last 30 days
            $sql = "SELECT COUNT(*) as new_customers FROM customers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $newCustomers = $result['new_customers'] ?? 0;
            
            // Get active customers (with shipments in the last 90 days)
            $sql = "SELECT COUNT(DISTINCT c.id) as active_customers 
                    FROM customers c 
                    JOIN shipments s ON c.code = s.customer_code 
                    WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $activeCustomers = $result['active_customers'] ?? 0;
            
            return [
                'total_customers' => $totalCustomers,
                'new_customers' => $newCustomers,
                'active_customers' => $activeCustomers
            ];
        } catch (PDOException $e) {
            error_log("Error getting customer statistics: " . $e->getMessage());
            return [
                'total_customers' => 0,
                'new_customers' => 0,
                'active_customers' => 0
            ];
        }
    }
    
    // เพิ่มเมธอดสำหรับตรวจสอบและสร้างตารางที่จำเป็น
    private function createCustomerTablesIfNotExist() {
        try {
            // ตรวจสอบว่าตาราง customers มีอยู่หรือไม่
            $sql = "SHOW TABLES LIKE 'customers'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                // สร้างตาราง customers ด้วย ULID เป็น primary key
                $sql = "CREATE TABLE IF NOT EXISTS customers (
                    id VARCHAR(26) PRIMARY KEY,
                    code VARCHAR(20) UNIQUE,
                    name VARCHAR(100) NOT NULL,
                    phone VARCHAR(20),
                    email VARCHAR(100),
                    address TEXT,
                    tax_id VARCHAR(20),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    created_by VARCHAR(26),
                    updated_by VARCHAR(26)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $this->db->exec($sql);
                error_log("Created customers table");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error creating customer tables: " . $e->getMessage());
            return false;
        }
    }
}
?>

