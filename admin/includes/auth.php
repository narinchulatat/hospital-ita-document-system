<?php
/**
 * Admin Authentication Check
 * Include this file to require admin authentication
 */

if (!isset($_SESSION)) {
    session_start();
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/classes/Database.php';
require_once dirname(__DIR__) . '/classes/User.php';

/**
 * Check if user is logged in and has admin access
 */
function requireAdminAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Check if user has specific role
 */
function requireRole($roleId) {
    requireAdminAuth();
    
    try {
        $user = new User();
        if (!$user->hasRole($_SESSION['user_id'], $roleId)) {
            header('Location: ' . BASE_URL . '/admin/');
            exit;
        }
    } catch (Exception $e) {
        error_log("Role check error: " . $e->getMessage());
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Check if user has specific permission
 */
function requirePermission($permission) {
    requireAdminAuth();
    
    try {
        $user = new User();
        if (!$user->hasPermission($_SESSION['user_id'], $permission)) {
            http_response_code(403);
            include '../includes/403.php';
            exit;
        }
    } catch (Exception $e) {
        error_log("Permission check error: " . $e->getMessage());
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $user = new User();
        return $user->getById($_SESSION['user_id']);
    } catch (Exception $e) {
        error_log("Get current user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field
 */
function getCSRFInput() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

/**
 * Check CSRF token from POST request
 */
function checkCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCSRFToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
}

// Auto-check admin authentication for all admin pages
requireAdminAuth();
?>