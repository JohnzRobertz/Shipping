<?php
// ไม่ต้อง require Database.php เพราะมี Config.php อยู่แล้ว
require_once 'debug.php';
require_once 'lib/UlidGenerator.php';

class Invoice {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        
        // ตรวจสอบและสร้างตารางที่จำเป็น
        $this->createInvoiceTablesIfNotExist();
        
        // ตรวจสอบและเพิ่มคอลัมน์ payment_status ถ้ายังไม่มี
        $this->addPaymentStatusColumnIfNotExists();
    }
    
    // เพิ่มเมธอดใหม่เพื่อตรวจสอบและเพิ่มคอลัมน์ payment_status
    private function addPaymentStatusColumnIfNotExists() {
        try {
            // ตรวจสอบว่ามีคอลัมน์ payment_status ในตาราง invoice_shipments หรือไม่
            $sql = "SHOW COLUMNS FROM invoice_shipments LIKE 'payment_status'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                // ถ้าไม่มีคอลัมน์ payment_status ให้เพิ่มเข้าไป
                $sql = "ALTER TABLE invoice_shipments ADD COLUMN payment_status ENUM('paid', 'unpaid') DEFAULT 'unpaid'";
                $this->db->exec($sql);
                
                // อัปเดตข้อมูลเดิม: ถ้า invoice มีสถานะ 'paid' ให้กำหนด payment_status เป็น 'paid'
                $sql = "UPDATE invoice_shipments is_rel
                        JOIN invoices i ON is_rel.invoice_id = i.id
                        SET is_rel.payment_status = 'paid'
                        WHERE i.status = 'paid'";
                $this->db->exec($sql);
                
                error_log("Added payment_status column to invoice_shipments table");
            }
        } catch (PDOException $e) {
            error_log("Error adding payment_status column: " . $e->getMessage());
        }
    }
    
    public function getAllInvoices($limit = 10, $offset = 0) {
        $sql = "SELECT i.*, c.name as customer_name,
                (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id) as total_shipments,
                (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id AND payment_status = 'paid') as paid_shipments
                FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id 
                ORDER BY i.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFilteredInvoices($filters = [], $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT i.*, c.name as customer_name, 
                    (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id) as total_shipments 
                    FROM invoices i 
                    LEFT JOIN customers c ON i.customer_id = c.id 
                    WHERE 1=1";
            $params = [];

            // Add filters
            if (isset($filters['month']) && $filters['month'] !== 'all') {
                $sql .= " AND MONTH(i.invoice_date) = :month";
                $params[':month'] = $filters['month'];
            }
            
            if (isset($filters['year']) && $filters['year'] !== 'all') {
                $sql .= " AND YEAR(i.invoice_date) = :year";
                $params[':year'] = $filters['year'];
            }
            
            if (isset($filters['customer']) && !empty($filters['customer'])) {
                $sql .= " AND (c.name LIKE :customer OR c.code LIKE :customer)";
                $params[':customer'] = '%' . $filters['customer'] . '%';
            }
            
            if (isset($filters['status']) && !empty($filters['status'])) {
                $sql .= " AND i.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            // Add order by
            $sql .= " ORDER BY i.invoice_date DESC, i.id DESC";
            
            // Add limit
            if ($limit > 0) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $limit;
                $params[':offset'] = $offset;
            }
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                if ($key == ':limit' || $key == ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting filtered invoices: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalFilteredInvoices($filters = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM invoices i 
                    LEFT JOIN customers c ON i.customer_id = c.id 
                    WHERE 1=1";
            $params = [];

            // Add filters
            if (isset($filters['month']) && $filters['month'] !== 'all') {
                $sql .= " AND MONTH(i.invoice_date) = :month";
                $params[':month'] = $filters['month'];
            }
            
            if (isset($filters['year']) && $filters['year'] !== 'all') {
                $sql .= " AND YEAR(i.invoice_date) = :year";
                $params[':year'] = $filters['year'];
            }
            
            if (isset($filters['customer']) && !empty($filters['customer'])) {
                $sql .= " AND (c.name LIKE :customer OR c.code LIKE :customer)";
                $params[':customer'] = '%' . $filters['customer'] . '%';
            }
            
            if (isset($filters['status']) && !empty($filters['status'])) {
                $sql .= " AND i.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting total filtered invoices: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getAvailableYears() {
        try {
            $sql = "SELECT DISTINCT YEAR(invoice_date) as year FROM invoices ORDER BY year DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // If no years found, add current year
            if (empty($years)) {
                $years = [date('Y')];
            }
            
            return $years;
        } catch (PDOException $e) {
            error_log("Error getting available years: " . $e->getMessage());
            return [date('Y')];
        }
    }
    
    public function getTotalInvoices() {
        $sql = "SELECT COUNT(*) as total FROM invoices";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    public function getInvoiceById($id) {
        try {
            $sql = "SELECT i.*, c.name as customer_name, c.code as customer_code 
                    FROM invoices i 
                    LEFT JOIN customers c ON i.customer_id = c.id 
                    WHERE i.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting invoice by ID: " . $e->getMessage());
            return false;
        }
    }
    
    public function createInvoice($data) {
        try {
            // สร้าง ULID สำหรับ invoice ID
            $id = UlidGenerator::generate();
            
            $sql = "INSERT INTO invoices (id, invoice_number, customer_id, invoice_date, due_date, subtotal, tax_rate, tax_amount, total_amount, status, notes) 
                    VALUES (:id, :invoice_number, :customer_id, :invoice_date, :due_date, :subtotal, :tax_rate, :tax_amount, :total_amount, :status, :notes)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':invoice_number', $data['invoice_number']);
            $stmt->bindParam(':customer_id', $data['customer_id']);
            $stmt->bindParam(':invoice_date', $data['invoice_date']);
            $stmt->bindParam(':due_date', $data['due_date']);
            $stmt->bindParam(':subtotal', $data['subtotal']);
            $stmt->bindParam(':tax_rate', $data['tax_rate']);
            $stmt->bindParam(':tax_amount', $data['tax_amount']);
            $stmt->bindParam(':total_amount', $data['total_amount']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':notes', $data['notes']);
            
            if ($stmt->execute()) {
                return $id; // คืนค่า ULID แทน lastInsertId()
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creating invoice: " . $e->getMessage());
            return false;
        }
    }
    
    // แก้ไขฟังก์ชัน updateInvoice() เพื่อตรวจสอบ customer_id ก่อนอัพเดท
    public function updateInvoice($data, $shipmentIds = null) {
        try {
            // ตรวจสอบว่ามี id หรือไม่
            if (!isset($data['id'])) {
                error_log("updateInvoice: No ID provided");
                return false;
            }
            
            $id = $data['id'];
            
            // Debug: แสดงข้อมูลที่จะอัพเดท
            error_log("updateInvoice: Updating invoice ID: " . $id);
            error_log("updateInvoice: Data: " . print_r($data, true));
            
            // ตรวจสอบว่า customer_id มีอยู่จริงหรือไม่
            if (isset($data['customer_id'])) {
                $checkSql = "SELECT id FROM customers WHERE id = :customer_id";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->bindParam(':customer_id', $data['customer_id']);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() === 0) {
                    error_log("updateInvoice: Invalid customer_id: " . $data['customer_id']);
                    return false;
                }
            }
            
            // ลบ id ออกจาก data เพื่อไม่ให้อัพเดท
            unset($data['id']);
            
            // สร้าง SQL สำหรับอัพเดทข้อมูล
            $sql = "UPDATE invoices SET ";
            $params = [];
            
            foreach ($data as $key => $value) {
                $sql .= "$key = :$key, ";
                $params[":$key"] = $value;
            }
            
            // ตัด comma ตัวสุดท้ายออก
            $sql = rtrim($sql, ", ");
            
            // เพิ่มเงื่อนไข WHERE
            $sql .= " WHERE id = :id";
            $params[':id'] = $id;
            
            // Debug: แสดง SQL และ parameters
            error_log("updateInvoice: SQL: " . $sql);
            error_log("updateInvoice: Params: " . print_r($params, true));
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $result = $stmt->execute();
            
            // Debug: แสดงผลลัพธ์การอัพเดท
            error_log("updateInvoice: Update result: " . ($result ? "success" : "failed"));
            
            // ถ้ามีการอัพเดท shipments
            if ($result && $shipmentIds !== null) {
                // Debug: แสดงข้อมูล shipmentIds
                error_log("updateInvoice: Updating shipments for invoice ID: " . $id);
                error_log("updateInvoice: Shipment IDs: " . print_r($shipmentIds, true));
                
                // ลบ shipments เดิมออก
                $removeResult = $this->removeAllShipmentsFromInvoice($id);
                error_log("updateInvoice: Remove all shipments result: " . ($removeResult ? "success" : "failed"));
                
                // เพิ่ม shipments ใหม่
                foreach ($shipmentIds as $shipmentId) {
                    $linkResult = $this->linkShipmentToInvoice($id, $shipmentId);
                    error_log("updateInvoice: Link shipment " . $shipmentId . " result: " . ($linkResult ? "success" : "failed"));
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating invoice: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteInvoice($id) {
        try {
            // ลบ shipments ที่เชื่อมโยงกับ invoice นี้
            $this->removeAllShipmentsFromInvoice($id);
            
            // ลบ invoice
            $sql = "DELETE FROM invoices WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting invoice: " . $e->getMessage());
            return false;
        }
    }
    
    public function linkShipmentToInvoice($invoiceId, $shipmentId) {
        try {
            // ตรวจสอบว่ามีคอลัมน์ payment_status ในตาราง invoice_shipments หรือไม่
            $hasPaymentStatus = $this->hasPaymentStatusColumn();
            
            if ($hasPaymentStatus) {
                // ถ้ามีคอลัมน์ payment_status
                $sql = "INSERT INTO invoice_shipments (invoice_id, shipment_id, payment_status) 
                        VALUES (:invoice_id, :shipment_id, 'unpaid')
                        ON DUPLICATE KEY UPDATE payment_status = 'unpaid'";
            } else {
                // ถ้าไม่มีคอลัมน์ payment_status
                $sql = "INSERT INTO invoice_shipments (invoice_id, shipment_id) 
                        VALUES (:invoice_id, :shipment_id)
                        ON DUPLICATE KEY UPDATE invoice_id = VALUES(invoice_id)";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoiceId);
            $stmt->bindParam(':shipment_id', $shipmentId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Link shipment to invoice error: " . $e->getMessage());
            return false;
        }
    }
    
    public function unlinkShipmentFromInvoice($invoiceId, $shipmentId) {
        $sql = "DELETE FROM invoice_shipments 
                WHERE invoice_id = :invoice_id AND shipment_id = :shipment_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->bindParam(':shipment_id', $shipmentId);
        
        return $stmt->execute();
    }

    public function unlinkAllShipmentsFromInvoice($invoiceId) {
        $sql = "DELETE FROM invoice_shipments WHERE invoice_id = :invoice_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoiceId);
        return $stmt->execute();
    }
    
    public function getShipmentsByInvoiceId($invoiceId) {
        try {
            // ตรวจสอบว่ามีคอลัมน์ payment_status ในตาราง invoice_shipments หรือไม่
            $hasPaymentStatus = $this->hasPaymentStatusColumn();
            
            if ($hasPaymentStatus) {
                $sql = "SELECT s.*, is_rel.payment_status 
                    FROM shipments s 
                    JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id 
                    WHERE is_rel.invoice_id = :invoice_id";
            } else {
                $sql = "SELECT s.* 
                    FROM shipments s 
                    JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id 
                    WHERE is_rel.invoice_id = :invoice_id";
            }
        
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoiceId);
            $stmt->execute();
        
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting shipments by invoice ID: " . $e->getMessage());
            return [];
        }
    }

    public function getInvoiceShipments($invoiceId) {
        try {
            $sql = "SELECT s.*, is.invoice_id 
                    FROM shipments s 
                    JOIN invoice_shipments is ON s.id = is.shipment_id 
                    WHERE is.invoice_id = :invoice_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoiceId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting invoice shipments: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateShipmentPaymentStatus($invoiceId, $status) {
        try {
            // ตรวจสอบว่ามีคอลัมน์ payment_status ในตาราง invoice_shipments หรือไม่
            $hasPaymentStatus = $this->hasPaymentStatusColumn();
            
            if (!$hasPaymentStatus) {
                // ถ้าไม่มีคอลัมน์ payment_status ให้เพิ่มเข้าไปก่อน
                $this->addPaymentStatusColumnIfNotExists();
            }
            
            $sql = "UPDATE invoice_shipments SET payment_status = :status WHERE invoice_id = :invoice_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':invoice_id', $invoiceId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating shipment payment status: " . $e->getMessage());
            return false;
        }
    }
    
    public function getInvoiceStatistics() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_invoices,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_invoices,
                    SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_invoices,
                    SUM(total_amount) as total_amount,
                    SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN status = 'unpaid' THEN total_amount ELSE 0 END) as unpaid_amount
                    FROM invoices";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting invoice statistics: " . $e->getMessage());
            return [
                'total_invoices' => 0,
                'paid_invoices' => 0,
                'unpaid_invoices' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'unpaid_amount' => 0
            ];
        }
    }
    
    public function getNextInvoiceNumber() {
        try {
            // Get current year and month
            $year = date('Y');
            $month = date('m');
            
            // Get the latest invoice number for this month
            $sql = "SELECT invoice_number FROM invoices 
                    WHERE YEAR(invoice_date) = :year AND MONTH(invoice_date) = :month 
                    ORDER BY created_at DESC LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':month', $month);
            $stmt->execute();
            
            $lastInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lastInvoice) {
                // Extract the sequence number from the last invoice number
                $lastNumber = $lastInvoice['invoice_number'];
                $sequence = (int)substr($lastNumber, -4);
                $sequence++;
            } else {
                // No invoices for this month yet, start with 1
                $sequence = 1;
            }
            
            // Format: INV-YYYYMM-XXXX (e.g., INV-202501-0001)
            $invoiceNumber = 'INV-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            
            return $invoiceNumber;
        } catch (PDOException $e) {
            error_log("Error generating invoice number: " . $e->getMessage());
            // Fallback to a timestamp-based number
            return 'INV-' . date('YmdHis');
        }
    }
    
    public function getUnpaidShipments() {
        $sql = "SELECT s.*, c.name as customer_name 
                FROM shipments s 
                JOIN customers c ON s.customer_id = c.id 
                WHERE s.status = 'delivered' 
                AND s.id NOT IN (SELECT shipment_id FROM invoice_shipments) 
                ORDER BY s.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getInvoicesByCustomerId($customerId) {
        try {
            $sql = "SELECT i.*, 
                    (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id) as total_shipments,
                    (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id AND payment_status = 'paid') as paid_shipments
                    FROM invoices i 
                    WHERE i.customer_id = :customer_id 
                    ORDER BY i.created_at DESC 
                    LIMIT 10"; // แสดงแค่ 10 รายการล่าสุด
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getInvoicesByCustomerId: " . $e->getMessage());
            return [];
        }
    }

    public function countCustomerInvoicesInPeriod($customer_id, $start_date) {
        try {
            $sql = "SELECT COUNT(*) as total FROM invoices 
                    WHERE customer_id = :customer_id 
                    AND invoice_date >= :start_date";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error counting customer invoices in period: " . $e->getMessage());
            return 0;
        }
    }

    public function getCustomerInvoicesInPeriod($customer_id, $start_date, $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT * FROM invoices 
                    WHERE customer_id = :customer_id 
                    AND invoice_date >= :start_date 
                    ORDER BY invoice_date DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting customer invoices in period: " . $e->getMessage());
            return [];
        }
    }

    public function removeAllShipmentsFromInvoice($invoiceId) {
        try {
            $sql = "DELETE FROM invoice_shipments WHERE invoice_id = :invoice_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoiceId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error removing shipments from invoice: " . $e->getMessage());
            return false;
        }
    }
    
    // เพิ่มเมธอดใหม่เพื่อตรวจสอบว่ามีคอลัมน์ payment_status ในตาราง invoice_shipments หรือไม่
    private function hasPaymentStatusColumn() {
        try {
            $sql = "SHOW COLUMNS FROM invoice_shipments LIKE 'payment_status'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking payment_status column: " . $e->getMessage());
            return false;
        }
    }

    // แก้ไขฟังก์ชัน createInvoiceTablesIfNotExist() เพื่อตรวจสอบและแก้ไข foreign key constraint
    private function createInvoiceTablesIfNotExist() {
        try {
            // ตรวจสอบว่าตาราง invoices มีอยู่หรือไม่
            $sql = "SHOW TABLES LIKE 'invoices'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                // สร้างตาราง invoices ด้วย ULID เป็น primary key
                $sql = "CREATE TABLE IF NOT EXISTS invoices (
                    id VARCHAR(26) PRIMARY KEY,
                    invoice_number VARCHAR(20) NOT NULL,
                    customer_id INT NOT NULL,
                    invoice_date DATE NOT NULL,
                    due_date DATE NOT NULL,
                    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                    tax_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
                    tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                    status ENUM('paid', 'unpaid', 'cancelled') NOT NULL DEFAULT 'unpaid',
                    payment_date DATE NULL,
                    payment_method VARCHAR(50) NULL,
                    payment_reference VARCHAR(100) NULL,
                    notes TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT fk_invoice_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT ON UPDATE RESTRICT
                )";
            
                $this->db->exec($sql);
            } else {
                // ตรวจสอบว่า foreign key constraint ถูกต้องหรือไม่
                $sql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_NAME = 'invoices' 
                    AND CONSTRAINT_TYPE = 'FOREIGN KEY' 
                    AND CONSTRAINT_SCHEMA = DATABASE()";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
                // ถ้ามี constraint ชื่อ fk_invoice_customer ให้ลบออกและสร้างใหม่
                if (in_array('fk_invoice_customer', $constraints)) {
                    $this->db->exec("ALTER TABLE invoices DROP FOREIGN KEY fk_invoice_customer");
                }
            
                // สร้าง constraint ใหม่
                $this->db->exec("ALTER TABLE invoices ADD CONSTRAINT fk_invoice_customer 
                            FOREIGN KEY (customer_id) REFERENCES customers(id) 
                            ON DELETE RESTRICT ON UPDATE RESTRICT");
            }
        
            // ตรวจสอบว่าตาราง invoice_shipments มีอยู่หรือไม่
            $sql = "SHOW TABLES LIKE 'invoice_shipments'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        
            if ($stmt->rowCount() === 0) {
                // สร้างตาราง invoice_shipments
                $sql = "CREATE TABLE IF NOT EXISTS invoice_shipments (
                    invoice_id VARCHAR(26) NOT NULL,
                    shipment_id VARCHAR(255) NOT NULL,
                    payment_status ENUM('paid', 'unpaid') NOT NULL DEFAULT 'unpaid',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (invoice_id, shipment_id),
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
                    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
                )";
            
                $this->db->exec($sql);
            }
        
            // ตรวจสอบว่าตาราง invoice_additional_charges มีอยู่หรือไม่
            $sql = "SHOW TABLES LIKE 'invoice_additional_charges'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        
            if ($stmt->rowCount() === 0) {
                // สร้างตาราง invoice_additional_charges
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
            }
        
            return true;
        } catch (PDOException $e) {
            error_log("Error creating invoice tables: " . $e->getMessage());
            return false;
        }
    }
}
?>

