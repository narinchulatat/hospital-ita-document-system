<?php
/**
 * ActivityLog Class
 * Handles activity logging and audit trail
 */

class ActivityLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Log user activity
     */
    public function log($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        return $this->db->insert('activity_logs', $data);
    }
    
    /**
     * Get activities by user
     */
    public function getByUser($userId, $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT al.*, u.username, u.first_name, u.last_name
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.user_id = ?
                  ORDER BY al.created_at DESC
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, [$userId, $limit, $offset]);
    }
    
    /**
     * Get activities by action
     */
    public function getByAction($action, $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT al.*, u.username, u.first_name, u.last_name
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.action = ?
                  ORDER BY al.created_at DESC
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, [$action, $limit, $offset]);
    }
    
    /**
     * Get activities by table
     */
    public function getByTable($tableName, $recordId = null, $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT al.*, u.username, u.first_name, u.last_name
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.table_name = ?";
        
        $params = [$tableName];
        
        if ($recordId) {
            $query .= " AND al.record_id = ?";
            $params[] = $recordId;
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get all recent activities
     */
    public function getRecent($page = 1, $limit = 100) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT al.*, u.username, u.first_name, u.last_name
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  ORDER BY al.created_at DESC
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, [$limit, $offset]);
    }
    
    /**
     * Clean old activity logs
     */
    public function cleanup($daysOld = 90) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        
        $query = "DELETE FROM activity_logs WHERE created_at < ?";
        $stmt = $this->db->execute($query, [$cutoffDate]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get activity statistics
     */
    public function getStats($dateRange = null) {
        $stats = [];
        
        $whereClause = "";
        $params = [];
        
        if ($dateRange) {
            $whereClause = "WHERE created_at >= ? AND created_at <= ?";
            $params = [$dateRange['start'], $dateRange['end']];
        }
        
        // Total activities
        $query = "SELECT COUNT(*) as count FROM activity_logs {$whereClause}";
        $result = $this->db->fetch($query, $params);
        $stats['total'] = $result['count'];
        
        // Activities by action
        $query = "SELECT action, COUNT(*) as count 
                  FROM activity_logs {$whereClause} 
                  GROUP BY action 
                  ORDER BY count DESC";
        $stats['by_action'] = $this->db->fetchAll($query, $params);
        
        // Activities by user (top 10)
        $query = "SELECT al.user_id, u.username, u.first_name, u.last_name, COUNT(*) as count
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  {$whereClause}
                  GROUP BY al.user_id
                  ORDER BY count DESC
                  LIMIT 10";
        $stats['by_user'] = $this->db->fetchAll($query, $params);
        
        // Activities by table
        $query = "SELECT table_name, COUNT(*) as count 
                  FROM activity_logs 
                  {$whereClause} 
                  AND table_name IS NOT NULL
                  GROUP BY table_name 
                  ORDER BY count DESC";
        $stats['by_table'] = $this->db->fetchAll($query, $params);
        
        return $stats;
    }
    
    /**
     * Get user login history
     */
    public function getLoginHistory($userId = null, $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT al.*, u.username, u.first_name, u.last_name
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.action IN ('login', 'logout')";
        
        $params = [];
        
        if ($userId) {
            $query .= " AND al.user_id = ?";
            $params[] = $userId;
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Export activity logs to CSV
     */
    public function exportToCsv($filters = []) {
        $query = "SELECT al.*, u.username, u.first_name, u.last_name
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id";
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $whereConditions[] = "al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $whereConditions[] = "al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "al.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "al.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $query .= " ORDER BY al.created_at DESC";
        
        return $this->db->fetchAll($query, $params);
    }
}