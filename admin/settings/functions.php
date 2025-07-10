<?php
/**
 * Settings Functions
 * Helper functions for settings management
 */

require_once '../../classes/Database.php';
require_once '../../classes/Setting.php';

/**
 * Validate setting data
 */
function validateSettingData($data, $isEdit = false) {
    $errors = [];
    
    // Required fields
    if (empty($data['key_name'])) {
        $errors[] = 'ชื่อคีย์ห้ามว่าง';
    }
    
    if (empty($data['value']) && $data['data_type'] !== 'boolean') {
        $errors[] = 'ค่าห้ามว่าง';
    }
    
    // Validate key format
    if (!empty($data['key_name']) && !preg_match('/^[a-z][a-z0-9_]*$/', $data['key_name'])) {
        $errors[] = 'ชื่อคีย์ต้องขึ้นต้นด้วยตัวอักษรและประกอบด้วยตัวอักษรพิมพ์เล็ก ตัวเลข และเครื่องหมาย _ เท่านั้น';
    }
    
    // Check duplicate key
    if (!empty($data['key_name'])) {
        $setting = new Setting();
        $existing = $setting->getByKey($data['key_name']);
        if ($existing && (!$isEdit || $existing['id'] != $data['id'])) {
            $errors[] = 'ชื่อคีย์นี้มีอยู่แล้ว';
        }
    }
    
    // Validate value based on data type
    if (!empty($data['value'])) {
        switch ($data['data_type']) {
            case 'integer':
                if (!is_numeric($data['value']) || !ctype_digit(str_replace('-', '', $data['value']))) {
                    $errors[] = 'ค่าต้องเป็นตัวเลขจำนวนเต็ม';
                }
                break;
            case 'boolean':
                if (!in_array($data['value'], ['0', '1', 'true', 'false'])) {
                    $errors[] = 'ค่าต้องเป็น true หรือ false';
                }
                break;
        }
    }
    
    return $errors;
}

/**
 * Get setting categories
 */
function getSettingCategories() {
    return [
        'general' => 'ทั่วไป',
        'upload' => 'การอัปโหลด',
        'display' => 'การแสดงผล',
        'security' => 'ความปลอดภัย',
        'backup' => 'การสำรองข้อมูล',
        'notification' => 'การแจ้งเตือน',
        'document' => 'เอกสาร',
        'system' => 'ระบบ'
    ];
}

/**
 * Get data types
 */
function getDataTypes() {
    return [
        'string' => 'ข้อความ',
        'integer' => 'ตัวเลข',
        'boolean' => 'ค่าตรรกกรรม',
        'text' => 'ข้อความยาว'
    ];
}

/**
 * Format value for display
 */
function formatSettingValue($value, $dataType) {
    switch ($dataType) {
        case 'boolean':
            return $value ? 'เปิด' : 'ปิด';
        case 'integer':
            return number_format($value);
        case 'text':
            return strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
        default:
            return $value;
    }
}

/**
 * Get category badge class
 */
function getCategoryBadgeClass($category) {
    $classes = [
        'general' => 'bg-blue-100 text-blue-800',
        'upload' => 'bg-green-100 text-green-800',
        'display' => 'bg-purple-100 text-purple-800',
        'security' => 'bg-red-100 text-red-800',
        'backup' => 'bg-yellow-100 text-yellow-800',
        'notification' => 'bg-indigo-100 text-indigo-800',
        'document' => 'bg-orange-100 text-orange-800',
        'system' => 'bg-gray-100 text-gray-800'
    ];
    
    return $classes[$category] ?? 'bg-gray-100 text-gray-800';
}

/**
 * Get category icon
 */
function getCategoryIcon($category) {
    $icons = [
        'general' => 'fa-cog',
        'upload' => 'fa-upload',
        'display' => 'fa-desktop',
        'security' => 'fa-shield-alt',
        'backup' => 'fa-database',
        'notification' => 'fa-bell',
        'document' => 'fa-file-alt',
        'system' => 'fa-server'
    ];
    
    return $icons[$category] ?? 'fa-cog';
}

/**
 * Check if setting can be deleted
 */
function canDeleteSetting($keyName) {
    // System settings that shouldn't be deleted
    $systemSettings = [
        'site_name',
        'max_file_size',
        'allowed_file_types',
        'items_per_page',
        'session_timeout',
        'backup_retention_days',
        'enable_notifications',
        'default_document_status',
        'require_approval',
        'enable_version_control'
    ];
    
    return !in_array($keyName, $systemSettings);
}

/**
 * Convert value based on data type
 */
function convertSettingValue($value, $dataType) {
    switch ($dataType) {
        case 'integer':
            return (int)$value;
        case 'boolean':
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on']) ? '1' : '0';
        case 'text':
        case 'string':
        default:
            return (string)$value;
    }
}

/**
 * Get setting suggestions based on key pattern
 */
function getSettingSuggestions($keyName) {
    $suggestions = [];
    
    // File size suggestions
    if (strpos($keyName, 'size') !== false || strpos($keyName, 'limit') !== false) {
        $suggestions[] = 'ใช้หน่วย bytes (เช่น 52428800 = 50MB)';
    }
    
    // Time suggestions
    if (strpos($keyName, 'timeout') !== false || strpos($keyName, 'duration') !== false) {
        $suggestions[] = 'ใช้หน่วยวินาที (เช่น 3600 = 1 ชั่วโมง)';
    }
    
    // Boolean suggestions
    if (strpos($keyName, 'enable') !== false || strpos($keyName, 'require') !== false) {
        $suggestions[] = 'ใช้ 1 สำหรับเปิด และ 0 สำหรับปิด';
    }
    
    return $suggestions;
}