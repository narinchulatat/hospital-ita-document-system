<?php
/**
 * Category Class
 * Handles category management operations
 */

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create new category
     */
    public function create($data) {
        // Set level based on parent
        if ($data['parent_id']) {
            $parent = $this->getById($data['parent_id']);
            $data['level'] = $parent['level'] + 1;
        } else {
            $data['level'] = 1;
        }
        
        $categoryId = $this->db->insert('categories', $data);
        
        // Log activity
        logActivity(ACTION_CREATE, 'categories', $categoryId, null, $data);
        
        return $categoryId;
    }
    
    /**
     * Get category by ID
     */
    public function getById($id) {
        $query = "SELECT c.*, u.first_name, u.last_name,
                         (SELECT COUNT(*) FROM categories WHERE parent_id = c.id) as children_count,
                         (SELECT COUNT(*) FROM documents WHERE category_id = c.id) as documents_count
                  FROM categories c
                  JOIN users u ON c.created_by = u.id
                  WHERE c.id = ?";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Update category with updated_by tracking
     */
    public function update($id, $data) {
        $oldCategory = $this->getById($id);
        
        // Update level if parent changed
        if (isset($data['parent_id']) && $data['parent_id'] != $oldCategory['parent_id']) {
            if ($data['parent_id']) {
                $parent = $this->getById($data['parent_id']);
                $data['level'] = $parent['level'] + 1;
            } else {
                $data['level'] = 1;
            }
        }
        
        // Set updated_by and updated_at
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (isset($_SESSION['user_id'])) {
            $data['updated_by'] = $_SESSION['user_id'];
        }
        
        $result = $this->db->update('categories', $data, ['id' => $id]);
        
        return $result;
    }
    
    /**
     * Delete category
     */
    public function delete($id) {
        $category = $this->getById($id);
        
        if ($category) {
            // Check if category has children or documents
            if ($category['children_count'] > 0) {
                throw new Exception('ไม่สามารถลบหมวดหมู่ที่มีหมวดหมู่ย่อยได้');
            }
            
            if ($category['documents_count'] > 0) {
                throw new Exception('ไม่สามารถลบหมวดหมู่ที่มีเอกสารได้');
            }
            
            $result = $this->db->delete('categories', ['id' => $id]);
            
            if ($result) {
                // Log activity
                logActivity(ACTION_DELETE, 'categories', $id, $category);
            }
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * Get all categories as tree structure
     */
    public function getTree($parentId = null, $activeOnly = true) {
        $query = "SELECT c.*, 
                         (SELECT COUNT(*) FROM categories WHERE parent_id = c.id" . 
                         ($activeOnly ? " AND is_active = 1" : "") . ") as children_count,
                         (SELECT COUNT(*) FROM documents WHERE category_id = c.id) as documents_count
                  FROM categories c
                  WHERE c.parent_id " . ($parentId ? "= ?" : "IS NULL");
        
        if ($activeOnly) {
            $query .= " AND c.is_active = 1";
        }
        
        $query .= " ORDER BY c.sort_order, c.name";
        
        $params = $parentId ? [$parentId] : [];
        $categories = $this->db->fetchAll($query, $params);
        
        // Get children for each category
        foreach ($categories as &$category) {
            if ($category['children_count'] > 0) {
                $category['children'] = $this->getTree($category['id'], $activeOnly);
            } else {
                $category['children'] = [];
            }
        }
        
        return $categories;
    }
    
    /**
     * Get flat list of categories
     */
    public function getAll($activeOnly = true, $search = '') {
        $query = "SELECT c.*, u.first_name, u.last_name,
                         (SELECT COUNT(*) FROM categories WHERE parent_id = c.id) as children_count,
                         (SELECT COUNT(*) FROM documents WHERE category_id = c.id) as documents_count
                  FROM categories c
                  JOIN users u ON c.created_by = u.id";
        
        $params = [];
        $whereConditions = [];
        
        if ($activeOnly) {
            $whereConditions[] = "c.is_active = 1";
        }
        
        if ($search) {
            $whereConditions[] = "(c.name LIKE ? OR c.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $query .= " ORDER BY c.level, c.sort_order, c.name";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get categories by level
     */
    public function getByLevel($level, $activeOnly = true) {
        $query = "SELECT * FROM categories WHERE level = ?";
        $params = [$level];
        
        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }
        
        $query .= " ORDER BY sort_order, name";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get parent categories (for dropdown)
     */
    public function getParentOptions($currentId = null) {
        $query = "SELECT * FROM categories WHERE level < 3 AND is_active = 1";
        $params = [];
        
        if ($currentId) {
            $query .= " AND id != ?";
            $params[] = $currentId;
        }
        
        $query .= " ORDER BY level, sort_order, name";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get breadcrumb path for category
     */
    public function getBreadcrumb($categoryId) {
        $breadcrumb = [];
        $currentId = $categoryId;
        
        while ($currentId) {
            $category = $this->getById($currentId);
            if ($category) {
                array_unshift($breadcrumb, $category);
                $currentId = $category['parent_id'];
            } else {
                break;
            }
        }
        
        return $breadcrumb;
    }
    
    /**
     * Update sort order with updated_by tracking
     */
    public function updateSortOrder($categoryId, $sortOrder) {
        $data = [
            'sort_order' => $sortOrder,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (isset($_SESSION['user_id'])) {
            $data['updated_by'] = $_SESSION['user_id'];
        }
        
        return $this->db->update('categories', $data, ['id' => $categoryId]);
    }
    
    /**
     * Toggle active status
     */
    public function toggleActive($categoryId) {
        $category = $this->getById($categoryId);
        if (!$category) {
            return false;
        }
        
        $newStatus = !$category['is_active'];
        $result = $this->db->update('categories', ['is_active' => $newStatus], ['id' => $categoryId]);
        
        if ($result) {
            // Log activity
            logActivity(ACTION_UPDATE, 'categories', $categoryId, $category, ['is_active' => $newStatus]);
        }
        
        return $result;
    }
    
    /**
     * Get category statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total categories
        $stats['total'] = $this->db->getRowCount('categories');
        
        // Active categories
        $stats['active'] = $this->db->getRowCount('categories', ['is_active' => 1]);
        
        // Categories by level
        for ($level = 1; $level <= 3; $level++) {
            $stats["level_{$level}"] = $this->db->getRowCount('categories', ['level' => $level, 'is_active' => 1]);
        }
        
        // Categories with documents
        $query = "SELECT COUNT(DISTINCT c.id) as count 
                  FROM categories c 
                  JOIN documents d ON c.id = d.category_id
                  WHERE c.is_active = 1";
        $result = $this->db->fetch($query);
        $stats['with_documents'] = $result ? (int)$result['count'] : 0;
        
        return $stats;
    }
    
    /**
     * Search categories and documents
     */
    public function search($searchTerm, $filters = []) {
        $query = "SELECT 'category' as type, c.id, c.name as title, c.description, 
                         c.created_at, NULL as file_type
                  FROM categories c
                  WHERE c.is_active = 1 AND (c.name LIKE ? OR c.description LIKE ?)
                  
                  UNION ALL
                  
                  SELECT 'document' as type, d.id, d.title, d.description,
                         d.created_at, d.file_type
                  FROM documents d
                  JOIN categories c ON d.category_id = c.id
                  WHERE d.is_public = 1 AND d.status = 'approved' 
                        AND (d.title LIKE ? OR d.description LIKE ?)";
        
        $searchPattern = "%{$searchTerm}%";
        $params = [$searchPattern, $searchPattern, $searchPattern, $searchPattern];
        
        // Add category filter
        if (!empty($filters['category_id'])) {
            $query .= " AND (c.id = ? OR d.category_id = ?)";
            $params[] = $filters['category_id'];
            $params[] = $filters['category_id'];
        }
        
        $query .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Check if category name exists
     */
    public function nameExists($name, $parentId = null, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM categories WHERE name = ? AND parent_id ";
        $params = [$name];
        
        if ($parentId) {
            $query .= "= ?";
            $params[] = $parentId;
        } else {
            $query .= "IS NULL";
        }
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        $query = "SELECT * FROM categories WHERE slug = ?";
        return $this->db->fetch($query, [$slug]);
    }
    
    /**
     * Get descendants of a category
     */
    public function getDescendants($categoryId) {
        $descendants = [];
        
        // Get direct children
        $children = $this->db->fetchAll("SELECT * FROM categories WHERE parent_id = ?", [$categoryId]);
        
        foreach ($children as $child) {
            $descendants[] = $child;
            // Recursively get descendants
            $childDescendants = $this->getDescendants($child['id']);
            $descendants = array_merge($descendants, $childDescendants);
        }
        
        return $descendants;
    }
}