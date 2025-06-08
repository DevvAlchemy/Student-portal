<?php
$pageTitle = "Manage Categories";
require_once 'config/database.php';
require_once 'config/category.php';
require_once 'config/session.php';
require_once 'includes/header.php';

// Only admins can manage categories
SecureSession::requireAdmin();

$categoryObj = new Category();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !SecureSession::validateCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $name = trim($_POST['name'] ?? '');
                $color = $_POST['color'] ?? '#3498db';
                $icon = $_POST['icon'] ?? 'folder';
                
                $result = $categoryObj->createCategory($name, $color, $icon);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $result = $categoryObj->deleteCategory($id);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Get all categories with counts
$categories = $categoryObj->getAllCategories();
$counts = $categoryObj->getCategoryCounts();
?>

<h2>Manage Categories</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Add New Category Form -->
<div class="category-form-section">
    <h3>Add New Category</h3>
    <form method="POST" class="category-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(SecureSession::getCsrfToken()); ?>">
        <input type="hidden" name="action" value="create">
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="color">Color</label>
                <input type="color" id="color" name="color" value="#3498db">
            </div>
            
            <div class="form-group">
                <label for="icon">Icon</label>
                <select id="icon" name="icon">
                    <option value="folder">📁 Folder</option>
                    <option value="users">👥 Users</option>
                    <option value="heart">❤️ Heart</option>
                    <option value="briefcase">💼 Briefcase</option>
                    <option value="book">📚 Book</option>
                    <option value="tag">🏷️ Tag</option>
                    <option value="star">⭐ Star</option>
                    <option value="home">🏠 Home</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Add Category</button>
            </div>
        </div>
    </form>
</div>

<!-- Existing Categories -->
<div class="categories-list">
    <h3>Existing Categories</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Color</th>
                <th>Contacts</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td>
                        <span class="category-badge" style="background-color: <?php echo htmlspecialchars($category['color']); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($category['color']); ?></td>
                    <td><?php echo $counts[$category['id']] ?? 0; ?> contacts</td>
                    <td>
                        <?php if ($category['id'] > 5): // Only allow deletion of non-default categories ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure? Contacts in this category will become uncategorized.');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(SecureSession::getCsrfToken()); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">Default category</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.category-form-section {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.form-row {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
}

.form-row .form-group {
    flex: 1;
    min-width: 200px;
}

.categories-list {
    margin-top: 2rem;
}

.text-muted {
    color: #6c757d;
    font-size: 0.875rem;
}
</style>

<?php 
require_once 'includes/footer.php';
?>