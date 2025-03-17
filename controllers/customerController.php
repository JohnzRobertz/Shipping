<?php
require_once 'models/Customer.php';
require_once 'models/Shipment.php';
require_once 'models/Invoice.php';
require_once 'config/Database.php';

class CustomerController {
    private $customerModel;
    private $shipmentModel;
    private $invoiceModel;
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        $this->customerModel = new Customer();
        $this->shipmentModel = new Shipment();
        $this->invoiceModel = new Invoice();
        
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?page=login');
            exit();
        }
    }
    
    public function index() {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10; // จำนวนรายการต่อหน้า
        $offset = ($page - 1) * $limit;
        
        // สร้างคำสั่ง SQL พร้อมเงื่อนไขการค้นหา
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            // ใช้เฉพาะคอลัมน์ที่มีอยู่จริงในฐานข้อมูล
            $whereClause = "WHERE name LIKE :search OR code LIKE :search OR email LIKE :search OR phone LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        // คำสั่ง SQL สำหรับนับจำนวนรายการทั้งหมด
        $countSql = "SELECT COUNT(*) as total FROM customers $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalCustomers = $result['total'] ?? 0;
        
        // คำสั่ง SQL สำหรับดึงข้อมูลลูกค้า
        $sql = "SELECT * FROM customers $whereClause ORDER BY name ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        
        // ต้องผูกพารามิเตอร์ limit และ offset แยกต่างหาก
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalPages = ceil($totalCustomers / $limit);
        
        // ส่งข้อมูลไปยัง view
        include 'views/customer/index.php';
    }
    
    public function create() {
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $code = $_POST['code'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $taxId = $_POST['tax_id'] ?? '';
            
            // Validate input
            $errors = [];
            if (empty($name)) {
                $errors[] = 'Customer name is required';
            }
            
            if (empty($errors)) {
                // Create customer
                $customerData = [
                    'name' => $name,
                    'code' => $code,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'tax_id' => $taxId
                ];
                
                $result = $this->customerModel->createCustomer($customerData);
                
                if ($result) {
                    $_SESSION['success'] = 'Customer created successfully';
                    header('Location: index.php?page=customer');
                    exit();
                } else {
                    $_SESSION['error'] = 'Failed to create customer';
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }
        
        // Load view
        include 'views/customer/create.php';
    }
    
    // แก้ไขฟังก์ชัน view เพื่อรองรับ pagination และแสดงใบแจ้งหนี้ 3 เดือนล่าสุด
    public function view() {
        // ดึงค่า ID จาก GET parameter
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // ตรวจสอบว่ามี ID หรือไม่
        if (!$id) {
            $_SESSION['error'] = 'Customer not found';
            header('Location: index.php?page=customer');
            exit();
        }
        
        // ดึงข้อมูลลูกค้า
        $customer = $this->customerModel->getCustomerById($id);
        
        if (!$customer) {
            $_SESSION['error'] = 'Customer not found';
            header('Location: index.php?page=customer');
            exit();
        }
        
        // รับค่า page สำหรับ pagination ของใบแจ้งหนี้
        $invoicePage = isset($_GET['invoice_page']) ? (int)$_GET['invoice_page'] : 1; // เปลี่ยนชื่อตัวแปร
        $per_page = 5; // จำนวนรายการต่อหน้า
        
        // คำนวณ offset สำหรับ SQL
        $offset = ($invoicePage - 1) * $per_page;
        
        // คำนวณวันที่ 3 เดือนย้อนหลัง
        $three_months_ago = date('Y-m-d', strtotime('-3 months'));
        
        // ดึงข้อมูลใบแจ้งหนี้ 3 เดือนล่าสุด
        $recent_invoices_sql = "SELECT i.*, c.name as customer_name 
                               FROM invoices i 
                               LEFT JOIN customers c ON i.customer_id = c.id 
                               WHERE i.customer_id = :customer_id 
                               AND i.invoice_date >= :three_months_ago
                               ORDER BY i.invoice_date DESC
                               LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($recent_invoices_sql);
        $stmt->bindParam(':customer_id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':three_months_ago', $three_months_ago);
        $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // นับจำนวนใบแจ้งหนี้ทั้งหมดในช่วง 3 เดือนล่าสุด
        $count_sql = "SELECT COUNT(*) as total 
                     FROM invoices 
                     WHERE customer_id = :customer_id 
                     AND invoice_date >= :three_months_ago";
        
        $stmt = $this->db->prepare($count_sql);
        $stmt->bindParam(':customer_id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':three_months_ago', $three_months_ago);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_invoices = $result['total'];
        
        // คำนวณจำนวนหน้าทั้งหมด
        $totalInvoicePages = ceil($total_invoices / $per_page); // เปลี่ยนชื่อตัวแปร
        
        // ปรับค่า invoicePage ให้อยู่ในช่วงที่ถูกต้อง
        if ($invoicePage < 1) {
            $invoicePage = 1;
        } elseif ($invoicePage > $totalInvoicePages && $totalInvoicePages > 0) {
            $invoicePage = $totalInvoicePages;
        }
        
        // คำนวณสถิติต่างๆ
        $statistics = $this->calculateCustomerStatistics($id);
        
        // แสดงหน้า view
        include 'views/customer/view.php';
    }
    
    // เพิ่มฟังก์ชันใหม่สำหรับคำนวณสถิติของลูกค้า
    private function calculateCustomerStatistics($customerId) {
        // ดึงข้อมูลลูกค้าเพื่อให้ได้ customer_code
        $customer = $this->customerModel->getCustomerById($customerId);
        if (!$customer || empty($customer['code'])) {
            return [
                'total_invoices' => 0,
                'total_amount' => 0,
                'unpaid_amount' => 0,
                'total_shipments' => 0
            ];
        }
        
        $customerCode = $customer['code'];
        
        // สถิติใบแจ้งหนี้
        $invoice_stats_sql = "SELECT 
                              COUNT(i.id) as total_invoices,
                              SUM(i.total_amount) as total_amount,
                              SUM(CASE WHEN i.status = 'unpaid' THEN i.total_amount ELSE 0 END) as unpaid_amount
                              FROM invoices i
                              WHERE i.customer_id = :customer_id";
        
        $stmt = $this->db->prepare($invoice_stats_sql);
        $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        $invoice_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // สถิติการขนส่ง
        $shipment_stats_sql = "SELECT COUNT(*) as total_shipments
                               FROM shipments
                               WHERE customer_code = :customer_code";
        
        $stmt = $this->db->prepare($shipment_stats_sql);
        $stmt->bindParam(':customer_code', $customerCode);
        $stmt->execute();
        $shipment_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_invoices' => $invoice_stats['total_invoices'] ?? 0,
            'total_amount' => $invoice_stats['total_amount'] ?? 0,
            'unpaid_amount' => $invoice_stats['unpaid_amount'] ?? 0,
            'total_shipments' => $shipment_stats['total_shipments'] ?? 0
        ];
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $customer = $this->customerModel->getCustomerById($id);
        
        if (!$customer) {
            $_SESSION['error'] = 'Customer not found';
            header('Location: index.php?page=customer');
            exit();
        }
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $code = $_POST['code'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $taxId = $_POST['tax_id'] ?? '';
            
            // Validate input
            $errors = [];
            if (empty($name)) {
                $errors[] = 'Customer name is required';
            }
            
            if (empty($errors)) {
                // Update customer
                $customerData = [
                    'name' => $name,
                    'code' => $code,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'tax_id' => $taxId
                ];
                
                $result = $this->customerModel->updateCustomer($id, $customerData);
                
                if ($result) {
                    $_SESSION['success'] = 'Customer updated successfully';
                    header('Location: index.php?page=customer&action=view&id=' . $id);
                    exit();
                } else {
                    $_SESSION['error'] = 'Failed to update customer';
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }
        
        // Load view
        include 'views/customer/edit.php';
    }
    
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $customer = $this->customerModel->getCustomerById($id);
        
        if (!$customer) {
            $_SESSION['error'] = 'Customer not found';
            header('Location: index.php?page=customer');
            exit();
        }
        
        // Check if customer has invoices
        $invoices = $this->invoiceModel->getInvoicesByCustomerId($id);
        
        if (!empty($invoices)) {
            $_SESSION['error'] = 'Cannot delete customer with existing invoices';
            header('Location: index.php?page=customer&action=view&id=' . $id);
            exit();
        }
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            $result = $this->customerModel->deleteCustomer($id);
            
            if ($result) {
                $_SESSION['success'] = 'Customer deleted successfully';
                header('Location: index.php?page=customer');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to delete customer';
                header('Location: index.php?page=customer&action=view&id=' . $id);
                exit();
            }
        }
        
        // Load view
        include 'views/customer/delete.php';
    }
    
    // เพิ่มฟังก์ชันใหม่เพื่อดึงสถิติพัสดุตาม customer_code
    private function getShipmentStatsByCustomerCode($customerCode) {
        if (empty($customerCode)) {
            return [
                'total' => 0,
                'delivered' => 0,
                'in_transit' => 0,
                'pending' => 0
            ];
        }
        
        try {
            // ดึงข้อมูลพัสดุทั้งหมดของลูกค้า
            $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN status IN ('shipped', 'local_delivery') THEN 1 ELSE 0 END) as in_transit,
                    SUM(CASE WHEN status IN ('pending', 'received', 'processing') THEN 1 ELSE 0 END) as pending
                    FROM shipments
                    WHERE customer_code = :customer_code";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_code', $customerCode);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'total' => 0,
                    'delivered' => 0,
                    'in_transit' => 0,
                    'pending' => 0
                ];
            }
            
            return [
                'total' => (int)$result['total'],
                'delivered' => (int)$result['delivered'],
                'in_transit' => (int)$result['in_transit'],
                'pending' => (int)$result['pending']
            ];
        } catch (Exception $e) {
            error_log("Error in getShipmentStatsByCustomerCode: " . $e->getMessage());
            return [
                'total' => 0,
                'delivered' => 0,
                'in_transit' => 0,
                'pending' => 0
            ];
        }
    }



}
?>

