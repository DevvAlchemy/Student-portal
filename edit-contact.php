<?php
$pageTitle = "Edit Contact";
require_once 'config/database.php';
require_once 'includes/header.php';

// Getting contact ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid contact ID";
    header("Location: index.php");
    exit();
}

// Fetching contact data
$sql = "SELECT * FROM contacts WHERE id = ?";
$result = $db->select($sql, [$id], "i");

if (!$result || $result->num_rows === 0) {
    $_SESSION['error'] = "Contact not found";
    header("Location: index.php");
    exit();
}

$contact = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate required fields
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists (excluding current contact)
    if (empty($errors)) {
        $checkSql = "SELECT id FROM contacts WHERE email = ? AND id != ?";
        $checkResult = $db->select($checkSql, [$email, $id], "si");
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // Handle file upload
    $profileImage = $contact['profile_image'];
    if (!empty($_FILES['profile_image']['name']) && empty($errors)) {
        $uploadDir = 'uploads/profiles/';
        $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                // Delete old image if it's not the default
                if ($contact['profile_image'] !== 'default.jpg' && file_exists($uploadDir . $contact['profile_image'])) {
                    unlink($uploadDir . $contact['profile_image']);
                }
                $profileImage = $newFileName;
            } else {
                $errors[] = "Failed to upload image";
            }
        } else {
            $errors[] = "Invalid image format. Allowed: JPG, JPEG, PNG, GIF";
        }
    }
    
    // Update contact if no errors
    if (empty($errors)) {
        $sql = "UPDATE contacts SET name = ?, email = ?, phone = ?, address = ?, profile_image = ? WHERE id = ?";
        $success = $db->execute($sql, [$name, $email, $phone, $address, $profileImage, $id], "sssssi");
        
        if ($success) {
            $_SESSION['success'] = "Contact updated successfully!";
            header("Location: view-contact.php?id=" . $id);
            exit();
        } else {
            $errors[] = "Failed to update contact";
        }
    }
}
?>

<h2>Edit Contact</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" 
               value="<?php echo htmlspecialchars($_POST['name'] ?? $contact['name']); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" 
               value="<?php echo htmlspecialchars($_POST['email'] ?? $contact['email']); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" 
               value="<?php echo htmlspecialchars($_POST['phone'] ?? $contact['phone']); ?>">
    </div>
    
    <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address"><?php echo htmlspecialchars($_POST['address'] ?? $contact['address']); ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="profile_image">Profile Image</label>
        <div>
            <img src="uploads/profiles/<?php echo htmlspecialchars($contact['profile_image']); ?>" 
                 alt="Current Profile" 
                 class="profile-image-large"
                 onerror="this.src='assets/images/default.jpg'">
        </div>
        <input type="file" id="profile_image" name="profile_image" accept="image/*">
        <small>Leave empty to keep current image</small>
    </div>
    
    <button type="submit" class="btn">Update Contact</button>
    <a href="view-contact.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
</form>

<?php 
require_once 'includes/footer.php';
?>