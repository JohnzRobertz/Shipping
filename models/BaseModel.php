<?php
/**
 * Base Model Class
 * 
 * This class provides common functionality for all models including
 * tracking of created_by and updated_by fields
 */
class BaseModel {
    protected $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Get current user ID from session
     * 
     * @return string|null User ID or null if not logged in
     */
    protected function getCurrentUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Add created_by and updated_by to data array if not present
     * 
     * @param array $data Data array to be inserted/updated
     * @return array Modified data array with audit fields
     */
    protected function addAuditFields($data) {
        $userId = $this->getCurrentUserId();
        
        // For new records, add created_by if not present
        if (!isset($data['created_by']) && $userId) {
            $data['created_by'] = $userId;
        }
        
        // For all records, add updated_by if not present
        if (!isset($data['updated_by']) && $userId) {
            $data['updated_by'] = $userId;
        }
        
        return $data;
    }
    
    /**
     * Build SQL SET clause for updates with audit fields
     * 
     * @param array $data Data to update
     * @return array Array with 'sql' and 'params' keys
     */
    protected function buildUpdateSetClause($data) {
        $data = $this->addAuditFields($data);
        
        $setClauses = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') { // Skip ID field
                $setClauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        // Always add updated_at = NOW() if not in data
        if (!isset($data['updated_at'])) {
            $setClauses[] = "updated_at = NOW()";
        }
        
        return [
            'sql' => implode(', ', $setClauses),
            'params' => $params
        ];
    }
    
    /**
     * Log database errors
     * 
     * @param string $operation Operation being performed
     * @param string $message Error message
     * @param array $data Optional data related to the error
     */
    protected function logError($operation, $message, $data = []) {
        $userId = $this->getCurrentUserId() ?: 'anonymous';
        $dataStr = !empty($data) ? ' Data: ' . json_encode($data) : '';
        error_log("Database $operation error by user $userId: $message.$dataStr");
    }
}
?>

