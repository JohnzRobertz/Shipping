<?php
class HomeController {
    private $shipmentModel;
    private $lotModel;
    
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
            
            $this->shipmentModel = new Shipment();
            $this->lotModel = new Lot();
        }
    }
    
    public function index() {
        global $db;
        
        // ส่งตัวแปรไปยัง view
        $shipmentModel = $this->shipmentModel;
        $lotModel = $this->lotModel;
        
        include 'views/layout/header.php';
        include 'views/home/index.php';
        include 'views/layout/footer.php';
    }
}
?>

