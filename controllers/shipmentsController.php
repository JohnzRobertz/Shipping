<?php
require_once 'lib/UlidGenerator.php';
require_once 'models/Shipment.php';
require_once 'models/Lot.php';

class ShipmentsController {
    private $shipmentModel;
    private $lotModel;
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->shipmentModel = new Shipment();
        $this->lotModel = new Lot();
    }

    public function index() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
        
        global $lang;
        
        // Get items per page
        $limit = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
        $page = max(1, $page); // Ensure page is at least 1
        $offset = ($page - 1) * $limit;
        
        // Build query conditions
        $conditions = [];
        $params = [];
        
        // Search
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = sanitizeInput($_GET['search']);
            $conditions[] = "(tracking_number LIKE ? OR sender_name LIKE ? OR receiver_name LIKE ? OR customer_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Status filter
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $status = sanitizeInput($_GET['status']);
            $conditions[] = "s.status = ?";
            $params[] = $status;
        }
        
        // Lot filter
        if (isset($_GET['lot_id']) && !empty($_GET['lot_id'])) {
            $lotId = (int)$_GET['lot_id'];
            $conditions[] = "lot_id = ?";
            $params[] = $lotId;
        }
        
        // Lot number filter
        if (isset($_GET['lot_number']) && !empty($_GET['lot_number'])) {
            $lotNumber = sanitizeInput($_GET['lot_number']);
            $conditions[] = "l.lot_number = ?";
            $params[] = $lotNumber;
        }
        
        // Customer code filter
        if (isset($_GET['customer_code']) && !empty($_GET['customer_code'])) {
            $customerCode = sanitizeInput($_GET['customer_code']);
            $conditions[] = "customer_code = ?";
            $params[] = $customerCode;
        }
        
        // Date range filter
        if (isset($_GET['date_range']) && !empty($_GET['date_range'])) {
            $dateRange = sanitizeInput($_GET['date_range']);
            
            switch ($dateRange) {
                case 'today':
                    $conditions[] = "DATE(created_at) = CURDATE()";
                    break;
                case 'yesterday':
                    $conditions[] = "DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                    break;
                case 'this_week':
                    $conditions[] = "YEARWEEK(created_at) = YEARWEEK(CURDATE())";
                    break;
                case 'last_week':
                    $conditions[] = "YEARWEEK(created_at) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK))";
                    break;
                case 'this_month':
                    $conditions[] = "YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
                    break;
                case 'last_month':
                    $conditions[] = "YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
                    break;
                case 'custom':
                    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                        $dateFrom = sanitizeInput($_GET['date_from']);
                        $conditions[] = "DATE(created_at) >= ?";
                        $params[] = $dateFrom;
                    }
                    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                        $dateTo = sanitizeInput($_GET['date_to']);
                        $conditions[] = "DATE(created_at) <= ?";
                        $params[] = $dateTo;
                    }
                    break;
            }
        }
        
        // Weight range filter
        if (isset($_GET['weight_min']) && !empty($_GET['weight_min'])) {
            $weightMin = (float)$_GET['weight_min'];
            $conditions[] = "weight >= ?";
            $params[] = $weightMin;
        }
        if (isset($_GET['weight_max']) && !empty($_GET['weight_max'])) {
            $weightMax = (float)$_GET['weight_max'];
            $conditions[] = "weight <= ?";
            $params[] = $weightMax;
        }
        
        // Price range filter
        if (isset($_GET['price_min']) && !empty($_GET['price_min'])) {
            $priceMin = (float)$_GET['price_min'];
            $conditions[] = "price >= ?";
            $params[] = $priceMin;
        }
        if (isset($_GET['price_max']) && !empty($_GET['price_max'])) {
            $priceMax = (float)$_GET['price_max'];
            $conditions[] = "price <= ?";
            $params[] = $priceMax;
        }
        
        // Build WHERE clause
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        // Build ORDER BY clause
        $orderBy = "ORDER BY created_at DESC"; // Default sorting
        
        if (isset($_GET['sort_by']) && !empty($_GET['sort_by'])) {
            $sortBy = sanitizeInput($_GET['sort_by']);
            
            switch ($sortBy) {
                case 'created_at_asc':
                    $orderBy = "ORDER BY created_at ASC";
                    break;
                case 'created_at_desc':
                    $orderBy = "ORDER BY created_at DESC";
                    break;
                case 'tracking_number_asc':
                    $orderBy = "ORDER BY tracking_number ASC";
                    break;
                case 'tracking_number_desc':
                    $orderBy = "ORDER BY tracking_number DESC";
                    break;
                case 'weight_asc':
                    $orderBy = "ORDER BY weight ASC";
                    break;
                case 'weight_desc':
                    $orderBy = "ORDER BY weight DESC";
                    break;
                case 'price_asc':
                    $orderBy = "ORDER BY price ASC";
                    break;
                case 'price_desc':
                    $orderBy = "ORDER BY price DESC";
                    break;
            }
        }
        
        // Get total shipments count
        $countQuery = "SELECT COUNT(*) as total FROM shipments s LEFT JOIN lots l ON s.lot_id = l.id $whereClause";
        $stmt = $this->db->prepare($countQuery);
        
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalShipments = $result['total'];
        $totalPages = ceil($totalShipments / $limit);
        
        // Get shipments with pagination
        $query = "SELECT s.* 
                  FROM shipments s 
                  LEFT JOIN lots l ON s.lot_id = l.id
                  $whereClause 
                  $orderBy 
                  LIMIT $offset, $limit";
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get counts for dashboard cards
        $pendingCount = $this->getStatusCount('pending');
        $inTransitCount = $this->getStatusCount('in_transit');
        $deliveredCount = $this->getStatusCount('delivered');
        
        // Load view
        include 'views/shipments/index.php';
    }
    
    // Helper method to get count of shipments by status
    private function getStatusCount($status) {
        try {
            $query = "SELECT COUNT(*) as count FROM shipments WHERE status = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (Exception $e) {
            // If there's an error, return 0
            return 0;
        }
    }

    public function create() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Get all lots for dropdown
        $lots = $this->lotModel->getAll(['status' => 'received']);
        
        require_once 'views/shipments/create.php';
    }

    // แก้ไขฟังก์ชัน store ให้สอดคล้องกับฟอร์มและโมเดล
    public function store() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = __('error_occurred');
            header('Location: index.php?page=shipments&action=create');
            exit();
        }

        // Validate input
        $errors = [];
        
        // Required fields
        $requiredFields = ['sender_name', 'sender_contact', 'receiver_name', 'receiver_contact', 'weight', 'length', 'width', 'height'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $errors[] = __($field) . ' ' . __('is_required');
            }
        }
        
        // Numeric fields
        $numericFields = ['weight', 'length', 'width', 'height'];
        foreach ($numericFields as $field) {
            if (isset($_POST[$field]) && !empty($_POST[$field]) && !is_numeric($_POST[$field])) {
                $errors[] = __($field) . ' ' . __('must_be_numeric');
            }
        }
        
        // Check if there are any errors
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?page=shipments&action=create');
            exit();
        }
        
        // Prepare shipment data
        $shipmentData = [
            'id' => UlidGenerator::generate(), // เพิ่มการสร้าง ULID
            'sender_name' => sanitizeInput($_POST['sender_name']),
            'sender_contact' => sanitizeInput($_POST['sender_contact']),
            'sender_phone' => sanitizeInput($_POST['sender_phone'] ?? ''),
            'receiver_name' => sanitizeInput($_POST['receiver_name']),
            'receiver_contact' => sanitizeInput($_POST['receiver_contact']),
            'receiver_phone' => sanitizeInput($_POST['receiver_phone'] ?? ''),
            'weight' => (float)$_POST['weight'],
            'length' => (float)$_POST['length'],
            'width' => (float)$_POST['width'],
            'height' => (float)$_POST['height'],
            'description' => sanitizeInput($_POST['description'] ?? ''),
            'customer_code' => sanitizeInput($_POST['customer_code'] ?? ''),
            'lot_number' => !empty($_POST['lot_number']) ? sanitizeInput($_POST['lot_number']) : null,
            'price' => !empty($_POST['price']) ? (float)$_POST['price'] : 0,
            'status' => 'received'
        ];
        
        // Debug log
        error_log('Creating shipment with lot_number: ' . $shipmentData['lot_number']);
        
        // Create shipment
        $shipmentId = $this->shipmentModel->create($shipmentData);
        
        if ($shipmentId) {
            // Add tracking history - เพิ่ม debug log
            error_log('Adding tracking history for shipment ID: ' . $shipmentId);
        
            $location = '';
            if (!empty($shipmentData['lot_number'])) {
                $lot = $this->lotModel->getByLotNumber($shipmentData['lot_number']);
                if ($lot) {
                    $location = $lot['origin'];
                    error_log('Found lot origin: ' . $location);
                } else {
                    error_log('Lot not found for lot_number: ' . $shipmentData['lot_number']);
                }
            }
        
            // ตรวจสอบว่าตาราง tracking_history มีอยู่หรือไม่
            $checkTableStmt = $this->db->prepare("SHOW TABLES LIKE 'tracking_history'");
            $checkTableStmt->execute();
            if ($checkTableStmt->rowCount() == 0) {
                error_log('tracking_history table does not exist, creating it');
            
                // สร้างตาราง tracking_history ถ้ายังไม่มี
                $createTableSql = "
                    CREATE TABLE IF NOT EXISTS tracking_history (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        shipment_id VARCHAR(26) NOT NULL,
                        status VARCHAR(50) NOT NULL,
                        location VARCHAR(255),
                        description TEXT,
                        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX (shipment_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                $this->db->exec($createTableSql);
            }
        
            $trackingResult = $this->shipmentModel->addTrackingHistory(
                $shipmentId,
                'received',
                $location,
                'Shipment created with status: received'
            );
        
            if ($trackingResult) {
                error_log('Successfully added tracking history');
            } else {
                error_log('Failed to add tracking history');
            }

            $_SESSION['success'] = __('shipment_created_successfully');
            header('Location: index.php?page=shipments&action=view&id=' . $shipmentId);
        } else {
            $_SESSION['error'] = __('error_creating_shipment');
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?page=shipments&action=create');
        }
        exit();
    }

    // Update the view method to properly fetch the package group
    public function view() {
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "ไม่พบรหัสพัสดุที่ต้องการดู";
            header('Location: index.php?page=shipments');
            exit;
        }
        
        $id = $_GET['id'];
        
        // ตรวจสอบความถูกต้องของ ULID
        if (!UlidGenerator::isValid($id)) {
            $_SESSION['error'] = "รหัสพัสดุไม่ถูกต้อง";
            header('Location: index.php?page=shipments');
            exit;
        }
        
        $shipmentModel = new Shipment();
        $shipment = $shipmentModel->getById($id);
        
        if (!$shipment) {
            $_SESSION['error'] = "ไม่พบข้อมูลพัสดุ";
            header('Location: index.php?page=shipments');
            exit;
        }
        
        // ดึงข้อมูล lot ถ้ามี
        $lot = null;
        if (!empty($shipment['lot_id'])) {
            $lotModel = new Lot();
            $lot = $lotModel->getById($shipment['lot_id']);
        }
        
        // ดึงประวัติการติดตาม
        $trackingHistory = $shipmentModel->getTrackingHistory($id);
        
        // แสดงหน้า view
        include 'views/shipments/view.php';
    }

    public function edit() {
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "ไม่พบรหัสพัสดุที่ต้องการแก้ไข";
            header('Location: index.php?page=shipments');
            exit;
        }
        
        $id = $_GET['id'];
        
        // ตรวจสอบความถูกต้องของ ULID
        if (!UlidGenerator::isValid($id)) {
            $_SESSION['error'] = "รหัสพัสดุไม่ถูกต้อง";
            header('Location: index.php?page=shipments');
            exit;
        }
        
        $shipmentModel = new Shipment();
        $shipment = $shipmentModel->getById($id);
        
        if (!$shipment) {
            $_SESSION['error'] = "ไม่พบข้อมูลพัสดุ";
            header('Location: index.php?page=shipments');
            exit;
        }
        
        // ดึงข้อมูล lots ทั้งหมดสำหรับ dropdown
        $lotModel = new Lot();
        $lots = $lotModel->getAll();
        
        // แสดงหน้า edit
        include 'views/shipments/edit.php';
    }

