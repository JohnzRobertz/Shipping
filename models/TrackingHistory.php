<?php
require_once 'models/BaseModel.php';

class TrackingHistory extends BaseModel {
    protected $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        parent::__construct();
    }
    
    /**
     * Get current user ID with fallback to SYSTEM
     * 
     * @return string User ID or 'SYSTEM' if not available
     */
    protected function getCurrentUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Debug session data
        error_log('Session data in TrackingHistory: ' . json_encode($_SESSION));
        
        // Check for user_id in session
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            error_log('Found user_id in session: ' . $_SESSION['user_id']);
            return $_SESSION['user_id'];
        }
        
        // Fallback to SYSTEM user
        error_log('No user_id found in session. Using SYSTEM user.');
        return 'SYSTEM';
    }
    
    /**
     * Create a new tracking history entry
     * 
     * @param array $data Tracking history data
     * @return string|bool Tracking history ID on success, false on failure
     */
    public function create($data) {
        try {
            // Generate ULID if not provided
            if (!isset($data['id']) || empty($data['id'])) {
                $data['id'] = UlidGenerator::generate();
            }
            
            // Ensure required fields are present
            if (!isset($data['shipment_id']) || empty($data['shipment_id'])) {
                error_log('Shipment ID is required for tracking history');
                return false;
            }
            
            if (!isset($data['status']) || empty($data['status'])) {
                error_log('Status is required for tracking history');
                return false;
            }
            
            // Set default values for optional fields
            $location = isset($data['location']) ? $data['location'] : '';
            $description = isset($data['description']) ? $data['description'] : '';
            
            // Get current user ID with fallback to SYSTEM
            $userId = $this->getCurrentUserId();
            error_log('Current user ID for tracking history create: ' . $userId);
            
            // Check if tracking_history table exists
            $this->ensureTableExists();

            // Insert tracking history with audit fields
            $sql = "INSERT INTO tracking_history (
                        id, 
                        shipment_id, 
                        status, 
                        location, 
                        description, 
                        timestamp, 
                        created_by,
                        updated_by,
                        created_at,
                        updated_at
                    ) VALUES (
                        :id, 
                        :shipment_id, 
                        :status, 
                        :location, 
                        :description, 
                        NOW(),
                        :created_by,
                        :updated_by,
                        NOW(),
                        NOW()
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_STR);
            $stmt->bindValue(':shipment_id', $data['shipment_id'], PDO::PARAM_STR);
            $stmt->bindValue(':status', $data['status']);
            $stmt->bindValue(':location', $location);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':created_by', $userId, PDO::PARAM_STR);
            $stmt->bindValue(':updated_by', $userId, PDO::PARAM_STR);
            
            error_log('SQL Query: ' . $sql);
            error_log('Parameters: ' . json_encode([
                'id' => $data['id'],
                'shipment_id' => $data['shipment_id'],
                'status' => $data['status'],
                'location' => $location,
                'description' => $description,
                'created_by' => $userId,
                'updated_by' => $userId
            ]));
            
            $result = $stmt->execute();
            
            if ($result) {
                error_log('Successfully created tracking history with ID: ' . $data['id']);
                return $data['id'];
            } else {
                error_log('Failed to create tracking history. PDO Error: ' . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log('Exception creating tracking history: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get tracking history by ID
     * 
     * @param string $id Tracking history ID
     * @return array|bool Tracking history data on success, false on failure
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM tracking_history WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get tracking history by ID error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get tracking history by shipment ID
     * 
     * @param string $shipmentId Shipment ID
     * @return array|bool Tracking history data on success, false on failure
     */
    public function getByShipmentId($shipmentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM tracking_history 
                WHERE shipment_id = :shipment_id 
                ORDER BY timestamp DESC
            ");
            $stmt->bindParam(':shipment_id', $shipmentId, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get tracking history by shipment ID error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update tracking history
     * 
     * @param string $id Tracking history ID
     * @param array $data Tracking history data
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        try {
            // Get current user ID with fallback to SYSTEM
            $userId = $this->getCurrentUserId();
            error_log('Current user ID for tracking history update: ' . $userId);
            
            $sql = "UPDATE tracking_history SET ";
            $updates = [];
            $params = [':id' => $id];
            
            // Build update statement
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'shipment_id') { // Don't update ID or shipment_id
                    $updates[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }
            
            // Add updated_by and updated_at
            $updates[] = "updated_by = :updated_by";
            $updates[] = "updated_at = NOW()";
            $params[':updated_by'] = $userId;
            
            $sql .= implode(', ', $updates);
            $sql .= " WHERE id = :id";
            
            error_log('Update SQL: ' . $sql);
            error_log('Update params: ' . json_encode($params));
            
            $stmt = $this->db->prepare($sql);
            
            // Bind all parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log('Update failed. PDO Error: ' . json_encode($stmt->errorInfo()));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log('Update tracking history error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete tracking history
     * 
     * @param string $id Tracking history ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM tracking_history WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Delete tracking history error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete all tracking history for a shipment
     * 
     * @param string $shipmentId Shipment ID
     * @return bool True on success, false on failure
     */
    public function deleteByShipmentId($shipmentId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM tracking_history WHERE shipment_id = :shipment_id");
            $stmt->bindParam(':shipment_id', $shipmentId, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Delete tracking history by shipment ID error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent tracking updates for all shipments
     * 
     * @param int $limit Maximum number of records to return
     * @return array Recent tracking updates
     */
    public function getRecentTrackings($limit = 5) {
        try {
            // ตรวจสอบว่าตาราง recent_trackings มีอยู่หรือไม่
            $checkTableStmt = $this->db->prepare("SHOW TABLES LIKE 'recent_trackings'");
            $checkTableStmt->execute();
            if ($checkTableStmt->rowCount() == 0) {
                // ถ้าไม่มีตาราง ให้สร้างตาราง
                $this->createRecentTrackingsTable();
            }
            
            // ดึงข้อมูลการติดตามล่าสุดจากตาราง tracking_history
            $stmt = $this->db->prepare("
                SELECT 
                    th.id, 
                    th.shipment_id, 
                    th.status, 
                    th.location, 
                    th.description as notes, 
                    th.timestamp,
                    s.tracking_number,
                    s.recipient_name
                FROM tracking_history th
                JOIN shipments s ON th.shipment_id = s.id
                ORDER BY th.timestamp DESC
                LIMIT ?
            ");
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting recent trackings: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create recent_trackings table if it doesn't exist
     */
    private function createRecentTrackingsTable() {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS recent_trackings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    shipment_id VARCHAR(26) NOT NULL,
                    tracking_id VARCHAR(26) NOT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY (shipment_id),
                    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
                    FOREIGN KEY (tracking_id) REFERENCES tracking_history(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log('Error creating recent_trackings table: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ensure tracking_history table exists
     * 
     * @return bool True if table exists or was created, false on failure
     */
    private function ensureTableExists() {
        try {
            // Check if tracking_history table exists
            $checkTableStmt = $this->db->prepare("SHOW TABLES LIKE 'tracking_history'");
            $checkTableStmt->execute();
            if ($checkTableStmt->rowCount() == 0) {
                error_log('tracking_history table does not exist, creating it');
                
                // Create tracking_history table if it doesn't exist
                $createTableSql = "
                    CREATE TABLE IF NOT EXISTS tracking_history (
                        id VARCHAR(26) PRIMARY KEY,
                        shipment_id VARCHAR(26) NOT NULL,
                        status VARCHAR(50) NOT NULL,
                        location VARCHAR(255),
                        description TEXT,
                        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                        created_by VARCHAR(26),
                        updated_by VARCHAR(26),
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX (shipment_id),
                        FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                $this->db->exec($createTableSql);
                
                // Verify table was created
                $checkTableAgain = $this->db->prepare("SHOW TABLES LIKE 'tracking_history'");
                $checkTableAgain->execute();
                if ($checkTableAgain->rowCount() == 0) {
                    error_log('Failed to create tracking_history table');
                    return false;
                }
            }
            
            return true;
        } catch (PDOException $e) {
            error_log('Ensure tracking_history table exists error: ' . $e->getMessage());
            return false;
        }
    }
}
?>

