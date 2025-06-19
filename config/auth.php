<?php
require_once 'database.php';
require_once 'session.php';

class Auth {
    private $db;
    private $maxLoginAttempts = 5;
    private $lockoutTime = 60; // 1 minute (60 seconds)
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function register($username, $email, $password) {
        // Validate inputs
        $errors = $this->validateRegistration($username, $email, $password);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        try {
            $success = $this->db->execute($sql, [$username, $email, $passwordHash], "sss");
            
            if ($success) {
                return ['success' => true, 'message' => 'Registration successful! Please login.'];
            } else {
                return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
            }
        } catch (Exception $e) {
            // Check for duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'username') !== false) {
                    return ['success' => false, 'errors' => ['Username already exists.']];
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    return ['success' => false, 'errors' => ['Email already registered.']];
                }
            }
            return ['success' => false, 'errors' => ['An error occurred. Please try again.']];
        }
    }
    
    public function login($username, $password, $remember = false) {
        // Debug mode
        $debug = isset($_GET['debug']) && $_GET['debug'] === '1';
        
        // Check if account is locked
        if ($this->isAccountLocked($username)) {
            if ($debug) {
                error_log("Auth Debug: Account is locked for user: $username");
            }
            return ['success' => false, 'error' => 'Account temporarily locked due to too many failed attempts. Please try again later.'];
        }
        
        // Get user by username or email
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
        $result = $this->db->select($sql, [$username, $username], "ss");
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($debug) {
                error_log("Auth Debug: User found - Failed attempts: " . $user['failed_attempts']);
            }
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Reset failed attempts
                $this->resetFailedAttempts($user['id']);
                
                // Update last login
                $this->updateLastLogin($user['id']);
                
                // Create session
                SecureSession::login($user['id'], $user['username'], $user['role']);
                
                // Handle remember me
                if ($remember) {
                    $this->setRememberToken($user['id']);
                }
                
                return ['success' => true];
            }
        }
        
        // Record failed attempt
        $this->recordFailedAttempt($username);
        
        if ($debug) {
            error_log("Auth Debug: Failed login attempt recorded for: $username");
        }
        
        return ['success' => false, 'error' => 'Invalid username or password.'];
    }
    
    public function logout() {
        // Clear remember me cookie if exists
        if (isset($_COOKIE['remember_token'])) {
            $this->clearRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Destroy session
        SecureSession::destroy();
    }
    
    public function checkRememberToken() {
        if (isset($_COOKIE['remember_token']) && !SecureSession::check()) {
            $token = $_COOKIE['remember_token'];
            
            $sql = "SELECT id, username, role FROM users WHERE remember_token = ? AND status = 'active'";
            $result = $this->db->select($sql, [$token], "s");
            
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Create new session
                SecureSession::login($user['id'], $user['username'], $user['role']);
                
                // Generate new remember token
                $this->setRememberToken($user['id']);
                
                return true;
            }
        }
        return false;
    }
    
    private function validateRegistration($username, $email, $password) {
        $errors = [];
        
        // Validate username
        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long.";
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = "Username can only contain letters, numbers, and underscores.";
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        
        // Validate password
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = "Password must contain at least one special character.";
        }
        
        return $errors;
    }
    
    private function isAccountLocked($username) {
        $sql = "SELECT failed_attempts, last_failed_attempt FROM users WHERE username = ? OR email = ?";
        $result = $this->db->select($sql, [$username, $username], "ss");
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Debug logging
            if (isset($_GET['debug']) && $_GET['debug'] === '1') {
                error_log("Auth Debug - isAccountLocked: Failed attempts = " . $user['failed_attempts'] . 
                         ", Last attempt = " . ($user['last_failed_attempt'] ?? 'NULL'));
            }
            
            if ($user['failed_attempts'] >= $this->maxLoginAttempts && $user['last_failed_attempt']) {
                $lockoutEnd = strtotime($user['last_failed_attempt']) + $this->lockoutTime;
                $currentTime = time();
                
                if (isset($_GET['debug']) && $_GET['debug'] === '1') {
                    error_log("Auth Debug - Lockout check: Current time = $currentTime, Lockout ends = $lockoutEnd, Remaining = " . ($lockoutEnd - $currentTime));
                }
                
                if ($currentTime < $lockoutEnd) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function recordFailedAttempt($username) {
        $sql = "UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_attempt = NOW() 
                WHERE username = ? OR email = ?";
        $this->db->execute($sql, [$username, $username], "ss");
    }
    
    private function resetFailedAttempts($userId) {
        $sql = "UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL WHERE id = ?";
        $this->db->execute($sql, [$userId], "i");
    }
    
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->db->execute($sql, [$userId], "i");
    }
    
    private function setRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        
        $sql = "UPDATE users SET remember_token = ? WHERE id = ?";
        $this->db->execute($sql, [$token, $userId], "si");
        
        // Set cookie for 30 days
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', isset($_SERVER['HTTPS']), true);
    }
    
    private function clearRememberToken($token) {
        $sql = "UPDATE users SET remember_token = NULL WHERE remember_token = ?";
        $this->db->execute($sql, [$token], "s");
    }
}
?>