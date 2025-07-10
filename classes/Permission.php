<?php
/**
 * Permission Class
 * Handles permission management system
 */

class Permission {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all permissions
     */
    public function getAll() {
        $query = "SELECT * FROM permissions ORDER BY module, name";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get permissions by module
     */
    public function getByModule($module) {
        $query = "SELECT * FROM permissions WHERE module = ? ORDER BY name";
        return $this->db->fetchAll($query, [$module]);
    }
    
    /**
     * Get permissions by role
     */
    public function getByRole($roleId) {
        $query = "SELECT p.* FROM permissions p
                  JOIN role_permissions rp ON p.id = rp.permission_id
                  WHERE rp.role_id = ?
                  ORDER BY p.module, p.name";
        
        return $this->db->fetchAll($query, [$roleId]);
    }
    
    /**
     * Get permission by name
     */
    public function getByName($name) {
        $query = "SELECT * FROM permissions WHERE name = ?";
        return $this->db->fetch($query, [$name]);
    }
    
    /**
     * Create new permission
     */
    public function create($name, $displayName, $description, $module) {
        // Check if permission already exists
        if ($this->getByName($name)) {
            throw new Exception("Permission '{$name}' already exists");
        }
        
        $data = [
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
            'module' => $module
        ];
        
        return $this->db->insert('permissions', $data);
    }
    
    /**
     * Update permission
     */
    public function update($id, $displayName, $description, $module) {
        $data = [
            'display_name' => $displayName,
            'description' => $description,
            'module' => $module
        ];
        
        return $this->db->update('permissions', $data, ['id' => $id]);
    }
    
    /**
     * Delete permission
     */
    public function delete($id) {
        // First remove from role_permissions
        $this->db->delete('role_permissions', ['permission_id' => $id]);
        
        // Then delete the permission
        return $this->db->delete('permissions', ['id' => $id]);
    }
    
    /**
     * Check if user has permission
     */
    public function checkPermission($userId, $permissionName) {
        $query = "SELECT COUNT(*) as count
                  FROM users u
                  JOIN user_roles ur ON u.id = ur.user_id
                  JOIN role_permissions rp ON ur.role_id = rp.role_id
                  JOIN permissions p ON rp.permission_id = p.id
                  WHERE u.id = ? AND p.name = ? AND u.is_active = 1";
        
        $result = $this->db->fetch($query, [$userId, $permissionName]);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Check if user has any of the specified permissions
     */
    public function checkAnyPermission($userId, $permissionNames) {
        if (empty($permissionNames)) {
            return false;
        }
        
        $placeholders = str_repeat('?,', count($permissionNames) - 1) . '?';
        
        $query = "SELECT COUNT(*) as count
                  FROM users u
                  JOIN user_roles ur ON u.id = ur.user_id
                  JOIN role_permissions rp ON ur.role_id = rp.role_id
                  JOIN permissions p ON rp.permission_id = p.id
                  WHERE u.id = ? AND p.name IN ({$placeholders}) AND u.is_active = 1";
        
        $params = array_merge([$userId], $permissionNames);
        $result = $this->db->fetch($query, $params);
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Check if user has all specified permissions
     */
    public function checkAllPermissions($userId, $permissionNames) {
        if (empty($permissionNames)) {
            return true;
        }
        
        $placeholders = str_repeat('?,', count($permissionNames) - 1) . '?';
        
        $query = "SELECT COUNT(DISTINCT p.name) as count
                  FROM users u
                  JOIN user_roles ur ON u.id = ur.user_id
                  JOIN role_permissions rp ON ur.role_id = rp.role_id
                  JOIN permissions p ON rp.permission_id = p.id
                  WHERE u.id = ? AND p.name IN ({$placeholders}) AND u.is_active = 1";
        
        $params = array_merge([$userId], $permissionNames);
        $result = $this->db->fetch($query, $params);
        
        return $result && $result['count'] == count($permissionNames);
    }
    
    /**
     * Grant permission to role
     */
    public function grantPermission($roleId, $permissionId) {
        // Check if already granted
        $existing = $this->db->fetch(
            "SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?",
            [$roleId, $permissionId]
        );
        
        if (!$existing) {
            return $this->db->insert('role_permissions', [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ]);
        }
        
        return true;
    }
    
    /**
     * Revoke permission from role
     */
    public function revokePermission($roleId, $permissionId) {
        return $this->db->delete('role_permissions', [
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }
    
    /**
     * Grant multiple permissions to role
     */
    public function grantMultiplePermissions($roleId, $permissionIds) {
        $this->db->beginTransaction();
        
        try {
            foreach ($permissionIds as $permissionId) {
                $this->grantPermission($roleId, $permissionId);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Set role permissions (replace all existing with new ones)
     */
    public function setRolePermissions($roleId, $permissionIds) {
        $this->db->beginTransaction();
        
        try {
            // Remove all existing permissions for this role
            $this->db->delete('role_permissions', ['role_id' => $roleId]);
            
            // Add new permissions
            foreach ($permissionIds as $permissionId) {
                $this->grantPermission($roleId, $permissionId);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get user permissions (direct from all roles)
     */
    public function getUserPermissions($userId) {
        $query = "SELECT DISTINCT p.name, p.display_name, p.description, p.module
                  FROM users u
                  JOIN user_roles ur ON u.id = ur.user_id
                  JOIN role_permissions rp ON ur.role_id = rp.role_id
                  JOIN permissions p ON rp.permission_id = p.id
                  WHERE u.id = ? AND u.is_active = 1
                  ORDER BY p.module, p.name";
        
        return $this->db->fetchAll($query, [$userId]);
    }
    
    /**
     * Get all modules
     */
    public function getModules() {
        $query = "SELECT DISTINCT module FROM permissions ORDER BY module";
        $modules = $this->db->fetchAll($query);
        
        return array_column($modules, 'module');
    }
    
    /**
     * Initialize default permissions
     */
    public function initializeDefaults() {
        $defaultPermissions = [
            // User Management
            ['name' => 'user_view', 'display_name' => 'View Users', 'description' => 'View user list and details', 'module' => 'users'],
            ['name' => 'user_create', 'display_name' => 'Create Users', 'description' => 'Create new users', 'module' => 'users'],
            ['name' => 'user_edit', 'display_name' => 'Edit Users', 'description' => 'Edit user information', 'module' => 'users'],
            ['name' => 'user_delete', 'display_name' => 'Delete Users', 'description' => 'Delete users', 'module' => 'users'],
            
            // Document Management
            ['name' => 'document_view', 'display_name' => 'View Documents', 'description' => 'View documents', 'module' => 'documents'],
            ['name' => 'document_upload', 'display_name' => 'Upload Documents', 'description' => 'Upload new documents', 'module' => 'documents'],
            ['name' => 'document_edit', 'display_name' => 'Edit Documents', 'description' => 'Edit document information', 'module' => 'documents'],
            ['name' => 'document_delete', 'display_name' => 'Delete Documents', 'description' => 'Delete documents', 'module' => 'documents'],
            ['name' => 'document_approve', 'display_name' => 'Approve Documents', 'description' => 'Approve or reject documents', 'module' => 'documents'],
            ['name' => 'document_download', 'display_name' => 'Download Documents', 'description' => 'Download documents', 'module' => 'documents'],
            
            // Category Management
            ['name' => 'category_view', 'display_name' => 'View Categories', 'description' => 'View categories', 'module' => 'categories'],
            ['name' => 'category_create', 'display_name' => 'Create Categories', 'description' => 'Create new categories', 'module' => 'categories'],
            ['name' => 'category_edit', 'display_name' => 'Edit Categories', 'description' => 'Edit categories', 'module' => 'categories'],
            ['name' => 'category_delete', 'display_name' => 'Delete Categories', 'description' => 'Delete categories', 'module' => 'categories'],
            
            // System Management
            ['name' => 'system_backup', 'display_name' => 'System Backup', 'description' => 'Create and manage backups', 'module' => 'system'],
            ['name' => 'system_settings', 'display_name' => 'System Settings', 'description' => 'Manage system settings', 'module' => 'system'],
            ['name' => 'system_logs', 'display_name' => 'View System Logs', 'description' => 'View activity and system logs', 'module' => 'system'],
            ['name' => 'system_reports', 'display_name' => 'Generate Reports', 'description' => 'Generate and view reports', 'module' => 'system'],
            
            // Role and Permission Management
            ['name' => 'role_view', 'display_name' => 'View Roles', 'description' => 'View roles and permissions', 'module' => 'roles'],
            ['name' => 'role_manage', 'display_name' => 'Manage Roles', 'description' => 'Create, edit, and assign roles', 'module' => 'roles'],
            ['name' => 'permission_manage', 'display_name' => 'Manage Permissions', 'description' => 'Manage permissions', 'module' => 'roles']
        ];
        
        foreach ($defaultPermissions as $permission) {
            // Only create if doesn't exist
            if (!$this->getByName($permission['name'])) {
                $this->create(
                    $permission['name'],
                    $permission['display_name'],
                    $permission['description'],
                    $permission['module']
                );
            }
        }
    }
    
    /**
     * Check permission middleware helper
     */
    public function requirePermission($userId, $permissionName, $redirectUrl = '/unauthorized') {
        if (!$this->checkPermission($userId, $permissionName)) {
            if (headers_sent()) {
                echo "<script>window.location.href='{$redirectUrl}';</script>";
            } else {
                header("Location: {$redirectUrl}");
            }
            exit;
        }
    }
    
    /**
     * Get permission statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total permissions
        $stats['total_permissions'] = $this->db->getRowCount('permissions');
        
        // Permissions by module
        $query = "SELECT module, COUNT(*) as count FROM permissions GROUP BY module ORDER BY module";
        $stats['by_module'] = $this->db->fetchAll($query);
        
        // Most assigned permissions
        $query = "SELECT p.name, p.display_name, COUNT(rp.id) as role_count
                  FROM permissions p
                  LEFT JOIN role_permissions rp ON p.id = rp.permission_id
                  GROUP BY p.id
                  ORDER BY role_count DESC
                  LIMIT 10";
        $stats['most_assigned'] = $this->db->fetchAll($query);
        
        return $stats;
    }
}