<?php
// /controllers/AuthController.php
// Authentication and Session Management

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->initSession();
    }
    
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
            
            // Set session timeout
            if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_LIFETIME)) {
                $this->logout();
            }
            $_SESSION['LAST_ACTIVITY'] = time();
        }
    }
    
    /**
     * Login user
     * @param string $username
     * @param string $password
     * @return array ['success' => bool, 'message' => string]
     */
    public function login($username, $password) {
        try {
            // Check if database exists first
            if (!$this->db->databaseExists()) {
                return [
                    'success' => false, 
                    'message' => 'Database not initialized. Please contact administrator.',
                    'redirect' => '/views/admin/db_manager.php'
                ];
            }
            
            $sql = "SELECT e.*, r.role_name 
                    FROM `employees` e 
                    JOIN `roles` r ON e.role_id = r.role_id 
                    WHERE e.username = ? AND e.status = 'Active'";
            
            $user = $this->db->fetchOne($sql, [$username]);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['employee_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name_en'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['theme_color'] = $user['theme_color_preference'];
            $_SESSION['user_language'] = $user['language_preference'];
            $_SESSION['profile_pic'] = $user['profile_pic_path'];
            $_SESSION['logged_in'] = true;
            
            return ['success' => true, 'message' => 'Login successful'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Check if user has specific role
     * @param string|array $allowedRoles
     * @return bool
     */
    public function hasRole($allowedRoles) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (is_string($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        
        return in_array($_SESSION['role_name'], $allowedRoles);
    }
    
    /**
     * Require login (redirect if not logged in)
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /index.php?error=login_required');
            exit();
        }
    }
    
    /**
     * Require specific role (redirect if not authorized)
     * @param string|array $allowedRoles
     */
    public function requireRole($allowedRoles) {
        $this->requireLogin();
        
        if (!$this->hasRole($allowedRoles)) {
            header('Location: /views/error/403.php');
            exit();
        }
    }
    
    /**
     * Generate OTP for password reset
     * @param string $email
     * @return array
     */
    public function generateOTP($email) {
        try {
            // Find user by email (assuming phone_no can also be email)
            $sql = "SELECT employee_id, full_name_en, username FROM employees WHERE phone_no = ? OR username = ?";
            $user = $this->db->fetchOne($sql, [$email, $email]);
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Generate 6-digit OTP
            $otp = sprintf("%06d", mt_rand(1, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Store OTP (you may want to create a separate OTP table)
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_user'] = $user['username'];
            $_SESSION['otp_expiry'] = $expiry;
            
            // Send OTP via email
            $this->sendOTPEmail($email, $otp, $user['full_name_en']);
            
            return ['success' => true, 'message' => 'OTP sent successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to generate OTP: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verify OTP
     * @param string $username
     * @param string $otp
     * @return array
     */
    public function verifyOTP($username, $otp) {
        if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_user']) || !isset($_SESSION['otp_expiry'])) {
            return ['success' => false, 'message' => 'No OTP request found'];
        }
        
        if ($_SESSION['otp_user'] !== $username) {
            return ['success' => false, 'message' => 'Invalid user'];
        }
        
        if (time() > strtotime($_SESSION['otp_expiry'])) {
            unset($_SESSION['otp'], $_SESSION['otp_user'], $_SESSION['otp_expiry']);
            return ['success' => false, 'message' => 'OTP expired'];
        }
        
        if ($_SESSION['otp'] !== $otp) {
            return ['success' => false, 'message' => 'Invalid OTP'];
        }
        
        // OTP verified, allow password reset
        $_SESSION['otp_verified'] = true;
        return ['success' => true, 'message' => 'OTP verified'];
    }
    
    /**
     * Reset password
     * @param string $username
     * @param string $newPassword
     * @return array
     */
    public function resetPassword($username, $newPassword) {
        if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_user'] !== $username) {
            return ['success' => false, 'message' => 'Unauthorized password reset'];
        }
        
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE employees SET password = ? WHERE username = ?";
            $this->db->query($sql, [$hashedPassword, $username]);
            
            // Clear OTP session
            unset($_SESSION['otp'], $_SESSION['otp_user'], $_SESSION['otp_expiry'], $_SESSION['otp_verified']);
            
            return ['success' => true, 'message' => 'Password reset successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to reset password: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send OTP email using SMTP
     * @param string $to
     * @param string $otp
     * @param string $name
     */
    private function sendOTPEmail($to, $otp, $name) {
        // Using PHPMailer would be ideal, but for simplicity, using mail()
        // In production, implement proper SMTP with PHPMailer
        
        $subject = "Mini HRMS - Password Reset OTP";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .otp { font-size: 32px; font-weight: bold; color: #3B82F6; letter-spacing: 5px; }
                .footer { margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Password Reset Request</h2>
                <p>Dear $name,</p>
                <p>You have requested to reset your password. Please use the following OTP:</p>
                <p class='otp'>$otp</p>
                <p>This OTP will expire in 15 minutes.</p>
                <p>If you did not request this, please ignore this email.</p>
                <div class='footer'>
                    <p>Mini HRMS System<br>Trax Inter Trade Co., Ltd.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">" . "\r\n";
        
        mail($to, $subject, $message, $headers);
    }
    
    /**
     * Update user theme color
     * @param string $color
     * @return bool
     */
    public function updateThemeColor($color) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        try {
            $sql = "UPDATE employees SET theme_color_preference = ? WHERE employee_id = ?";
            $this->db->query($sql, [$color, $_SESSION['user_id']]);
            $_SESSION['theme_color'] = $color;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Update user language preference
     * @param string $lang
     * @return bool
     */
    public function updateLanguage($lang) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (!in_array($lang, SUPPORTED_LANGUAGES)) {
            return false;
        }
        
        try {
            $sql = "UPDATE employees SET language_preference = ? WHERE employee_id = ?";
            $this->db->query($sql, [$lang, $_SESSION['user_id']]);
            $_SESSION['user_language'] = $lang;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get current user info
     * @return array|null
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $sql = "SELECT e.*, r.role_name 
                    FROM employees e 
                    JOIN roles r ON e.role_id = r.role_id 
                    WHERE e.employee_id = ?";
            return $this->db->fetchOne($sql, [$_SESSION['user_id']]);
        } catch (Exception $e) {
            return null;
        }
    }
}
?>