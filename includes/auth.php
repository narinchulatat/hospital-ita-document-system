<?php
/**
 * Session Management and Authentication Helper
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name'],
        'role_id' => $_SESSION['user_role'],
        'role_name' => $_SESSION['role_name']
    ];
}

/**
 * Login user
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['user_role'] = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'];
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Log activity
    logActivity(ACTION_LOGIN, 'users', $user['id']);
}

/**
 * Logout user
 */
function logoutUser() {
    if (isLoggedIn()) {
        logActivity(ACTION_LOGOUT, 'users', getCurrentUserId());
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    // Start new session
    session_start();
    session_regenerate_id(true);
}

/**
 * Check if user has permission
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    static $userPermissions = null;
    
    if ($userPermissions === null) {
        $user = new User();
        $permissions = $user->getPermissions(getCurrentUserId());
        $userPermissions = array_column($permissions, 'name');
    }
    
    return in_array($permission, $userPermissions);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirectTo('/login.php');
        exit;
    }
    
    // Check session timeout
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
        logoutUser();
        redirectTo('/login.php?timeout=1');
        exit;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
}

/**
 * Require specific permission
 */
function requirePermission($permission) {
    requireLogin();
    
    if (!hasPermission($permission)) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. You do not have permission to access this resource.');
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    
    if (getCurrentUserRole() != $role) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. You do not have the required role.');
    }
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return getCurrentUserRole() == ROLE_ADMIN;
}

/**
 * Check if user is staff
 */
function isStaff() {
    return getCurrentUserRole() == ROLE_STAFF;
}

/**
 * Check if user is approver
 */
function isApprover() {
    return getCurrentUserRole() == ROLE_APPROVER;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRE) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           isset($_SESSION['csrf_token_time']) &&
           hash_equals($_SESSION['csrf_token'], $token) &&
           (time() - $_SESSION['csrf_token_time']) <= CSRF_TOKEN_EXPIRE;
}

/**
 * Get CSRF token HTML input
 */
function getCSRFTokenInput() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

/**
 * Redirect to URL
 */
function redirectTo($url) {
    if (strpos($url, 'http') !== 0) {
        $url = BASE_URL . $url;
    }
    header("Location: $url");
    exit;
}

/**
 * Get redirect URL based on user role
 */
function getRoleRedirectUrl($role) {
    switch ($role) {
        case ROLE_ADMIN:
            return '/admin/';
        case ROLE_STAFF:
            return '/staff/';
        case ROLE_APPROVER:
            return '/approver/';
        default:
            return '/public/';
    }
}