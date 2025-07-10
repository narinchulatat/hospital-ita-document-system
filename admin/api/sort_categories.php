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

$orderData = $_POST['order'] ?? '';

if (empty($orderData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No order data provided']);
    exit;
}

try {
    $order = json_decode($orderData, true);
    
    if (!is_array($order)) {
        throw new Exception('Invalid order data format');
    }
    
    $category = new Category();
    
    if ($category->updateSortOrder($order)) {
        // Log activity
        logAdminActivity(ACTION_UPDATE, 'categories', null, null, ['bulk_sort_order' => count($order)]);
        
        echo json_encode([
            'success' => true,
            'message' => 'จัดเรียงลำดับเรียบร้อย'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการจัดเรียง'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Sort categories error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดเซิร์ฟเวอร์'
    ]);
}
?>