// แก้ไขฟังก์ชัน update เพื่อรองรับฟิลด์การขนส่งภายในประเทศ
public function update() {
    // ตรวจสอบว่ามีการส่งข้อมูลผ่าน POST หรือไม่
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?page=shipments');
        exit;
    }
    
    // ตรวจสอบ CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token";
        header('Location: index.php?page=shipments');
        exit;
    }
    
    // ตรวจสอบว่ามี ID ส่งมาหรือไม่
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        $_SESSION['error'] = "ไม่พบรหัสพัสดุที่ต้องการอัปเดต";
        header('Location: index.php?page=shipments');
        exit;
    }
    
    $id = $_POST['id'];
    
    // ตรวจสอบความถูกต้องของ ULID
    if (!UlidGenerator::isValid($id)) {
        $_SESSION['error'] = "รหัสพัสดุไม่ถูกต้อง";
        header('Location: index.php?page=shipments');
        exit;
    }
    
    // ดึงข้อมูลจากฟอร์ม
    $data = [
        'lot_number' => $_POST['lot_number'] ?? '', // เพิ่มการรับค่า lot_number
        'customer_code' => $_POST['customer_code'] ?? '',
        'sender_name' => $_POST['sender_name'] ?? '',
        'sender_contact' => $_POST['sender_contact'] ?? '',
        'sender_phone' => $_POST['sender_phone'] ?? '',
        'receiver_name' => $_POST['receiver_name'] ?? '',
        'receiver_contact' => $_POST['receiver_contact'] ?? '',
        'receiver_phone' => $_POST['receiver_phone'] ?? '',
        'weight' => $_POST['weight'] ?? 0,
        'length' => $_POST['length'] ?? 0,
        'width' => $_POST['width'] ?? 0,
        'height' => $_POST['height'] ?? 0,
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'domestic_carrier' => $_POST['domestic_carrier'] ?? null,
        'domestic_tracking_number' => $_POST['domestic_tracking_number'] ?? null,
        'handover_date' => $_POST['handover_date'] ?? null,
    ];
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($data['sender_name']) || empty($data['sender_contact']) || 
        empty($data['receiver_name']) || empty($data['receiver_contact'])) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
        header('Location: index.php?page=shipments&action=edit&id=' . $id);
        exit;
    }
    
    // เพิ่ม debug log
    error_log('Updating shipment with lot_number: ' . $data['lot_number']);
    
    // อัปเดตข้อมูล
    $shipmentModel = new Shipment();
    $result = $shipmentModel->update($id, $data);
    
    if ($result) {
        $_SESSION['success'] = "อัปเดตข้อมูลพัสดุเรียบร้อยแล้ว";
        header('Location: index.php?page=shipments&action=view&id=' . $id);
        exit;
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        header('Location: index.php?page=shipments&action=edit&id=' . $id);
        exit;
    }
}

    /**
     * Process shipment deletion
     */
    public function delete() {
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "ไม่พบรหัสพัสดุที่ต้องการลบ";
            header('Location: index.php?page=shipments');
            exit;
        }
        
        $id = $_GET['id'];
        
        // ตรวจสอบความถูกต้องของ ULID
        if (!UlidGenerator::isValid($id)) {
            $_SESSION['error'] = "รหัสพัสดุไม่ถูกต้อง";
            header('Location: index.php?page=shipments');
            exit;
        }
        
        // ลบประวัติการติดตามก่อน (เพื่อแก้ปัญหา foreign key constraints)
        $this->db->beginTransaction();
        try {
            // ลบประวัติการติดตาม
            $deleteTrackingStmt = $this->db->prepare("DELETE FROM tracking_history WHERE shipment_id = :id");
            $deleteTrackingStmt->bindParam(':id', $id, PDO::PARAM_STR);
            $deleteTrackingStmt->execute();
            
            // ลบความสัมพันธ์กับใบแจ้งหนี้ (ถ้ามี)
            $deleteInvoiceRelStmt = $this->db->prepare("DELETE FROM invoice_shipments WHERE shipment_id = :id");
            $deleteInvoiceRelStmt->bindParam(':id', $id, PDO::PARAM_STR);
            $deleteInvoiceRelStmt->execute();
            
            // ลบพัสดุ
            $shipmentModel = new Shipment();
            $result = $shipmentModel->delete($id);
            
            if ($result) {
                $this->db->commit();
                $_SESSION['success'] = "ลบข้อมูลพัสดุเรียบร้อยแล้ว";
            } else {
                $this->db->rollBack();
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล";
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล: " . $e->getMessage();
            error_log("Error deleting shipment: " . $e->getMessage());
        }
        
        header('Location: index.php?page=shipments');
        exit;
    }

    public function assignLot() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Check if ID is provided
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header('Location: index.php?controller=shipments');
            exit;
        }

        $id = $_GET['id'];
        $shipment = $this->shipmentModel->getById($id);

        if (!$shipment) {
            $_SESSION['error'] = getTranslation('shipment_not_found');
            header('Location: index.php?controller=shipments');
            exit;
        }

        // Get all lots for dropdown
        $lots = $this->lotModel->getAll();

        require_once 'views/shipments/assign_lot.php';
    }

    public function updateLot() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Check if ID is provided
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            header('Location: index.php?controller=shipments');
            exit;
        }

        $id = $_POST['id'];
        
        // ถ้ามี lot_number ให้ใช้ lot_number แทน lot_id
        if (isset($_POST['lot_number']) && !empty($_POST['lot_number'])) {
            $lotNumber = $_POST['lot_number'];
            $result = $this->shipmentModel->updateLotByNumber($id, $lotNumber);
        } else {
            $lotId = isset($_POST['lot_id']) && !empty($_POST['lot_id']) ? $_POST['lot_id'] : null;
            $result = $this->shipmentModel->updateLot($id, $lotId);
        }

        if ($result) {
            $_SESSION['success'] = getTranslation('lot_assigned_successfully');
            header('Location: index.php?controller=shipments&action=view&id=' . $id);
            exit;
        } else {
            $_SESSION['error'] = getTranslation('error_assigning_lot');
            header('Location: index.php?controller=shipments&action=assignLot&id=' . $id);
            exit;
        }
    }

    // เพิ่มเมธอด updateStatus ในคลาส ShipmentsController
    public function updateStatus() {
        // ตรวจสอบว่ามีการส่ง ID มาหรือไม่
        if (!isset($_GET['id'])) {
            $_SESSION['error'] = getTranslation('shipment_not_found');
            header('Location: index.php?controller=shipments');
            exit;
        }
        
        $id = $_GET['id'];
        $shipmentModel = new Shipment();
        $shipment = $shipmentModel->getById($id);
        
        // ตรวจสอบว่าพบพัสดุหรือไม่
        if (!$shipment) {
            $_SESSION['error'] = getTranslation('shipment_not_found');
            header('Location: index.php?controller=shipments');
            exit;
        }
        
        // ถ้ามีการส่งฟอร์ม
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ตรวจสอบ CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                $_SESSION['error'] = getTranslation('invalid_csrf_token');
                header('Location: index.php?controller=shipments&action=updateStatus&id=' . $id);
                exit;
            }
        
            // รับค่าจากฟอร์ม
            $status = $_POST['status'] ?? '';
            $location = $_POST['location'] ?? '';
            $description = $_POST['description'] ?? '';
            $domestic_carrier = $_POST['domestic_carrier'] ?? '';
            $domestic_tracking_number = $_POST['domestic_tracking_number'] ?? '';
            $handover_date = $_POST['handover_date'] ?? '';
        
            // อัพเดทสถานะพัสดุ
            $updateData = [
                'status' => $status
            ];
        
            // ถ้าเป็นสถานะที่เกี่ยวข้องกับการขนส่งภายในประเทศ ให้บันทึกข้อมูลเพิ่มเติม
            if ($status == 'out_for_delivery' || $status == 'local_delivery') {
                $updateData['domestic_carrier'] = $domestic_carrier;
                $updateData['domestic_tracking_number'] = $domestic_tracking_number;
                $updateData['handover_date'] = $handover_date;
            }
        
            // บันทึกข้อมูล
            $result = $shipmentModel->update($id, $updateData);
        
            // บันทึกประวัติการเปลี่ยนสถานะ
            $trackingResult = $shipmentModel->addTrackingHistory(
                $id, 
                $status, 
                $location, 
                $description
            );
        
            if ($result && $trackingResult) {
                $_SESSION['success'] = getTranslation('status_updated_successfully');
                header('Location: index.php?page=shipments&action=view&id=' . $id);
                exit;
            } else {
                $_SESSION['error'] = getTranslation('failed_to_update_status');
                header('Location: index.php?page=shipments&action=updateStatus&id=' . $id);
                exit;
            }
        }
    
        // โหลดข้อมูลที่จำเป็น
        $statusOptions = [
            'received' => getTranslation('status_received'),
            'processing' => getTranslation('status_processing'),
            'in_transit' => getTranslation('status_in_transit'),
            'arrived_destination' => getTranslation('status_arrived_destination'),
            'local_delivery' => getTranslation('status_local_delivery'),
            'out_for_delivery' => getTranslation('status_out_for_delivery'),
            'delivered' => getTranslation('status_delivered'),
            'cancelled' => getTranslation('status_cancelled')
        ];
    
        // โหลดประวัติการติดตาม
        $trackingHistory = $shipmentModel->getTrackingHistory($id);
    
        // แสดงหน้า update_status
        include 'views/shipments/update_status.php';
    }

    // เพิ่มเมธอดสำหรับการแสดงหน้านำเข้าข้อมูลจาก CSV
    public function import() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }
        
        include 'views/shipments/import.php';
    }
    
    // เพิ่มเมธอดสำหรับการดาวน์โหลดเทมเพลต CSV
    public function download_template() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }
        
        // Check if template type is provided
        if (!isset($_GET['type']) || empty($_GET['type'])) {
            $_SESSION['error'] = 'Template type is required.';
            header('Location: index.php?page=shipments&action=import');
            exit;
        }
        
        $template_type = $_GET['type'];
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $template_type . '_template.csv"');
        
        // Create a file pointer
        $output = fopen('php://output', 'w');
        
        // Set column headers based on template type
        if ($template_type === 'create') {
            // Headers for creating new shipments
            $headers = [
                'tracking_number', 'sender_name', 'sender_contact', 'sender_phone',
                'receiver_name', 'receiver_contact', 'receiver_phone', 'weight',
                'length', 'width', 'height', 'transport_type', 'customer_code',
                'description', 'price', 'lot_number'
            ];
        } else if ($template_type === 'update') {
            // Headers for updating existing shipments
            $headers = [
                'tracking_number', 'lot_number', 'domestic_carrier',
                'domestic_tracking_number', 'handover_date', 'status'
            ];
        } else {
            $_SESSION['error'] = 'Invalid template type.';
            header('Location: index.php?page=shipments&action=import');
            exit;
        }
        
        // Output the column headers
        fputcsv($output, $headers);
        
        // Add a sample row with example data
        if ($template_type === 'create') {
            $sample_data = [
                'TRK123456789', 'John Doe', 'john@example.com', '1234567890',
                'Jane Smith', 'jane@example.com', '0987654321', '5.5',
                '30', '20', '15', '1', 'CUST001',
                'Sample package', '100.50', 'LOT-SEA-202401-001'
            ];
        } else {
            $sample_data = [
                'TRK123456789', 'LOT-SEA-202401-001', 'DHL',
                'DHL123456789', date('Y-m-d'), 'local_delivery'
            ];
        }
        
        // Output the sample row
        fputcsv($output, $sample_data);
        
        // Close the file pointer
        fclose($output);
        exit;
    }
    
    // เพิ่มเมธอดสำหรับประมวลผลไฟล์ CSV ที่อัพโหลด
    public function process_import() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = __('error_occurred');
            header('Location: index.php?page=shipments&action=import');
            exit;
        }
        
        // Check if file and import type are provided
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK || !isset($_POST['import_type'])) {
            $_SESSION['error'] = 'Please select a valid CSV file and import type.';
            header('Location: index.php?page=shipments&action=import');
            exit;
        }
        
        $import_type = $_POST['import_type'];
        $file = $_FILES['csv_file'];
        
        // Check file type
        $file_info = pathinfo($file['name']);
        if ($file_info['extension'] !== 'csv') {
            $_SESSION['error'] = 'Only CSV files are allowed.';
            header('Location: index.php?page=shipments&action=import');
            exit;
        }
        
        // Open the uploaded file
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $_SESSION['error'] = 'Failed to open the uploaded file.';
            header('Location: index.php?page=shipments&action=import');
            exit;
        }
        
        // Read the header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            $_SESSION['error'] = 'Failed to read the CSV file headers.';
            fclose($handle);
            header('Location: index.php?page=shipments&action=import');
        exit;
        }
        
        // Convert headers to lowercase for case-insensitive comparison
        $headers = array_map('strtolower', $headers);
        
        // Validate headers based on import type
        if ($import_type === 'create') {
            $required_headers = ['tracking_number', 'sender_name', 'receiver_name', 'weight', 'transport_type'];
            $all_headers = [
                'tracking_number', 'sender_name', 'sender_contact', 'sender_phone',
                'receiver_name', 'receiver_contact', 'receiver_phone', 'weight',
                'length', 'width', 'height', 'transport_type', 'customer_code',
                'description', 'price', 'lot_number'
            ];
        } else if ($import_type === 'update') {
            $required_headers = ['tracking_number'];
            $all_headers = [
                'tracking_number', 'lot_number', 'domestic_carrier',
                'domestic_tracking_number', 'handover_date', 'status'
            ];
        } else {
            $_SESSION['error'] = 'Invalid import type.';
            fclose($handle);
            header('Location: index.php?page=shipments&action=import');
            exit;
        }
        
        // Check if all required headers are present
        foreach ($required_headers as $header) {
            if (!in_array($header, $headers)) {
                $_SESSION['error'] = 'Missing required column: ' . $header;
                fclose($handle);
                header('Location: index.php?page=shipments&action=import');
                exit;
            }
        }
        
        // Create a mapping of header indices
        $header_map = [];
        foreach ($all_headers as $header) {
            $index = array_search($header, $headers);
            if ($index !== false) {
                $header_map[$header] = $index;
            }
        }
        
        // Process the data rows
        $row_number = 1; // Start from 1 to account for header row
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        
        while (($data = fgetcsv($handle)) !== false) {
            $row_number++;
            
            // Skip empty rows
            if (count(array_filter($data)) === 0) {
                continue;
            }
            
            // Process the row based on import type
            if ($import_type === 'create') {
                $result = $this->processCreateRow($data, $header_map, $row_number);
            } else {
                $result = $this->processUpdateRow($data, $header_map, $row_number);
            }
            
            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = $result['message'];
            }
        }
        
        fclose($handle);
        
        // Set session messages
        if ($success_count > 0) {
            $_SESSION['success'] = "Successfully processed $success_count shipment(s).";
        }
        
        if ($error_count > 0) {
            $_SESSION['error'] = "Failed to process $error_count shipment(s):<br>" . implode('<br>', $errors);
        }
        
        header('Location: index.php?page=shipments&action=import');
        exit;
    }
    
    // เมธอดช่วยสำหรับประมวลผลข้อมูลแถวในโหมดสร้างใหม่
    private function processCreateRow($data, $header_map, $row_number) {
        // Extract data from the row using the header map
        $tracking_number = isset($header_map['tracking_number']) && isset($data[$header_map['tracking_number']]) ? 
            trim($data[$header_map['tracking_number']]) : '';
        $sender_name = isset($header_map['sender_name']) && isset($data[$header_map['sender_name']]) ? 
            trim($data[$header_map['sender_name']]) : '';
        $sender_contact = isset($header_map['sender_contact']) && isset($data[$header_map['sender_contact']]) ? 
            trim($data[$header_map['sender_contact']]) : '';
        $sender_phone = isset($header_map['sender_phone']) && isset($data[$header_map['sender_phone']]) ? 
            trim($data[$header_map['sender_phone']]) : '';
        $receiver_name = isset($header_map['receiver_name']) && isset($data[$header_map['receiver_name']]) ? 
            trim($data[$header_map['receiver_name']]) : '';
        $receiver_contact = isset($header_map['receiver_contact']) && isset($data[$header_map['receiver_contact']]) ? 
            trim($data[$header_map['receiver_contact']]) : '';
        $receiver_phone = isset($header_map['receiver_phone']) && isset($data[$header_map['receiver_phone']]) ? 
            trim($data[$header_map['receiver_phone']]) : '';
        $weight = isset($header_map['weight']) && isset($data[$header_map['weight']]) ? 
            trim($data[$header_map['weight']]) : '';
        $length = isset($header_map['length']) && isset($data[$header_map['length']]) ? 
            trim($data[$header_map['length']]) : '';
        $width = isset($header_map['width']) && isset($data[$header_map['width']]) ? 
            trim($data[$header_map['width']]) : '';
        $height = isset($header_map['height']) && isset($data[$header_map['height']]) ? 
            trim($data[$header_map['height']]) : '';
        $transport_type = isset($header_map['transport_type']) && isset($data[$header_map['transport_type']]) ? 
            trim($data[$header_map['transport_type']]) : '';
        $customer_code = isset($header_map['customer_code']) && isset($data[$header_map['customer_code']]) ? 
            trim($data[$header_map['customer_code']]) : '';
        $description = isset($header_map['description']) && isset($data[$header_map['description']]) ? 
            trim($data[$header_map['description']]) : '';
        $price = isset($header_map['price']) && isset($data[$header_map['price']]) ? 
            trim($data[$header_map['price']]) : '';
        $lot_number = isset($header_map['lot_number']) && isset($data[$header_map['lot_number']]) ? 
            trim($data[$header_map['lot_number']]) : '';
        
        // Validate required fields
        if (empty($tracking_number)) {
            return ['success' => false, 'message' => "Row $row_number: Tracking number is required."];
        }
        if (empty($sender_name)) {
            return ['success' => false, 'message' => "Row $row_number: Sender name is required."];
        }
        if (empty($receiver_name)) {
            return ['success' => false, 'message' => "Row $row_number: Receiver name is required."];
        }
        if (empty($weight) || !is_numeric($weight) || $weight <= 0) {
            return ['success' => false, 'message' => "Row $row_number: Weight must be a positive number."];
        }
        if (empty($transport_type) || !is_numeric($transport_type)) {
            return ['success' => false, 'message' => "Row $row_number: Transport type is required and must be a number."];
        }
        
        // Check if tracking number already exists
        $existingShipment = $this->shipmentModel->getByTrackingNumber($tracking_number);
        if ($existingShipment) {
            return ['success' => false, 'message' => "Row $row_number: Tracking number already exists."];
        }
        
        // Validate dimensions if provided
        if (!empty($length) && (!is_numeric($length) || $length <= 0)) {
            return ['success' => false, 'message' => "Row $row_number: Length must be a positive number."];
        }
        if (!empty($width) && (!is_numeric($width) || $width <= 0)) {
            return ['success' => false, 'message' => "Row $row_number: Width must be a positive number."];
        }
        if (!empty($height) && (!is_numeric($height) || $height <= 0)) {
            return ['success' => false, 'message' => "Row $row_number: Height must be a positive number."];
        }
        
        // Prepare shipment data
        $shipmentData = [
            'id' => UlidGenerator::generate(), // เพิ่มการสร้าง ULID
            'tracking_number' => $tracking_number,
            'sender_name' => $sender_name,
            'sender_contact' => $sender_contact,
            'sender_phone' => $sender_phone,
            'receiver_name' => $receiver_name,
            'receiver_contact' => $receiver_contact,
            'receiver_phone' => $receiver_phone,
            'weight' => (float) $weight,
            'length' => !empty($length) ? (float) $length : null,
            'width' => !empty($width) ? (float) $width : null,
            'height' => !empty($height) ? (float) $height : null,
            'transport_type' => (int) $transport_type,
            'customer_code' => $customer_code,
            'description' => $description,
            'price' => !empty($price) ? (float) $price : 0,
            'lot_number' => $lot_number, // ใช้ lot_number แทน lot_id
            'status' => 'pending'
        ];
        
        // Create shipment
        $shipmentId = $this->shipmentModel->create($shipmentData);
        
        if ($shipmentId) {
            return ['success' => true, 'message' => "Row $row_number: Shipment created successfully."];
        } else {
            return ['success' => false, 'message' => "Row $row_number: Failed to create shipment."];
        }
    }
    
    // เมธอดช่วยสำหรับประมวลผลข้อมูลแถวในโหมดอัพเดท
    private function processUpdateRow($data, $header_map, $row_number) {
        // Extract data from the row using the header map
        $tracking_number = isset($header_map['tracking_number']) && isset($data[$header_map['tracking_number']]) ? 
            trim($data[$header_map['tracking_number']]) : '';
        $lot_number = isset($header_map['lot_number']) && isset($data[$header_map['lot_number']]) ? 
            trim($data[$header_map['lot_number']]) : '';
        $domestic_carrier = isset($header_map['domestic_carrier']) && isset($data[$header_map['domestic_carrier']]) ? 
            trim($data[$header_map['domestic_carrier']]) : '';
        $domestic_tracking_number = isset($header_map['domestic_tracking_number']) && isset($data[$header_map['domestic_tracking_number']]) ? 
            trim($data[$header_map['domestic_tracking_number']]) : '';
        $handover_date = isset($header_map['handover_date']) && isset($data[$header_map['handover_date']]) ? 
            trim($data[$header_map['handover_date']]) : '';
        $status = isset($header_map['status']) && isset($data[$header_map['status']]) ? 
            trim($data[$header_map['status']]) : '';
        
        // Validate tracking number
        if (empty($tracking_number)) {
            return ['success' => false, 'message' => "Row $row_number: Tracking number is required."];
        }
        
        // Get existing shipment
        $shipment = $this->shipmentModel->getByTrackingNumber($tracking_number);
        if (!$shipment) {
            return ['success' => false, 'message' => "Row $row_number: Shipment with tracking number $tracking_number not found."];
        }
        
        // Prepare update data
        $updateData = [];
        
        // Update lot if provided
        if (!empty($lot_number)) {
            $updateData['lot_number'] = $lot_number;
        }
        
        // Update domestic shipping information if provided
        if (!empty($domestic_carrier)) {
            $updateData['domestic_carrier'] = $domestic_carrier;
            $updateData['domestic_tracking_number'] = $domestic_tracking_number;
            
            if (!empty($handover_date)) {
                // Validate date format
                $date = DateTime::createFromFormat('Y-m-d', $handover_date);
                if (!$date || $date->format('Y-m-d') !== $handover_date) {
                    return ['success' => false, 'message' => "Row $row_number: Invalid handover date format. Use YYYY-MM-DD."];
                }
                $updateData['handover_date'] = $handover_date;
            }
            
            // If domestic tracking is added and status is still 'in_transit', update to 'local_delivery'
            if ($shipment['status'] == 'in_transit' && empty($status)) {
                $updateData['status'] = 'local_delivery';
            }
        }
        
        // Update status if provided
        if (!empty($status)) {
            $valid_statuses = ['pending', 'processing', 'in_transit', 'local_delivery', 'delivered', 'cancelled'];
            if (!in_array($status, $valid_statuses)) {
                return ['success' => false, 'message' => "Row $row_number: Invalid status. Valid statuses are: " . implode(', ', $valid_statuses)];
            }
            $updateData['status'] = $status;
        }
        
        // If no updates, return success
        if (empty($updateData)) {
            return ['success' => true, 'message' => "Row $row_number: No changes to update."];
        }
        
        // Update shipment
        $success = $this->shipmentModel->update($shipment['id'], $updateData);
        
        if ($success) {
            return ['success' => true, 'message' => "Row $row_number: Shipment updated successfully."];
        } else {
            return ['success' => false, 'message' => "Row $row_number: Failed to update shipment."];
        }
    }

    public function export() {
        // ตรวจสอบการล็อกอิน
        if (!isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }
        
        // สร้าง filters จากพารามิเตอร์ GET
        $filters = [];
        
        if (isset($_GET['lot_id']) && $_GET['lot_id'] !== '') {
            $filters['lot_id'] = $_GET['lot_id'];
        }
        
        if (isset($_GET['lot_number']) && $_GET['lot_number'] !== '') {
            $filters['lot_number'] = $_GET['lot_number'];
        }
        
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $filters['status'] = $_GET['status'];
        }
        
        if (isset($_GET['customer_code']) && $_GET['customer_code'] !== '') {
            $filters['customer_code'] = $_GET['customer_code'];
        }
        
        if (isset($_GET['tracking_number']) && $_GET['tracking_number'] !== '') {
            $filters['tracking_number'] = $_GET['tracking_number'];
        }
        
        if (isset($_GET['name']) && $_GET['name'] !== '') {
            $filters['name'] = $_GET['name'];
        }
        
        // ดึงข้อมูล shipments ตาม filters (ไม่มีการแบ่งหน้า)
        $shipments = $this->shipmentModel->getAll($filters);
        
        // กำหนดชื่อไฟล์
        $filename = 'shipments_export_' . date('Y-m-d') . '.xls';
        
        // กำหนด headers สำหรับการดาวน์โหลด
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // เริ่มสร้างไฟล์ HTML
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        echo '<title>รายงานพัสดุ - ' . date('Y-m-d') . '</title>';
        echo '<style>';
        echo 'body { font-family: "Angsana New", "TH SarabunPSK", sans-serif; }';
        echo 'table { border-collapse: collapse; width: 100%; }';
        echo 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        echo 'th { background-color: #4CAF50; color: white; font-weight: bold; }';
        echo 'tr:nth-child(even) { background-color: #f2f2f2; }';
        echo '.text-center { text-align: center; }';
        echo '.text-right { text-align: right; }';
        echo '.header { font-size: 18px; font-weight: bold; margin-bottom: 10px; }';
        echo '.subheader { font-size: 14px; margin-bottom: 20px; }';
        echo '.status-received { background-color: #e0e0e0; }';
        echo '.status-in_transit { background-color: #bbdefb; }';
        echo '.status-arrived_destination { background-color: #c8e6c9; }';
        echo '.status-out_for_delivery { background-color: #ffecb3; }';
        echo '.status-delivered { background-color: #c8e6c9; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        
        // เพิ่มส่วนหัวรายงาน
        echo '<div class="header">รายงานพัสดุ</div>';
        echo '<div class="subheader">วันที่ออกรายงาน: ' . date('d/m/Y H:i:s') . '</div>';
        
        // เพิ่มข้อมูลการกรอง (ถ้ามี)
        if (!empty($filters)) {
            echo '<div class="subheader">ตัวกรอง: ';
            $filterTexts = [];
            
            if (!empty($filters['lot_id'])) {
                $lot = $this->lotModel->getById($filters['lot_id']);
                if ($lot) {
                    $filterTexts[] = 'ล็อต: ' . $lot['lot_number'];
                }
            }
            
            if (!empty($filters['lot_number'])) {
                $filterTexts[] = 'เลขล็อต: ' . $filters['lot_number'];
            }
            
            if (!empty($filters['status'])) {
                $statusText = '';
                switch ($filters['status']) {
                    case 'received':
                        $statusText = 'รับเข้าระบบแล้ว';
                        break;
                    case 'in_transit':
                        $statusText = 'อยู่ระหว่างขนส่ง';
                        break;
                    case 'local_delivery': // แก้ไขจาก status_local_delivery เป็น local_delivery
                        $statusText = 'ส่งในประเทศ';
                        break;
                    case 'arrived_destination':
                        $statusText = 'ถึงปลายทางแล้ว';
                        break;
                    case 'out_for_delivery':
                        $statusText = 'กำลังนำส่ง';
                        break;
                    case 'delivered':
                        $statusText = 'ส่งมอบแล้ว';
                        break;
                }
                $filterTexts[] = 'สถานะ: ' . $statusText;
            }
            
            if (!empty($filters['customer_code'])) {
                $filterTexts[] = 'รหัสลูกค้า: ' . $filters['customer_code'];
            }
            
            if (!empty($filters['tracking_number'])) {
                $filterTexts[] = 'เลขติดตาม: ' . $filters['tracking_number'];
            }
            
            if (!empty($filters['name'])) {
                $filterTexts[] = 'ชื่อผู้ส่ง/ผู้รับ: ' . $filters['name'];
            }
            
            echo implode(', ', $filterTexts);
            echo '</div>';
        }
        
        echo '<table>';
        
        // สร้างหัวตาราง
        echo '<thead>';
        echo '<tr>';
        echo '<th>ลำดับ</th>';
        echo '<th>เลขติดตาม</th>';
        echo '<th>รหัสลูกค้า</th>';
        echo '<th>เลขล็อต</th>';
        echo '<th>ชื่อผู้ส่ง</th>';
        echo '<th>ติดต่อผู้ส่ง</th>';
        echo '<th>ชื่อผู้รับ</th>';
        echo '<th>ติดต่อผู้รับ</th>';
        echo '<th>น้ำหนัก (กก.)</th>';
        echo '<th>ขนาด (ซม.)</th>';
        echo '<th>บริษัทขนส่งในประเทศ</th>';
        echo '<th>เลขติดตามในประเทศ</th>';
        echo '<th>สถานะ</th>';
        echo '<th>วันที่สร้าง</th>';
        echo '</tr>';
        echo '</thead>';
        
        // สร้างเนื้อหาตาราง
        echo '<tbody>';
        $i = 1;
        foreach ($shipments as $shipment) {
            $dimensions = $shipment['length'] . ' × ' . $shipment['width'] . ' × ' . $shipment['height'];
            
            // แปลงสถานะเป็นภาษาไทย
            $status_th = $shipment['status'];
            switch ($shipment['status']) {
                case 'received':
                    $status_th = 'รับเข้าระบบแล้ว';
                    break;
                case 'in_transit':
                    $status_th = 'อยู่ระหว่างขนส่ง';
                    break;
                case 'local_delivery': // แก้ไขจาก status_local_delivery เป็น local_delivery
                    $status_th = 'ส่งในประเทศ';
                    break;
                case 'arrived_destination':
                    $status_th = 'ถึงปลายทางแล้ว';
                    break;
                case 'out_for_delivery':
                    $status_th = 'กำลังนำส่ง';
                    break;
                case 'delivered':
                    $status_th = 'ส่งมอบแล้ว';
                    break;
            }
            
            echo '<tr class="status-' . $shipment['status'] . '">';
            echo '<td class="text-center">' . $i++ . '</td>';
            echo '<td>' . htmlspecialchars($shipment['tracking_number']) . '</td>';
            echo '<td>' . htmlspecialchars($shipment['customer_code'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($shipment['lot_number'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($shipment['sender_name']) . '</td>';
            echo '<td>' . htmlspecialchars($shipment['sender_contact'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($shipment['receiver_name']) . '</td>';
            echo '<td>' . htmlspecialchars($shipment['receiver_contact'] ?? '') . '</td>';
            echo '<td class="text-right">' . htmlspecialchars($shipment['weight']) . '</td>';
            echo '<td>' . htmlspecialchars($dimensions) . '</td>';
            echo '<td>' . htmlspecialchars($shipment['domestic_carrier'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($shipment['domestic_tracking_number'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($status_th) . '</td>';
            echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($shipment['created_at']))) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        
        // เพิ่มส่วนท้ายตาราง (สรุป)
        echo '<tfoot>';
        echo '<tr>';
        echo '<td colspan="8" class="text-right"><strong>จำนวนพัสดุทั้งหมด:</strong></td>';
        echo '<td class="text-right"><strong>' . count($shipments) . '</strong></td>';
        echo '<td colspan="5"></td>';
        echo '</tr>';
        echo '</tfoot>';
        
        // ปิดตารางและ HTML
        echo '</table>';
        
        // เพิ่มส่วนท้ายรายงาน
        echo '<div style="margin-top: 20px; font-size: 12px; text-align: center;">รายงานนี้สร้างโดยระบบจัดการพัสดุ</div>';
        
        echo '</body>';
        echo '</html>';
        
        exit;
    }
}
?>