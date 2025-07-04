<?php
$pageTitle = "Add Contact";
require_once 'config/database.php';
require_once 'config/category.php';
require_once 'includes/header.php';

// Get categories for dropdown
$categoryObj = new Category();
$categories = $categoryObj->getAllCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    
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
    
    // Check if email already exists
    if (empty($errors)) {
        $checkSql = "SELECT id FROM contacts WHERE email = ?";
        $checkResult = $db->select($checkSql, [$email], "s");
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // Handle file upload
    $profileImage = 'default.jpg';
    if (!empty($_FILES['profile_image']['name']) && empty($errors)) {
        $uploadDir = 'uploads/profiles/';
        $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                $profileImage = $newFileName;
            } else {
                $errors[] = "Failed to upload image";
            }
        } else {
            $errors[] = "Invalid image format. Allowed: JPG, JPEG, PNG, GIF";
        }
    }
    
    // Insert contact if no errors
    if (empty($errors)) {
        $sql = "INSERT INTO contacts (name, email, phone, address, category_id, profile_image) VALUES (?, ?, ?, ?, ?, ?)";
        $success = $db->execute($sql, [$name, $email, $phone, $address, $categoryId, $profileImage], "ssssss");
        
        if ($success) {
            $_SESSION['success'] = "Contact added successfully!";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Failed to add contact";
        }
    }
}
?>

<h2>Add New Contact</h2>

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
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">Select a category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" 
                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="profile_image">Profile Image</label>
        <input type="file" id="profile_image" name="profile_image" accept="image/*">
    </div>
    
    <button type="submit" class="btn">Add Contact</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php 
require_once 'includes/footer.php';
?>