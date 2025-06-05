<?php
$pageTitle = "All Contacts";
require_once 'config/database.php';
require_once 'includes/header.php';

// Fetch all contacts
$sql = "SELECT * FROM contacts ORDER BY created_at DESC";
$result = $db->select($sql);

// Debug code
if ($result && $result->num_rows > 0) {
    $test = $result->fetch_assoc();
    echo "Debug - Image filename in DB: " . $test['profile_image'] . "<br>";
    echo "Debug - Full path: /student-portal/uploads/profiles/" . $test['profile_image'] . "<br>";
    echo "Debug - File exists: " . (file_exists(__DIR__ . '/uploads/profiles/' . $test['profile_image']) ? 'Yes' : 'No') . "<br><br>";
    $result->data_seek(0); // Reset result pointer
}
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
                            <img src="/student-portal/uploads/profiles/<?php echo htmlspecialchars($contact['profile_image']); ?>" 
                                 alt="Profile" 
                                 class="profile-image"
                                 onerror="this.src='/student-portal/assets/images/default.jpg'">
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