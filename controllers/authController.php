<?php
require_once 'models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Display login form
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            header('Location: index.php?page=dashboard');
            exit();
        }
        
        // Load view
        include 'views/auth/login.php';
    }
    
    /**
     * Process login
     */
    public function authenticate() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = __('error_occurred');
            header('Location: index.php?page=auth&action=login');
            exit();
        }
        
        // Validate input
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password']; // Don't sanitize password
        
        // For debugging
        error_log("Login attempt: Email: $email, Password length: " . strlen($password));
        
        // Authenticate user
        $user = $this->userModel->authenticate($email, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Set remember me cookie if requested
            if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
                $token = bin2hex(random_bytes(32));
                $this->userModel->storeRememberToken($user['id'], $token);
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
            }
            
            $_SESSION['success'] = __('login_success');
            header('Location: index.php?page=dashboard');
        } else {
            $_SESSION['error'] = __('login_failed');
            header('Location: index.php?page=auth&action=login');
        }
        exit();
    }
    
    /**
     * Process logout
     */
    public function logout() {
        // Clear session
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            $this->userModel->clearRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, "/");
        }
        
        $_SESSION['success'] = __('logout_success');
        header('Location: index.php');
        exit();
    }
    
    /**
     * Display registration form
     */
    public function register() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            header('Location: index.php?page=dashboard');
            exit();
        }
        
        // Load view
        include 'views/auth/register.php';
    }
    
    /**
     * Process registration
     */
    public function store() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = __('error_occurred');
            header('Location: index.php?page=auth&action=register');
            exit();
        }
        
        // Validate input
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password']; // Don't sanitize password
        $confirmPassword = $_POST['confirm_password']; // Don't sanitize password
        
        // Check if passwords match
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = __('passwords_dont_match');
            header('Location: index.php?page=auth&action=register');
            exit();
        }
        
        // Check if email already exists
        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = __('email_already_exists');
            header('Location: index.php?page=auth&action=register');
            exit();
        }
        
        // Create user
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'user' // Default role
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            $_SESSION['success'] = __('register_success');
            header('Location: index.php?page=auth&action=login');
        } else {
            $_SESSION['error'] = __('error_occurred');
            header('Location: index.php?page=auth&action=register');
        }
        exit();
    }
}
?>
