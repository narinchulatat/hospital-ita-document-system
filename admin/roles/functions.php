<?php
/**
 * Roles Functions
 * Helper functions for role management
 */

require_once '../../classes/Database.php';
require_once '../../classes/Role.php';

/**
 * Validate role data
 */
function validateRoleData($data, $isEdit = false) {
    $errors = [];
    
    // Required fields
    if (empty($data['name'])) {
        $errors[] = 'ชื่อบทบาทห้ามว่าง';
    }
    
    if (empty($data['display_name'])) {
        $errors[] = 'ชื่อที่แสดงห้ามว่าง';
    }
    
    // Validate name format (lowercase, alphanumeric, underscore only)
    if (!empty($data['name']) && !preg_match('/^[a-z0-9_]+$/', $data['name'])) {
        $errors[] = 'ชื่อบทบาทต้องประกอบด้วยตัวอักษรภาษาอังกฤษพิมพ์เล็ก ตัวเลข และเครื่องหมาย _ เท่านั้น';
    }
    
    // Check duplicate name
    if (!empty($data['name'])) {
        $role = new Role();
        $existing = $role->getByName($data['name']);
        if ($existing && (!$isEdit || $existing['id'] != $data['id'])) {
            $errors[] = 'ชื่อบทบาทนี้มีอยู่แล้ว';
        }
    }
    
    return $errors;
}

/**
 * Generate role name from display name
 */
function generateRoleName($displayName) {
    // Convert Thai to English transliteration (basic)
    $thaiToEng = [
        'ก' => 'k', 'ข' => 'kh', 'ค' => 'kh', 'ง' => 'ng',
        'จ' => 'j', 'ฉ' => 'ch', 'ช' => 'ch', 'ซ' => 's',
        'ฎ' => 'd', 'ฏ' => 't', 'ฐ' => 'th', 'ฑ' => 'th',
        'ฒ' => 'th', 'ณ' => 'n', 'ด' => 'd', 'ต' => 't',
        'ถ' => 'th', 'ท' => 'th', 'ธ' => 'th', 'น' => 'n',
        'บ' => 'b', 'ป' => 'p', 'ผ' => 'ph', 'ฝ' => 'f',
        'พ' => 'ph', 'ฟ' => 'f', 'ภ' => 'ph', 'ม' => 'm',
        'ย' => 'y', 'ร' => 'r', 'ล' => 'l', 'ว' => 'w',
        'ศ' => 's', 'ษ' => 's', 'ส' => 's', 'ห' => 'h',
        'ฬ' => 'l', 'อ' => 'o', 'ฮ' => 'h',
        'ะ' => 'a', 'ั' => 'a', 'า' => 'a', 'ำ' => 'am',
        'ิ' => 'i', 'ี' => 'i', 'ึ' => 'ue', 'ื' => 'ue',
        'ุ' => 'u', 'ู' => 'u', 'เ' => 'e', 'แ' => 'ae',
        'โ' => 'o', 'ใ' => 'ai', 'ไ' => 'ai', '่' => '', '้' => '',
        '๊' => '', '๋' => '', '์' => '', 'ํ' => '', 'ๆ' => ''
    ];
    
    // Replace Thai characters
    $name = strtr($displayName, $thaiToEng);
    
    // Convert to lowercase and replace spaces with underscores
    $name = strtolower($name);
    $name = preg_replace('/\s+/', '_', $name);
    
    // Remove special characters except underscores
    $name = preg_replace('/[^a-z0-9_]/', '', $name);
    
    // Remove multiple underscores
    $name = preg_replace('/_+/', '_', $name);
    
    // Trim underscores from start and end
    $name = trim($name, '_');
    
    return $name;
}

/**
 * Check if role can be deleted
 */
function canDeleteRole($roleId) {
    $db = new Database();
    
    // Check if role has users
    $userCount = $db->query("SELECT COUNT(*) as count FROM users WHERE role_id = ?", [$roleId])->fetch()['count'];
    
    if ($userCount > 0) {
        return false;
    }
    
    // Check if it's a system role (admin, staff, etc.)
    $role = new Role();
    $roleData = $role->getById($roleId);
    
    if ($roleData && in_array($roleData['name'], ['admin', 'staff', 'approver', 'visitor'])) {
        return false;
    }
    
    return true;
}

/**
 * Get role badge class
 */
function getRoleBadgeClass($roleName) {
    $classes = [
        'admin' => 'bg-red-100 text-red-800',
        'staff' => 'bg-blue-100 text-blue-800',
        'approver' => 'bg-green-100 text-green-800',
        'visitor' => 'bg-gray-100 text-gray-800'
    ];
    
    return $classes[$roleName] ?? 'bg-purple-100 text-purple-800';
}

/**
 * Get role icon
 */
function getRoleIcon($roleName) {
    $icons = [
        'admin' => 'fa-crown',
        'staff' => 'fa-user-tie',
        'approver' => 'fa-user-check',
        'visitor' => 'fa-user'
    ];
    
    return $icons[$roleName] ?? 'fa-user-shield';
}

/**
 * Format role name for display
 */
function formatRoleName($name) {
    return ucfirst(str_replace('_', ' ', $name));
}

/**
 * Get status label
 */
function getStatusLabel($isActive) {
    return $isActive ? 'ใช้งาน' : 'ไม่ใช้งาน';
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($isActive) {
    return $isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
}