<?php
// ไม่ต้อง require Database.php เพราะมี Config.php อยู่แล้ว
require_once 'debug.php';

class Invoice {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
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
        $sql = "SELECT i.*, c.name as customer_name,
                (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id) as total_shipments,
                (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id AND payment_status = 'paid') as paid_shipments
                FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        // Add month filter
        if (isset($filters['month']) && $filters['month'] !== 'all') {
            $sql .= " AND MONTH(i.invoice_date) = :month";
            $params[':month'] = $filters['month'];
        }
        
        // Add year filter
        if (isset($filters['year']) && $filters['year'] !== 'all') {
            $sql .= " AND YEAR(i.invoice_date) = :year";
            $params[':year'] = $filters['year'];
        }
        
        // Add customer search
        if (isset($filters['customer']) && !empty($filters['customer'])) {
            $sql .= " AND (c.name LIKE :customer OR c.code LIKE :customer)";
            $params[':customer'] = '%' . $filters['customer'] . '%';
        }
        
        // Add status filter
        if (isset($filters['status']) && !empty($filters['status'])) {
            $sql .= " AND i.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            if (in_array($key, [':limit', ':offset'])) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalFilteredInvoices($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        // Add month filter
        if (isset($filters['month']) && $filters['month'] !== 'all') {
            $sql .= " AND MONTH(i.invoice_date) = :month";
            $params[':month'] = $filters['month'];
        }
        
        // Add year filter
        if (isset($filters['year']) && $filters['year'] !== 'all') {
            $sql .= " AND YEAR(i.invoice_date) = :year";
            $params[':year'] = $filters['year'];
        }
        
        // Add customer search
        if (isset($filters['customer']) && !empty($filters['customer'])) {
            $sql .= " AND (c.name LIKE :customer OR c.code LIKE :customer)";
            $params[':customer'] = '%' . $filters['customer'] . '%';
        }
        
        // Add status filter
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
    }
    
