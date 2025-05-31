<?php
$pageTitle = "All Contacts";
require_once 'config/database.php';
require_once 'includes/header.php';

// Fetch all contacts
$sql = "SELECT * FROM contacts ORDER BY created_at DESC";
$result = $db->select($sql);
?>

<h2>All Contacts</h2>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($contact = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="uploads/profiles/<?php echo htmlspecialchars($contact['profile_image']); ?>" 
                                 alt="Profile" 
                                 class="profile-image"
                                 onerror="this.src='assets/images/default.jpg'">
                        </td>
                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                        <td>
                            <div class="actions">
                                <a href="view-contact.php?id=<?php echo $contact['id']; ?>" class="btn btn-secondary">View</a>
                                <a href="edit-contact.php?id=<?php echo $contact['id']; ?>" class="btn">Edit</a>
                                <a href="delete-contact.php?id=<?php echo $contact['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this contact?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>No contacts found. <a href="add-contact.php">Add your first contact</a></p>
<?php endif; ?>

<?php 
require_once 'includes/footer.php';
?>