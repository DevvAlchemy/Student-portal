<?php
$pageTitle = "All Contacts";
require_once 'config/database.php';
require_once 'config/category.php';
require_once 'includes/header.php';

// Initialize category class
$categoryObj = new Category();
$categories = $categoryObj->getAllCategories();

// Handle search and category filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build SQL query with search and category filters
$sql = "SELECT c.*, cat.name as category_name, cat.color as category_color 
        FROM contacts c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        WHERE 1=1";

$params = [];
$types = "";

// Add search condition if search term exists
if (!empty($search)) {
    $sql .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

// Add category filter if selected
if ($categoryFilter > 0) {
    $sql .= " AND c.category_id = ?";
    $params[] = $categoryFilter;
    $types .= "i";
}

$sql .= " ORDER BY c.created_at DESC";

// Execute query
if (!empty($params)) {
    $result = $db->select($sql, $params, $types);
} else {
    $result = $db->select($sql);
}
?>

<h2>All Contacts</h2>

<!-- Search and Filter Section -->
<div class="search-filter-section">
    <form method="GET" action="" class="search-form">
        <div class="search-bar">
            <input type="text" 
                   name="search" 
                   placeholder="Search by name, email, or phone..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="search-input">
            
            <select name="category" class="category-filter">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" 
                            <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn">Search</button>
            
            <?php if (!empty($search) || $categoryFilter > 0): ?>
                <a href="index.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Results count -->
<?php if (!empty($search) || $categoryFilter > 0): ?>
    <div class="results-info">
        <?php 
        $count = $result ? $result->num_rows : 0;
        echo "Found {$count} contact(s)";
        if (!empty($search)) echo " matching '{$search}'";
        if ($categoryFilter > 0) {
            $selectedCategory = array_filter($categories, function($cat) use ($categoryFilter) {
                return $cat['id'] == $categoryFilter;
            });
            if ($selectedCategory) {
                $catName = reset($selectedCategory)['name'];
                echo " in category '{$catName}'";
            }
        }
        ?>
    </div>
<?php endif; ?>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Category</th>
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
                            <?php if ($contact['category_name']): ?>
                                <span class="category-badge" style="background-color: <?php echo htmlspecialchars($contact['category_color']); ?>">
                                    <?php echo htmlspecialchars($contact['category_name']); ?>
                                </span>
                            <?php else: ?>
                                <span class="category-badge" style="background-color: #95a5a6">Uncategorized</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="/student-portal/view-contact.php?id=<?php echo $contact['id']; ?>" class="btn btn-secondary">View</a>
                                <a href="/student-portal/edit-contact.php?id=<?php echo $contact['id']; ?>" class="btn">Edit</a>
                                <a href="/student-portal/delete-contact.php?id=<?php echo $contact['id']; ?>" 
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
    <div class="no-results">
        <?php if (!empty($search) || $categoryFilter > 0): ?>
            <p>No contacts found matching your criteria.</p>
            <a href="index.php" class="btn">View All Contacts</a>
        <?php else: ?>
            <p>No contacts found. <a href="/student-portal/add-contact.php">Add your first contact</a></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php 
require_once 'includes/footer.php';
?>