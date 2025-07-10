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
$isActive = (int)($_POST['is_active'] ?? 0);

if ($categoryId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

try {
    $category = new Category();
    
    if ($category->updateStatus($categoryId, $isActive)) {
        // Log activity
        logAdminActivity(ACTION_UPDATE, 'categories', $categoryId, null, ['is_active' => $isActive]);
        
        echo json_encode([
            'success' => true,
            'message' => 'อัปเดตสถานะเรียบร้อย'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการอัปเดต'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Toggle category status error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดเซิร์ฟเวอร์'
    ]);
}
?>