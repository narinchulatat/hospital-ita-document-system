<?php
/**
 * Notification Class
 * Handles notification management
 */

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create new notification with correct enum values
     */
    public function create($userId, $type, $title, $message, $data = null) {
        $notificationData = [
            'user_id' => $userId,
            'type' => $type,  // Use database enum: 'document_uploaded','document_approved','document_rejected','document_expiring','system_alert'
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null
        ];
        
        return $this->db->insert('notifications', $notificationData);
    }
    
    /**
     * Get notifications for user
     */
    public function getForUser($userId, $page = 1, $limit = 20, $unreadOnly = false) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $result = $this->db->fetch($query, [$userId]);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId) {
        $data = [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->update('notifications', $data, [
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId) {
        $data = [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->update('notifications', $data, ['user_id' => $userId]);
    }
    
    /**
     * Delete notification
     */
    public function delete($notificationId, $userId) {
        return $this->db->delete('notifications', [
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Send notification to multiple users
     */
    public function sendToUsers($userIds, $type, $title, $message, $data = null) {
        $results = [];
        
        foreach ($userIds as $userId) {
            $results[] = $this->create($userId, $type, $title, $message, $data);
        }
        
        return $results;
    }
    
    /**
     * Send notification to all users with specific role
     */
    public function sendToRole($roleId, $title, $message, $type = NOTIF_TYPE_INFO, $actionUrl = null) {
        $query = "SELECT id FROM users WHERE role_id = ? AND status = 'active'";
        $users = $this->db->fetchAll($query, [$roleId]);
        
        $userIds = array_column($users, 'id');
        return $this->sendToUsers($userIds, $title, $message, $type, $actionUrl);
    }
    
    /**
     * Send notification to all admins
     */
    public function sendToAdmins($title, $message, $type = NOTIF_TYPE_INFO, $actionUrl = null) {
        return $this->sendToRole(ROLE_ADMIN, $title, $message, $type, $actionUrl);
    }
    
    /**
     * Clean old notifications and expired ones
     */
    public function cleanOldNotifications($daysOld = 30) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        
        // Delete read notifications older than specified days
        $query1 = "DELETE FROM notifications WHERE created_at < ? AND is_read = 1";
        $this->db->execute($query1, [$cutoffDate]);
        
        // Delete expired notifications
        $query2 = "DELETE FROM notifications WHERE expires_at IS NOT NULL AND expires_at < NOW()";
        $this->db->execute($query2);
        
        return true;
    }
    
    /**
     * Create document-related notification using correct enum
     */
    public function createDocumentNotification($action, $documentId, $documentTitle, $recipientUserId, $senderName = null) {
        $typeMap = [
            'uploaded' => 'document_uploaded',
            'approved' => 'document_approved', 
            'rejected' => 'document_rejected',
            'expiring' => 'document_expiring'
        ];
        
        $type = $typeMap[$action] ?? 'system_alert';
        
        $title = "การดำเนินการเอกสาร";
        $message = $senderName ? 
            "{$senderName} ได้ดำเนินการ {$action} เอกสาร: \"{$documentTitle}\"" : 
            "เอกสาร \"{$documentTitle}\" ได้รับการ {$action}";
        
        $data = [
            'document_id' => $documentId,
            'action' => $action,
            'sender' => $senderName
        ];
        
        return $this->create($recipientUserId, $type, $title, $message, $data);
    }
    
    /**
     * Get notification statistics
     */
    public function getStatistics($userId = null) {
        $stats = [];
        
        if ($userId) {
            // User-specific stats
            $stats['total'] = $this->db->getRowCount('notifications', ['user_id' => $userId]);
            $stats['unread'] = $this->db->getRowCount('notifications', ['user_id' => $userId, 'is_read' => 0]);
            $stats['read'] = $stats['total'] - $stats['unread'];
        } else {
            // System-wide stats
            $stats['total'] = $this->db->getRowCount('notifications');
            $stats['unread'] = $this->db->getRowCount('notifications', ['is_read' => 0]);
            $stats['read'] = $stats['total'] - $stats['unread'];
            
            // Stats by type
            foreach ([NOTIF_TYPE_INFO, NOTIF_TYPE_SUCCESS, NOTIF_TYPE_WARNING, NOTIF_TYPE_ERROR] as $type) {
                $stats["type_{$type}"] = $this->db->getRowCount('notifications', ['type' => $type]);
            }
        }
        
        return $stats;
    }
    
    /**
     * Create document notification
     */
    public function createDocumentNotification($action, $documentId, $documentTitle, $recipientUserId, $senderName = null) {
        $actionMessages = [
            'uploaded' => 'อัปโหลดเอกสารใหม่',
            'approved' => 'อนุมัติเอกสาร',
            'rejected' => 'ไม่อนุมัติเอกสาร',
            'updated' => 'แก้ไขเอกสาร'
        ];
        
        $actionTypes = [
            'uploaded' => NOTIF_TYPE_INFO,
            'approved' => NOTIF_TYPE_SUCCESS,
            'rejected' => NOTIF_TYPE_WARNING,
            'updated' => NOTIF_TYPE_INFO
        ];
        
        $title = $actionMessages[$action] ?? 'การดำเนินการเอกสาร';
        $type = $actionTypes[$action] ?? NOTIF_TYPE_INFO;
        
        $message = $senderName ? 
            "{$senderName} {$title}: \"{$documentTitle}\"" : 
            "{$title}: \"{$documentTitle}\"";
        
        $actionUrl = "/documents/view.php?id={$documentId}";
        
        return $this->create($recipientUserId, $title, $message, $type, $actionUrl);
    }
    
    /**
     * Create user notification
     */
    public function createUserNotification($action, $username, $recipientUserId, $senderName = null) {
        $actionMessages = [
            'created' => 'สร้างผู้ใช้ใหม่',
            'updated' => 'แก้ไขข้อมูลผู้ใช้',
            'deleted' => 'ลบผู้ใช้',
            'activated' => 'เปิดใช้งานผู้ใช้',
            'deactivated' => 'ปิดใช้งานผู้ใช้'
        ];
        
        $title = $actionMessages[$action] ?? 'การจัดการผู้ใช้';
        $message = $senderName ? 
            "{$senderName} {$title}: {$username}" : 
            "{$title}: {$username}";
        
        return $this->create($recipientUserId, $title, $message, NOTIF_TYPE_INFO);
    }
    
    /**
     * Create system notification
     */
    public function createSystemNotification($title, $message, $type = NOTIF_TYPE_INFO, $sendToAllAdmins = true) {
        if ($sendToAllAdmins) {
            return $this->sendToAdmins($title, $message, $type);
        } else {
            // Send to all users
            $query = "SELECT id FROM users WHERE status = 'active'";
            $users = $this->db->fetchAll($query);
            $userIds = array_column($users, 'id');
            return $this->sendToUsers($userIds, $title, $message, $type);
        }
    }
}