<?php
/**
 * Notifications API Endpoint
 */

header('Content-Type: application/json');
require_once '../includes/auth.php';

// Require login for all notification operations
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $notification = new Notification();
    $currentUserId = getCurrentUserId();
    
    switch ($method) {
        case 'GET':
            handleGetRequest($notification, $currentUserId, $action);
            break;
            
        case 'POST':
            handlePostRequest($notification, $currentUserId);
            break;
            
        case 'DELETE':
            handleDeleteRequest($notification, $currentUserId);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Notifications API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function handleGetRequest($notification, $userId, $action) {
    switch ($action) {
        case 'list':
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            
            $notifications = $notification->getForUser($userId, $page, $limit, $unreadOnly);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'page' => $page,
                'limit' => $limit
            ]);
            break;
            
        case 'unread_count':
            $count = $notification->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true,
                'count' => $count
            ]);
            break;
            
        case 'statistics':
            $stats = $notification->getStatistics($userId);
            
            echo json_encode([
                'success' => true,
                'statistics' => $stats
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handlePostRequest($notification, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }
    
    // Verify CSRF token
    if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        return;
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'mark_read':
            if (!isset($input['notification_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing notification_id']);
                return;
            }
            
            $result = $notification->markAsRead($input['notification_id'], $userId);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'ทำเครื่องหมายว่าอ่านแล้ว' : 'เกิดข้อผิดพลาด'
            ]);
            break;
            
        case 'mark_all_read':
            $result = $notification->markAllAsRead($userId);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'ทำเครื่องหมายทั้งหมดว่าอ่านแล้ว' : 'เกิดข้อผิดพลาด'
            ]);
            break;
            
        case 'create':
            // Only admins can create notifications for other users
            if (!hasPermission('user.create')) {
                http_response_code(403);
                echo json_encode(['error' => 'ไม่มีสิทธิ์สร้างการแจ้งเตือน']);
                return;
            }
            
            $requiredFields = ['recipient_id', 'title', 'message'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Missing required field: {$field}"]);
                    return;
                }
            }
            
            $result = $notification->create(
                $input['recipient_id'],
                $input['title'],
                $input['message'],
                $input['type'] ?? NOTIF_TYPE_INFO,
                $input['action_url'] ?? null
            );
            
            echo json_encode([
                'success' => $result !== false,
                'notification_id' => $result,
                'message' => $result !== false ? 'สร้างการแจ้งเตือนสำเร็จ' : 'เกิดข้อผิดพลาด'
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function handleDeleteRequest($notification, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['notification_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing notification_id']);
        return;
    }
    
    // Verify CSRF token
    if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        return;
    }
    
    $result = $notification->delete($input['notification_id'], $userId);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'ลบการแจ้งเตือนสำเร็จ' : 'เกิดข้อผิดพลาด'
    ]);
}
?>