<?php
class Shipment {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
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
        // Generate tracking number if not provided
        if (empty($data['tracking_number'])) {
            $lotId = isset($data['lot_id']) && !empty($data['lot_id']) ? $data['lot_id'] : null;
            $data['tracking_number'] = $this->generateTrackingNumber($lotId);
        }
        
        // Set default values for fields that might be missing
        $lotId = isset($data['lot_id']) && !empty($data['lot_id']) ? $data['lot_id'] : null;
        $description = isset($data['description']) ? $data['description'] : '';
        $status = isset($data['status']) ? $data['status'] : 'received';
        $customerCode = isset($data['customer_code']) ? $data['customer_code'] : '';
        $pricePerKg = isset($data['price']) ? $data['price'] : 0; // แก้จาก price_per_kg เป็น price
        
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
        
        $stmt = $this->db->prepare("
    INSERT INTO shipments (
        tracking_number, lot_id, customer_code, sender_name, sender_contact, sender_phone,
        receiver_name, receiver_contact, receiver_phone, weight, length, width, height,
        volumetric_weight, chargeable_weight, description, status, 
        price, total_price, created_at
    ) VALUES (
        :tracking_number, :lot_id, :customer_code, :sender_name, :sender_contact, :sender_phone,
        :receiver_name, :receiver_contact, :receiver_phone, :weight, :length, :width, :height,
        :volumetric_weight, :chargeable_weight, :description, :status, 
        :price, :total_price, NOW()
    )
");
       
       $stmt->bindParam(':tracking_number', $data['tracking_number']);
       $stmt->bindParam(':lot_id', $lotId, PDO::PARAM_INT);
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
       $stmt->bindParam(':price', $pricePerKg); // แก้จาก price_per_kg เป็น price
       $stmt->bindParam(':total_price', $totalPrice);
       
       $stmt->execute();
       return $this->db->lastInsertId();
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
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
        
        $sql = "UPDATE shipments SET ";
        $updates = [];
        $params = [':id' => $id];
        
        // Build update statement
        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'tracking_number') { // Don't update tracking number
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
            $stmt = $this->db->prepare("DELETE FROM shipments WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
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
            $stmt = $this->db->prepare("
                UPDATE shipments 
                SET status = :status, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            return $stmt->execute();
        } catch (PDOException $e) {
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
     * @param int $shipmentId Shipment ID
     * @param string $status Status
     * @param string $location Location
     * @param string $description Description
     * @return int|bool History ID on success, false on failure
     */
    public function addTrackingHistory($shipmentId, $status, $location, $description) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO tracking_history (
                    shipment_id, status, location, description, timestamp
                ) VALUES (
                    :shipment_id, :status, :location, :description, NOW()
                )
            ");
            
            $stmt->bindParam(':shipment_id', $shipmentId);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':description', $description);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Add tracking history error: ' . $e->getMessage());
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
            $stmt = $this->db->prepare("SELECT id FROM lots WHERE lot_number = :lot_number");
            $stmt->bindParam(':lot_number', $lotNumber);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
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
            
            $sql .= ", updated_at = NOW() WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
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
                        'Local Delivery', 
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
                LEFT JOIN invoices i ON is_rel.invoice_id = i.id
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
                SET lot_id = :lot_id, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':lot_id', $lotId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Update shipment lot error: ' . $e->getMessage());
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
            
            $stmt = $this->db->prepare("UPDATE shipments SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);
            return true;
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
}
?>

