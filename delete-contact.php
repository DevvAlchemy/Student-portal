<?php
require_once 'config/database.php';
session_start();

// Get contact ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid contact ID";
    header("Location: index.php");
    exit();
}

// Checking if contact exists
$sql = "SELECT id, profile_image FROM contacts WHERE id = ?";
$result = $db->select($sql, [$id], "i");

if (!$result || $result->num_rows === 0) {
    $_SESSION['error'] = "Contact not found";
    header("Location: index.php");
    exit();
}

$contact = $result->fetch_assoc();

// Delete contact
$deleteSql = "DELETE FROM contacts WHERE id = ?";
$success = $db->execute($deleteSql, [$id], "i");

if ($success) {
    // Delete profile image if it's not the default
    if ($contact['profile_image'] !== 'default.jpg') {
        $imagePath = 'uploads/profiles/' . $contact['profile_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    $_SESSION['success'] = "Contact deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete contact";
}

header("Location: index.php");
exit();
?>