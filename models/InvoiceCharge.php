<?php
require_once 'debug.php';
require_once 'lib/UlidGenerator.php';

class InvoiceCharge {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        
        // ตรวจสอบและสร้างตารางที่จำเป็น
        $this->createInvoiceChargeTableIfNotExist();
    }
    
    public function addCharge($data) {
        try {
            // Debug: แสดงข้อมูลที่จะเพิ่ม
            error_log("addCharge: Data: " . print_r($data, true));
            
            // สร้าง ULID สำหรับรายการค่าใช้จ่ายเพิ่มเติม
            $id = isset($data['id']) && !empty($data['id']) ? $data['id'] : UlidGenerator::generate();
            
            // ตรวจสอบว่ามีรายการนี้อยู่แล้วหรือไม่
            $existingCharge = null;
            if (isset($data['id']) && !empty($data['id'])) {
                $existingCharge = $this->getChargeById($data['id']);
            }
            
            if ($existingCharge) {
                // อัพเดทรายการที่มีอยู่แล้ว
                return $this->updateCharge($id, $data);
            } else {
                // เพิ่มรายการใหม่
                $sql = "INSERT INTO invoice_additional_charges (id, invoice_id, charge_type, description, amount, is_percentage) 
                        VALUES (:id, :invoice_id, :charge_type, :description, :amount, :is_percentage)";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':invoice_id', $data['invoice_id']);
                $stmt->bindParam(':charge_type', $data['charge_type']);
                $stmt->bindParam(':description', $data['description']);
                $stmt->bindParam(':amount', $data['amount']);
                $stmt->bindParam(':is_percentage', $data['is_percentage']);
                
                $result = $stmt->execute();
                
                // Debug: แสดงผลลัพธ์การเพิ่มข้อมูล
                error_log("addCharge: Insert result: " . ($result ? "success" : "failed"));
                
                if ($result) {
                    return $id; // คืนค่า ULID ที่สร้างขึ้น
                }
                
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error adding charge: " . $e->getMessage());
            return false;
        }
    }
    
    public function getChargesByInvoiceId($invoiceId) {
        try {
            $sql = "SELECT * FROM invoice_additional_charges WHERE invoice_id = :invoice_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoiceId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting charges by invoice ID: " . $e->getMessage());
            return [];
        }
    }
    
    public function getChargeById($id) {
        try {
            $sql = "SELECT * FROM invoice_additional_charges WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting charge by ID: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateCharge($id, $data) {
        try {
            // Debug: แสดงข้อมูลที่จะอัพเดท
            error_log("updateCharge: ID: " . $id);
            error_log("updateCharge: Data: " . print_r($data, true));
            
            $sql = "UPDATE invoice_additional_charges 
                    SET charge_type = :charge_type, description = :description, amount = :amount, is_percentage = :is_percentage 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':charge_type', $data['charge_type']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':amount', $data['amount']);
            $stmt->bindParam(':is_percentage', $data['is_percentage']);
            
            $result = $stmt->execute();
            
            // Debug: แสดงผลลัพธ์การอัพเดท
            error_log("updateCharge: Update result: " . ($result ? "success" : "failed"));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating charge: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteCharge($id) {
        try {
            // Debug: แสดงข้อมูลที่จะลบ
            error_log("deleteCharge: ID: " . $id);
            
            $sql = "DELETE FROM invoice_additional_charges WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            $result = $stmt->execute();
            
            // Debug: แสดงผลลัพธ์การลบ
            error_log("deleteCharge: Delete result: " . ($result ? "success" : "failed"));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting charge: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteAllChargesByInvoiceId($invoiceId) {
        try {
            // Debug: แสดงข้อมูลที่จะลบ
            error_log("deleteAllChargesByInvoiceId: Invoice ID: " . $invoiceId);
            
            $sql = "DELETE FROM invoice_additional_charges WHERE invoice_id = :invoice_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoiceId);
            
            $result = $stmt->execute();
            
            // Debug: แสดงผลลัพธ์การลบ
            error_log("deleteAllChargesByInvoiceId: Delete result: " . ($result ? "success" : "failed"));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting all charges by invoice ID: " . $e->getMessage());
            return false;
        }
    }
    
    // เพิ่มเมธอดสำหรับตรวจสอบและสร้างตารางที่จำเป็น
    private function createInvoiceChargeTableIfNotExist() {
        try {
            // ตรวจสอบว่าตาราง invoice_additional_charges มีอยู่หรือไม่
            $sql = "SHOW TABLES LIKE 'invoice_additional_charges'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                // สร้างตาราง invoice_additional_charges ด้วย ULID เป็น primary key
                $sql = "CREATE TABLE IF NOT EXISTS invoice_additional_charges (
                    id VARCHAR(26) PRIMARY KEY,
                    invoice_id VARCHAR(26) NOT NULL,
                    charge_type ENUM('fee', 'discount', 'tax') NOT NULL DEFAULT 'fee',
                    description VARCHAR(255) NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    is_percentage TINYINT(1) NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
                )";
                
                $this->db->exec($sql);
                
                // สร้าง index สำหรับการค้นหาที่รวดเร็ว
                $this->db->exec("CREATE INDEX idx_invoice_additional_charges_invoice_id ON invoice_additional_charges(invoice_id)");
            } else {
                // ตรวจสอบว่าคอลัมน์ id เป็น VARCHAR(26) หรือไม่
                $sql = "DESCRIBE invoice_additional_charges id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $idColumn = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // ถ้า id ไม่ใช่ VARCHAR(26) ให้ทำการย้ายข้อมูลไปยังตารางใหม่
                if ($idColumn && strpos($idColumn['Type'], 'char') === false) {
                    // ตารางมีอยู่แล้วแต่ id ไม่ใช่ VARCHAR(26) (ULID)
                    // ให้แจ้งเตือนว่าต้องทำการย้ายข้อมูล
                    error_log("Table invoice_additional_charges exists but id is not VARCHAR(26). Please run the migration script.");
                }
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error creating invoice_additional_charges table: " . $e->getMessage());
            return false;
        }
    }
}
?>

