<?php
require_once 'models/Lot.php';
require_once 'models/Shipment.php';
require_once 'models/Invoice.php';
require_once 'helpers/auth.php';

class DashboardController {
    private $lotModel;
    private $shipmentModel;
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        $this->lotModel = new Lot();
        $this->shipmentModel = new Shipment();
        
        // Require login for all actions
        requireLogin();
    }
    
    /**
     * Display dashboard with statistics
     */
    public function index() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            header('Location: index.php?page=auth&action=login');
            exit;
        }
        
        // Get summary data for dashboard cards
        $summaryData = $this->getSummaryData();
        
        // Get status counts for pie chart
        $statusCounts = $this->getStatusCounts();
        
        // Get monthly revenue data for line chart
        $monthlyRevenue = $this->getMonthlyRevenue(6);
        
        // Debug information if needed
        $showDebug = isset($_GET['debug']) && $_GET['debug'] == 1;
        if ($showDebug) {
            $debugInfo = $this->debugDatabaseConnection();
        }
        
        // ปรับปรุงการดึงข้อมูล Recent Shipments - ไม่ใช้ pagination เพื่อเพิ่มความเร็ว
        // แสดงเพียง 5 รายการล่าสุดเท่านั้น
        $recentShipments = $this->shipmentModel->getRecent(5, 0);
        
        // ปรับปรุงการดึงข้อมูล Recent Lots - ไม่ใช้ pagination เพื่อเพิ่มความเร็ว
        // แสดงเพียง 5 รายการล่าสุดเท่านั้น
        $recentLots = $this->lotModel->getRecent(5, 0);
        
        // Load view
        include 'views/dashboard/index.php';
    }
    
    private function debugDatabaseConnection() {
        $debugInfo = [];
        
        try {
            // Check if we can connect to the database
            $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $debugInfo['tables'] = $tables;
            error_log('Database tables: ' . print_r($tables, true));
            
            // Check shipments table structure
            if (in_array('shipments', $tables)) {
                $columns = $this->db->query("DESCRIBE shipments")->fetchAll(PDO::FETCH_COLUMN);
                $debugInfo['shipments_columns'] = $columns;
                error_log('Shipments table columns: ' . print_r($columns, true));
                
                // Check if there are any shipments
                $count = $this->db->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
                $debugInfo['shipments_count'] = $count;
                error_log('Total shipments: ' . $count);
                
                // Get a sample shipment if available
                if ($count > 0) {
                    $sample = $this->db->query("SELECT * FROM shipments LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                    $debugInfo['sample_shipment'] = $sample;
                    error_log('Sample shipment: ' . print_r($sample, true));
                }
            } else {
                $debugInfo['error'] = 'Shipments table does not exist!';
                error_log('Shipments table does not exist!');
            }
            
            // Check lots table structure
            if (in_array('lots', $tables)) {
                $columns = $this->db->query("DESCRIBE lots")->fetchAll(PDO::FETCH_COLUMN);
                $debugInfo['lots_columns'] = $columns;
                error_log('Lots table columns: ' . print_r($columns, true));
                
                // Get a sample lot if available
                $count = $this->db->query("SELECT COUNT(*) FROM lots")->fetchColumn();
                if ($count > 0) {
                    $sample = $this->db->query("SELECT * FROM lots LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                    $debugInfo['sample_lot'] = $sample;
                    error_log('Sample lot: ' . print_r($sample, true));
                }
            }
        } catch (PDOException $e) {
            $debugInfo['error'] = 'Database debug error: ' . $e->getMessage();
            error_log('Database debug error: ' . $e->getMessage());
        }
        
        return $debugInfo;
    }
    
    private function getSummaryData() {
        // Initialize summary array with default values
        $summary = [
            'total_revenue' => 0,
            'total_shipments' => 0,
            'total_weight' => 0,
            'monthly_shipments' => 0,
            'monthly_revenue' => 0
        ];
        
        try {
            // Debug: Check if we can get any shipments
            $checkStmt = $this->db->query("SELECT COUNT(*) as count FROM shipments");
            $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
            error_log('Total shipments in database: ' . ($checkResult['count'] ?? 0));
            
            // If there are no shipments, return default values
            if (($checkResult['count'] ?? 0) == 0) {
                error_log('No shipments found in database, returning default values');
                return $summary;
            }
            
            // Total revenue - use COALESCE to handle NULL values
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(total_price), 0) as total_revenue 
                FROM shipments
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Total revenue query result: ' . print_r($result, true));
            $summary['total_revenue'] = floatval($result['total_revenue']);
            
            // Total shipments
            $stmt = $this->db->query("
                SELECT COUNT(*) as total_shipments 
                FROM shipments
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Total shipments query result: ' . print_r($result, true));
            $summary['total_shipments'] = intval($result['total_shipments']);
            
            // Total weight
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(chargeable_weight), 0) as total_weight 
                FROM shipments
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Total weight query result: ' . print_r($result, true));
            $summary['total_weight'] = floatval($result['total_weight']);
            
            // Shipments this month
            $startOfMonth = date('Y-m-01');
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as monthly_shipments 
                FROM shipments 
                WHERE created_at >= ?
            ");
            $stmt->execute([$startOfMonth]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Monthly shipments query result: ' . print_r($result, true));
            $summary['monthly_shipments'] = intval($result['monthly_shipments']);
            
            // Revenue this month
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(total_price), 0) as monthly_revenue 
                FROM shipments 
                WHERE created_at >= ?
            ");
            $stmt->execute([$startOfMonth]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Monthly revenue query result: ' . print_r($result, true));
            $summary['monthly_revenue'] = floatval($result['monthly_revenue']);
            
            // Debug: Output final summary data
            error_log('Final summary data: ' . print_r($summary, true));
        } catch (PDOException $e) {
            error_log('Dashboard summary data error: ' . $e->getMessage());
            // Default values already set
        }
        
        return $summary;
    }
    
    private function getStatusCounts() {
        try {
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM shipments
                GROUP BY status
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $statusCounts = [];
            foreach ($results as $row) {
                $statusCounts[$row['status']] = intval($row['count']);
            }
            
            error_log('Status counts: ' . print_r($statusCounts, true));
            return $statusCounts;
        } catch (PDOException $e) {
            error_log('Dashboard status counts error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getMonthlyRevenue($months = 6) {
        $data = [];
        
        try {
            // Get current month and past months
            for ($i = 0; $i < $months; $i++) {
                $month = date('Y-m', strtotime("-$i months"));
                $startDate = $month . '-01';
                $endDate = date('Y-m-t', strtotime($startDate));
                
                $stmt = $this->db->prepare("
                    SELECT COALESCE(SUM(total_price), 0) as revenue
                    FROM shipments
                    WHERE created_at BETWEEN ? AND ?
                ");
                $stmt->execute([$startDate, $endDate . ' 23:59:59']);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Revenue for $month: " . print_r($result, true));
                
                $data[] = [
                    'month' => date('M Y', strtotime($startDate)),
                    'revenue' => floatval($result['revenue'] ?? 0)
                ];
            }
        } catch (PDOException $e) {
            error_log('Dashboard monthly revenue error: ' . $e->getMessage());
        }
        
        // Reverse to get chronological order
        $reversed = array_reverse($data);
        error_log('Monthly revenue data: ' . print_r($reversed, true));
        return $reversed;
    }
}
?>

