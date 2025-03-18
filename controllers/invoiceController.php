<?php
require_once 'models/Invoice.php';
require_once 'models/Customer.php';
require_once 'models/Shipment.php';
require_once 'models/InvoiceCharge.php';
require_once 'debug.php';
require_once 'lib/UlidGenerator.php';

class InvoiceController {
    private $invoiceModel;
    private $customerModel;
    private $shipmentModel;
    private $invoiceChargeModel;
    
    public function __construct() {
        $this->invoiceModel = new Invoice();
        $this->customerModel = new Customer();
        $this->shipmentModel = new Shipment();
        $this->invoiceChargeModel = new InvoiceCharge();
        
        // Check if user is logged in
        if (!isLoggedIn() && !in_array($_GET['action'] ?? 'index', ['view', 'print'])) {
            header('Location: index.php?page=login');
            exit();
        }
    }

    private function isValidUlid($id) {
        // ปรับปรุงการตรวจสอบ ULID
        // ตรวจสอบเฉพาะว่าเป็นสตริงที่มีความยาว 26 ตัวอักษร
        if (!is_string($id) || strlen($id) != 26) {
            return false;
        }
        
        // ตรวจสอบรูปแบบพื้นฐาน - สามารถขยายเพิ่มเติมได้ถ้าต้องการ
        return preg_match('/^[0-9A-Z]{26}$/i', $id) === 1;
    }
    
