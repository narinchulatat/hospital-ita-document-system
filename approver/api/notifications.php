<?php
/**
 * Notifications API Endpoint
 * Handles notification management for approvers
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Include configuration and authentication
require_once __DIR__ . '/../includes/header.php';

$currentUserId = getCurrentUserId();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get notifications
        $db = Database::getInstance();
        
        $limit = min(50, intval($_GET['limit'] ?? 10));
        $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === '1';
        
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$currentUserId];
        
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $notifications = $db->fetchAll($query, $params);
        
        // Get unread count
        $unreadCount = $db->fetch(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
            [$currentUserId]
        )['count'] ?? 0;
        
        // Format notifications
        $formattedNotifications = array_map(function($notification) {
            return [
                'id' => $notification['id'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'type' => $notification['type'],
                'is_read' => $notification['is_read'],
                'action_url' => $notification['action_url'],
                'created_at' => $notification['created_at'],
                'time_ago' => getTimeAgo($notification['created_at'])
            ];
        }, $notifications);
        
        echo json_encode([
            'success' => true,
            'notifications' => $formattedNotifications,
            'unread_count' => $unreadCount
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle notification actions
        $action = $_POST['action'] ?? '';
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('โทเค็นความปลอดภัยไม่ถูกต้อง');
        }
        
        $db = Database::getInstance();
        
        switch ($action) {
            case 'mark_read':
                $notificationId = intval($_POST['id'] ?? 0);
                if ($notificationId) {
                    $db->update('notifications', 
                        ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 
                        ['id' => $notificationId, 'user_id' => $currentUserId]
                    );
                    echo json_encode(['success' => true, 'message' => 'อ่านแล้ว']);
                } else {
                    throw new Exception('ไม่ได้ระบุรหัสการแจ้งเตือน');
                }
                break;
                
            case 'mark_all_read':
                $db->update('notifications', 
                    ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 
                    ['user_id' => $currentUserId, 'is_read' => 0]
                );
                echo json_encode(['success' => true, 'message' => 'อ่านทั้งหมดแล้ว']);
                break;
                
            case 'delete':
                $notificationId = intval($_POST['id'] ?? 0);
                if ($notificationId) {
                    $db->delete('notifications', ['id' => $notificationId, 'user_id' => $currentUserId]);
                    echo json_encode(['success' => true, 'message' => 'ลบแล้ว']);
                } else {
                    throw new Exception('ไม่ได้ระบุรหัสการแจ้งเตือน');
                }
                break;
                
            case 'clear_all':
                $db->delete('notifications', ['user_id' => $currentUserId]);
                echo json_encode(['success' => true, 'message' => 'ล้างทั้งหมดแล้ว']);
                break;
                
            default:
                throw new Exception('การดำเนินการไม่ถูกต้อง');
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Notifications API error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}