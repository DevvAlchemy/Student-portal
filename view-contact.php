<?php
$pageTitle = "View Contact";
require_once 'config/database.php';
require_once 'includes/header.php';

// Get contact ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid contact ID";
    header("Location: index.php");
    exit();
}

// Fetch contact data
$sql = "SELECT * FROM contacts WHERE id = ?";
$result = $db->select($sql, [$id], "i");

if (!$result || $result->num_rows === 0) {
    $_SESSION['error'] = "Contact not found";
    header("Location: index.php");
    exit();
}

$contact = $result->fetch_assoc();
?>

<h2>Contact Details</h2>

<div style="text-align: center;">
    <img src="uploads/profiles/<?php echo htmlspecialchars($contact['profile_image']); ?>" 
         alt="Profile" 
         class="profile-image-large"
         onerror="this.src='assets/images/default.jpg'">
</div>

<div class="contact-details">
    <div class="detail-row">
        <div class="detail-label">Name:</div>
        <div><?php echo htmlspecialchars($contact['name']); ?></div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Email:</div>
        <div><?php echo htmlspecialchars($contact['email']); ?></div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Phone:</div>
        <div><?php echo htmlspecialchars($contact['phone'] ?: 'Not provided'); ?></div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Address:</div>
        <div><?php echo nl2br(htmlspecialchars($contact['address'] ?: 'Not provided')); ?></div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Added On:</div>
        <div><?php echo date('F j, Y g:i A', strtotime($contact['created_at'])); ?></div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">Last Updated:</div>
        <div><?php echo date('F j, Y g:i A', strtotime($contact['updated_at'])); ?></div>
    </div>
</div>

<div style="margin-top: 2rem;">
    <a href="edit-contact.php?id=<?php echo $contact['id']; ?>" class="btn">Edit Contact</a>
    <a href="delete-contact.php?id=<?php echo $contact['id']; ?>" 
       class="btn btn-danger"
       onclick="return confirm('Are you sure you want to delete this contact?')">Delete Contact</a>
    <a href="index.php" class="btn btn-secondary">Back to List</a>
</div>

<?php 
require_once 'includes/footer.php';
?>