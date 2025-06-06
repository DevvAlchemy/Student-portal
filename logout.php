<?php
require_once 'config/auth.php';

// Logout user
$auth = new Auth();
$auth->logout();

// Redirect to login page
header('Location: login.php');
exit();
?>