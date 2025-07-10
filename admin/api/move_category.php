<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../classes/Category.php';

requireAdminAuth();
requirePermission(PERM_CATEGORY_EDIT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$categoryId = (int)($_POST['id'] ?? 0);
$direction = $_POST['direction'] ?? '';

if ($categoryId <= 0 || !in_array($direction, ['up', 'down'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $category = new Category();
    
    if ($category->moveSortOrder($categoryId, $direction)) {
        // Log activity
        logAdminActivity(ACTION_UPDATE, 'categories', $categoryId, null, ['sort_order_direction' => $direction]);
        
        echo json_encode([
            'success' => true,
            'message' => 'เปลี่ยนลำดับเรียบร้อย'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถเปลี่ยนลำดับได้'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Move category error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดเซิร์ฟเวอร์'
    ]);
}
?>