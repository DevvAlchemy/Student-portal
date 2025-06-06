<?php
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
    </style>
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
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required 
                           autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember" 
                           <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    <label for="remember">Remember me for 30 days</label>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">Login</button>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
