<?php
require_once 'models/Shipment.php';
require_once 'models/Lot.php';

class TrackingController {
   private $shipmentModel;
   private $lotModel;

   public function __construct() {
       global $db;
       $this->db = $db;
       $this->shipmentModel = new Shipment();
       $this->lotModel = new Lot();
   }

   public function index() {
        // Get tracking history if tracking number is provided
        $trackingNumber = isset($_GET['tracking_number']) ? sanitizeInput($_GET['tracking_number']) : '';
        $trackingHistory = [];
        $shipment = null;
        
        // Pagination parameters for recent trackings
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // Get recent trackings with pagination
        $recentTrackings = $this->getRecentTrackings($limit, $offset);
        $totalTrackings = $this->countRecentTrackings();
        
        // Prepare pagination data
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalTrackings / $limit),
            'limit' => $limit,
            'total_items' => $totalTrackings
        ];
        
        if (!empty($trackingNumber)) {
            $shipment = $this->shipmentModel->getByTrackingNumber($trackingNumber);
            
            if ($shipment) {
                $trackingHistory = $this->shipmentModel->getTrackingHistory($shipment['id']);
                
                // Add this tracking to recent trackings if not already exists
                $this->addToRecentTrackings($trackingNumber);
            }
        }
        
        include 'views/tracking/index.php';
    }

   public function track() {
       try {
           // Get tracking number from GET or POST
           $trackingNumber = isset($_GET['tracking_number']) ? sanitizeInput($_GET['tracking_number']) : 
                       (isset($_POST['tracking_number']) ? sanitizeInput($_POST['tracking_number']) : null);
       
           if (!$trackingNumber) {
               require_once 'views/tracking/index.php';
               return;
           }
       
           // Get shipment data
           $shipment = $this->shipmentModel->getByTrackingNumber($trackingNumber);
           
           if (!$shipment) {
               $error = __('shipment_not_found');
           } else {
               // Get lot information if assigned
               $lot = null;
               if (!empty($shipment['lot_id'])) {
                   $lot = $this->lotModel->getById($shipment['lot_id']);
               }
           
               // Get tracking history
               $trackingHistory = $this->shipmentModel->getTrackingHistory($shipment['id']);
               
               // If no tracking history, create an initial entry
               if (empty($trackingHistory)) {
                   // Add initial tracking history
                   $initialStatus = $shipment['status'];
                   $location = isset($lot) ? $lot['origin'] : '';
                   $description = "พัสดุถูกรับเข้าระบบ";
                   
                   $this->shipmentModel->addTrackingHistory(
                       $shipment['id'],
                       $initialStatus,
                       $location,
                       $description
                   );
                   
                   // Refresh tracking history
                   $trackingHistory = $this->shipmentModel->getTrackingHistory($shipment['id']);
               }
           }
           
           // Load the tracking result view
           require_once 'views/tracking/index.php';
           
       } catch (Exception $e) {
           error_log('Tracking error: ' . $e->getMessage());
           $error = __('error_occurred');
           require_once 'views/tracking/index.php';
       }
   }

   public function group() {
       require_once 'views/tracking/group.php';
   }


   public function trackByCustomer() {
       if (!isset($_POST['customer_code']) || empty($_POST['customer_code'])) {
           $_SESSION['error'] = __('customer_code_required');
           header('Location: index.php?page=tracking&action=group');
           exit;
       }

       $customerCode = $_POST['customer_code'];

       require_once 'views/tracking/customer_result.php';
   }
   
   /**
    * Subscribe to tracking updates via email
    */
   public function subscribeEmail() {
       // This would normally connect to a notification service
       // For now, we'll just return a success response
       header('Content-Type: application/json');
       echo json_encode(['success' => true, 'message' => __('subscription_success')]);
       exit;
   }
   
   /**
    * Subscribe to tracking updates via SMS
    */
   public function subscribeSms() {
       // This would normally connect to an SMS service
       // For now, we'll just return a success response
       header('Content-Type: application/json');
       echo json_encode(['success' => true, 'message' => __('subscription_success')]);
       exit;
   }
   
   /**
    * Generate QR code for tracking
    */
   public function generateQR() {
       $trackingNumber = isset($_GET['tracking_number']) ? sanitizeInput($_GET['tracking_number']) : null;
       
       if (!$trackingNumber) {
           header('HTTP/1.1 400 Bad Request');
           exit;
       }
       
       // We'll use a library to generate QR codes in a real implementation
       // For now, we'll redirect to an external QR code generator
       $trackingUrl = urlencode(APP_URL . '/index.php?page=tracking&action=track&tracking_number=' . $trackingNumber);
       header('Location: https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $trackingUrl);
       exit;
   }

    /**
     * Get recent trackings with pagination
     * 
     * @param int $limit Number of items per page
     * @param int $offset Offset for pagination
     * @return array Recent trackings
     */
    private function getRecentTrackings($limit = 10, $offset = 0) {
        try {
            $sql = "SELECT * FROM recent_trackings ORDER BY tracked_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $recentTrackings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fetch shipment details for each tracking
            foreach ($recentTrackings as &$tracking) {
                $shipment = $this->shipmentModel->getByTrackingNumber($tracking['tracking_number']);
                if ($shipment) {
                    $tracking['shipment'] = $shipment;
                }
            }
            
            return $recentTrackings;
        } catch (PDOException $e) {
            error_log('Get recent trackings error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count total recent trackings
     * 
     * @return int Total number of recent trackings
     */
    private function countRecentTrackings() {
        try {
            $sql = "SELECT COUNT(*) FROM recent_trackings";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Count recent trackings error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Add tracking number to recent trackings
     * 
     * @param string $trackingNumber Tracking number
     * @return bool True on success, false on failure
     */
    private function addToRecentTrackings($trackingNumber) {
        try {
            // Check if tracking already exists
            $sql = "SELECT id FROM recent_trackings WHERE tracking_number = :tracking_number";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':tracking_number', $trackingNumber);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update tracked_at timestamp
                $sql = "UPDATE recent_trackings SET tracked_at = NOW() WHERE tracking_number = :tracking_number";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':tracking_number', $trackingNumber);
                return $stmt->execute();
            } else {
                // Insert new tracking
                $sql = "INSERT INTO recent_trackings (tracking_number, tracked_at) VALUES (:tracking_number, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':tracking_number', $trackingNumber);
                return $stmt->execute();
            }
        } catch (PDOException $e) {
            error_log('Add to recent trackings error: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Helper function for status badge colors
 */
function getStatusBadgeClass($status) {
   switch (strtolower($status)) {
       case 'received':
           return 'info';
       case 'in_transit':
           return 'primary';
       case 'out_for_delivery':
           return 'warning';
       case 'delivered':
           return 'success';
       case 'cancelled':
           return 'danger';
       default:
           return 'secondary';
   }
}

/**
 * Helper function for status icons
 */
function getStatusIcon($status) {
   switch (strtolower($status)) {
       case 'received':
           return 'box-seam';
       case 'processing':
           return 'gear';
       case 'in_transit':
           return 'truck';
       case 'out_for_delivery':
           return 'bicycle';
       case 'delivered':
           return 'check-circle';
       case 'cancelled':
           return 'x-circle';
       default:
           return 'circle';
   }
}
?>

