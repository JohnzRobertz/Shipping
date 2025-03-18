<?php
class HomeController {
    private $shipmentModel;
    private $lotModel;
    private $trackingHistoryModel;

    public function __construct() {
        global $db;
        
        // ตรวจสอบว่ามีการเชื่อมต่อกับฐานข้อมูลหรือไม่
        if ($db) {
            // โหลดโมเดลที่จำเป็น
            if (!class_exists('Shipment')) {
                require_once 'models/Shipment.php';
            }
            
            if (!class_exists('Lot')) {
                require_once 'models/Lot.php';
            }

            if (!class_exists('TrackingHistory')) {
                require_once 'models/TrackingHistory.php';
            }
            
            $this->shipmentModel = new Shipment();
            $this->lotModel = new Lot();
            $this->trackingHistoryModel = new TrackingHistory();
        }
    }
    
    public function index() {
        global $db;
        
        // ส่งตัวแปรไปยัง view
        $shipmentModel = $this->shipmentModel;
        $lotModel = $this->lotModel;
        $trackingHistoryModel = $this->trackingHistoryModel;
        
        // ดึงข้อมูลสถิติ
        $totalShipments = 0;
        $pendingShipments = 0;
        $processingShipments = 0;
        $totalLots = 0;
        
        // ดึงข้อมูลพัสดุล่าสุด
        $recentShipments = [];
        
        if ($db && $this->shipmentModel) {
            try {
                $totalShipments = $this->shipmentModel->countAll();
                $pendingShipments = $this->shipmentModel->countByStatus('pending');
                $processingShipments = $this->shipmentModel->countByStatus('processing');
                
                // ใช้เมธอด getRecentShipments แทน getRecent เพื่อแก้ปัญหาข้อมูลไม่แสดง
                $recentShipments = $this->shipmentModel->getRecentShipments(5);
                
                if ($this->lotModel) {
                    $totalLots = $this->lotModel->countAll();
                }
            } catch (Exception $e) {
                error_log('Error in HomeController: ' . $e->getMessage());
            }
        }

        include 'views/layout/header.php';
        include 'views/home/index.php';
        include 'views/layout/footer.php';
    }
}
?>

