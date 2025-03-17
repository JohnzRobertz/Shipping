<?php
require_once 'models/Shipment.php';
require_once 'models/Lot.php';
require_once 'models/User.php';

class AdminController {
    private $shipmentModel;
    private $lotModel;
    private $userModel;

    public function __construct() {
        $this->shipmentModel = new Shipment();
        $this->lotModel = new Lot();
        $this->userModel = new User();
        
        // Check if user is admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
            header('Location: index.php');
            exit;
        }
    }
    
    public function index() {
        // Admin dashboard
        require_once 'views/admin/dashboard.php';
    }
    
    public function updateStatusForm() {
        // If tracking number is provided, search for the shipment
        if (isset($_POST['tracking_number']) && !empty($_POST['tracking_number'])) {
            $trackingNumber = sanitizeInput($_POST['tracking_number']);
            $shipment = $this->shipmentModel->getByTrackingNumber($trackingNumber);
            
            if (!$shipment) {
                $_SESSION['error'] = __('shipment_not_found');
            } else {
                // Get tracking history
                $trackingHistory = $this->shipmentModel->getTrackingHistory($shipment['id']);
            }
        }
        
        require_once 'views/admin/update_status.php';
    }
    
    public function updateStatus() {
        // Validate CSRF token
        validateCsrfToken();
        
        // Check if all required fields are provided
        if (!isset($_POST['shipment_id']) || !isset($_POST['new_status']) || 
            !isset($_POST['location']) || !isset($_POST['description'])) {
            $_SESSION['error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
            header('Location: index.php?page=admin&action=updateStatusForm');
            exit;
        }
        
        $shipmentId = (int)$_POST['shipment_id'];
        $newStatus = sanitizeInput($_POST['new_status']);
        $location = sanitizeInput($_POST['location']);
        $description = sanitizeInput($_POST['description']);
        
        // Update shipment status
        $success = $this->shipmentModel->updateStatus($shipmentId, $newStatus);
        
        if (!$success) {
            $_SESSION['error'] = 'ไม่สามารถอัปเดตสถานะพัสดุได้';
            header('Location: index.php?page=admin&action=updateStatusForm');
            exit;
        }
        
        // Add tracking history
        $success = $this->shipmentModel->addTrackingHistory(
            $shipmentId,
            $newStatus,
            $location,
            $description
        );
        
        if (!$success) {
            $_SESSION['error'] = 'ไม่สามารถเพิ่มประวัติการติดตามได้';
            header('Location: index.php?page=admin&action=updateStatusForm');
            exit;
        }
        
        // Get shipment details for redirect
        $shipment = $this->shipmentModel->getById($shipmentId);
        
        $_SESSION['success'] = 'อัปเดตสถานะพัสดุเรียบร้อยแล้ว';
        header('Location: index.php?page=admin&action=updateStatusForm');
        exit;
    }
    
    // Other admin methods...
}

