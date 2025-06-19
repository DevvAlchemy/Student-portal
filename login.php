<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/auth.php';

// Check if already logged in
if (SecureSession::check()) {
    header('Location: index.php');
    exit();
}

// Check remember me token
$auth = new Auth();
if ($auth->checkRememberToken()) {
    header('Location: index.php');
    exit();
}

$error = '';
$remainingAttempts = 5;
$isLocked = false;
$lockoutTimeRemaining = 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecureSession::validateCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $result = $auth->login($username, $password, $remember);
            
            if ($result['success']) {
                // Redirect to intended page or dashboard
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit();
            } else {
                $error = $result['error'];
                
                // Check if it's a lockout message and get exact remaining time
                if (strpos($error, 'temporarily locked') !== false) {
                    $sql = "SELECT failed_attempts, last_failed_attempt FROM users WHERE username = ? OR email = ?";
                    $stmt = $db->select($sql, [$username, $username], "ss");
                    if ($stmt && $stmt->num_rows > 0) {
                        $user = $stmt->fetch_assoc();
                        if ($user['failed_attempts'] >= 5 && $user['last_failed_attempt']) {
                            $lockoutEnd = strtotime($user['last_failed_attempt']) + 60;
                            $lockoutTimeRemaining = max(0, $lockoutEnd - time());
                            $isLocked = true;
                            if ($lockoutTimeRemaining > 0) {
                                $error = "Account temporarily locked due to too many failed attempts.";
                            }
                        }
                    }
                } else if (strpos($error, 'Invalid username or password') !== false) {
                    // Get remaining attempts
                    $sql = "SELECT failed_attempts FROM users WHERE username = ? OR email = ?";
                    $stmt = $db->select($sql, [$username, $username], "ss");
                    if ($stmt && $stmt->num_rows > 0) {
                        $user = $stmt->fetch_assoc();
                        $remainingAttempts = max(0, 5 - $user['failed_attempts']);
                    }
                }
            }
        }
    }
}

