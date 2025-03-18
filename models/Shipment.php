<?php
require_once 'models/BaseModel.php';
require_once 'lib/UlidGenerator.php';

class Shipment extends BaseModel {
    protected $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        parent::__construct();
    // เรียกใช้เมธอดเพื่อเพิ่มคอลัมน์ payment_status ถ้ายังไม่มี
    $this->addPaymentStatusColumnIfNotExists();
}
    
    /**
     * Generate tracking number
     * 
     * @param int|null $lotId Lot ID (optional)
     * @return string Generated tracking number
     */
    public function generateTrackingNumber($lotId = null, $defaultPrefix = 'DKC') {
        global $db;
        
        $prefix = $defaultPrefix;
        
        // ถ้ามี lotId ให้ใช้ประเภทของล็อตเป็น prefix
        if ($lotId) {
            $type = $this->getLotType($lotId);
            $prefix = $prefix.''.strtoupper(substr($type, 0, 1)); // ใช้ 3 ตัวอักษรแรกของประเภทล็อต
        }
        
        // Get current date in format YYYYMMDD
        $date = date('Ymd');
        
        // Get the last tracking number with the same prefix and date
        $sql = "SELECT tracking_number FROM shipments 
            WHERE tracking_number LIKE ? 
            ORDER BY id DESC LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(["$prefix$date%"]);
        $lastTracking = $stmt->fetchColumn();
        
        if ($lastTracking) {
            // Extract the sequence number and increment
            $sequence = intval(substr($lastTracking, -4)) + 1;
        } else {
            // Start with sequence 1
            $sequence = 1;
        }
        
        // Format the sequence number with leading zeros
        $sequenceFormatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        // Combine to form the new tracking number
        return "$prefix$date$sequenceFormatted";
    }
    
    /**
     * Calculate volumetric weight
     * 
     * @param float $length Length in cm
     * @param float $width Width in cm
     * @param float $height Height in cm
     * @param string $type Shipping type (air, sea, land)
     * @return float Volumetric weight in kg
     */
    public function calculateVolumetricWeight($length, $width, $height, $type) {
        $volume = $length * $width * $height;
        
        $divisor = SEA_DIVISOR; // Default to sea
        switch ($type) {
            case 'air':
                $divisor = AIR_DIVISOR;
                break;
            case 'land':
                $divisor = LAND_DIVISOR;
                break;
        }
        
        return $volume / $divisor;
    }
    
    /**
     * Calculate chargeable weight
     * 
     * @param float $actualWeight Actual weight in kg
     * @param float $volumetricWeight Volumetric weight in kg
     * @return float Chargeable weight in kg
     */
    public function calculateChargeableWeight($actualWeight, $volumetricWeight) {
        return max($actualWeight, $volumetricWeight);
    }
    
    /**
     * Calculate shipping price
     * 
     * @param float $chargeableWeight Chargeable weight in kg
     * @param float $pricePerKg Price per kg
     * @return float Total price
     */
    public function calculateShippingPrice($chargeableWeight, $pricePerKg) {
        return $chargeableWeight * $pricePerKg;
    }
    
    /**
     * Create a new shipment
     * 
     * @param array $data Shipment data
     * @return int|bool Shipment ID on success, false on failure
     */
   public function create($data) {
    try {
        // Debug log
        error_log('Creating shipment with data: ' . print_r($data, true));
        
        // ตรวจสอบว่ามี ID ส่งมาหรือไม่ ถ้าไม่มีให้สร้างใหม่
        if (!isset($data['id'])) {
            $data['id'] = UlidGenerator::generate();
        }
        
        // Generate tracking number if not provided
        if (empty($data['tracking_number'])) {
            $lotId = null;
            
            // ถ้ามี lot_id ให้ใช้ lot_id ที่ส่งมา
            if (isset($data['lot_id']) && !empty($data['lot_id'])) {
                $lotId = $data['lot_id'];
                error_log('Using provided lot_id: ' . $lotId);
            }
            // ถ้ามี lot_number ให้หา lot_id
            else if (isset($data['lot_number']) && !empty($data['lot_number'])) {
                $lotId = $this->getLotIdByNumber($data['lot_number']);
                error_log('Found lot_id from lot_number: ' . $lotId);
            }
            
            $data['tracking_number'] = $this->generateTrackingNumber($lotId);
            error_log('Generated tracking number: ' . $data['tracking_number']);
        }
        
        // Set default values for fields that might be missing
        $lotId = null;
        
        // ถ้ามี lot_id ให้ใช้ lot_id ที่ส่งมา
        if (isset($data['lot_id']) && !empty($data['lot_id'])) {
            $lotId = $data['lot_id'];
            error_log('Using provided lot_id: ' . $lotId);
        }
        // ถ้ามี lot_number ให้หา lot_id
        else if (isset($data['lot_number']) && !empty($data['lot_number'])) {
            $lotId = $this->getLotIdByNumber($data['lot_number']);
            error_log('Found lot_id from lot_number: ' . $data['lot_number'] . ', lot_id: ' . ($lotId ? $lotId : 'null'));
            
            // ถ้าไม่พบ lot_id ให้ลองสร้าง log เพิ่มเติมเพื่อตรวจสอบ
            if (!$lotId) {
                // ลองดึงข้อมูล lot ทั้งหมดเพื่อตรวจสอบ
                $checkLotsStmt = $this->db->prepare("SELECT id, lot_number FROM lots LIMIT 10");
                $checkLotsStmt->execute();
                $lots = $checkLotsStmt->fetchAll(PDO::FETCH_ASSOC);
                error_log('Available lots: ' . json_encode($lots));
            }
        }
        
        $description = isset($data['description']) ? $data['description'] : '';
        $status = isset($data['status']) ? $data['status'] : 'received';
        $customerCode = isset($data['customer_code']) ? $data['customer_code'] : '';
        $pricePerKg = isset($data['price']) ? $data['price'] : 0;
        
        // Calculate volumetric weight
        $volumetricWeight = $this->calculateVolumetricWeight(
            $data['length'],
            $data['width'],
            $data['height'],
            $lotId ? $this->getLotType($lotId) : 'sea' // Default to sea if no lot
        );
        
        // Calculate chargeable weight
        $chargeableWeight = $this->calculateChargeableWeight(
            $data['weight'],
            $volumetricWeight
        );
        
        // Calculate shipping price
        $totalPrice = $this->calculateShippingPrice($chargeableWeight, $pricePerKg);

        $data['created_by'] = $this->getCurrentUserId();
        
        $stmt = $this->db->prepare("
            INSERT INTO shipments (
                id, tracking_number, lot_id, customer_code, sender_name, sender_contact, sender_phone,
                receiver_name, receiver_contact, receiver_phone, weight, length, width, height,
                volumetric_weight, chargeable_weight, description, status, 
                price, total_price, created_at, created_by
            ) VALUES (
                :id, :tracking_number, :lot_id, :customer_code, :sender_name, :sender_contact, :sender_phone,
                :receiver_name, :receiver_contact, :receiver_phone, :weight, :length, :width, :height,
                :volumetric_weight, :chargeable_weight, :description, :status, 
                :price, :total_price, NOW(), :created_by
            )
        ");
       
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':tracking_number', $data['tracking_number']);
        $stmt->bindParam(':lot_id', $lotId, PDO::PARAM_STR);
        $stmt->bindParam(':customer_code', $customerCode);
        $stmt->bindParam(':sender_name', $data['sender_name']);
        $stmt->bindParam(':sender_contact', $data['sender_contact']);
        $stmt->bindParam(':sender_phone', $data['sender_phone']);
        $stmt->bindParam(':receiver_name', $data['receiver_name']);
        $stmt->bindParam(':receiver_contact', $data['receiver_contact']);
        $stmt->bindParam(':receiver_phone', $data['receiver_phone']);
        $stmt->bindParam(':weight', $data['weight']);
        $stmt->bindParam(':length', $data['length']);
        $stmt->bindParam(':width', $data['width']);
        $stmt->bindParam(':height', $data['height']);
        $stmt->bindParam(':volumetric_weight', $volumetricWeight);
        $stmt->bindParam(':chargeable_weight', $chargeableWeight);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':price', $pricePerKg);
        $stmt->bindParam(':total_price', $totalPrice);
        $stmt->bindParam(':created_by', $data['created_by']);
       
        $result = $stmt->execute();
        
        if (!$result) {
            error_log('Error creating shipment: ' . print_r($stmt->errorInfo(), true));
            return false;
        }
        
        error_log('Shipment created successfully with ID: ' . $data['id']);
        return $data['id'];
    } catch (PDOException $e) {
        error_log('Shipment creation error: ' . $e->getMessage());
        return false;
    }
}

    /**
     * Get lot type for volumetric calculation
     * 
     * @param int $lotId Lot ID
     * @return string Lot type
     */
    protected function getLotType($lotId) {
        try {
            $stmt = $this->db->prepare("SELECT lot_type FROM lots WHERE id = :lot_id");
            $stmt->bindParam(':lot_id', $lotId);
            $stmt->execute();
            $lot = $stmt->fetch(PDO::FETCH_ASSOC);
            return $lot ? $lot['lot_type'] : 'sea';
        } catch (PDOException $e) {
            error_log('Get lot type error: ' . $e->getMessage());
            return 'sea';
        }
    }
    
    /**
     * Get shipment by ID
     * 
     * @param int $id Shipment ID
     * @return array|bool Shipment data on success, false on failure
     */
    public function getById($id) {
        $sql = "SELECT s.*, l.lot_number, l.lot_type, l.origin, l.destination 
        FROM shipments s 
        LEFT JOIN lots l ON s.lot_id = l.id 
        WHERE s.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR); // เปลี่ยนจาก PDO::PARAM_INT เป็น PDO::PARAM_STR
        $stmt->execute();
        
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug: แสดงข้อมูล shipment ที่ดึงมา
        error_log("Shipment ID: " . $id);
        error_log("Shipment data: " . print_r($shipment, true));
        
        return $shipment;
    }
    
    /**
     * Get shipment by tracking number
     * 
     * @param string $trackingNumber Tracking number
     * @return array|bool Shipment data on success, false on failure
     */
    public function getByTrackingNumber($trackingNumber) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       l.lot_number, l.lot_type, l.status as lot_status,
                       l.origin, l.destination,
                       i.id as invoice_id, i.invoice_number, i.status as invoice_status,
                       is_rel.payment_status
                FROM shipments s
                LEFT JOIN lots l ON s.lot_id = l.id
                LEFT JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id
                LEFT JOIN invoices i ON is_rel.invoice_id = i.id
                WHERE s.tracking_number = :tracking_number
            ");
            $stmt->bindParam(':tracking_number', $trackingNumber);
            $stmt->execute();
            
            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($shipment) {
                // Add financial_status based on payment_status
                $shipment['financial_status'] = $this->getFinancialStatus($shipment);
            }
            
            return $shipment;
        } catch (PDOException $e) {
            error_log('Get shipment by tracking number error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all shipments with optional filtering
     * 
     * @param array $filters Optional filters
     * @return array|bool Shipments data on success, false on failure
     */
    public function getAll($filters = []) {
        try {
            $sql = "
                SELECT s.*, COALESCE(l.lot_number, '') as lot_number, COALESCE(l.lot_type, '') as lot_type,
                       i.id as invoice_id, i.invoice_number, i.status as invoice_status,
                       is_rel.payment_status
                FROM shipments s
                LEFT JOIN lots l ON s.lot_id = l.id
                LEFT JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id
                LEFT JOIN invoices i ON is_rel.invoice_id = i.id
                WHERE 1=1
            ";
            $params = [];
            
            // Apply filters
            if (!empty($filters['lot_id'])) {
                $sql .= " AND s.lot_id = :lot_id";
                $params[':lot_id'] = $filters['lot_id'];
            }
            
            if (!empty($filters['lot_number'])) {
                $sql .= " AND l.lot_number = :lot_number";
                $params[':lot_number'] = $filters['lot_number'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND s.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($filters['customer_code'])) {
                $sql .= " AND s.customer_code = :customer_code";
                $params[':customer_code'] = $filters['customer_code'];
            }
            
            // Add ordering
            $sql .= " ORDER BY s.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add financial_status to each shipment
            foreach ($shipments as &$shipment) {
                $shipment['financial_status'] = $this->getFinancialStatus($shipment);
            }
            
            return $shipments;
        } catch (PDOException $e) {
            error_log('Get shipments error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate financial status based on payment_status
     * 
     * @param array $shipment Shipment data with payment_status
     * @return string Financial status (pending, invoiced, paid)
     */
    protected function getFinancialStatus($shipment) {
        if (isset($shipment['payment_status'])) {
            if ($shipment['payment_status'] === 'paid') {
                return 'paid';
            } elseif ($shipment['payment_status'] === 'invoiced') {
                return 'invoiced';
            }
        }
        
        // If no invoice_shipments record or payment_status is null
        return 'pending';
    }
    
    /**
     * Update shipment
     * 
     * @param int $id Shipment ID
     * @param array $data Shipment data
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
    try {
        // Get current shipment data
        $current = $this->getById($id);
        
        // ถ้ามี lot_number ให้หา lot_id
        if (isset($data['lot_number']) && !empty($data['lot_number'])) {
            $lotId = $this->getLotIdByNumber($data['lot_number']);
            if ($lotId) {
                $data['lot_id'] = $lotId;
            } else {
                error_log('Lot number not found: ' . $data['lot_number']);
            }
        }
        
        // If dimensions or weight changed, recalculate volumetric and chargeable weights
        if (isset($data['length']) || isset($data['width']) || isset($data['height']) || isset($data['weight'])) {
            // Get values to use in calculation
            $length = isset($data['length']) ? $data['length'] : $current['length'];
            $width = isset($data['width']) ? $data['width'] : $current['width'];
            $height = isset($data['height']) ? $data['height'] : $current['height'];
            $weight = isset($data['weight']) ? $data['weight'] : $current['weight'];
            
            // Calculate new volumetric weight
            $volumetricWeight = $this->calculateVolumetricWeight(
                $length,
                $width,
                $height,
                $current['lot_type'] ?: 'sea'
            );
            
            // Calculate new chargeable weight
            $chargeableWeight = $this->calculateChargeableWeight(
                $weight,
                $volumetricWeight
            );
            
            // Add to data array
            $data['volumetric_weight'] = $volumetricWeight;
            $data['chargeable_weight'] = $chargeableWeight;
            
            // Recalculate price if price is set
            if (isset($data['price'])) {
                $pricePerKg = $data['price'];
            } else {
                $pricePerKg = $current['price'] ?: 0;
            }
            
            $data['total_price'] = $this->calculateShippingPrice($chargeableWeight, $pricePerKg);
        } else if (isset($data['price'])) {
            // If only price changed, recalculate total price
            $data['total_price'] = $this->calculateShippingPrice(
                $current['chargeable_weight'],
                $data['price']
            );
        }
        
        $data['updated_by'] = $this->getCurrentUserId();

        $sql = "UPDATE shipments SET ";
        $updates = [];
        $params = [':id' => $id];
        
        // Build update statement
        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'tracking_number' && $key !== 'lot_number') { // Don't update tracking number or lot_number
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        $sql .= implode(', ', $updates);
        $sql .= ", updated_at = NOW() WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log('Update shipment error: ' . $e->getMessage());
        return false;
    }
}
    
    /**
     * Delete shipment
     * 
     * @param int $id Shipment ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
    try {
        // เพิ่ม debug log
        error_log('Attempting to delete shipment with ID: ' . $id);
        
        $stmt = $this->db->prepare("DELETE FROM shipments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $result = $stmt->execute();
        
        if ($result) {
            error_log('Successfully deleted shipment with ID: ' . $id);
            return true;
        } else {
            error_log('Failed to delete shipment. PDO Error: ' . json_encode($stmt->errorInfo()));
            return false;
        }
    } catch (PDOException $e) {
        error_log('Delete shipment error: ' . $e->getMessage());
        return false;
    }
}
    
    /**
     * Update shipment status
     * 
     * @param int $id Shipment ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateStatus($id, $status) {
    try {
        $this->db->beginTransaction();
        
        // Update shipment status
        $stmt = $this->db->prepare("
            UPDATE shipments 
            SET status = :status, updated_at = NOW(), updated_by = :updated_by
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':updated_by', $this->getCurrentUserId());
        $statusUpdateResult = $stmt->execute();
        
        if (!$statusUpdateResult) {
            $this->db->rollBack();
            error_log('Failed to update shipment status. Error: ' . json_encode($stmt->errorInfo()));
            return false;
        }
        
        $this->db->commit();
        return true;
    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log('Update shipment status error: ' . $e->getMessage());
        return false;
    }
}

    public function getUnpaidShipments() {
        try {
            $sql = "SELECT DISTINCT s.*, c.name as customer_name, l.origin, l.destination 
                    FROM shipments s 
                    LEFT JOIN customers c ON s.customer_code = c.code 
                    LEFT JOIN lots l ON s.lot_id = l.id
                    WHERE s.status = 'delivered' 
                    AND s.id NOT IN (SELECT shipment_id FROM invoice_shipments)
                    ORDER BY s.customer_code, s.tracking_number";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get unpaid shipments error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get shipment tracking history
     * 
     * @param int $id Shipment ID
     * @return array|bool Tracking history on success, false on failure
     */
    public function getTrackingHistory($shipmentId) {
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
                    INDEX (shipment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $this->db->exec($createTableSql);
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM tracking_history 
            WHERE shipment_id = :shipment_id 
            ORDER BY timestamp DESC
        ");
        $stmt->bindParam(':shipment_id', $shipmentId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Get tracking history error: ' . $e->getMessage());
        return [];
    }
}
    
    /**
     * Add tracking history entry
     * 
     * @param string $shipmentId Shipment ID
     * @param string $status Status
     * @param string $location Location
     * @param string $description Description
     * @return bool True on success, false on failure
     */
    public function addTrackingHistory($shipmentId, $status, $location, $description) {
    try {
        error_log('Adding tracking history for shipment: ' . $shipmentId . ', status: ' . $status . ', location: ' . $location);
        
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
                    INDEX (shipment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $this->db->exec($createTableSql);
        }
        
        // Generate ULID for tracking history entry
        $trackingHistoryId = UlidGenerator::generate();
        
        // Insert tracking history
        $sql = "INSERT INTO tracking_history (id, shipment_id, status, location, description, timestamp, created_by) 
                VALUES (:id, :shipment_id, :status, :location, :description, NOW(), :created_by)";
        
        $stmt = $this->db->prepare($sql);
        
        // Use bindValue instead of bindParam
        $stmt->bindValue(':id', $trackingHistoryId, PDO::PARAM_STR);
        $stmt->bindValue(':shipment_id', $shipmentId, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':location', $location);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':created_by', $this->getCurrentUserId());
        
        $result = $stmt->execute();
        
        if ($result) {
            error_log('Successfully added tracking history for shipment: ' . $shipmentId . ' with ID: ' . $trackingHistoryId);
            return true;
        } else {
            error_log('Failed to add tracking history. PDO Error: ' . json_encode($stmt->errorInfo()));
            return false;
        }
    } catch (PDOException $e) {
        error_log('Exception adding tracking history: ' . $e->getMessage());
        return false;
    }
}
    
    /**
     * Validate shipment data for CSV import
     * 
     * @param array $data Shipment data from CSV
     * @param string $mode Import mode ('create' or 'update')
     * @return array Array with 'valid' (bool) and 'errors' (array) keys
     */
    public function validateImportData($data, $mode = 'create') {
        $errors = [];
        
        // Check required fields for create mode
        if ($mode === 'create') {
            $requiredFields = ['sender_name', 'sender_contact', 'receiver_name', 'receiver_contact', 'weight', 'length', 'width', 'height'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[] = "Field '$field' is required";
                }
            }
        }
        
        // Check tracking number for update mode
        if ($mode === 'update' && empty($data['tracking_number'])) {
            $errors[] = "Tracking number is required for update mode";
        }
        
        // Validate numeric fields
        $numericFields = ['weight', 'length', 'width', 'height', 'price'];
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && !is_numeric($data[$field])) {
                $errors[] = "Field '$field' must be numeric";
            }
        }
        
        // Validate date fields
        if (isset($data['handover_date']) && $data['handover_date'] !== '') {
            $date = date_create_from_format('Y-m-d', $data['handover_date']);
            if (!$date) {
                $errors[] = "Invalid date format for 'handover_date'. Use YYYY-MM-DD format.";
            }
        }
        
        // Validate status if provided
        if (isset($data['status']) && $data['status'] !== '') {
            $validStatuses = ['pending', 'received', 'processing', 'shipped', 'delivered', 'returned', 'cancelled', 'local_delivery'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = "Invalid status value. Valid values are: " . implode(', ', $validStatuses);
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Import shipment from CSV data
     * 
     * @param array $data Shipment data from CSV
     * @param string $mode Import mode ('create' or 'update')
     * @return array Result with 'success' (bool), 'message' (string), and 'id' (int) keys
     */
    public function importFromCsv($data, $mode = 'create') {
        // Validate data
        $validation = $this->validateImportData($data, $mode);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validation['errors']),
                'id' => null
            ];
        }
        
        try {
            if ($mode === 'create') {
                // Create new shipment
                $id = $this->create($data);
                if ($id) {
                    return [
                        'success' => true,
                        'message' => 'Shipment created successfully',
                        'id' => $id
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to create shipment',
                        'id' => null
                    ];
                }
            } else {
                // Update existing shipment
                $shipment = $this->getByTrackingNumber($data['tracking_number']);
                if (!$shipment) {
                    return [
                        'success' => false,
                        'message' => 'Shipment with tracking number ' . $data['tracking_number'] . ' not found',
                        'id' => null
                    ];
                }
                
                $result = $this->update($shipment['id'], $data);
                if ($result) {
                    return [
                        'success' => true,
                        'message' => 'Shipment updated successfully',
                        'id' => $shipment['id']
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to update shipment',
                        'id' => $shipment['id']
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'id' => null
            ];
        }
    }
    
    /**
     * Get lot ID by lot number
     * 
     * @param string $lotNumber Lot number
     * @return int|null Lot ID or null if not found
     */
    public function getLotIdByNumber($lotNumber) {
    try {
        error_log('Getting lot ID for lot number: ' . $lotNumber);
        
        // ตรวจสอบว่า lot_number ไม่ว่างเปล่า
        if (empty($lotNumber)) {
            error_log('Lot number is empty');
            return null;
        }
        
        // ทำความสะอาด lot_number
        $lotNumber = trim($lotNumber);
        
        // ตรวจสอบว่าตาราง lots มีอยู่จริง
        $checkTableStmt = $this->db->prepare("SHOW TABLES LIKE 'lots'");
        $checkTableStmt->execute();
        if ($checkTableStmt->rowCount() == 0) {
            error_log('Lots table does not exist');
            return null;
        }
        
        // ตรวจสอบว่ามีข้อมูลในตาราง lots หรือไม่
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM lots");
        $countStmt->execute();
        $count = $countStmt->fetchColumn();
        error_log('Total lots in database: ' . $count);
        
        // ดึงข้อมูล lot จาก lot_number
        $stmt = $this->db->prepare("SELECT id FROM lots WHERE lot_number = :lot_number");
        $stmt->bindParam(':lot_number', $lotNumber);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$result) {
            error_log('Lot number not found: ' . $lotNumber);
            
            // ลองค้นหาแบบไม่ตรงทั้งหมด
            $likeStmt = $this->db->prepare("SELECT id, lot_number FROM lots WHERE lot_number LIKE :lot_number_like LIMIT 5");
            $likeParam = '%' . $lotNumber . '%';
            $likeStmt->bindParam(':lot_number_like', $likeParam);
            $likeStmt->execute();
            $similarLots = $likeStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($similarLots) > 0) {
                error_log('Similar lot numbers found: ' . json_encode($similarLots));
            } else {
                error_log('No similar lot numbers found');
            }
            
            return null;
        }
        
        error_log('Found lot ID: ' . $result['id']);
        return $result['id'];
    } catch (PDOException $e) {
        error_log('Get lot ID by number error: ' . $e->getMessage());
        return null;
    }
}
    
    /**
     * Update domestic tracking information
     * 
     * @param int $id Shipment ID
     * @param string $carrier Domestic carrier
     * @param string $trackingNumber Domestic tracking number
     * @param string $handoverDate Handover date (YYYY-MM-DD)
     * @return bool True on success, false on failure
     */
    public function updateDomesticTracking($id, $carrier, $trackingNumber, $handoverDate = null) {
        try {
            $sql = "UPDATE shipments SET 
                    domestic_carrier = :carrier, 
                    domestic_tracking_number = :tracking_number";
            
            $params = [
                ':id' => $id,
                ':carrier' => $carrier,
                ':tracking_number' => $trackingNumber
            ];
            
            if ($handoverDate) {
                $sql .= ", handover_date = :handover_date";
                $params[':handover_date'] = $handoverDate;
            }
            
            $sql .= ", updated_at = NOW(), updated_by = :updated_by WHERE id = :id";
            $params[':updated_by'] = $this->getCurrentUserId();
            
            $stmt = $this->db->prepare($sql);
            
            // เปลี่ยนวิธีการ bind parameter
            foreach ($params as $key => $value) {
                if ($key === ':id') {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $result = $stmt->execute();
            
            // If domestic tracking is added, update status to local_delivery if not already
            if ($result && $carrier && $trackingNumber) {
                $shipment = $this->getById($id);
                if ($shipment && $shipment['status'] != 'local_delivery') {
                    $this->updateStatus($id, 'local_delivery');
                    
                    // Add tracking history entry
                    $this->addTrackingHistory(
                        $id, 
                        'local_delivery', 
                        'Shipment handed over to domestic carrier: ' . $carrier . ' with tracking number: ' . $trackingNumber
                    );
                }
            }
        
            return $result;
        } catch (PDOException $e) {
            error_log('Update domestic tracking error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the total count of shipments
     * 
     * @return int The total count of shipments
     */
    public function getCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM shipments");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting shipment count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get the count of shipments by status
     * 
     * @param string $status The status to count
     * @return int The count of shipments with the given status
     */
    public function getCountByStatus($status) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM shipments WHERE status = :status");
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting shipment count by status: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count all shipments
     * 
     * @return int Total number of shipments
     */
    public function countAll() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM shipments");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Count shipments error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count shipments by status
     * 
     * @param string $status Status to count
     * @return int Number of shipments with the given status
     */
    public function countByStatus($status) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM shipments WHERE status = :status");
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Count shipments by status error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get recent shipments
     * 
     * @param int $limit Number of shipments to retrieve
     * @param int $offset Offset for pagination
     * @return array|bool Recent shipments on success, false on failure
     */
    public function getRecent($limit = 10, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, COALESCE(l.lot_number, '') as lot_number, COALESCE(l.lot_type, '') as lot_type,
                       i.id as invoice_id, i.invoice_number, i.status as invoice_status,
                       is_rel.payment_status
                FROM shipments s
                LEFT JOIN lots l ON s.lot_id = l.id
                LEFT JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id
                LEFT JOIN invoices i ON is_rel.invoice_id = is_rel.id
                ORDER BY s.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add financial_status to each shipment
            foreach ($shipments as &$shipment) {
                $shipment['financial_status'] = $this->getFinancialStatus($shipment);
            }
            
            return $shipments;
        } catch (PDOException $e) {
            error_log('Get recent shipments error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update lot assignment for a shipment
     * 
     * @param int $id Shipment ID
     * @param int|null $lotId Lot ID or null to remove assignment
     * @return bool True on success, false on failure
     */
    public function updateLot($id, $lotId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE shipments 
                SET lot_id = :lot_id, updated_at = NOW(), updated_by = :updated_by
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR); // เปลี่ยนจาก PDO::PARAM_INT เป็น PDO::PARAM_STR
            $stmt->bindParam(':lot_id', $lotId, PDO::PARAM_STR); // เปลี่ยนจาก PDO::PARAM_INT เป็น PDO::PARAM_STR
            $stmt->bindParam(':updated_by', $this->getCurrentUserId());
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Update shipment lot error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update lot assignment for a shipment using lot number
     * 
     * @param int $id Shipment ID
     * @param string|null $lotNumber Lot number or null to remove assignment
     * @return bool True on success, false on failure
     */
    public function updateLotByNumber($id, $lotNumber) {
        try {
            // ถ้าไม่มี lot_number ให้ลบการเชื่อมโยงกับล็อต
            if (empty($lotNumber)) {
                $lotId = null;
            } else {
                // หา lot_id จาก lot_number
                $lotId = $this->getLotIdByNumber($lotNumber);
                if (!$lotId) {
                    error_log('Lot number not found: ' . $lotNumber);
                    return false;
                }
            }
            
            // อัพเดทข้อมูล
            return $this->updateLot($id, $lotId);
        } catch (PDOException $e) {
            error_log('Update shipment lot by number error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update shipment status (shipping status only, not financial status)
     * 
     * @param int $id Shipment ID
     * @param string $status New shipping status
     * @return bool True on success, false on failure
     */
    public function updateShipmentStatus($id, $status) {
        try {
            // Only update if status is a valid shipping status
            $validShippingStatuses = ['pending', 'received', 'processing', 'shipped', 'delivered', 'returned', 'cancelled', 'local_delivery'];
            
            if (!in_array($status, $validShippingStatuses)) {
                error_log('Invalid shipping status: ' . $status);
                return false;
            }
            
            $stmt = $this->db->prepare("UPDATE shipments SET status = :status, updated_at = NOW(), updated_by = :updated_by WHERE id = :id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->bindParam(':updated_by', $this->getCurrentUserId());
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Update shipment status error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get shipments by financial status
     * 
     * @param string $financialStatus Financial status (pending, invoiced, paid)
     * @return array Shipments with the given financial status
     */
    public function getByFinancialStatus($financialStatus) {
        try {
            $sql = "";
            $params = [];
            
            if ($financialStatus === 'pending') {
                // Shipments not in invoice_shipments
                $sql = "
                    SELECT s.*, COALESCE(l.lot_number, '') as lot_number, COALESCE(l.lot_type, '') as lot_type
                    FROM shipments s
                    LEFT JOIN lots l ON s.lot_id = l.id
                    WHERE s.id NOT IN (SELECT shipment_id FROM invoice_shipments)
                    ORDER BY s.created_at DESC
                ";
            } elseif ($financialStatus === 'invoiced') {
                // Shipments in invoice_shipments with payment_status = 'invoiced'
                $sql = "
                    SELECT s.*, COALESCE(l.lot_number, '') as lot_number, COALESCE(l.lot_type, '') as lot_type,
                           i.id as invoice_id, i.invoice_number, i.status as invoice_status,
                           is_rel.payment_status
                    FROM shipments s
                    LEFT JOIN lots l ON s.lot_id = l.id
                    JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id
                    JOIN invoices i ON is_rel.invoice_id = i.id
                    WHERE is_rel.payment_status = 'invoiced'
                    ORDER BY s.created_at DESC
                ";
            } elseif ($financialStatus === 'paid') {
                // Shipments in invoice_shipments with payment_status = 'paid'
                $sql = "
                    SELECT s.*, COALESCE(l.lot_number, '') as lot_number, COALESCE(l.lot_type, '') as lot_type,
                           i.id as invoice_id, i.invoice_number, i.status as invoice_status,
                           is_rel.payment_status
                    FROM shipments s
                    LEFT JOIN lots l ON s.lot_id = l.id
                    JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id
                    JOIN invoices i ON is_rel.invoice_id = i.id
                    WHERE is_rel.payment_status = 'paid'
                    ORDER BY s.created_at DESC
                ";
            } else {
                // Invalid financial status
                return [];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add financial_status to each shipment
            foreach ($shipments as &$shipment) {
                $shipment['financial_status'] = $financialStatus;
            }
            
            return $shipments;
        } catch (PDOException $e) {
            error_log('Get shipments by financial status error: ' . $e->getMessage());
            return [];
        }
    }

    // เพิ่มเมธอดสำหรับดึงสถิติการขนส่งของลูกค้า
    public function getCustomerShipmentStats($customer_code) {
        if (empty($customer_code)) {
            return [
                'total' => 0,
                'delivered' => 0,
                'in_transit' => 0,
                'pending' => 0
            ];
        }
        
        try {
            // นับจำนวนการขนส่งทั้งหมด
            $sql_total = "SELECT COUNT(*) as total FROM shipments WHERE customer_code = :customer_code";
            $stmt_total = $this->db->prepare($sql_total);
            $stmt_total->bindParam(':customer_code', $customer_code, PDO::PARAM_STR);
            $stmt_total->execute();
            $total = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
            
            // นับจำนวนการขนส่งที่ส่งถึงแล้ว
            $sql_delivered = "SELECT COUNT(*) as count FROM shipments WHERE customer_code = :customer_code AND status = 'delivered'";
            $stmt_delivered = $this->db->prepare($sql_delivered);
            $stmt_delivered->bindParam(':customer_code', $customer_code, PDO::PARAM_STR);
            $stmt_delivered->execute();
            $delivered = $stmt_delivered->fetch(PDO::FETCH_ASSOC)['count'];
            
            // นับจำนวนการขนส่งที่กำลังขนส่ง
            $sql_transit = "SELECT COUNT(*) as count FROM shipments WHERE customer_code = :customer_code AND status = 'in_transit'";
            $stmt_transit = $this->db->prepare($sql_transit);
            $stmt_transit->bindParam(':customer_code', $customer_code, PDO::PARAM_STR);
            $stmt_transit->execute();
            $in_transit = $stmt_transit->fetch(PDO::FETCH_ASSOC)['count'];
            
            // นับจำนวนการขนส่งที่รอดำเนินการ
            $sql_pending = "SELECT COUNT(*) as count FROM shipments WHERE customer_code = :customer_code AND status = 'pending'";
            $stmt_pending = $this->db->prepare($sql_pending);
            $stmt_pending->bindParam(':customer_code', $customer_code, PDO::PARAM_STR);
            $stmt_pending->execute();
            $pending = $stmt_pending->fetch(PDO::FETCH_ASSOC)['count'];
            
            return [
                'total' => $total,
                'delivered' => $delivered,
                'in_transit' => $in_transit,
                'pending' => $pending
            ];
        } catch (PDOException $e) {
            error_log("Error getting customer shipment stats: " . $e->getMessage());
            return [
                'total' => 0,
                'delivered' => 0,
                'in_transit' => 0,
                'pending' => 0
            ];
        }
    }

    public function getAllByStatus($status) {
        $sql = "SELECT * FROM shipments WHERE status = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status]);

        $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $shipments;
    }

    // เพิ่มเมธอดนี้ในคลาส Shipment
    public function getRecentShipments($limit = 5) {
        $sql = "SELECT s.*, l.lot_number, 
                (SELECT status FROM tracking_history WHERE shipment_id = s.id ORDER BY timestamp DESC LIMIT 1) as current_status
                FROM shipments s
                LEFT JOIN lots l ON s.lot_id = l.id
                ORDER BY s.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestShipments($limit = 5) {
        try {
            // Debug log
            error_log('Fetching latest shipments with limit: ' . $limit);
            
            // ตรวจสอบการเชื่อมต่อฐานข้อมูล
            if (!$this->db) {
                error_log('Database connection is null in getLatestShipments');
                return [];
            }
            
            // SQL query ที่ปรับปรุงแล้ว - ไม่ใช้ customer_id แต่ใช้ customer_code แทน
            $sql = "SELECT s.*, l.origin, l.destination, l.lot_number, l.lot_type, 
                         c.name as customer_name
                  FROM shipments s
                  LEFT JOIN lots l ON s.lot_id = l.id
                  LEFT JOIN customers c ON s.customer_code = c.code
                  ORDER BY s.created_at DESC
                  LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug log
            error_log('Found ' . count($shipments) . ' latest shipments');
            
            return $shipments;
        } catch (PDOException $e) {
            error_log("Error fetching latest shipments: " . $e->getMessage());
            return [];
        }
    }

// เพิ่มเมธอดนี้ในคลาส Shipment

public function addPaymentStatusColumnIfNotExists() {
    try {
        global $db;
        // ตรวจสอบว่ามีคอลัมน์ payment_status ในตาราง shipments หรือไม่
        $sql = "SHOW COLUMNS FROM shipments LIKE 'payment_status'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // ถ้าไม่มีคอลัมน์ payment_status ให้เพิ่มเข้าไป
            $sql = "ALTER TABLE shipments ADD COLUMN payment_status ENUM('unpaid', 'invoiced', 'paid') DEFAULT 'unpaid'";
            $db->exec($sql);
            
            // อัปเดตข้อมูลเดิม: ถ้า shipment อยู่ในใบแจ้งหนี้ที่มีสถานะ 'paid' ให้กำหนด payment_status เป็น 'paid'
            $sql = "UPDATE shipments s
                    JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id
                    JOIN invoices i ON is_rel.invoice_id = i.id
                    SET s.payment_status = 'paid'
                    WHERE i.status = 'paid'";
            $db->exec($sql);
            
            // อัปเดตข้อมูลเดิม: ถ้า shipment อยู่ในใบแจ้งหนี้ที่มีสถานะ 'unpaid' ให้กำหนด payment_status เป็น 'invoiced'
            $sql = "UPDATE shipments s
                    JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id
                    JOIN invoices i ON is_rel.invoice_id = i.id
                    SET s.payment_status = 'invoiced'
                    WHERE i.status = 'unpaid'";
            $db->exec($sql);
            
            error_log("Added payment_status column to shipments table");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error adding payment_status column to shipments: " . $e->getMessage());
        return false;
    }
}
}
?>

