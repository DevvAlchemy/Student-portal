<?php
/**
 * Category Management Class
 * Handles all category-related operations for the student portal
 * 
 * @author Student Portal
 * @version 1.0
 */

require_once 'database.php';

class Category {
    private $db;
    
  
     // Constructor - Initialize database connection
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Get all categories
     * 
     * @return array List of all categories
     */
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $result = $this->db->select($sql);
        
        $categories = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        return $categories;
    }
    
    /**
     * Get category by ID
     * 
     * @param int $id Category ID
     * @return array|null Category data or null if not found
     */
    public function getCategoryById($id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $result = $this->db->select($sql, [$id], "i");
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create new category
     */
    public function createCategory($name, $color = '#3498db', $icon = 'folder') {
        // Validate inputs
        $name = trim($name);
        if (empty($name)) {
            return ['success' => false, 'message' => 'Category name is required'];
        }
        
        // Check if category already exists
        $sql = "SELECT id FROM categories WHERE name = ?";
        $result = $this->db->select($sql, [$name], "s");
        
        if ($result && $result->num_rows > 0) {
            return ['success' => false, 'message' => 'Category already exists'];
        }
        
        // Insert new category
        $sql = "INSERT INTO categories (name, color, icon) VALUES (?, ?, ?)";
        $success = $this->db->execute($sql, [$name, $color, $icon], "sss");
        
        if ($success) {
            return ['success' => true, 'message' => 'Category created successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to create category'];
    }
    
    /**
     * Update category
     */
    public function updateCategory($id, $name, $color, $icon) {
        // Validate inputs
        $name = trim($name);
        if (empty($name)) {
            return ['success' => false, 'message' => 'Category name is required'];
        }
        
        // Check if name already exists (excluding current category)
        $sql = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $result = $this->db->select($sql, [$name, $id], "si");
        
        if ($result && $result->num_rows > 0) {
            return ['success' => false, 'message' => 'Category name already exists'];
        }
        
        // Update category
        $sql = "UPDATE categories SET name = ?, color = ?, icon = ? WHERE id = ?";
        $success = $this->db->execute($sql, [$name, $color, $icon, $id], "sssi");
        
        if ($success) {
            return ['success' => true, 'message' => 'Category updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update category'];
    }
    
    /**
     * Delete category
     * 
     * @param int $id Category ID
     * @return array Result with success status and message
     */
    public function deleteCategory($id) {
        // Don't allow deletion of default categories (IDs 1-5)
        if ($id <= 5) {
            return ['success' => false, 'message' => 'Cannot delete default categories'];
        }
        
        // Delete category (contacts will have category_id set to NULL due to ON DELETE SET NULL)
        $sql = "DELETE FROM categories WHERE id = ?";
        $success = $this->db->execute($sql, [$id], "i");
        
        if ($success) {
            return ['success' => true, 'message' => 'Category deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete category'];
    }
    
    /**
     * Get contact count for each category
     * 
     * @return array Category IDs with their contact counts
     */
    public function getCategoryCounts() {
        $sql = "SELECT category_id, COUNT(*) as count 
                FROM contacts 
                GROUP BY category_id";
        $result = $this->db->select($sql);
        
        $counts = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $counts[$row['category_id']] = $row['count'];
            }
        }
        
        return $counts;
    }
}
?>