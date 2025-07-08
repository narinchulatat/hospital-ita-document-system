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
                  JOIN roles r ON u.role_id = r.id 
                  WHERE u.username = ? AND u.status = 'active'";
        
        $user = $this->db->fetch($query, [$username]);
        
        if (!$user) {
            return false;
        }
        
        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
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
                  JOIN roles r ON u.role_id = r.id 
                  WHERE u.id = ?";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Get user by username
     */
    public function getByUsername($username) {
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.id 
                  WHERE u.username = ?";
        
        return $this->db->fetch($query, [$username]);
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default values
        $data['status'] = $data['status'] ?? STATUS_ACTIVE;
        $data['failed_attempts'] = 0;
        
        return $this->db->insert('users', $data);
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
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
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.id";
        
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
                  JOIN role_permissions rp ON u.role_id = rp.role_id
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
                  JOIN role_permissions rp ON u.role_id = rp.role_id
                  JOIN permissions p ON rp.permission_id = p.id
                  WHERE u.id = ?
                  ORDER BY p.module, p.name";
        
        return $this->db->fetchAll($query, [$userId]);
    }
    
    /**
     * Increment failed login attempts
     */
    private function incrementFailedAttempts($userId) {
        $query = "UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?";
        $this->db->execute($query, [$userId]);
        
        // Check if account should be locked
        $user = $this->getById($userId);
        if ($user['failed_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
            $this->db->update('users', ['locked_until' => $lockUntil], ['id' => $userId]);
        }
    }
    
    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts($userId) {
        $data = [
            'failed_attempts' => 0,
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
}