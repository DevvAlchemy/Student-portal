<?php
require_once 'config/database.php';

// Check lockout status for a user
$username = isset($_GET['user']) ? $_GET['user'] : 'admin';

$sql = "SELECT username, email, failed_attempts, last_failed_attempt, 
        CASE 
            WHEN failed_attempts >= 5 AND TIMESTAMPDIFF(SECOND, last_failed_attempt, NOW()) < 60 
            THEN CONCAT('Locked for ', 60 - TIMESTAMPDIFF(SECOND, last_failed_attempt, NOW()), ' seconds')
            ELSE 'Not locked'
        END as lockout_status
        FROM users 
        WHERE username = ? OR email = ?";

$result = $db->select($sql, [$username, $username], "ss");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Lockout Status Check</title>
    <link rel="stylesheet" href="/student-portal/css/app.css">
</head>
<body>
    <div class="container" style="padding: 2rem;">
        <h2>Account Lockout Status</h2>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <?php $user = $result->fetch_assoc(); ?>
            <table>
                <tr>
                    <th>Username</th>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                </tr>
                <tr>
                    <th>Failed Attempts</th>
                    <td><?php echo $user['failed_attempts']; ?> / 5</td>
                </tr>
                <tr>
                    <th>Last Failed Attempt</th>
                    <td><?php echo $user['last_failed_attempt'] ?: 'Never'; ?></td>
                </tr>
                <tr>
                    <th>Lockout Status</th>
                    <td><?php echo $user['lockout_status']; ?></td>
                </tr>
            </table>
            
            <br>
            <h3>Test the Lockout:</h3>
            <ol>
                <li>Go to the <a href="login.php">login page</a></li>
                <li>Try logging in with wrong password 5 times</li>
                <li>After 5 failed attempts, the account will be locked for 1 minute</li>
                <li>Refresh this page to see the lockout countdown</li>
            </ol>
            
            <br>
            <h3>Reset Failed Attempts (for testing):</h3>
            <form method="post">
                <button type="submit" name="reset" class="btn">Reset Failed Attempts</button>
            </form>
            
            <?php
            if (isset($_POST['reset'])) {
                $resetSql = "UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL WHERE username = ?";
                $db->execute($resetSql, [$username], "s");
                echo '<p class="alert alert-success">Failed attempts reset! <a href="">Refresh page</a></p>';
            }
            ?>
            
        <?php else: ?>
            <p>User not found.</p>
        <?php endif; ?>
        
        <br>
        <p><a href="index.php" class="btn btn-secondary">Back to Home</a></p>
    </div>
</body>
</html>