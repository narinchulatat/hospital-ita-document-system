<?php
/**
 * Document Approval API
 */

header('Content-Type: application/json');
require_once '../includes/auth.php';

// Require approver permission
requirePermission('document.approve');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $action = $_POST['action'] ?? '';
    $documentId = (int)($_POST['document_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
    
    if (!$documentId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing document ID']);
        exit;
    }
    
    $document = new Document();
    $notification = new Notification();
    $currentUserId = getCurrentUserId();
    
    // Get document details
    $doc = $document->getById($documentId);
    if (!$doc) {
        http_response_code(404);
        echo json_encode(['error' => 'Document not found']);
        exit;
    }
    
    // Check if document is pending
    if ($doc['status'] !== DOC_STATUS_PENDING) {
        http_response_code(400);
        echo json_encode(['error' => 'Document is not pending approval']);
        exit;
    }
    
    switch ($action) {
        case 'approve':
            $result = $document->approve($documentId, $currentUserId, $comment);
            
            if ($result) {
                // Send notification to uploader
                $notification->createDocumentNotification(
                    'approved',
                    $documentId,
                    $doc['title'],
                    $doc['uploaded_by'],
                    getCurrentUser()['first_name'] . ' ' . getCurrentUser()['last_name']
                );
                
                echo json_encode([
                    'success' => true,
                    'message' => 'อนุมัติเอกสารสำเร็จ'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการอนุมัติ'
                ]);
            }
            break;
            
        case 'reject':
            if (empty($comment)) {
                http_response_code(400);
                echo json_encode(['error' => 'Comment is required for rejection']);
                exit;
            }
            
            $result = $document->reject($documentId, $currentUserId, $comment);
            
            if ($result) {
                // Send notification to uploader
                $notification->createDocumentNotification(
                    'rejected',
                    $documentId,
                    $doc['title'],
                    $doc['uploaded_by'],
                    getCurrentUser()['first_name'] . ' ' . getCurrentUser()['last_name']
                );
                
                echo json_encode([
                    'success' => true,
                    'message' => 'ไม่อนุมัติเอกสารสำเร็จ'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการไม่อนุมัติ'
                ]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Approval API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>