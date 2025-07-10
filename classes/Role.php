<?php
/**
 * Role Class
 * Handles role management system
 */

class Role {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all roles
     */
    public function getAll($activeOnly = false) {
        $query = "SELECT * FROM roles";
        $params = [];
        
        if ($activeOnly) {
            $query .= " WHERE is_active = 1";
        }
        
        $query .= " ORDER BY name";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get active roles
     */
    public function getActive() {
        return $this->getAll(true);
    }
    
    /**
     * Get role by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM roles WHERE id = ?";
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Get role by name
     */
    public function getByName($name) {
        $query = "SELECT * FROM roles WHERE name = ?";
        return $this->db->fetch($query, [$name]);
    }
    
    /**
     * Create new role
     */
    public function create($data) {
        // Check if role already exists
        if ($this->getByName($data['name'])) {
            throw new Exception("Role '{$data['name']}' already exists");
        }
        
        $roleId = $this->db->insert('roles', $data);
        
        // Log activity
        logActivity(ACTION_CREATE, 'roles', $roleId, null, $data);
        
        return $roleId;
    }
    
    /**
     * Update role
     */
    public function update($id, $data) {
        $oldData = $this->getById($id);
        
        $result = $this->db->update('roles', $data, ['id' => $id]);
        
        if ($result) {
            // Log activity
            logActivity(ACTION_UPDATE, 'roles', $id, $oldData, $data);
        }
        
        return $result;
    }
    
    /**
     * Delete role
     */
    public function delete($id) {
        $role = $this->getById($id);
        
        if (!$role) {
            return false;
        }
        
        // Check if role is being used
        $userCount = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role_id = ?", [$id])->fetch()['count'];
        if ($userCount > 0) {
            throw new Exception("Cannot delete role: {$userCount} users are assigned to this role");
        }
        
        $this->db->beginTransaction();
        
        try {
            // Remove role permissions
            $this->db->delete('role_permissions', ['role_id' => $id]);
            
            // Delete the role
            $result = $this->db->delete('roles', ['id' => $id]);
            
            if ($result) {
                // Log activity
                logActivity(ACTION_DELETE, 'roles', $id, $role, null);
            }
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Toggle role active status
     */
    public function toggleActive($id) {
        $role = $this->getById($id);
        if (!$role) {
            return false;
        }
        
        $newStatus = !$role['is_active'];
        return $this->db->update('roles', ['is_active' => $newStatus], ['id' => $id]);
    }
    
    /**
     * Get role permissions
     */
    public function getPermissions($roleId) {
        $query = "SELECT p.* FROM permissions p
                  JOIN role_permissions rp ON p.id = rp.permission_id
                  WHERE rp.role_id = ?
                  ORDER BY p.module, p.name";
        
        return $this->db->fetchAll($query, [$roleId]);
    }
    
    /**
     * Assign permission to role
     */
    public function assignPermission($roleId, $permissionId) {
        // Check if already assigned
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
     * Remove permission from role
     */
    public function removePermission($roleId, $permissionId) {
        return $this->db->delete('role_permissions', [
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }
    
    /**
     * Set role permissions (replace all existing)
     */
    public function setPermissions($roleId, $permissionIds) {
        $this->db->beginTransaction();
        
        try {
            // Remove all existing permissions
            $this->db->delete('role_permissions', ['role_id' => $roleId]);
            
            // Add new permissions
            foreach ($permissionIds as $permissionId) {
                $this->assignPermission($roleId, $permissionId);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get users with this role
     */
    public function getUsersWithRole($roleId, $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT u.*, u.created_at as assigned_at
                  FROM users u
                  WHERE u.role_id = ?
                  ORDER BY u.first_name, u.last_name
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, [$roleId, $limit, $offset]);
    }
    
    /**
     * Assign role to user
     */
    public function assignToUser($roleId, $userId) {
        // Check if already assigned
        $existing = $this->db->fetch(
            "SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?",
            [$userId, $roleId]
        );
        
        if (!$existing) {
            return $this->db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleId
            ]);
        }
        
        return true;
    }
    
    /**
     * Remove role from user
     */
    public function removeFromUser($roleId, $userId) {
        return $this->db->delete('user_roles', [
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }
    
    /**
     * Get role hierarchy (if roles have parent-child relationships)
     */
    public function getHierarchy() {
        // For future implementation if role hierarchy is needed
        return $this->getAll(true);
    }
    
    /**
     * Check if role has permission
     */
    public function hasPermission($roleId, $permissionName) {
        $query = "SELECT COUNT(*) as count
                  FROM role_permissions rp
                  JOIN permissions p ON rp.permission_id = p.id
                  WHERE rp.role_id = ? AND p.name = ?";
        
        $result = $this->db->fetch($query, [$roleId, $permissionName]);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Copy permissions from one role to another
     */
    public function copyPermissions($fromRoleId, $toRoleId) {
        $permissions = $this->getPermissions($fromRoleId);
        $permissionIds = array_column($permissions, 'id');
        
        return $this->setPermissions($toRoleId, $permissionIds);
    }
    
    /**
     * Get role statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total roles
        $stats['total'] = $this->db->getRowCount('roles');
        
        // Active roles
        $stats['active'] = $this->db->getRowCount('roles', ['is_active' => 1]);
        
        // Role with most users
        $query = "SELECT r.name, r.display_name, COUNT(ur.user_id) as user_count
                  FROM roles r
                  LEFT JOIN user_roles ur ON r.id = ur.role_id
                  GROUP BY r.id
                  ORDER BY user_count DESC
                  LIMIT 10";
        $stats['by_user_count'] = $this->db->fetchAll($query);
        
        // Role with most permissions
        $query = "SELECT r.name, r.display_name, COUNT(rp.permission_id) as permission_count
                  FROM roles r
                  LEFT JOIN role_permissions rp ON r.id = rp.role_id
                  GROUP BY r.id
                  ORDER BY permission_count DESC
                  LIMIT 10";
        $stats['by_permission_count'] = $this->db->fetchAll($query);
        
        return $stats;
    }
    
    /**
     * Initialize default roles
     */
    public function initializeDefaults() {
        $defaultRoles = [
            [
                'name' => 'admin',
                'display_name' => 'ผู้ดูแลระบบ',
                'description' => 'ผู้ดูแลระบบที่มีสิทธิ์เต็ม'
            ],
            [
                'name' => 'staff',
                'display_name' => 'เจ้าหน้าที่',
                'description' => 'เจ้าหน้าที่ที่สามารถจัดการเอกสาร'
            ],
            [
                'name' => 'approver',
                'display_name' => 'ผู้อนุมัติ',
                'description' => 'ผู้มีสิทธิ์อนุมัติเอกสาร'
            ],
            [
                'name' => 'viewer',
                'display_name' => 'ผู้ดู',
                'description' => 'ผู้ที่สามารถดูเอกสารได้เท่านั้น'
            ]
        ];
        
        foreach ($defaultRoles as $role) {
            // Only create if doesn't exist
            if (!$this->getByName($role['name'])) {
                $this->create($role['name'], $role['display_name'], $role['description']);
            }
        }
    }
    
    /**
     * Get roles for dropdown/select
     */
    public function getForSelect($activeOnly = true) {
        $roles = $this->getAll($activeOnly);
        $options = [];
        
        foreach ($roles as $role) {
            $options[$role['id']] = $role['display_name'];
        }
        
        return $options;
    }
    
    /**
     * Bulk assign users to role
     */
    public function bulkAssignUsers($roleId, $userIds) {
        $this->db->beginTransaction();
        
        try {
            foreach ($userIds as $userId) {
                $this->assignToUser($roleId, $userId);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Export role data
     */
    public function exportRoleData($roleId) {
        $role = $this->getById($roleId);
        if (!$role) {
            return null;
        }
        
        $data = [
            'role' => $role,
            'permissions' => $this->getPermissions($roleId),
            'users' => $this->getUsersWithRole($roleId, 1, 1000)
        ];
        
        return $data;
    }
}