<?php
// Start session for flash messages
require_once __DIR__ . '/../config/session.php';
SecureSession::requireLogin(); // Protect all pages by default
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Student Portal'; ?></title>
    <link rel="stylesheet" href="/student-portal/css/app.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Student Management Portal</h1>
            <nav>
                <ul>
                    <li><a href="/student-portal/index.php">All Contacts</a></li>
                    <li><a href="/student-portal/add-contact.php">Add Contact</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="/student-portal/manage-categories.php">Categories</a></li>
                    <?php endif; ?>
                    <li style="margin-left: auto;">
                        <span style="color: white; margin-right: 1rem;">
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                        <a href="/student-portal/logout.php">Logout</a>
                    </li>
                </ul>
            </nav>
        </header>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <main>