<?php

require_once 'models/Shipment.php';
require_once 'models/Customer.php';

class Shipment_labelsController {
    private $shipmentModel;
    private $customerModel;

    public function __construct() {
        $this->shipmentModel = new Shipment();
        $this->customerModel = new Customer();
    }

    public function index() {
        // Get only shipments with status "received" and order by created_at desc
        $shipments = $this->shipmentModel->getAllByStatus('received');
        
        // Set page variable for active menu highlighting
        $page = 'shipments';
        
        include 'views/shipment_labels/index.php';
    }

    public function printLabel() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $shipment = $this->shipmentModel->getById($id);
            
            if ($shipment) {
                // Set page variable for active menu highlighting
                $page = 'shipments';
                
                include 'views/shipment_labels/print.php';
            } else {
                $_SESSION['error'] = "ไม่พบข้อมูลพัสดุ";
                header('Location: index.php?page=shipments');
                exit;
            }
        } else {
            $_SESSION['error'] = "ไม่ได้ระบุรหัสพัสดุ";
            header('Location: index.php?page=shipments');
            exit;
        }
    }

    public function printMultiple() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shipment_ids'])) {
            $shipmentIds = $_POST['shipment_ids'];
            $shipments = [];
            
            foreach ($shipmentIds as $id) {
                $shipment = $this->shipmentModel->getById($id);
                if ($shipment) {
                    $shipments[] = $shipment;
                }
            }
            
            // Set page variable for active menu highlighting
            $page = 'shipments';
            
            include 'views/shipment_labels/print_multiple.php';
        } else {
            $_SESSION['error'] = "กรุณาเลือกพัสดุที่ต้องการพิมพ์ Tag";
            header('Location: index.php?page=shipments');
            exit;
        }
    }
}

