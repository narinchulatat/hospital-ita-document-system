<?php
/**
 * Categories Functions
 * Helper functions for category management
 */

require_once '../../classes/Database.php';
require_once '../../classes/Category.php';

/**
 * Get category tree for select options
 */
function getCategoryOptions($selectedId = null, $excludeId = null) {
    $category = new Category();
    $categories = $category->getAll();
    $options = '';
    
    foreach ($categories as $cat) {
        if ($excludeId && $cat['id'] == $excludeId) continue;
        
        $selected = ($selectedId == $cat['id']) ? 'selected' : '';
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', ($cat['level'] - 1) * 2);
        $options .= sprintf(
            '<option value="%d" %s>%s%s</option>',
            $cat['id'],
            $selected,
            $indent,
            htmlspecialchars($cat['name'])
        );
    }
    
    return $options;
}

/**
 * Validate category data
 */
function validateCategoryData($data, $isEdit = false) {
    $errors = [];
    
    // Required fields
    if (empty($data['name'])) {
        $errors[] = 'ชื่อหมวดหมู่ห้ามว่าง';
    }
    
    // Validate parent category
    if (!empty($data['parent_id'])) {
        $category = new Category();
        $parent = $category->getById($data['parent_id']);
        if (!$parent) {
            $errors[] = 'ไม่พบหมวดหมู่หลักที่เลือก';
        } elseif ($parent['level'] >= 3) {
            $errors[] = 'ไม่สามารถสร้างหมวดหมู่ย่อยใต้หมวดหมู่ระดับ 3 ได้';
        }
    }
    
    // Check duplicate name in same level
    if (!empty($data['name'])) {
        $category = new Category();
        $existing = $category->nameExists($data['name'], $data['parent_id'], $isEdit ? $data['id'] : null);
        if ($existing) {
            $errors[] = 'ชื่อหมวดหมู่นี้มีอยู่แล้วในระดับเดียวกัน';
        }
    }
    
    return $errors;
}

/**
 * Get category breadcrumb
 */
function getCategoryBreadcrumb($categoryId) {
    $category = new Category();
    $breadcrumb = [];
    $current = $category->getById($categoryId);
    
    while ($current) {
        array_unshift($breadcrumb, $current);
        $current = $current['parent_id'] ? $category->getById($current['parent_id']) : null;
    }
    
    return $breadcrumb;
}

/**
 * Check if category can be deleted
 */
function canDeleteCategory($categoryId) {
    $category = new Category();
    $categoryData = $category->getById($categoryId);
    
    if (!$categoryData) {
        return false;
    }
    
    // Check if has children
    if ($categoryData['children_count'] > 0) {
        return false;
    }
    
    // Check if has documents
    if ($categoryData['documents_count'] > 0) {
        return false;
    }
    
    return true;
}

/**
 * Format category display name with level indicator
 */
function formatCategoryName($category) {
    $indent = str_repeat('└─ ', $category['level'] - 1);
    return $indent . $category['name'];
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