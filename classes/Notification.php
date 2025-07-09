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
     * Create new notification
     */
    public function create($userId, $title, $message, $type = NOTIF_TYPE_INFO, $actionUrl = null) {
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl
        ];
        
        return $this->db->insert('notifications', $data);
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
    public function sendToUsers($userIds, $title, $message, $type = NOTIF_TYPE_INFO, $actionUrl = null) {
        $results = [];
        
        foreach ($userIds as $userId) {
            $results[] = $this->create($userId, $title, $message, $type, $actionUrl);
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
     * Clean old notifications
     */
    public function cleanOldNotifications($daysOld = 30) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        
        $query = "DELETE FROM notifications WHERE created_at < ? AND is_read = 1";
        $this->db->execute($query, [$cutoffDate]);
        
        return true;
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
    
    /**
     * Get total notification count for user
     */
    public function getTotalCount($userId, $unreadOnly = false) {
        $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        
        $result = $this->db->fetch($query, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Delete old notifications (alias for cleanOldNotifications)
     */
    public function deleteOld($daysOld = 30) {
        return $this->cleanOldNotifications($daysOld);
    }
    
    /**
     * Get notification by ID
     */
    public function getById($notificationId, $userId = null) {
        $query = "SELECT * FROM notifications WHERE id = ?";
        $params = [$notificationId];
        
        if ($userId) {
            $query .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        return $this->db->fetch($query, $params);
    }
    
    /**
     * Bulk mark notifications as read
     */
    public function bulkMarkAsRead($notificationIds, $userId) {
        if (empty($notificationIds)) {
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($notificationIds), '?'));
        $query = "UPDATE notifications 
                  SET is_read = 1, read_at = ? 
                  WHERE id IN ({$placeholders}) AND user_id = ?";
        
        $params = [date('Y-m-d H:i:s')];
        $params = array_merge($params, $notificationIds);
        $params[] = $userId;
        
        $stmt = $this->db->execute($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Bulk delete notifications
     */
    public function bulkDelete($notificationIds, $userId) {
        if (empty($notificationIds)) {
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($notificationIds), '?'));
        $query = "DELETE FROM notifications WHERE id IN ({$placeholders}) AND user_id = ?";
        
        $params = $notificationIds;
        $params[] = $userId;
        
        $stmt = $this->db->execute($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Get notifications by type
     */
    public function getByType($type, $userId = null, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM notifications WHERE type = ?";
        $params = [$type];
        
        if ($userId) {
            $query .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
}