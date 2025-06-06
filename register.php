<?php
require_once 'config/session.php';
require_once 'config/auth.php';

// Check if already logged in
if (SecureSession::check()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecureSession::validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            $errors[] = 'All fields are required.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        } else {
            // Register user
            $auth = new Auth();
            $result = $auth->register($username, $email, $password);
            
            if ($result['success']) {
                $success = $result['message'];
                // Clear form data
                $_POST = [];
            } else {
                $errors = $result['errors'];
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
    <title>Register - Student Portal</title>
    <link rel="stylesheet" href="/student-portal/css/app.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h1 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .password-requirements {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.5rem;
            padding-left: 1rem;
        }
        
        .password-requirements li {
            margin-bottom: 0.25rem;
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
        <div class="register-container">
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Join the Student Portal</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success); ?></p>
                    <p><a href="login.php">Click here to login</a></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           pattern="[a-zA-Z0-9_]{3,}"
                           title="Username must be at least 3 characters and contain only letters, numbers, and underscores"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           minlength="8"
                           required>
                    <ul class="password-requirements">
                        <li>At least 8 characters long</li>
                        <li>One uppercase letter (A-Z)</li>
                        <li>One lowercase letter (a-z)</li>
                        <li>One number (0-9)</li>
                        <li>One special character (!@#$%^&*)</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           minlength="8"
                           required>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">Create Account</button>
                
                <div class="form-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
