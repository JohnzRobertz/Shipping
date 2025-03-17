<?php
require_once 'models/User.php';

class ProfileController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
        
        // Require login
        requireLogin();
    }
    
    /**
     * Display profile page
     */
    public function index() {
        // Load view
        include 'views/profile/index.php';
    }
    
    /**
     * Update profile
     */
    public function update() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = __('error_occurred');
            header('Location: index.php?page=profile');
            exit();
        }
        
        // Validate input
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Check if email exists (excluding current user)
        if ($this->userModel->emailExists($email, $_SESSION['user_id'])) {
            $_SESSION['error'] = __('email_already_exists');
            header('Location: index.php?page=profile');
            exit();
        }
        
        // Prepare update data
        $userData = [
            'name' => $name,
            'email' => $email
        ];
        
        // If new password is provided
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = __('passwords_dont_match');
                header('Location: index.php?page=profile');
                exit();
            }
            $userData['password'] = $newPassword;
        }
        
        // Update user
        $success = $this->userModel->update($_SESSION['user_id'], $userData);
        
        if ($success) {
            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            $_SESSION['success'] = __('profile_updated');
        } else {
            $_SESSION['error'] = __('error_occurred');
        }
        
        header('Location: index.php?page=profile');
        exit();
    }
}
?>

