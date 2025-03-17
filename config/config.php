<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shipping_system');

// Application configuration
define('APP_NAME', 'NBB CARGO');
define('APP_URL', 'http://localhost/shipping');
define('APP_VERSION', '1.0.0');

// Dimensional weight divisors
define('AIR_DIVISOR', 6000); // For air freight (cm³/kg)
define('SEA_DIVISOR', 6000); // For sea freight (cm³/kg)
define('LAND_DIVISOR', 6000); // For land freight (cm³/kg)

// Lot number prefix
define('LOT_SEA_PREFIX', 'SEA');
define('LOT_AIR_PREFIX', 'AIR');
define('LOT_LAND_PREFIX', 'LAND');

// Security
define('HASH_COST', 10); // For password hashing

// เพิ่มค่าคงที่ DEBUG_MODE
define('DEBUG_MODE', false); // เปลี่ยนเป็น true เมื่อต้องการเปิดใช้งานโหมดดีบัก

// Connect to database
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>

