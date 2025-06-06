<?php
// Secure session configuration
class SecureSession {
    
    public static function init() {
        // Only start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session security
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', 1800); // 30 minutes
            
            // Set session name
            session_name('STUDENT_PORTAL_SESSION');
            
            // Start session
            session_start();
            
            // Regenerate session ID periodically
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }
    
    public static function login($userId, $username, $role) {
        self::init();
        
        // Regenerate session ID on login
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    public static function check() {
        self::init();
        
        // Check if user is logged in
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Check session timeout (30 minutes of inactivity)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            self::destroy();
            return false;
        }
        
        // Check for session hijacking
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            self::destroy();
            return false;
        }
        
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            self::destroy();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    public static function requireLogin() {
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /student-portal/login.php');
            exit();
        }
    }
    
    public static function requireAdmin() {
        self::requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Access denied. Admin privileges required.';
            header('Location: /student-portal/index.php');
            exit();
        }
    }
    
    public static function getCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function destroy() {
        self::init();
        
        // Clear session data
        $_SESSION = array();
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
    }
}
?>