    // แก้ไขเมธอด index() เพื่อรองรับการเปลี่ยนแปลงขนาดหน้า (page size)
    public function index() {
        try {
            // Get filter parameters
            $month = isset($_GET['month']) ? $_GET['month'] : date('m');
            $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
            $customerSearch = isset($_GET['customer']) ? $_GET['customer'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';
            
            // Get pagination parameters
            $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // เพิ่มการรองรับ limit จาก URL
            $offset = ($page - 1) * $limit;
            
            // Prepare filter array
            $filters = [];
            
            // Add month/year filter if not "all"
            if ($month !== 'all') {
                $filters['month'] = $month;
            }
            if ($year !== 'all') {
                $filters['year'] = $year;
            }
            
            // Add customer search if provided
            if (!empty($customerSearch)) {
                $filters['customer'] = $customerSearch;
            }
            
            // Add status filter if provided
            if (!empty($status)) {
                $filters['status'] = $status;
            }
            
            // Get invoices with pagination and filters
            $invoices = $this->invoiceModel->getFilteredInvoices($filters, $limit, $offset);
            $totalInvoices = $this->invoiceModel->getTotalFilteredInvoices($filters);
            $totalPages = ceil($totalInvoices / $limit);
            
            // Get statistics
            $statistics = $this->invoiceModel->getInvoiceStatistics();
            
            // Get all customers for search dropdown
            $customers = $this->customerModel->getAllCustomers();
            
            // Get available years for filter
            $availableYears = $this->invoiceModel->getAvailableYears();
            
            // Pass variables to view
            include 'views/invoice/index.php';
        } catch (Exception $e) {
            // Log error
            error_log("Error in InvoiceController::index: " . $e->getMessage());
            
            // Set error message
            $_SESSION['error'] = __('error_loading_invoices') . ': ' . $e->getMessage();
            
            // Show empty data
            $invoices = [];
            $totalInvoices = 0;
            $totalPages = 0;
            $statistics = [];
            $customers = [];
            $availableYears = [];
            
            // Load view with empty data
            include 'views/invoice/index.php';
        }
    }
    
    public function create() {
        // Display form
        $customers = $this->customerModel->getAllCustomers();
        
        // Get delivered shipments that haven't been invoiced yet
        // ใช้ SQL query โดยตรงเพื่อให้แน่ใจว่าได้ข้อมูลที่ถูกต้อง
        global $db;
        $sql = "SELECT s.id, s.tracking_number, s.customer_code, s.status, s.total_price, s.lot_id, s.created_at, 
                c.name as customer_name, c.code as customer_code 
            FROM shipments s 
            JOIN customers c ON s.customer_code = c.code 
            WHERE  s.id NOT IN (SELECT shipment_id FROM invoice_shipments)
            ORDER BY s.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rawShipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: แสดงข้อมูล shipments ที่ดึงมาจาก SQL โดยตรง
        debug_log("Raw shipments from SQL: " . count($rawShipments));
        
        // ตรวจสอบข้อมูลซ้ำในผลลัพธ์ SQL
        $idCounts = [];
        foreach ($rawShipments as $shipment) {
            $id = $shipment['id'];
            if (!isset($idCounts[$id])) {
                $idCounts[$id] = 1;
            } else {
                $idCounts[$id]++;
            }
        }
        
        // แสดงข้อมูล ID ที่ซ้ำกัน
        foreach ($idCounts as $id => $count) {
            if ($count > 1) {
                debug_log("ID $id appears $count times in raw SQL result");
            }
        }
        
        // กรองข้อมูลซ้ำก่อนเพิ่มข้อมูล origin และ destination
        $uniqueShipmentsBeforeLot = [];
        $uniqueIdsBeforeLot = [];
        
        foreach ($rawShipments as $shipment) {
            if (!in_array($shipment['id'], $uniqueIdsBeforeLot)) {
                $uniqueIdsBeforeLot[] = $shipment['id'];
                $uniqueShipmentsBeforeLot[] = $shipment;
            }
        }
        
        debug_log("Unique shipments before adding lot data: " . count($uniqueShipmentsBeforeLot));
        
        // เพิ่มข้อมูล origin และ destination จาก lot
        $unpaidShipments = [];
        foreach ($uniqueShipmentsBeforeLot as $shipment) {
            if (isset($shipment['lot_id']) && !empty($shipment['lot_id'])) {
                $lot = $this->getLotById($shipment['lot_id']);
                if ($lot) {
                    $shipment['origin'] = $lot['origin'] ?? 'N/A';
                    $shipment['destination'] = $lot['destination'] ?? 'N/A';
                } else {
                    $shipment['origin'] = 'N/A';
                    $shipment['destination'] = 'N/A';
                }
            } else {
                $shipment['origin'] = 'N/A';
                $shipment['destination'] = 'N/A';
            }
            $unpaidShipments[] = $shipment;
        }
        
        debug_log("Shipments after adding lot data: " . count($unpaidShipments));
        
        // ตรวจสอบข้อมูลซ้ำหลังจากเพิ่มข้อมูล lot
        $uniqueShipments = [];
        $uniqueIds = [];
        $duplicates = [];

        foreach ($unpaidShipments as $shipment) {
            if (!in_array($shipment['id'], $uniqueIds)) {
                $uniqueIds[] = $shipment['id'];
                $uniqueShipments[] = $shipment;
            } else {
                // เก็บข้อมูลที่ซ้ำกัน
                $duplicates[] = $shipment;
            }
        }

        // แทนที่ข้อมูลเดิมด้วยข้อมูลที่ไม่ซ้ำกัน
        $unpaidShipments = $uniqueShipments;

        debug_log("Final unique shipments count: " . count($unpaidShipments));

        // แสดงข้อมูลที่ซ้ำกัน
        if (!empty($duplicates)) {
            debug_log("Found " . count($duplicates) . " duplicate shipments after processing:");
            foreach ($duplicates as $duplicate) {
                debug_log("Duplicate shipment ID: " . $duplicate['id'] . ", Tracking: " . $duplicate['tracking_number'] . ", Customer: " . $duplicate['customer_name']);
            }
        }
        
        // Load view
        include 'views/invoice/create.php';
    }
    
    public function store() {
        // Process form submission
        $customerID = $_POST['customer_id'] ?? null;
        $shipmentIDs = $_POST['shipment_ids'] ?? [];
        $invoiceDate = $_POST['invoice_date'] ?? date('Y-m-d');
        $dueDate = $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $notes = $_POST['notes'] ?? '';
        $taxRate = isset($_POST['tax_rate']) ? floatval($_POST['tax_rate']) : 0.07;
        
        // Validate input
        $errors = [];
        if (!$customerID) {
            $errors[] = __('customer_is_required');
        }
        if (empty($shipmentIDs)) {
            $errors[] = __('at_least_one_shipment_must_be_selected');
        }
        
        if (empty($errors)) {
            try {
                // Calculate subtotal
                $subtotal = 0;
                foreach ($shipmentIDs as $shipmentID) {
                    $shipment = $this->shipmentModel->getById($shipmentID);
                    if ($shipment) {
                        $subtotal += $shipment['total_price'] ?? 0;
                    }
                }
                
                // Calculate tax amount
                $taxAmount = $subtotal * $taxRate;
                
                // Calculate total amount
                $totalAmount = $subtotal + $taxAmount;
                
                // Add additional charges if any
                if (isset($_POST['additional_charges']) && !empty($_POST['additional_charges'])) {
                    foreach ($_POST['additional_charges'] as $charge) {
                        $chargeAmount = 0;
                        if ($charge['is_percentage'] == 1) {
                            $chargeAmount = $subtotal * ($charge['amount'] / 100);
                        } else {
                            $chargeAmount = $charge['amount'];
                        }
                        
                        // If it's a discount, make it negative
                        if ($charge['type'] == 'discount') {
                            $chargeAmount = -$chargeAmount;
                        }
                        
                        $totalAmount += $chargeAmount;
                    }
                }
                
                // Generate invoice number
                $invoiceNumber = $this->invoiceModel->getNextInvoiceNumber();
                
                // Create invoice
                $invoiceData = [
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $customerID,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'subtotal' => $subtotal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => 'unpaid',
                    'notes' => $notes
                ];
                
                $invoiceID = $this->invoiceModel->createInvoice($invoiceData);
                
                if ($invoiceID) {
                    // Link shipments to invoice
                    foreach ($shipmentIDs as $shipmentID) {
                        // Debug: แสดงข้อมูล shipment ที่กำลังเพิ่ม
                        debug_log("Shipment ID: " . $shipmentID);
                        $shipment = $this->shipmentModel->getById($shipmentID);
                        debug_log("Shipment data: " . print_r($shipment, true));
                    
                        $this->invoiceModel->linkShipmentToInvoice($invoiceID, $shipmentID);
                    
                        // อัพเดทสถานะการชำระเงินของ shipment
                        global $db;
                        $checkSql = "SHOW COLUMNS FROM shipments LIKE 'payment_status'";
                        $checkStmt = $db->prepare($checkSql);
                        $checkStmt->execute();
                        if ($checkStmt->rowCount() > 0) {
                            $sql = "UPDATE shipments SET payment_status = 'invoiced' WHERE id = :id";
                            $stmt = $db->prepare($sql);
                            $stmt->bindParam(':id', $shipmentID);
                            $stmt->execute();
                        }
                    }
                
                // Add additional charges if any
                if (isset($_POST['additional_charges']) && !empty($_POST['additional_charges'])) {
                    foreach ($_POST['additional_charges'] as $charge) {
                        $chargeData = [
                            'invoice_id' => $invoiceID,
                            'charge_type' => $charge['type'],
                            'description' => $charge['description'],
                            'amount' => $charge['amount'],
                            'is_percentage' => isset($charge['is_percentage']) ? $charge['is_percentage'] : 0
                        ];
                        $chargeId = $this->invoiceChargeModel->addCharge($chargeData);
                        if ($chargeId) {
                            debug_log("Added charge with ID: " . $chargeId);
                        } else {
                            debug_log("Failed to add charge");
                        }
                    }
                }
                
                $_SESSION['success'] = __('invoice_created_successfully');
                // Redirect to invoice view
                header('Location: index.php?page=invoice&action=view&id=' . $invoiceID);
                exit();
            } else {
                $_SESSION['error'] = __('failed_to_create_invoice');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = __('error_occurred') . ': ' . $e->getMessage();
        }
    } else {
        // Store errors in session
        $_SESSION['error'] = implode('<br>', $errors);
    }
    
    // If we get here, there were errors - redirect back to create form
    header('Location: index.php?page=invoice&action=create');
    exit();
}
    
    public function view() {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        if (!$this->isValidUlid($id)) {
            $_SESSION['error'] = __('invalid_invoice_id');
            header('Location: index.php?page=invoice');
            exit();
        }

        $invoice = $this->invoiceModel->getInvoiceById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = __('invoice_not_found');
            header('Location: index.php?page=invoice');
            exit();
        }
        
        $customer = $this->customerModel->getCustomerById($invoice['customer_id']);
        
        // ใช้เมธอด getShipmentsWithLotInfo() แทน
        $shipments = $this->getShipmentsWithLotInfo($id);
        
        // ดึงข้อมูลค่าใช้จ่ายเพิ่มเติม
        $additionalCharges = $this->invoiceChargeModel->getChargesByInvoiceId($id);
        
        include 'views/invoice/view.php';
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        if (!$this->isValidUlid($id)) {
            $_SESSION['error'] = __('invalid_invoice_id');
            header('Location: index.php?page=invoice');
            exit();
        }
        
        $invoice = $this->invoiceModel->getInvoiceById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = __('invoice_not_found');
            header('Location: index.php?page=invoice');
            exit();
        }
        
        // ตรวจสอบว่าใบแจ้งหนี้ยังไม่ได้ชำระเงิน
        if ($invoice['status'] === 'paid') {
            $_SESSION['error'] = __('cannot_edit_paid_invoice');
            header('Location: index.php?page=invoice&action=view&id=' . $id);
            exit();
        }
        
        $customers = $this->customerModel->getAllCustomers();
        
        // ดึงข้อมูล shipments ที่อยู่ในใบแจ้งหนี้
        $invoiceShipments = $this->invoiceModel->getShipmentsByInvoiceId($id);
        
        // Debug: แสดงข้อมูล shipments ที่ดึงมา
        error_log("Invoice shipments for invoice $id: " . print_r($invoiceShipments, true));
        
        $shipmentIds = array_column($invoiceShipments, 'id');
        
        // ดึงข้อมูล shipments ที่ยังไม่ได้ออกใบแจ้งหนี้
        $unpaidShipments = $this->shipmentModel->getUnpaidShipments();
        
        // ดึงข้อมูลค่าใช้จ่ายเพิ่มเติม
        $additionalCharges = $this->invoiceChargeModel->getChargesByInvoiceId($id);
        
        // เพิ่มข้อมูล origin และ destination จาก lot
        foreach ($invoiceShipments as &$shipment) {
            if (isset($shipment['lot_id']) && !empty($shipment['lot_id'])) {
                $lot = $this->getLotById($shipment['lot_id']);
                if ($lot) {
                    $shipment['origin'] = $lot['origin'] ?? 'N/A';
                    $shipment['destination'] = $lot['destination'] ?? 'N/A';
                } else {
                    $shipment['origin'] = 'N/A';
                    $shipment['destination'] = 'N/A';
                }
            } else {
                $shipment['origin'] = 'N/A';
                $shipment['destination'] = 'N/A';
            }
        }
        
        foreach ($unpaidShipments as &$shipment) {
            if (isset($shipment['lot_id']) && !empty($shipment['lot_id'])) {
                $lot = $this->getLotById($shipment['lot_id']);
                if ($lot) {
                    $shipment['origin'] = $lot['origin'] ?? 'N/A';
                    $shipment['destination'] = $lot['destination'] ?? 'N/A';
                } else {
                    $shipment['origin'] = 'N/A';
                    $shipment['destination'] = 'N/A';
                }
            } else {
                $shipment['origin'] = 'N/A';
                $shipment['destination'] = 'N/A';
            }
        }
        
        include 'views/invoice/edit.php';
    }
    
