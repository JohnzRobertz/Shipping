<?php
/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user ID with fallback to SYSTEM
 * 
 * @return string User ID or SYSTEM if not logged in
 */
function getCurrentUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Debug session data
    error_log('Session data in getCurrentUserId: ' . json_encode($_SESSION));
    
    // Return user ID from session if available
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        error_log('Returning user ID from session: ' . $_SESSION['user_id']);
        return $_SESSION['user_id'];
    }
    
    // If no user ID in session, return SYSTEM as fallback
    error_log('No user ID in session, returning SYSTEM as fallback');
    return 'SYSTEM';
}

/**
 * Require login to access page
 * 
 * @return void Redirects to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit();
    }
}

/**
 * Require admin role to access page
 * 
 * @return void Redirects to dashboard if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php?page=dashboard');
        exit();
    }
}
?>

