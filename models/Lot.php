<?php
require_once 'lib/UlidGenerator.php';
require_once 'models/BaseModel.php';

class Lot extends BaseModel {
    protected $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        parent::__construct();
    }
    
    /**
     * Generate a new lot number
     * 
     * @param string $type Lot type (SEA, AIR, LAND)
     * @return string Generated lot number
     */
    public function generateLotNumber($type) {
        $prefix = '';
        switch ($type) {
            case 'sea':
                $prefix = LOT_SEA_PREFIX;
                break;
            case 'air':
                $prefix = LOT_AIR_PREFIX;
                break;
            case 'land':
                $prefix = LOT_LAND_PREFIX;
                break;
            default:
                $prefix = 'LOT';
        }
        
        $yearMonth = date('Ym');
        
        // Get the latest lot number for this type and year-month
        $stmt = $this->db->prepare("
            SELECT lot_number FROM lots 
            WHERE lot_number LIKE :pattern 
            ORDER BY id DESC LIMIT 1
        ");
        $pattern = $prefix . '-' . $yearMonth . '-%';
        $stmt->bindParam(':pattern', $pattern);
        $stmt->execute();
        
        $lastNumber = 0;
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $parts = explode('-', $row['lot_number']);
            $lastNumber = (int)end($parts);
        }
        
        // Generate new number
        $newNumber = $lastNumber + 1;
        return $prefix . '-' . $yearMonth . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Create a new lot
     * 
     * @param array $data Lot data
     * @return string|bool Lot ID (ULID) on success, false on failure
     */
    public function create($data) {
        try {
            // Generate ULID for new record
            $id = UlidGenerator::generate();
            
            $data['created_by'] = $this->getCurrentUserId();
            
            $stmt = $this->db->prepare("
                INSERT INTO lots (
                    id, lot_number, lot_type, departure_date, arrival_date, 
                    origin, destination, status, created_at, created_by
                ) VALUES (
                    :id, :lot_number, :lot_type, :departure_date, :arrival_date,
                    :origin, :destination, :status, NOW(), :created_by
                )
            ");
            
            // Generate lot number if not provided
            if (empty($data['lot_number'])) {
                $data['lot_number'] = $this->generateLotNumber($data['lot_type']);
            }
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':lot_number', $data['lot_number']);
            $stmt->bindParam(':lot_type', $data['lot_type']);
            $stmt->bindParam(':departure_date', $data['departure_date']);
            $stmt->bindParam(':arrival_date', $data['arrival_date']);
            $stmt->bindParam(':origin', $data['origin']);
            $stmt->bindParam(':destination', $data['destination']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':created_by', $data['created_by']);
            
            $stmt->execute();
            return $id; // Return the ULID instead of lastInsertId()
        } catch (PDOException $e) {
            error_log('Lot creation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get lot by ID
     * 
     * @param string $id Lot ID (ULID)
     * @return array|bool Lot data on success, false on failure
     */
    public function getById($id) {
        try {
            // Validate ULID format
            if (!UlidGenerator::isValid($id)) {
                error_log('Invalid ULID format: ' . $id);
                return false;
            }
            
            $stmt = $this->db->prepare("SELECT * FROM lots WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR); // เปลี่ยนจาก PDO::PARAM_INT เป็น PDO::PARAM_STR
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get lot error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get lot by lot number
     * 
     * @param string $lotNumber Lot number
     * @return array|bool Lot data on success, false on failure
     */
    public function getByLotNumber($lotNumber) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM lots WHERE lot_number = :lot_number");
            $stmt->bindParam(':lot_number', $lotNumber);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get lot error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all lots with optional filtering
     * 
     * @param array $filters Optional filters
     * @return array|bool Lots data on success, false on failure
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT * FROM lots WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['lot_type'])) {
                $sql .= " AND lot_type = :lot_type";
                $params[':lot_type'] = $filters['lot_type'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }
            
            // Search filter
            if (!empty($filters['search'])) {
                $sql .= " AND (lot_number LIKE :search OR origin LIKE :search OR destination LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Date range filters
            if (!empty($filters['date_from'])) {
                $sql .= " AND departure_date >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND departure_date <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            // Origin and destination filters
            if (!empty($filters['origin'])) {
                $sql .= " AND origin LIKE :origin";
                $params[':origin'] = '%' . $filters['origin'] . '%';
            }
            
            if (!empty($filters['destination'])) {
                $sql .= " AND destination LIKE :destination";
                $params[':destination'] = '%' . $filters['destination'] . '%';
            }
            
            // Add ordering
            if (!empty($filters['sort'])) {
                $allowedSortFields = ['id', 'lot_number', 'lot_type', 'departure_date', 'arrival_date', 'origin', 'destination', 'status', 'created_at'];
                $sort = in_array($filters['sort'], $allowedSortFields) ? $filters['sort'] : 'created_at';
                
                $order = (!empty($filters['order']) && strtoupper($filters['order']) === 'ASC') ? 'ASC' : 'DESC';
                
                $sql .= " ORDER BY $sort $order";
            } else {
                $sql .= " ORDER BY created_at DESC";
            }
            
            // Add pagination if limit is set
            if (isset($filters['limit'])) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = (int)$filters['limit'];
                
                if (isset($filters['offset'])) {
                    $sql .= " OFFSET :offset";
                    $params[':offset'] = (int)$filters['offset'];
                }
            }
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                if (in_array($key, [':limit', ':offset'])) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get lots error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update lot
     * 
     * @param string $id Lot ID (ULID)
     * @param array $data Lot data
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        try {
            // Validate ULID format
            if (!UlidGenerator::isValid($id)) {
                error_log('Invalid ULID format: ' . $id);
                return false;
            }
            
            $sql = "UPDATE lots SET ";
            $updates = [];
            $params = [':id' => $id];
            
            // Build update statement
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'lot_number') { // Don't update lot number
                    $updates[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }
            
            $sql .= implode(', ', $updates);
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
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Update lot error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete lot
     * 
     * @param string $id Lot ID (ULID)
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        try {
            // Validate ULID format
            if (!UlidGenerator::isValid($id)) {
                error_log('Invalid ULID format: ' . $id);
                return false;
            }
            
            // Check if lot has shipments
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM shipments WHERE lot_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                // Lot has shipments, cannot delete
                return false;
            }
            
            $stmt = $this->db->prepare("DELETE FROM lots WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Delete lot error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update lot status
     * 
     * @param string $id Lot ID (ULID)
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateStatus($id, $status) {
        try {
            // Validate ULID format
            if (!UlidGenerator::isValid($id)) {
                error_log('Invalid ULID format: ' . $id);
                return false;
            }
            
            $stmt = $this->db->prepare("
                UPDATE lots 
                SET status = :status, updated_at = NOW(), updated_by = :updated_by
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':updated_by', $this->getCurrentUserId());
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Update lot status error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update lot status and all shipments in the lot
     * 
     * @param string $id Lot ID (ULID)
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateStatusWithShipments($id, $status) {
        try {
            // Validate ULID format
            if (!UlidGenerator::isValid($id)) {
                error_log('Invalid ULID format: ' . $id);
                return false;
            }
            
            // Debug log
            error_log('Starting updateStatusWithShipments for lot: ' . $id . ' with status: ' . $status);
            
            $this->db->beginTransaction();
            
            // First, update the lot status
            $stmt = $this->db->prepare("
                UPDATE lots 
                SET status = :status, updated_at = NOW(), updated_by = :updated_by
                WHERE id = :id
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':updated_by', $this->getCurrentUserId());
            $lotUpdateResult = $stmt->execute();
            
            if (!$lotUpdateResult) {
                error_log('Failed to update lot status. Error: ' . json_encode($stmt->errorInfo()));
                $this->db->rollBack();
                return false;
            }
            
            error_log('Lot status updated successfully');
            
            // Map lot status to shipment status
            $shipmentStatus = $this->mapLotStatusToShipmentStatus($status);
            error_log('Mapped shipment status: ' . $shipmentStatus);
            
            // Update all shipments in this lot
            $stmt = $this->db->prepare("
                UPDATE shipments 
                SET status = :status, updated_at = NOW(), updated_by = :updated_by
                WHERE lot_id = :lot_id
            ");
            $stmt->bindValue(':lot_id', $id, PDO::PARAM_STR);
            $stmt->bindValue(':status', $shipmentStatus);
            $stmt->bindValue(':updated_by', $this->getCurrentUserId());
            $shipmentsUpdateResult = $stmt->execute();
            
            if (!$shipmentsUpdateResult) {
                error_log('Failed to update shipments status. Error: ' . json_encode($stmt->errorInfo()));
                $this->db->rollBack();
                return false;
            }
            
            error_log('Shipments status updated successfully');
            
            // Get all shipments in this lot
            $stmt = $this->db->prepare("
                SELECT id FROM shipments WHERE lot_id = :lot_id
            ");
            $stmt->bindValue(':lot_id', $id, PDO::PARAM_STR);
            $stmt->execute();
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log('Found ' . count($shipments) . ' shipments in lot');
            
            if (count($shipments) === 0) {
                error_log('No shipments found in lot, committing transaction');
                $this->db->commit();
                return true; // No shipments to update, but lot was updated successfully
            }
            
            // Get lot info for location
            $stmt = $this->db->prepare("
                SELECT origin, destination FROM lots WHERE id = :id
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            $lot = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Determine location based on status
            $location = '';
            switch ($status) {
                case 'received':
                    $location = $lot['origin'];
                    break;
                case 'in_transit':
                    $location = 'In Transit';
                    break;
                case 'arrived_destination':
                    $location = $lot['destination'];
                    break;
                default:
                    $location = '';
            }
            
            error_log('Location determined: ' . $location);
            
            // Add tracking history for each shipment
            foreach ($shipments as $shipment) {
                $shipmentId = $shipment['id'];
                
                error_log('Adding tracking history for shipment: ' . $shipmentId);
                
                // Generate ULID for tracking history entry
                $trackingHistoryId = UlidGenerator::generate();
                
                // Add description based on status
                $description = "Status updated to " . $shipmentStatus . " (Lot status: " . $status . ")";
                
                // Insert tracking history
                $stmt = $this->db->prepare("
                    INSERT INTO tracking_history (
                        id, shipment_id, status, location, description, timestamp
                    ) VALUES (
                        :id, :shipment_id, :status, :location, :description, NOW()
                    )
                ");
                $stmt->bindValue(':id', $trackingHistoryId, PDO::PARAM_STR);
                $stmt->bindValue(':shipment_id', $shipmentId, PDO::PARAM_STR);
                $stmt->bindValue(':status', $shipmentStatus);
                $stmt->bindValue(':location', $location);
                $stmt->bindValue(':description', $description);
                
                $historyResult = $stmt->execute();
                
                if (!$historyResult) {
                    error_log('Failed to add tracking history for shipment: ' . $shipmentId . '. Error: ' . json_encode($stmt->errorInfo()));
                    // Continue with other shipments even if one fails
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Update lot status with shipments error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Map lot status to shipment status
     * 
     * @param string $lotStatus Lot status
     * @return string Shipment status
     */
    private function mapLotStatusToShipmentStatus($lotStatus) {
        switch ($lotStatus) {
            case 'received':
                return 'received';
            case 'in_transit':
                return 'in_transit';
            case 'arrived_destination':
                return 'in_transit'; // Still in transit until delivered
            case 'local_delivery':
                return 'out_for_delivery';
            case 'delivered':
                return 'delivered';
            default:
                return 'received';
        }
    }

    /**
     * Get the total count of lots
     * 
     * @return int The total count of lots
     */
    public function getCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lots");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting lot count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count lots with filters
     * 
     * @param array $filters Optional filters
     * @return int Total number of lots matching filters
     */
    public function countAll($filters = []) {
        try {
            $sql = "SELECT COUNT(*) FROM lots WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['lot_type'])) {
                $sql .= " AND lot_type = :lot_type";
                $params[':lot_type'] = $filters['lot_type'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }
            
            // Search filter
            if (!empty($filters['search'])) {
                $sql .= " AND (lot_number LIKE :search OR origin LIKE :search OR destination LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Date range filters
            if (!empty($filters['date_from'])) {
                $sql .= " AND departure_date >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND departure_date <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            // Origin and destination filters
            if (!empty($filters['origin'])) {
                $sql .= " AND origin LIKE :origin";
                $params[':origin'] = '%' . $filters['origin'] . '%';
            }
            
            if (!empty($filters['destination'])) {
                $sql .= " AND destination LIKE :destination";
                $params[':destination'] = '%' . $filters['destination'] . '%';
            }
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Count lots error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count lots by status
     * 
     * @param string $status Status to count
     * @return int Number of lots with the given status
     */
    public function countByStatus($status) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lots WHERE status = :status");
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Count lots by status error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get recent lots
     * 
     * @param int $limit Number of lots to retrieve
     * @param int $offset Offset for pagination
     * @return array|bool Recent lots on success, false on failure
     */
    public function getRecent($limit = 5, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM lots
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get recent lots error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get distinct origins for autocomplete
     * 
     * @return array|bool Array of distinct origins on success, false on failure
     */
    public function getDistinctOrigins() {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT origin FROM lots 
                WHERE origin != '' 
                ORDER BY origin ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log('Get distinct origins error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get distinct destinations for autocomplete
     * 
     * @return array|bool Array of distinct destinations on success, false on failure
     */
    public function getDistinctDestinations() {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT destination FROM lots 
                WHERE destination != '' 
                ORDER BY destination ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log('Get distinct destinations error: ' . $e->getMessage());
            return false;
        }
    }
}
?>