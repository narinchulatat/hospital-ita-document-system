<?php
/**
 * Approval API Endpoint
 * Handles document approval/rejection requests
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Include configuration and authentication
require_once __DIR__ . '/../includes/header.php';

// Check if user has approval permissions
if (!canApproveDocuments()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ในการอนุมัติเอกสาร']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('โทเค็นความปลอดภัยไม่ถูกต้อง');
    }
    
    $action = $_POST['action'] ?? '';
    $comment = trim($_POST['comment'] ?? '');
    $currentUserId = getCurrentUserId();
    
    // Get document IDs (single or multiple)
    $documentIds = [];
    if (isset($_POST['document_id'])) {
        $documentIds = [intval($_POST['document_id'])];
    } elseif (isset($_POST['document_ids'])) {
        $documentIds = array_map('intval', (array)$_POST['document_ids']);
    }
    
    if (empty($documentIds)) {
        throw new Exception('ไม่ได้ระบุเอกสารที่ต้องการดำเนินการ');
    }
    
    if (!in_array($action, ['approve', 'reject'])) {
        throw new Exception('การดำเนินการไม่ถูกต้อง');
    }
    
    if ($action === 'reject' && empty($comment)) {
        throw new Exception('กรุณาระบุเหตุผลในการไม่อนุมัติ');
    }
    
    $document = new Document();
    $db = Database::getInstance();
    
    $db->beginTransaction();
    
    $processedCount = 0;
    $errors = [];
    $processedDocuments = [];
    
    foreach ($documentIds as $documentId) {
        try {
            // Get document info
            $doc = $document->getById($documentId);
            if (!$doc) {
                $errors[] = "ไม่พบเอกสาร ID: $documentId";
                continue;
            }
            
            if ($doc['status'] !== DOC_STATUS_PENDING) {
                $errors[] = "เอกสาร '{$doc['title']}' ได้รับการดำเนินการแล้ว";
                continue;
            }
            
            // Process approval/rejection
            if ($action === 'approve') {
                $document->approve($documentId, $currentUserId, $comment);
                $newStatus = DOC_STATUS_APPROVED;
                $notificationTitle = 'เอกสารได้รับการอนุมัติ';
                $notificationType = 'document_approved';
            } else {
                $document->reject($documentId, $currentUserId, $comment);
                $newStatus = DOC_STATUS_REJECTED;
                $notificationTitle = 'เอกสารไม่ได้รับการอนุมัติ';
                $notificationType = 'document_rejected';
            }
            
            // Log approval activity
            $logData = [
                'document_id' => $documentId,
                'approver_id' => $currentUserId,
                'action' => $action,
                'comments' => $comment,
                'previous_status' => DOC_STATUS_PENDING,
                'new_status' => $newStatus,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->insert('approval_logs', $logData);
            
            // Send notification to uploader
            $uploaderNotification = [
                'user_id' => $doc['uploaded_by'],
                'title' => $notificationTitle,
                'message' => "เอกสาร \"{$doc['title']}\" " . ($action === 'approve' ? 'ได้รับการอนุมัติ' : 'ไม่ได้รับการอนุมัติ') . " โดย {$currentUser['first_name']} {$currentUser['last_name']}",
                'type' => $notificationType,
                'action_url' => "/public/documents/view.php?id=$documentId",
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->insert('notifications', $uploaderNotification);
            
            // Log activity
            logActivity($action === 'approve' ? ACTION_APPROVE : ACTION_REJECT, 'documents', $documentId);
            
            $processedCount++;
            $processedDocuments[] = [
                'id' => $documentId,
                'title' => $doc['title'],
                'action' => $action,
                'status' => $newStatus
            ];
            
        } catch (Exception $e) {
            $errors[] = "เอกสาร ID $documentId: " . $e->getMessage();
        }
    }
    
    if ($processedCount === 0) {
        throw new Exception('ไม่สามารถดำเนินการกับเอกสารใดได้: ' . implode(', ', $errors));
    }
    
    $db->commit();
    
    // Prepare response message
    $actionText = $action === 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ';
    if (count($documentIds) === 1) {
        $message = "{$actionText}เอกสารเรียบร้อยแล้ว";
    } else {
        $message = "{$actionText}เอกสาร $processedCount รายการเรียบร้อยแล้ว";
        if (!empty($errors)) {
            $message .= " (มีข้อผิดพลาด " . count($errors) . " รายการ)";
        }
    }
    
    $response = [
        'success' => true,
        'message' => $message,
        'processed_count' => $processedCount,
        'total_count' => count($documentIds),
        'processed_documents' => $processedDocuments
    ];
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    
    error_log("Approval API error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}