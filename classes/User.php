<?php
/**
 * User Class
 * Handles user authentication and management
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user login
     */
    public function authenticate($username, $password) {
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id 
                  WHERE u.username = ? AND u.is_active = 1";
        
        $user = $this->db->fetch($query, [$username]);
        
        if (!$user) {
            return false;
        }
        
        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return false;
        }
        
        // Verify password (using correct field name password_hash)
        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementFailedAttempts($user['id']);
            return false;
        }
        
        // Reset failed attempts and update last login
        $this->resetFailedAttempts($user['id']);
        $this->updateLastLogin($user['id']);
        
        return $user;
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id 
                  WHERE u.id = ?";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Get user by username
     */
    public function getByUsername($username) {
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id 
                  WHERE u.username = ?";
        
        return $this->db->fetch($query, [$username]);
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        // Hash password (using correct field name password_hash)
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['password_changed_at'] = date('Y-m-d H:i:s');
            unset($data['password']); // Remove plain password
        }
        
        // Set default values
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['failed_login_attempts'] = 0; // Use correct field name
        $data['two_factor_enabled'] = $data['two_factor_enabled'] ?? 0;
        
        return $this->db->insert('users', $data);
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        // Hash password if provided (using correct field name password_hash)
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['password_changed_at'] = date('Y-m-d H:i:s');
            unset($data['password']); // Remove plain password
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('users', $data, ['id' => $id]);
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        return $this->db->delete('users', ['id' => $id]);
    }
    
    /**
     * Get all users with pagination
     */
    public function getAll($page = 1, $limit = 20, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id";
        
        $params = [];
        
        if ($search) {
            $query .= " WHERE (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        $query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get total user count
     */
    public function getTotalCount($search = '') {
        $query = "SELECT COUNT(*) as count FROM users u";
        $params = [];
        
        if ($search) {
            $query .= " WHERE (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        $result = $this->db->fetch($query, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $permission) {
        $query = "SELECT COUNT(*) as count 
                  FROM users u
                  JOIN user_roles ur ON u.id = ur.user_id
                  JOIN role_permissions rp ON ur.role_id = rp.role_id
                  JOIN permissions p ON rp.permission_id = p.id
                  WHERE u.id = ? AND p.name = ?";
        
        $result = $this->db->fetch($query, [$userId, $permission]);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Get user permissions
     */
    public function getPermissions($userId) {
        $query = "SELECT p.name, p.description, p.module
                  FROM users u
                  JOIN user_roles ur ON u.id = ur.user_id
                  JOIN role_permissions rp ON ur.role_id = rp.role_id
                  JOIN permissions p ON rp.permission_id = p.id
                  WHERE u.id = ?
                  ORDER BY p.module, p.name";
        
        return $this->db->fetchAll($query, [$userId]);
    }
    
    /**
     * Increment failed login attempts (using correct field name)
     */
    private function incrementFailedAttempts($userId) {
        $query = "UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?";
        $this->db->execute($query, [$userId]);
        
        // Check if account should be locked
        $user = $this->getById($userId);
        if ($user['failed_login_attempts'] >= 5) { // MAX_LOGIN_ATTEMPTS
            $lockUntil = date('Y-m-d H:i:s', time() + (15 * 60)); // 15 minutes lockout
            $this->db->update('users', ['locked_until' => $lockUntil], ['id' => $userId]);
        }
    }
    
    /**
     * Reset failed login attempts (using correct field name)
     */
    private function resetFailedAttempts($userId) {
        $data = [
            'failed_login_attempts' => 0,
            'locked_until' => null
        ];
        $this->db->update('users', $data, ['id' => $userId]);
    }
    
    /**
     * Update last login time
     */
    private function updateLastLogin($userId) {
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], ['id' => $userId]);
    }
    
    /**
     * Get all roles
     */
    public function getRoles() {
        return $this->db->fetchAll("SELECT * FROM roles ORDER BY id");
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Change user password
     */
    public function changePassword($userId, $newPassword, $currentPassword = null) {
        // Verify current password if provided
        if ($currentPassword) {
            $user = $this->getById($userId);
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                throw new Exception('Current password is incorrect');
            }
        }
        
        $data = [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'password_changed_at' => date('Y-m-d H:i:s'),
            'failed_login_attempts' => 0,
            'locked_until' => null
        ];
        
        return $this->db->update('users', $data, ['id' => $userId]);
    }
    
    /**
     * Lock user account
     */
    public function lockAccount($userId, $minutes = 15) {
        $lockUntil = date('Y-m-d H:i:s', time() + ($minutes * 60));
        return $this->db->update('users', ['locked_until' => $lockUntil], ['id' => $userId]);
    }
    
    /**
     * Unlock user account
     */
    public function unlockAccount($userId) {
        $data = [
            'locked_until' => null,
            'failed_login_attempts' => 0
        ];
        return $this->db->update('users', $data, ['id' => $userId]);
    }
    
    /**
     * Enable two-factor authentication
     */
    public function enableTwoFactor($userId, $secret = null) {
        if (!$secret) {
            // Generate a random secret (simplified version)
            $secret = bin2hex(random_bytes(16));
        }
        
        $data = [
            'two_factor_enabled' => 1,
            'two_factor_secret' => $secret
        ];
        
        return $this->db->update('users', $data, ['id' => $userId]);
    }
    
    /**
     * Disable two-factor authentication
     */
    public function disableTwoFactor($userId) {
        $data = [
            'two_factor_enabled' => 0,
            'two_factor_secret' => null
        ];
        
        return $this->db->update('users', $data, ['id' => $userId]);
    }
    
    /**
     * Verify two-factor code
     */
    public function verifyTwoFactorCode($userId, $code) {
        $user = $this->getById($userId);
        if (!$user || !$user['two_factor_enabled'] || !$user['two_factor_secret']) {
            return false;
        }
        
        // Simple time-based verification (in real implementation, use proper TOTP library)
        $timeSlot = floor(time() / 30);
        $expectedCode = substr(hash('sha256', $user['two_factor_secret'] . $timeSlot), 0, 6);
        
        return $code === $expectedCode;
    }
    
    /**
     * Get user roles
     */
    public function getUserRoles($userId) {
        $query = "SELECT r.* FROM roles r 
                  JOIN user_roles ur ON r.id = ur.role_id 
                  WHERE ur.user_id = ? AND r.is_active = 1
                  ORDER BY r.name";
        
        return $this->db->fetchAll($query, [$userId]);
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId) {
        // Check if role already assigned
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
    public function removeRole($userId, $roleId) {
        return $this->db->delete('user_roles', [
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }
}