    // แก้ไขฟังก์ชัน update() ในไฟล์ controllers/InvoiceController.php
    // เพิ่ม debug log เพื่อตรวจสอบข้อมูลที่ส่งไป

    public function update() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?page=invoice');
        exit();
    }
    
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    
    // Debug: แสดงข้อมูลที่ได้รับจากฟอร์ม
    error_log("Update invoice - POST data: " . print_r($_POST, true));

    if (!$this->isValidUlid($id)) {
        $_SESSION['error'] = __('invalid_invoice_id');
        header('Location: index.php?page=invoice');
        exit();
    }
    
    $invoice = $this->invoiceModel->getInvoiceById($id);
    
    if (!$invoice) {
        $_SESSION['error'] = __('invoice_not_found');
        header('Location: index.php?page=invoice');
        exit();
    }
    
    // ตรวจสอบว่าใบแจ้งหนี้ยังไม่ได้ชำระเงิน
    if ($invoice['status'] === 'paid') {
        $_SESSION['error'] = __('cannot_edit_paid_invoice');
        header('Location: index.php?page=invoice&action=view&id=' . $id);
        exit();
    }
    
    $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
    $invoice_date = isset($_POST['invoice_date']) ? $_POST['invoice_date'] : date('Y-m-d');
    $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d', strtotime('+30 days'));
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $shipment_ids = isset($_POST['shipment_ids']) ? $_POST['shipment_ids'] : [];
    
    // Debug: แสดงข้อมูล shipment_ids
    error_log("Shipment IDs: " . print_r($shipment_ids, true));
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($customer_id) || empty($shipment_ids)) {
        $_SESSION['error'] = __('please_fill_all_required_fields');
        header('Location: index.php?page=invoice&action=edit&id=' . $id);
        exit();
    }
    
    // คำนวณยอดรวม
    $total_amount = 0;
    foreach ($shipment_ids as $shipment_id) {
        $shipment = $this->shipmentModel->getById($shipment_id);
        if ($shipment) {
            $total_amount += $shipment['total_price'];
        }
    }
    
    // ข้อมูลค่าใช้จ่ายเพิ่มเติม
    $charges = isset($_POST['charges']) ? $_POST['charges'] : [];
    $subtotal = $total_amount;
    
    // คำนวณยอดรวมหลังจากเพิ่มค่าใช้จ่ายเพิ่มเติม
    foreach ($charges as $charge) {
        if (!empty($charge['description']) && isset($charge['amount'])) {
            $amount = (float)$charge['amount'];
            $is_percentage = isset($charge['is_percentage']) ? 1 : 0;
            $charge_type = $charge['charge_type'] ?? 'fee';
    
            if ($is_percentage) {
                $charge_amount = $subtotal * ($amount / 100);
            } else {
                $charge_amount = $amount;
            }
    
            if ($charge_type === 'discount') {
                $total_amount -= $charge_amount;
            } else {
                $total_amount += $charge_amount;
            }
        }
    }
    
    // อัพเดทข้อมูลใบแจ้งหนี้
    $data = [
        'id' => $id,
        'customer_id' => $customer_id,
        'invoice_date' => $invoice_date,
        'due_date' => $due_date,
        'total_amount' => $total_amount,
        'subtotal' => $subtotal,
        'notes' => $notes
    ];
    
    // Debug: แสดงข้อมูลที่จะอัพเดท
    error_log("Update invoice data: " . print_r($data, true));

    // ลบข้อมูล shipments เก่าออกก่อน
    $this->invoiceModel->removeAllShipmentsFromInvoice($id);
    
    // รีเซ็ตสถานะการชำระเงินของ shipments ที่เคยอยู่ในใบแจ้งหนี้นี้
    global $db;
    $sql = "UPDATE shipments SET payment_status = 'unpaid' WHERE id IN (SELECT shipment_id FROM invoice_shipments WHERE invoice_id = :invoice_id)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':invoice_id', $id);
    $stmt->execute();
    
    $result = $this->invoiceModel->updateInvoice($data);
    
    // Debug: แสดงผลลัพธ์การอัพเดท
    error_log("Update invoice result: " . ($result ? "success" : "failed"));

    if ($result) {
        // บันทึกข้อมูล shipments ใหม่
        if (!empty($shipment_ids)) {
            foreach ($shipment_ids as $shipment_id) {
                $this->invoiceModel->linkShipmentToInvoice($id, $shipment_id);
                
                // อัพเดทสถานะการชำระเงินของ shipment
                $checkSql = "SHOW COLUMNS FROM shipments LIKE 'payment_status'";
                $checkStmt = $db->prepare($checkSql);
                $checkStmt->execute();
                if ($checkStmt->rowCount() > 0) {
                    $sql = "UPDATE shipments SET payment_status = 'invoiced' WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id', $shipment_id);
                    $stmt->execute();
                }
            }
        }
        
        // อัพเดทข้อมูลค่าใช้จ่ายเพิ่มเติม
        $this->invoiceChargeModel->deleteAllChargesByInvoiceId($id);
    
        foreach ($charges as $charge) {
            if (!empty($charge['description']) && isset($charge['amount'])) {
                $charge_data = [
                    'invoice_id' => $id,
                    'description' => $charge['description'],
                    'amount' => (float)$charge['amount'],
                    'is_percentage' => isset($charge['is_percentage']) ? 1 : 0,
                    'charge_type' => $charge['charge_type'] ?? 'fee'
                ];
            
                $chargeId = $this->invoiceChargeModel->addCharge($charge_data);
                if ($chargeId) {
                    debug_log("Added charge with ID: " . $chargeId);
                } else {
                    debug_log("Failed to add charge");
                }
            }
        }
    
        $_SESSION['success'] = __('invoice_updated_successfully');
        header('Location: index.php?page=invoice&action=view&id=' . $id);
        exit();
    } else {
        $_SESSION['error'] = __('error_updating_invoice');
        header('Location: index.php?page=invoice&action=edit&id=' . $id);
        exit();
    }
}
    
    public function delete() {
        // แก้ไขการรับค่า ID ให้รองรับทั้ง GET และ POST
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
        }
        
        // Debug: แสดงข้อมูล ID ที่ได้รับ
        error_log("Delete invoice - ID: " . $id);
        
        // ตรวจสอบความถูกต้องของ ID
        if (!$this->isValidUlid($id)) {
            $_SESSION['error'] = __('invalid_invoice_id');
            header('Location: index.php?page=invoice');
            exit();
        }
        
        $invoice = $this->invoiceModel->getInvoiceById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = __('invoice_not_found');
            header('Location: index.php?page=invoice');
            exit();
        }
        
        // ตรวจสอบว่าเป็นการส่งฟอร์มยืนยันการลบหรือไม่
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            try {
                // ลบข้อมูลค่าใช้จ่ายเพิ่มเติมก่อน
                $this->invoiceChargeModel->deleteAllChargesByInvoiceId($id);
                
                // ลบความเชื่อมโยงกับ shipments
                $this->invoiceModel->removeAllShipmentsFromInvoice($id);
                
                // ลบใบแจ้งหนี้
                $result = $this->invoiceModel->deleteInvoice($id);
                
                if ($result) {
                    $_SESSION['success'] = __('invoice_deleted_successfully');
                    header('Location: index.php?page=invoice');
                    exit();
                } else {
                    $_SESSION['error'] = __('failed_to_delete_invoice');
                    header('Location: index.php?page=invoice&action=view&id=' . $id);
                    exit();
                }
            } catch (Exception $e) {
                $_SESSION['error'] = __('error_occurred') . ': ' . $e->getMessage();
                header('Location: index.php?page=invoice&action=view&id=' . $id);
                exit();
            }
        }
        
        // แสดงหน้ายืนยันการลบ
        $customer = $this->customerModel->getCustomerById($invoice['customer_id']);
        $shipments = $this->invoiceModel->getShipmentsByInvoiceId($id);
        
        // เพิ่มข้อมูล origin และ destination จาก lot
        foreach ($shipments as &$shipment) {
            if (isset($shipment['lot_id']) && !empty($shipment['lot_id'])) {
                $lot = $this->getLotById($shipment['lot_id']);
                if ($lot) {
                    $shipment['origin'] = $lot['origin'] ?? 'N/A';
                    $shipment['destination'] = $lot['destination'] ?? 'N/A';
                } else {
                    $shipment['origin'] = 'N/A';
                    $shipment['destination'] = 'N/A';
                }
            } else {
                $shipment['origin'] = 'N/A';
                $shipment['destination'] = 'N/A';
            }
        }
        
        include 'views/invoice/delete.php';
    }
    
    public function print() {
        // รับค่า ID จาก URL
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        
        // Debug: แสดงข้อมูล ID ที่ได้รับ
        error_log("Print invoice - ID: " . $id);
        
        // ตรวจสอบความถูกต้องของ ID
        if (!$this->isValidUlid($id)) {
            $_SESSION['error'] = __('invalid_invoice_id');
            header('Location: index.php?page=invoice');
            exit();
        }
        
        // ดึงข้อมูลใบแจ้งหนี้
        $invoice = $this->invoiceModel->getInvoiceById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = __('invoice_not_found');
            header('Location: index.php?page=invoice');
            exit();
        }
        
        // ดึงข้อมูลลูกค้า
        $customer = $this->customerModel->getCustomerById($invoice['customer_id']);
        
        // ดึงข้อมูล shipments พร้อมข้อมูล lot
        $shipments = $this->getShipmentsWithLotInfo($id);
        
        // ดึงข้อมูลค่าใช้จ่ายเพิ่มเติม
        $additionalCharges = $this->invoiceChargeModel->getChargesByInvoiceId($id);
        
        // แสดงหน้าพิมพ์
        include 'views/invoice/print.php';
    }

    // เพิ่มเมธอดใหม่เพื่อดึงข้อมูล shipments พร้อมข้อมูล lot
    private function getShipmentsWithLotInfo($invoiceId) {
        global $db;
        
        // ดึงข้อมูล shipments พร้อมข้อมูล lot
        $sql = "SELECT s.*, l.origin, l.destination, l.lot_number, l.lot_type 
                FROM shipments s 
                LEFT JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id 
                LEFT JOIN lots l ON s.lot_id = l.id 
                WHERE is_rel.invoice_id = :invoice_id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();
        
        $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: แสดงข้อมูล shipments ที่ดึงมา
        error_log("Shipments with lot info for invoice $invoiceId: " . print_r($shipments, true));
        
        return $shipments;
    }
    
    public function markAsPaid() {
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
    }
    
    $invoice = $this->invoiceModel->getInvoiceById($id);
    
    if (!$invoice) {
        $_SESSION['error'] = __('invoice_not_found');
        header('Location: index.php?page=invoice');
        exit();
    }
    
    // Only allow marking unpaid invoices as paid
    if ($invoice['status'] !== 'unpaid') {
        $_SESSION['error'] = __('invoice_is_already_marked_as_paid');
        header('Location: index.php?page=invoice&action=view&id=' . $id);
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Process form submission
            $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
            $paymentMethod = $_POST['payment_method'] ?? '';
            $paymentReference = $_POST['payment_reference'] ?? '';
            
            // Update invoice
            $invoiceData = [
                'id' => $id,
                'status' => 'paid',
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference
            ];
            
            $result = $this->invoiceModel->updateInvoice($invoiceData);
            
            if ($result) {
                // Update payment_status in invoice_shipments to 'paid'
                $this->invoiceModel->updateShipmentPaymentStatus($id, 'paid');
                
                // อัพเดทสถานะการชำระเงินของ shipments ที่���ยู่ในใบแจ้งหนี้นี้
                global $db;
                $checkSql = "SHOW COLUMNS FROM shipments LIKE 'payment_status'";
                $checkStmt = $db->prepare($checkSql);
                $checkStmt->execute();
                if ($checkStmt->rowCount() > 0) {
                    $sql = "UPDATE shipments SET payment_status = 'paid' WHERE id IN (SELECT shipment_id FROM invoice_shipments WHERE invoice_id = :invoice_id)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':invoice_id', $id);
                    $stmt->execute();
                }
                
                $_SESSION['success'] = __('invoice_marked_as_paid_successfully');
                header('Location: index.php?page=invoice&action=view&id=' . $id);
                exit();
            } else {
                $_SESSION['error'] = __('failed_to_mark_invoice_as_paid');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = __('error_occurred') . ': ' . $e->getMessage();
        }
    }
    
    $customer = $this->customerModel->getCustomerById($invoice['customer_id']);
    $shipments = $this->invoiceModel->getShipmentsByInvoiceId($id);
    
    // เพิ่มข้อมูล origin และ destination จาก lot
    foreach ($shipments as &$shipment) {
        if (isset($shipment['lot_id']) && !empty($shipment['lot_id'])) {
            $lot = $this->getLotById($shipment['lot_id']);
            if ($lot) {
                $shipment['origin'] = $lot['origin'] ?? 'N/A';
                    $shipment['destination'] = $lot['destination'] ?? 'N/A';
                } else {
                    $shipment['origin'] = 'N/A';
                    $shipment['destination'] = 'N/A';
                }
            } else {
                $shipment['origin'] = 'N/A';
                $shipment['destination'] = 'N/A';
            }
        }
        
        include 'views/invoice/mark_as_paid.php';
    }

    private function getLotById($lotId) {
        global $db;
        $stmt = $db->prepare("SELECT * FROM lots WHERE id = ?");
        $stmt->execute([$lotId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // เพิ่มเมธอดสำหรับการส่งออกข้อมูล (export)
    public function export() {
        try {
            $format = isset($_GET['format']) ? $_GET['format'] : 'csv';
            
            // Get filter parameters (เหมือนกับใน index)
            $month = isset($_GET['month']) ? $_GET['month'] : 'all';
            $year = isset($_GET['year']) ? $_GET['year'] : 'all';
            $customerSearch = isset($_GET['customer']) ? $_GET['customer'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';
            
            // Prepare filter array
            $filters = [];
            
            // Add month/year filter if not "all"
            if ($month !== 'all') {
                $filters['month'] = $month;
            }
            if ($year !== 'all') {
                $filters['year'] = $year;
            }
            
            // Add customer search if provided
            if (!empty($customerSearch)) {
                $filters['customer'] = $customerSearch;
            }
            
            // Add status filter if provided
            if (!empty($status)) {
                $filters['status'] = $status;
            }
            
            // Get all invoices with filters (without pagination)
            $invoices = $this->invoiceModel->getFilteredInvoices($filters, 1000, 0); // ใช้ limit สูงเพื่อให้ได้ข้อมูลทั้งหมด
            
            // Generate filename
            $filename = 'invoices_export_' . date('Y-m-d_H-i-s');
            
            // Export based on format
            switch ($format) {
                case 'csv':
                    $this->exportCSV($invoices, $filename);
                    break;
                case 'excel':
                    $this->exportExcel($invoices, $filename);
                    break;
                case 'pdf':
                    $this->exportPDF($invoices, $filename);
                    break;
                default:
                    $_SESSION['error'] = __('invalid_export_format');
                    header('Location: index.php?page=invoice');
                    exit();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = __('error_exporting_invoices') . ': ' . $e->getMessage();
            header('Location: index.php?page=invoice');
            exit();
        }
    }

    private function exportCSV($invoices, $filename) {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header row
        fputcsv($output, [
            __('invoice_number'),
            __('customer'),
            __('date'),
            __('due_date'),
            __('subtotal'),
            __('tax_amount'),
            __('total_amount'),
            __('status'),
            __('shipments')
        ]);
        
        // Add data rows
        foreach ($invoices as $invoice) {
            fputcsv($output, [
                $invoice['invoice_number'],
                isset($invoice['customer_name']) ? $invoice['customer_name'] : 'N/A',
                isset($invoice['invoice_date']) ? date('d/m/Y', strtotime($invoice['invoice_date'])) : 'N/A',
                isset($invoice['due_date']) ? date('d/m/Y', strtotime($invoice['due_date'])) : 'N/A',
                isset($invoice['subtotal']) ? number_format($invoice['subtotal'], 2) : '0.00',
                isset($invoice['tax_amount']) ? number_format($invoice['tax_amount'], 2) : '0.00',
                isset($invoice['total_amount']) ? number_format($invoice['total_amount'], 2) : '0.00',
                isset($invoice['status']) && $invoice['status'] === 'paid' ? __('paid') : __('unpaid'),
                isset($invoice['total_shipments']) ? $invoice['total_shipments'] : '0'
            ]);
        }
        
        // Close output stream
        fclose($output);
        exit();
    }

    private function exportExcel($invoices, $filename) {
        // For simplicity, we'll redirect to CSV export
        // In a real application, you would use a library like PhpSpreadsheet
        $_SESSION['info'] = __('excel_export_not_implemented_using_csv');
        $this->exportCSV($invoices, $filename);
    }


    private function exportPDF($invoices, $filename) {
        // For simplicity, we'll redirect to CSV export
        // In a real application, you would use a library like TCPDF or FPDF
        $_SESSION['info'] = __('pdf_export_not_implemented_using_csv');
        $this->exportCSV($invoices, $filename);
    }
}
?>

