<?php
/**
 * Statistics API Endpoint
 * Provides real-time statistics for the approver dashboard
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Include configuration and authentication
require_once __DIR__ . '/../includes/header.php';

// Check if user has approval permissions
if (!canApproveDocuments()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้']);
    exit;
}

try {
    $document = new Document();
    $currentUserId = getCurrentUserId();
    
    // Get basic statistics
    $stats = [
        'pending_documents' => $document->getTotalCount(['status' => DOC_STATUS_PENDING]),
        'approved_by_me' => $document->getTotalCount(['approved_by' => $currentUserId, 'status' => DOC_STATUS_APPROVED]),
        'rejected_by_me' => $document->getTotalCount(['approved_by' => $currentUserId, 'status' => DOC_STATUS_REJECTED]),
        'total_approved' => $document->getTotalCount(['status' => DOC_STATUS_APPROVED]),
        'total_documents' => $document->getTotalCount([]),
        'urgent_pending' => $document->getTotalCount(['status' => DOC_STATUS_PENDING, 'urgent' => true]),
        'this_month_approved' => $document->getTotalCount([
            'approved_by' => $currentUserId,
            'status' => DOC_STATUS_APPROVED,
            'approved_at_month' => date('Y-m')
        ])
    ];
    
    // Calculate derived statistics
    $totalProcessed = $stats['approved_by_me'] + $stats['rejected_by_me'];
    $approvalRate = $totalProcessed > 0 ? round(($stats['approved_by_me'] / $totalProcessed) * 100, 1) : 0;
    
    $stats['total_processed'] = $totalProcessed;
    $stats['approval_rate'] = $approvalRate;
    
    // Get weekly performance data
    $db = Database::getInstance();
    $weeklyData = $db->fetchAll(
        "SELECT 
            DATE(al.created_at) as date,
            SUM(CASE WHEN al.action = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN al.action = 'reject' THEN 1 ELSE 0 END) as rejected
         FROM approval_logs al
         WHERE al.approver_id = ? 
           AND al.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         GROUP BY DATE(al.created_at)
         ORDER BY date ASC",
        [$currentUserId]
    );
    
    // Get category breakdown
    $categoryStats = $document->getApprovalStatsByCategory($currentUserId);
    
    // Recent activity count
    $recentActivityCount = $db->fetch(
        "SELECT COUNT(*) as count 
         FROM approval_logs 
         WHERE approver_id = ? 
           AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
        [$currentUserId]
    )['count'] ?? 0;
    
    $stats['recent_activity_count'] = $recentActivityCount;
    
    $response = [
        'success' => true,
        'stats' => $stats,
        'weekly_data' => $weeklyData,
        'category_stats' => $categoryStats,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Statistics API error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการโหลดสถิติ'
    ]);
}