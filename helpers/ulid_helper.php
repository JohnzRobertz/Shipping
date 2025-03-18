<?php
require_once 'lib/UlidGenerator.php';

/**
 * Generate a new ULID
 * 
 * @return string The generated ULID
 */
function generate_ulid() {
    return UlidGenerator::generate();
}

/**
 * Check if a string is a valid ULID
 * 
 * @param string $ulid The string to check
 * @return bool True if the string is a valid ULID, false otherwise
 */
function is_valid_ulid($ulid) {
    return UlidGenerator::isValid($ulid);
}

/**
 * Get the timestamp from a ULID
 * 
 * @param string $ulid The ULID to extract the timestamp from
 * @return int|false The timestamp in milliseconds, or false if the ULID is invalid
 */
function get_ulid_timestamp($ulid) {
    return UlidGenerator::getTimestamp($ulid);
}

/**
 * Generate a ULID from a specific timestamp
 * 
 * @param int $timestamp The timestamp in milliseconds
 * @return string The generated ULID
 */
function generate_ulid_from_timestamp($timestamp) {
    return UlidGenerator::fromTimestamp($timestamp);
}

/**
 * Validate ULID from request parameter
 * 
 * @param string $ulid The ULID to validate
 * @param string $redirect_url URL to redirect to if validation fails
 * @param string $error_message Error message to display if validation fails
 * @return string The validated ULID
 */
function validate_ulid_param($ulid, $redirect_url = 'index.php', $error_message = 'Invalid ID format') {
    if (!is_valid_ulid($ulid)) {
        $_SESSION['error'] = $error_message;
        header('Location: ' . $redirect_url);
        exit;
    }
    return $ulid;
}

