<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';

requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get dashboard statistics
    $stats = [];
    
    // Total users
    $result = $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
    $stats['total_users'] = $result['count'] ?? 0;
    
    // New users today
    $result = $db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
    $stats['new_users_today'] = $result['count'] ?? 0;
    
    // Total documents
    $result = $db->fetch("SELECT COUNT(*) as count FROM documents");
    $stats['total_documents'] = $result['count'] ?? 0;
    
    // Pending documents
    $result = $db->fetch("SELECT COUNT(*) as count FROM documents WHERE status = 'pending'");
    $stats['pending_documents'] = $result['count'] ?? 0;
    
    // Total downloads today
    $result = $db->fetch("SELECT COUNT(*) as count FROM downloads WHERE DATE(created_at) = CURDATE()");
    $stats['downloads_today'] = $result['count'] ?? 0;
    
    // Total downloads this month
    $result = $db->fetch("SELECT COUNT(*) as count FROM downloads WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $stats['downloads_month'] = $result['count'] ?? 0;
    
    // Storage used
    $result = $db->fetch("SELECT SUM(file_size) as total FROM documents");
    $stats['storage_used'] = $result['total'] ?? 0;
    
    // Last backup
    $result = $db->fetch("SELECT created_at FROM backups WHERE status = 'completed' ORDER BY created_at DESC LIMIT 1");
    $stats['last_backup'] = $result['created_at'] ?? null;
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'เกิดข้อผิดพลาดเซิร์ฟเวอร์'
    ]);
}
?>