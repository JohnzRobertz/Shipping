<?php
/**
 * Helper Functions
 * 
 * This file contains general helper functions used throughout the application
 */

/**
 * Sanitize input data
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
function verifyCSRFToken($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Translate text using language files
 * 
 * @param string $key Text key to translate
 * @return string Translated text
 */
// Remove or comment out the duplicate function
// function __($key) {
//     global $translations;
//     
//     if (isset($translations[$key])) {
//         return $translations[$key];
//     }
//     
//     return $key;
// }

/**
 * Check if environment is development
 * 
 * @return bool True if in development environment
 */
function isDevelopment() {
    return true; // Change this based on your environment configuration
}

/**
 * Format date to local format
 * 
 * @param string $date Date to format
 * @param string $format Format to use
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Generate a random string
 * 
 * @param int $length Length of the string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// เพิ่มฟังก์ชัน getStatusColor ถ้ายังไม่มี
function getStatusColor($status) {
    switch ($status) {
        case 'received':
            return 'secondary';
        case 'processing':
            return 'info';
        case 'in_transit':
            return 'primary';
        case 'arrived_destination':
            return 'info';
        case 'local_delivery':
            return 'warning';
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

function getStatusBadgeClass($status) {
    switch($status) {
        case 'pending': return 'secondary';
        case 'processing': return 'info';
        case 'in_transit': return 'primary';
        case 'local_delivery': return 'warning';
        case 'delivered': return 'success';
        case 'completed': return 'success';
        default: return 'secondary';
    }
}


/**
 * Format currency
 * 
 * @param float $amount The amount to format
 * @param int $decimals The number of decimal places
 * @return string The formatted amount
 */
function formatCurrency($amount, $decimals = 2) {
    return '฿' . number_format($amount, $decimals);
}

/**
 * Format weight
 * 
 * @param float $weight The weight to format
 * @param int $decimals The number of decimal places
 * @return string The formatted weight
 */
function formatWeight($weight, $decimals = 2) {
    return number_format($weight, $decimals) . ' kg';
}

/**
 * Get transport type label
 * 
 * @param string $type The transport type
 * @return string The transport type label
 */
function getTransportTypeLabel($type) {
    global $lang;
    
    switch($type) {
        case 'air': return $lang['air'] ?? 'Air';
        case 'sea': return $lang['sea'] ?? 'Sea';
        case 'land': return $lang['land'] ?? 'Land';
        default: return $type;
    }
}

