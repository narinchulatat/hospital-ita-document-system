<?php
require_once '../includes/auth.php';

// Require admin role
requireRole(ROLE_ADMIN);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'การดำเนินการไม่ถูกต้อง'];

try {
    $user = new User();
    $activityLog = new ActivityLog();
    
    switch ($action) {
        case 'toggle_status':
            $userId = (int)($_POST['user_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if (!$userId || !in_array($status, ['active', 'inactive'])) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            if ($userId == $_SESSION['user_id']) {
                throw new Exception('ไม่สามารถเปลี่ยนสถานะของตนเองได้');
            }
            
            $result = $user->updateStatus($userId, $status);
            
            if ($result) {
                $userData = $user->getById($userId);
                $activityLog->log($_SESSION['user_id'], ACTION_UPDATE, 'users', $userId, 
                    json_encode(['status' => $status]));
                
                $response = [
                    'success' => true, 
                    'message' => $status === 'active' ? 'เปิดใช้งานผู้ใช้เรียบร้อยแล้ว' : 'ปิดใช้งานผู้ใช้เรียบร้อยแล้ว'
                ];
            }
            break;
            
        case 'reset_password':
            $userId = (int)($_POST['user_id'] ?? 0);
            
            if (!$userId) {
                throw new Exception('ข้อมูลไม่ถูกต้อง');
            }
            
            // Generate new password
            $newPassword = generateRandomPassword();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $result = $user->updatePassword($userId, $hashedPassword);
            
            if ($result) {
                $userData = $user->getById($userId);
                $activityLog->log($_SESSION['user_id'], ACTION_UPDATE, 'users', $userId, 
                    'Password reset');
                
                // TODO: Send email notification to user
                
                $response = [
                    'success' => true, 
                    'message' => 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว',
                    'new_password' => $newPassword
                ];
            }
            break;
            
        case 'send_notification':
            $userId = (int)($_POST['user_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            
            if (!$userId || !$message) {
                throw new Exception('ข้อมูลไม่ครบถ้วน');
            }
            
            // TODO: Implement notification system
            $notification = new Notification();
            $result = $notification->send($userId, 'ข้อความจากผู้ดูแลระบบ', $message, $_SESSION['user_id']);
            
            if ($result) {
                $response = [
                    'success' => true, 
                    'message' => 'ส่งการแจ้งเตือนเรียบร้อยแล้ว'
                ];
            }
            break;
            
        case 'bulk_activate':
        case 'bulk_deactivate':
        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            
            if (!is_array($ids) || empty($ids)) {
                throw new Exception('กรุณาเลือกรายการที่ต้องการดำเนินการ');
            }
            
            // Remove current user from bulk actions
            $ids = array_filter($ids, function($id) {
                return $id != $_SESSION['user_id'];
            });
            
            if (empty($ids)) {
                throw new Exception('ไม่สามารถดำเนินการกับบัญชีของตนเองได้');
            }
            
            $successCount = 0;
            
            foreach ($ids as $userId) {
                $userId = (int)$userId;
                if (!$userId) continue;
                
                try {
                    switch ($action) {
                        case 'bulk_activate':
                            if ($user->updateStatus($userId, 'active')) {
                                $successCount++;
                                $activityLog->log($_SESSION['user_id'], ACTION_UPDATE, 'users', $userId, 
                                    json_encode(['status' => 'active']));
                            }
                            break;
                            
                        case 'bulk_deactivate':
                            if ($user->updateStatus($userId, 'inactive')) {
                                $successCount++;
                                $activityLog->log($_SESSION['user_id'], ACTION_UPDATE, 'users', $userId, 
                                    json_encode(['status' => 'inactive']));
                            }
                            break;
                            
                        case 'bulk_delete':
                            $userData = $user->getById($userId);
                            if ($user->delete($userId)) {
                                $successCount++;
                                $activityLog->log($_SESSION['user_id'], ACTION_DELETE, 'users', $userId, 
                                    json_encode(['username' => $userData['username']]));
                            }
                            break;
                    }
                } catch (Exception $e) {
                    error_log("Bulk action error for user $userId: " . $e->getMessage());
                }
            }
            
            if ($successCount > 0) {
                $actionText = [
                    'bulk_activate' => 'เปิดใช้งาน',
                    'bulk_deactivate' => 'ปิดใช้งาน',
                    'bulk_delete' => 'ลบ'
                ];
                
                $response = [
                    'success' => true, 
                    'message' => $actionText[$action] . "ผู้ใช้เรียบร้อยแล้ว จำนวน {$successCount} รายการ"
                ];
            } else {
                throw new Exception('ไม่สามารถดำเนินการได้');
            }
            break;
            
        default:
            throw new Exception('การดำเนินการไม่ถูกต้อง');
    }
    
} catch (Exception $e) {
    error_log("User actions error: " . $e->getMessage());
    $response = ['success' => false, 'message' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);

function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}
?>