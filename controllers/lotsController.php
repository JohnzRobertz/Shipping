<?php
require_once 'lib/UlidGenerator.php';
require_once 'models/Lot.php';
require_once 'models/Shipment.php';

class LotsController {
    private $lotModel;
    private $shipmentModel;
    
    public function __construct() {
        $this->lotModel = new Lot();
        $this->shipmentModel = new Shipment();
        
        // Require login for all actions
        requireLogin();
    }
    
    /**
     * Display lots list
     */
    public function index() {
        // Get filter parameters
        $lotType = isset($_GET['lot_type']) ? sanitizeInput($_GET['lot_type']) : '';
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
        $dateFrom = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
        $dateTo = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';
        $origin = isset($_GET['origin']) ? sanitizeInput($_GET['origin']) : '';
        $destination = isset($_GET['destination']) ? sanitizeInput($_GET['destination']) : '';
        
        // Sorting parameters
        $sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'created_at';
        $order = isset($_GET['order']) ? sanitizeInput($_GET['order']) : 'DESC';
        
        // Pagination parameters
        $page = isset($_GET['page_no']) ? (int)$_GET['page_no'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;
        
        // Get lots with filters
        $filters = [
            'lot_type' => $lotType,
            'status' => $status,
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'origin' => $origin,
            'destination' => $destination,
            'sort' => $sort,
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '';
        });
        
        $lots = $this->lotModel->getAll($filters);
        
        // Count total lots for pagination
        $totalLots = $this->lotModel->countAll($filters);
        
        // Prepare pagination data
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalLots / $limit),
            'limit' => $limit,
            'total_items' => $totalLots
        ];
        
        // If AJAX request, return JSON
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            echo json_encode([
                'lots' => $lots,
                'pagination' => $pagination
            ]);
            exit;
        }
        
        // Load view
        include 'views/lots/index.php';
    }
    
    /**
     * Display lot creation form
     */
    public function create() {
        // Load view
        include 'views/lots/create.php';
    }
    
    /**
     * Process lot creation
     */
    public function store() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = __('error_occurred');
            header('Location: index.php?page=lots&action=create');
            exit();
        }
        
        // Validate input
        $lotType = sanitizeInput($_POST['lot_type']);
        $departureDate = sanitizeInput($_POST['departure_date']);
        $arrivalDate = sanitizeInput($_POST['arrival_date']);
        $origin = sanitizeInput($_POST['origin']);
        $destination = sanitizeInput($_POST['destination']);
        $status = sanitizeInput($_POST['status']);
        
        // Create lot
        $lotData = [
            'lot_type' => $lotType,
            'departure_date' => $departureDate,
            'arrival_date' => $arrivalDate,
            'origin' => $origin,
            'destination' => $destination,
            'status' => $status
        ];
        
        $lotId = $this->lotModel->create($lotData);
        
        if ($lotId) {
            $_SESSION['success'] = __('create_success');
            header('Location: index.php?page=lots');
        } else {
            $_SESSION['error'] = __('error_occurred');
            header('Location: index.php?page=lots&action=create');
        }
        exit();
    }
    
    /**
     * Display lot details
     */
    public function view() {
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "ไม่พบรหัสล็อตที่ต้องการดู";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $id = $_GET['id'];
        
        // ตรวจสอบความถูกต้องของ ULID
        if (!UlidGenerator::isValid($id)) {
            $_SESSION['error'] = "รหัสล็อตไม่ถูกต้อง";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $lotModel = new Lot();
        $lot = $lotModel->getById($id);
        
        if (!$lot) {
            $_SESSION['error'] = "ไม่พบข้อมูลล็อต";
            header('Location: index.php?page=lots');
            exit;
        }
        
        // ดึงข้อมูลพัสดุในล็อตนี้
        $shipmentModel = new Shipment();
        $shipments = $shipmentModel->getAll(['lot_id' => $id]);
        
        // แสดงหน้า view
        include 'views/lots/view.php';
    }
    
    /**
     * Display lot edit form
     */
    public function edit() {
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "ไม่พบรหัสล็อตที่ต้องการแก้ไข";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $id = $_GET['id'];
        
        // ตรวจสอบความถูกต้องของ ULID
        if (!UlidGenerator::isValid($id)) {
            $_SESSION['error'] = "รหัสล็อตไม่ถูกต้อง";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $lotModel = new Lot();
        $lot = $lotModel->getById($id);
        
        if (!$lot) {
            $_SESSION['error'] = "ไม่พบข้อมูลล็อต";
            header('Location: index.php?page=lots');
            exit;
        }
        
        // แสดงหน้า edit
        include 'views/lots/edit.php';
    }
    
    /**
     * Process lot update
     */
    public function update() {
        // ตรวจสอบว่ามีการส่งข้อมูลผ่าน POST หรือไม่
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=lots');
            exit;
        }
        
        // ตรวจสอบ CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = "Invalid CSRF token";
            header('Location: index.php?page=lots');
            exit;
        }
        
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            $_SESSION['error'] = "ไม่พบรหัสล็อตที่ต้องการอัปเดต";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $id = $_POST['id'];
        
        // ตรวจสอบความถูกต้องของ ULID
        if (!UlidGenerator::isValid($id)) {
            $_SESSION['error'] = "รหัสล็อตไม่ถูกต้อง";
            header('Location: index.php?page=lots');
            exit;
        }
        
        // ดึงข้อมูลจากฟอร์ม
        $data = [
            'origin' => $_POST['origin'] ?? '',
            'destination' => $_POST['destination'] ?? '',
            'departure_date' => $_POST['departure_date'] ?? '',
            'arrival_date' => $_POST['arrival_date'] ?? '',
            'status' => $_POST['status'] ?? 'received',
        ];
        
        // ตรวจสอบข้อมูลที่จำเป็น
        if (empty($data['origin']) || empty($data['destination']) || 
            empty($data['departure_date']) || empty($data['arrival_date'])) {
            $_SESSION['error'] = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
            header('Location: index.php?page=lots&action=edit&id=' . $id);
            exit;
        }
        
        // อัปเดตข้อมูล
        $lotModel = new Lot();
        $result = $lotModel->update($id, $data);
        
        if ($result) {
            $_SESSION['success'] = "อัปเดตข้อมูลล็อตเรียบร้อยแล้ว";
            header('Location: index.php?page=lots&action=view&id=' . $id);
            exit;
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
            header('Location: index.php?page=lots&action=edit&id=' . $id);
            exit;
        }
    }
    
    /**
     * Process lot deletion
     */
    public function delete() {
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "ไม่พบรหัสล็อตที่ต้องการลบ";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $id = $_GET['id'];
        
        // ตรวจสอบความถูกต้องของ ULID
        if (!UlidGenerator::isValid($id)) {
            $_SESSION['error'] = "รหัสล็อตไม่ถูกต้อง";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $lotModel = new Lot();
        $result = $lotModel->delete($id);
        
        if ($result) {
            $_SESSION['success'] = "ลบข้อมูลล็อตเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล หรือล็อตนี้มีพัสดุอยู่";
        }
        
        header('Location: index.php?page=lots');
        exit;
    }
    
    /**
     * Update lot status
     */
    public function updateStatus() {
        // ตรวจสอบว่ามีการส่งข้อมูลผ่าน POST หรือไม่
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=lots');
            exit;
        }
        
        // ตรวจสอบ CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = "Invalid CSRF token";
            header('Location: index.php?page=lots');
            exit;
        }
        
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            $_SESSION['error'] = "ไม่พบรหัสล็อตที่ต้องการอัปเดตสถานะ";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $id = $_POST['id'];
        
        // ตรวจสอบความถูกต้องของ ULID
        if (!UlidGenerator::isValid($id)) {
            $_SESSION['error'] = "รหัสล็อตไม่ถูกต้อง";
            header('Location: index.php?page=lots');
            exit;
        }
        
        $status = $_POST['status'] ?? 'received';
        
        // Debug log
        error_log('Updating lot status: ' . $id . ' to ' . $status);
        
        $lotModel = new Lot();
        $result = $lotModel->updateStatusWithShipments($id, $status);
        
        if ($result) {
            $_SESSION['success'] = "อัปเดตสถานะล็อตและพัสดุเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตสถานะ";
        }
        
        header('Location: index.php?page=lots&action=view&id=' . $id);
        exit;
    }
    
    /**
     * Export lots data
     */
    public function export() {
        // GET filter parameters
        $lotType = isset($_GET['lot_type']) ? sanitizeInput($_GET['lot_type']) : '';
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
        $dateFrom = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
        $dateTo = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';
        $origin = isset($_GET['origin']) ? sanitizeInput($_GET['origin']) : '';
        $destination = isset($_GET['destination']) ? sanitizeInput($_GET['destination']) : '';
        
        // Sorting parameters
        $sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'created_at';
        $order = isset($_GET['order']) ? sanitizeInput($_GET['order']) : 'DESC';
        
        // Get lots with filters
        $filters = [
            'lot_type' => $lotType,
            'status' => $status,
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'origin' => $origin,
            'destination' => $destination,
            'sort' => $sort,
            'order' => $order
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '';
        });
        
        $lots = $this->lotModel->getAll($filters);
        
        // Export format
        $format = isset($_GET['format']) ? sanitizeInput($_GET['format']) : 'csv';
        
        // Export data
        switch ($format) {
            case 'csv':
                $this->exportCSV($lots);
                break;
            case 'excel':
                $this->exportExcel($lots);
                break;
            case 'pdf':
                $this->exportPDF($lots);
                break;
            default:
                $this->exportCSV($lots);
        }
    }
    
    /**
     * Export lots data to CSV
     */
    private function exportCSV($lots) {
        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=lots_export_' . date('Y-m-d') . '.csv');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        fputcsv($output, [
            'ID',
            'Lot Number',
            'Type',
            'Departure Date',
            'Arrival Date',
            'Origin',
            'Destination',
            'Status',
            'Created At'
        ]);
        
        // Add data
        foreach ($lots as $lot) {
            fputcsv($output, [
                $lot['id'],
                $lot['lot_number'],
                $lot['lot_type'],
                $lot['departure_date'],
                $lot['arrival_date'],
                $lot['origin'],
                $lot['destination'],
                $lot['status'],
                $lot['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export lots data to Excel
     */
    private function exportExcel($lots) {
        // This is a simplified version. In a real application, you would use a library like PhpSpreadsheet
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=lots_export_' . date('Y-m-d') . '.xls');
        
        echo '<table border="1">';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Lot Number</th>';
        echo '<th>Type</th>';
        echo '<th>Departure Date</th>';
        echo '<th>Arrival Date</th>';
        echo '<th>Origin</th>';
        echo '<th>Destination</th>';
        echo '<th>Status</th>';
        echo '<th>Created At</th>';
        echo '</tr>';
        
        foreach ($lots as $lot) {
            echo '<tr>';
            echo '<td>' . $lot['id'] . '</td>';
            echo '<td>' . $lot['lot_number'] . '</td>';
            echo '<td>' . $lot['lot_type'] . '</td>';
            echo '<td>' . $lot['departure_date'] . '</td>';
            echo '<td>' . $lot['arrival_date'] . '</td>';
            echo '<td>' . $lot['origin'] . '</td>';
            echo '<td>' . $lot['destination'] . '</td>';
            echo '<td>' . $lot['status'] . '</td>';
            echo '<td>' . $lot['created_at'] . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        exit;
    }
    
    /**
     * Export lots data to PDF
     */
    private function exportPDF($lots) {
        // This is a placeholder. In a real application, you would use a library like TCPDF or FPDF
        echo "PDF export is not implemented yet.";
        exit;
    }
}
?>