    public function getAvailableYears() {
        $sql = "SELECT DISTINCT YEAR(invoice_date) as year FROM invoices ORDER BY year DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getTotalInvoices() {
        $sql = "SELECT COUNT(*) as total FROM invoices";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    public function getInvoiceById($id) {
        $sql = "SELECT i.*, c.name as customer_name,
                (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id) as total_shipments,
                (SELECT COUNT(*) FROM invoice_shipments WHERE invoice_id = i.id AND payment_status = 'paid') as paid_shipments
                FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id 
                WHERE i.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createInvoice($data) {
        $sql = "INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, subtotal, tax_rate, tax_amount, total_amount, status, notes, created_at, updated_at) 
                VALUES (:invoice_number, :customer_id, :invoice_date, :due_date, :subtotal, :tax_rate, :tax_amount, :total_amount, :status, :notes, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_number', $data['invoice_number']);
        $stmt->bindParam(':customer_id', $data['customer_id'], PDO::PARAM_INT);
        $stmt->bindParam(':invoice_date', $data['invoice_date']);
        $stmt->bindParam(':due_date', $data['due_date']);
        $stmt->bindParam(':subtotal', $data['subtotal']);
        $stmt->bindParam(':tax_rate', $data['tax_rate']);
        $stmt->bindParam(':tax_amount', $data['tax_amount']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':notes', $data['notes']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    public function updateInvoice($id, $data) {
        // ถ้า $id เป็น array และมี key 'id' ให้ใช้ค่านั้น (รองรับการส่งข้อมูลแบบเก่า)
        if (is_array($id) && isset($id['id'])) {
            $invoiceId = $id['id'];
            $updateData = $id;
        } else {
            $invoiceId = $id;
            $updateData = $data;
        }
        
        $fields = [];
        $params = [];
        
        foreach ($updateData as $key => $value) {
            if ($key !== 'id') { // ไม่รวม id ในการอัพเดท
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        $fields[] = "updated_at = NOW()";
        
        $sql = "UPDATE invoices SET " . implode(', ', $fields) . " WHERE id = :id";
        $params[':id'] = $invoiceId;
        
        $stmt = $this->db->prepare($sql);
        
        // ทำการ execute คำสั่ง SQL
        $result = $stmt->execute($params);
        
        // ถ้ามีการส่ง shipment_ids มาด้วย (พารามิเตอร์ที่ 2)
        if (is_array($data) && !empty($data) && isset($data[0])) { // เพิ่มเงื่อนไขตรวจสอบว่า $data เป็น array ของ shipment_ids
            // ลบความสัมพันธ์เดิมทั้งหมด
            $this->unlinkAllShipmentsFromInvoice($invoiceId);
            
            // เพิ่มความสัมพันธ์ใหม่
            foreach ($data as $shipmentId) {
                $this->linkShipmentToInvoice($invoiceId, $shipmentId);
            }
        }
        
        return $result;
    }
    
    public function deleteInvoice($id) {
        // First delete related invoice_shipments
        $sql = "DELETE FROM invoice_shipments WHERE invoice_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Then delete the invoice
        $sql = "DELETE FROM invoices WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function linkShipmentToInvoice($invoiceId, $shipmentId) {
        $sql = "INSERT INTO invoice_shipments (invoice_id, shipment_id, payment_status, created_at) 
                VALUES (:invoice_id, :shipment_id, 'invoiced', NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoiceId, PDO::PARAM_INT);
        $stmt->bindParam(':shipment_id', $shipmentId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    public function unlinkShipmentFromInvoice($invoiceId, $shipmentId) {
        $sql = "DELETE FROM invoice_shipments 
                WHERE invoice_id = :invoice_id AND shipment_id = :shipment_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoiceId, PDO::PARAM_INT);
        $stmt->bindParam(':shipment_id', $shipmentId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // เพิ่มเมธอดใหม่เพื่อลบความสัมพันธ์ทั้งหมดระหว่างใบแจ้งหนี้กับสินค้า
    public function unlinkAllShipmentsFromInvoice($invoiceId) {
        $sql = "DELETE FROM invoice_shipments WHERE invoice_id = :invoice_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoiceId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getShipmentsByInvoiceId($invoiceId) {
        try {
            // ใช้ SQL query ที่ผู้ใช้ยืนยันว่าทำงานได้
            $sql = "SELECT s.*, is_rel.payment_status 
                    FROM shipments s 
                    JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id 
                    WHERE is_rel.invoice_id = :invoice_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoiceId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Debug: แสดงข้อมูลที่ดึงมาจาก database
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ////debug_log("SQL Query: " . $sql);
            ////debug_log("Invoice ID: " . $invoiceId);
            ////debug_log("Shipments count: " . count($shipments));
            ////debug_log("Shipments data: ", $shipments);
            
            return $shipments;
        } catch (Exception $e) {
            ////debug_log("Error in getShipmentsByInvoiceId: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateShipmentPaymentStatus($invoiceId, $status) {
        $sql = "UPDATE invoice_shipments 
                SET payment_status = :status 
                WHERE invoice_id = :invoice_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':invoice_id', $invoiceId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function getInvoiceStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_invoices,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_invoices,
                    SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_invoices,
                    SUM(CASE WHEN status = 'unpaid' AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue_invoices,
                    SUM(total_amount) as total_amount,
                    SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN status = 'unpaid' THEN total_amount ELSE 0 END) as unpaid_amount,
                    SUM(CASE WHEN status = 'unpaid' AND due_date < CURDATE() THEN total_amount ELSE 0 END) as overdue_amount
                FROM invoices";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return [
                'total_invoices' => 0,
                'paid_invoices' => 0,
                'unpaid_invoices' => 0,
                'overdue_invoices' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'unpaid_amount' => 0,
                'overdue_amount' => 0
            ];
        }
        
        return $result;
    }
    
    public function getNextInvoiceNumber() {
        // Get current year and month
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $sql = "SELECT MAX(invoice_number) as last_number FROM invoices 
                WHERE invoice_number LIKE :prefix";
        
        $prefix = "INV-{$year}{$month}-";
        $prefixParam = $prefix . '%';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':prefix', $prefixParam);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $lastNumber = $result['last_number'] ?? null;
        
        if ($lastNumber) {
            // Extract the sequence number
            $parts = explode('-', $lastNumber);
            $sequence = (int)end($parts);
            $sequence++;
        } else {
            $sequence = 1;
        }
        
        // Format the sequence number with leading zeros
        $formattedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        // Return the new invoice number
        return "{$prefix}{$formattedSequence}";
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
    
    // เพิ่มฟังก์ชันใหม่เพื่อดึงใบแจ้งหนี้ตาม customer_id
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
            
            // Debug: แสดงข้อมูลที่ดึงมาจาก database
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ////debug_log("Customer ID: " . $customerId);
            ////debug_log("Invoices count: " . count($invoices));
            
            return $invoices;
        } catch (Exception $e) {
            ////debug_log("Error in getInvoicesByCustomerId: " . $e->getMessage());
            return [];
        }
    }

    // เพิ่มเมธอดสำหรับนับจำนวนใบแจ้งหนี้ในช่วงเวลาที่กำหนด
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




    // เพิ่มเมธอดสำหรับดึงข้อมูลใบแจ้งหนี้ในช่วงเวลาที่กำหนดพร้อม pagination
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
}
?>