// Initialize session for CSRF token
SecureSession::init();
$csrfToken = SecureSession::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Portal</title>
    <link rel="stylesheet" href="/student-portal/css/app.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .form-check input {
            margin-right: 0.5rem;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .form-footer a {
            color: #3498db;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .lockout-timer {
            font-weight: bold;
            color: #e74c3c;
            margin-top: 0.5rem;
        }
        
        .attempts-warning {
            font-size: 0.875rem;
            color: #e67e22;
            margin-top: 0.5rem;
            font-weight: 600;
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: #95a5a6 !important;
        }
        
        input:disabled {
            background-color: #ecf0f1;
            cursor: not-allowed;
        }
    </style>
    
    <script>
        // Countdown timer for lockout
        document.addEventListener('DOMContentLoaded', function() {
            const timerElement = document.querySelector('.lockout-timer');
            const loginForm = document.getElementById('loginForm');
            
            if (timerElement) {
                let seconds = parseInt(timerElement.getAttribute('data-seconds'));
                const countdownSpan = document.getElementById('countdown');
                const submitButton = loginForm.querySelector('button[type="submit"]');
                const inputs = loginForm.querySelectorAll('input');
                
                if (seconds > 0) {
                    const interval = setInterval(function() {
                        seconds--;
                        if (seconds > 0) {
                            countdownSpan.textContent = seconds;
                        } else {
                            clearInterval(interval);
                            // Re-enable form
                            submitButton.disabled = false;
                            submitButton.textContent = 'Login';
                            inputs.forEach(input => input.disabled = false);
                            
                            // Update message
                            timerElement.innerHTML = 'You can now try logging in again.';
                            timerElement.style.color = '#27ae60';
                            
                            // Optionally reload page
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        }
                    }, 1000);
                }
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h1>Student Portal</h1>
                <p>Please login to continue</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <p><?php echo htmlspecialchars($error); ?></p>
                    
                    <?php if ($isLocked && $lockoutTimeRemaining > 0): ?>
                        <p class="lockout-timer" data-seconds="<?php echo $lockoutTimeRemaining; ?>">
                            Please wait <span id="countdown"><?php echo $lockoutTimeRemaining; ?></span> seconds before trying again.
                        </p>
                    <?php elseif ($remainingAttempts < 5 && $remainingAttempts > 0 && strpos($error, 'Invalid') !== false): ?>
                        <p class="attempts-warning">
                            ⚠️ Warning: <?php echo $remainingAttempts; ?> attempt(s) remaining before account lockout.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                <div style="background: #f0f0f0; padding: 15px; margin: 15px 0; border: 2px solid #333; border-radius: 5px;">
                    <h4>🐛 Debug Information:</h4>
                    <?php
                    if (!empty($_POST['username'])) {
                        $debugUsername = $_POST['username'];
                        $debugSql = "SELECT username, failed_attempts, last_failed_attempt, 
                                    CASE 
                                        WHEN last_failed_attempt IS NULL THEN 'Never'
                                        ELSE last_failed_attempt 
                                    END as last_attempt_display,
                                    CASE 
                                        WHEN failed_attempts >= 5 AND last_failed_attempt IS NOT NULL 
                                        THEN TIMESTAMPDIFF(SECOND, last_failed_attempt, NOW())
                                        ELSE NULL
                                    END as seconds_since_last_attempt
                                    FROM users 
                                    WHERE username = ? OR email = ?";
                        
                        $debugResult = $db->select($debugSql, [$debugUsername, $debugUsername], "ss");
                        
                        if ($debugResult && $debugResult->num_rows > 0) {
                            $debugUser = $debugResult->fetch_assoc();
                            echo "<strong>Username:</strong> " . htmlspecialchars($debugUser['username']) . "<br>";
                            echo "<strong>Failed Attempts:</strong> " . $debugUser['failed_attempts'] . " / 5<br>";
                            echo "<strong>Last Failed Attempt:</strong> " . $debugUser['last_attempt_display'] . "<br>";
                            
                            if ($debugUser['seconds_since_last_attempt'] !== null) {
                                echo "<strong>Seconds Since Last Attempt:</strong> " . $debugUser['seconds_since_last_attempt'] . "<br>";
                                $timeRemaining = max(0, 60 - $debugUser['seconds_since_last_attempt']);
                                echo "<strong>Lockout Time Remaining:</strong> " . $timeRemaining . " seconds<br>";
                                
                                if ($debugUser['failed_attempts'] >= 5) {
                                    echo "<strong>Account Status:</strong> ";
                                    if ($timeRemaining > 0) {
                                        echo "<span style='color: red;'>LOCKED</span><br>";
                                    } else {
                                        echo "<span style='color: green;'>UNLOCKED (lockout expired)</span><br>";
                                    }
                                }
                            }
                            
                            echo "<hr>";
                            echo "<strong>Variables:</strong><br>";
                            echo "\$isLocked = " . ($isLocked ? 'true' : 'false') . "<br>";
                            echo "\$lockoutTimeRemaining = " . $lockoutTimeRemaining . "<br>";
                            echo "\$remainingAttempts = " . $remainingAttempts . "<br>";
                            echo "\$error = '" . htmlspecialchars($error) . "'<br>";
                        } else {
                            echo "User not found in database.<br>";
                        }
                    } else {
                        echo "No username submitted yet.<br>";
                    }
                    ?>
                    <hr>
                    <small>Add ?debug=1 to URL to see this info</small>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required 
                           autofocus
                           <?php echo ($isLocked && $lockoutTimeRemaining > 0) ? 'disabled' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           <?php echo ($isLocked && $lockoutTimeRemaining > 0) ? 'disabled' : ''; ?>>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember" 
                           <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>
                           <?php echo ($isLocked && $lockoutTimeRemaining > 0) ? 'disabled' : ''; ?>>
                    <label for="remember">Remember me for 30 days</label>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;" 
                        <?php echo ($isLocked && $lockoutTimeRemaining > 0) ? 'disabled' : ''; ?>>
                    <?php echo ($isLocked && $lockoutTimeRemaining > 0) ? 'Account Locked' : 'Login'; ?>
                </button>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>