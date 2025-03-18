<?php
// กำหนดค่า BASE_PATH เพื่อป้องกันการเข้าถึงไฟล์โดยตรง
define('BASE_PATH', __DIR__);

session_start();
require_once 'config/config.php';
require_once 'helpers/language.php';
require_once 'helpers/auth.php';
require_once 'helpers/security.php';
require_once 'helpers/translation.php'; // Add this line

// Set default language if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'th';
    setcookie('lang', 'th', time() + (86400 * 30), "/");
}

// Handle language switching
if (isset($_GET['lang']) && ($_GET['lang'] == 'en' || $_GET['lang'] == 'th')) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_GET['lang'], time() + (86400 * 30), "/");
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Load language file
$lang = loadLanguage($_SESSION['lang']);

// Simple routing
$page = isset($_GET['page']) ? sanitizeInput($_GET['page']) : 'home';
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'index';

// Security check for valid pages
$validPages = ['home', 'login', 'register', 'dashboard', 'lots', 'shipments', 'profile', 'admin', 'auth', 'tracking', 'invoice', 'customer', 'shipmentLabel', 'shipment_labels'];
if (!in_array($page, $validPages)) {
    $page = 'home';
}

// Include controller
$controllerFile = 'controllers/' . $page . 'Controller.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controllerName = ucfirst($page) . 'Controller';
    $controller = new $controllerName();
    
    // Check if method exists
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->index();
    }
} else {
    // Fallback to home page
    require_once 'controllers/homeController.php';
    $controller = new HomeController();
    $controller->index();
}
?>

