<?php
/**
 * Audit Class
 * Handles audit logging and security monitoring
 */

class Audit {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Log activity
     */
    public function logActivity($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null, $userId = null) {
        $data = [
            'user_id' => $userId ?: getCurrentUserId(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        return $this->db->insert('activity_logs', $data);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get activity logs
     */
    public function getActivityLogs($filters = [], $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT al.*, 
                         CONCAT(u.first_name, ' ', u.last_name) as user_name,
                         u.username
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id";
        
        $params = [];
        $whereConditions = [];
        
        // Apply filters
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
        
        if (!empty($filters['start_date'])) {
            $whereConditions[] = "al.created_at >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $whereConditions[] = "al.created_at <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['ip_address'])) {
            $whereConditions[] = "al.ip_address = ?";
            $params[] = $filters['ip_address'];
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get total activity count
     */
    public function getTotalActivityCount($filters = []) {
        $query = "SELECT COUNT(*) as count FROM activity_logs al";
        $params = [];
        $whereConditions = [];
        
        // Apply same filters as getActivityLogs
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
        
        if (!empty($filters['start_date'])) {
            $whereConditions[] = "al.created_at >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $whereConditions[] = "al.created_at <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['ip_address'])) {
            $whereConditions[] = "al.ip_address = ?";
            $params[] = $filters['ip_address'];
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $result = $this->db->fetch($query, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId, $days = 30) {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $query = "SELECT 
                    action,
                    COUNT(*) as count,
                    MAX(created_at) as last_activity
                  FROM activity_logs 
                  WHERE user_id = ? AND created_at >= ?
                  GROUP BY action
                  ORDER BY count DESC";
        
        return $this->db->fetchAll($query, [$userId, $startDate]);
    }
    
    /**
     * Get suspicious activities
     */
    public function getSuspiciousActivities($days = 7) {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $suspiciousActivities = [];
        
        // Multiple failed login attempts
        $query = "SELECT 
                    ip_address,
                    COUNT(*) as failed_attempts,
                    MAX(created_at) as last_attempt
                  FROM activity_logs 
                  WHERE action = 'login_failed' 
                    AND created_at >= ?
                  GROUP BY ip_address
                  HAVING failed_attempts >= 5
                  ORDER BY failed_attempts DESC";
        
        $failedLogins = $this->db->fetchAll($query, [$startDate]);
        if (!empty($failedLogins)) {
            $suspiciousActivities['failed_logins'] = $failedLogins;
        }
        
        // Multiple downloads from same IP
        $query = "SELECT 
                    ip_address,
                    COUNT(*) as download_count,
                    COUNT(DISTINCT user_id) as user_count
                  FROM activity_logs 
                  WHERE action = 'download' 
                    AND created_at >= ?
                  GROUP BY ip_address
                  HAVING download_count >= 50
                  ORDER BY download_count DESC";
        
        $bulkDownloads = $this->db->fetchAll($query, [$startDate]);
        if (!empty($bulkDownloads)) {
            $suspiciousActivities['bulk_downloads'] = $bulkDownloads;
        }
        
        // Unusual activity hours (outside business hours)
        $query = "SELECT 
                    al.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    COUNT(*) as after_hours_count
                  FROM activity_logs al
                  JOIN users u ON al.user_id = u.id
                  WHERE al.created_at >= ?
                    AND (HOUR(al.created_at) < 8 OR HOUR(al.created_at) > 18)
                    AND WEEKDAY(al.created_at) < 5
                  GROUP BY al.user_id, user_name
                  HAVING after_hours_count >= 10
                  ORDER BY after_hours_count DESC";
        
        $afterHours = $this->db->fetchAll($query, [$startDate]);
        if (!empty($afterHours)) {
            $suspiciousActivities['after_hours'] = $afterHours;
        }
        
        return $suspiciousActivities;
    }
    
    /**
     * Get failed login attempts
     */
    public function getFailedLoginAttempts($hours = 24) {
        $startTime = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        $query = "SELECT 
                    ip_address,
                    user_agent,
                    COUNT(*) as attempts,
                    MIN(created_at) as first_attempt,
                    MAX(created_at) as last_attempt
                  FROM activity_logs 
                  WHERE action = 'login_failed' 
                    AND created_at >= ?
                  GROUP BY ip_address, user_agent
                  ORDER BY attempts DESC, last_attempt DESC";
        
        return $this->db->fetchAll($query, [$startTime]);
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent($eventType, $description, $severity = 'medium', $userId = null) {
        $data = [
            'user_id' => $userId ?: getCurrentUserId(),
            'action' => 'security_event',
            'table_name' => 'security',
            'new_values' => json_encode([
                'event_type' => $eventType,
                'description' => $description,
                'severity' => $severity
            ]),
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        return $this->db->insert('activity_logs', $data);
    }
    
    /**
     * Get data access trail
     */
    public function getDataAccessTrail($tableName, $recordId) {
        $query = "SELECT al.*, 
                         CONCAT(u.first_name, ' ', u.last_name) as user_name,
                         u.username
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.table_name = ? AND al.record_id = ?
                  ORDER BY al.created_at DESC";
        
        return $this->db->fetchAll($query, [$tableName, $recordId]);
    }
    
    /**
     * Get user session information
     */
    public function getUserSessions($userId, $days = 30) {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $query = "SELECT 
                    DATE(created_at) as session_date,
                    MIN(CASE WHEN action = 'login' THEN created_at END) as login_time,
                    MAX(CASE WHEN action = 'logout' THEN created_at END) as logout_time,
                    ip_address,
                    user_agent
                  FROM activity_logs 
                  WHERE user_id = ? 
                    AND action IN ('login', 'logout')
                    AND created_at >= ?
                  GROUP BY DATE(created_at), ip_address, user_agent
                  ORDER BY session_date DESC, login_time DESC";
        
        return $this->db->fetchAll($query, [$userId, $startDate]);
    }
    
    /**
     * Export audit logs
     */
    public function exportAuditLogs($filters = [], $format = 'csv') {
        $logs = $this->getActivityLogs($filters, 1, 10000); // Large limit for export
        
        if ($format === 'csv') {
            return $this->exportToCSV($logs);
        } elseif ($format === 'json') {
            return $this->exportToJSON($logs);
        } else {
            throw new Exception('รูปแบบการส่งออกไม่ถูกต้อง');
        }
    }
    
    /**
     * Export to CSV
     */
    private function exportToCSV($logs) {
        $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = TEMP_PATH . $filename;
        
        // Create temp directory if it doesn't exist
        if (!is_dir(TEMP_PATH)) {
            mkdir(TEMP_PATH, 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        if (!$file) {
            throw new Exception("ไม่สามารถสร้างไฟล์ส่งออกได้");
        }
        
        // Write UTF-8 BOM for Excel compatibility
        fwrite($file, "\xEF\xBB\xBF");
        
        // Write headers
        $headers = [
            'ID', 'วันที่', 'ผู้ใช้', 'กิจกรรม', 'ตาราง', 
            'รหัสเรคอร์ด', 'IP Address', 'User Agent'
        ];
        fputcsv($file, $headers);
        
        // Write data
        foreach ($logs as $log) {
            $row = [
                $log['id'],
                $log['created_at'],
                $log['user_name'] ?: 'ระบบ',
                $log['action'],
                $log['table_name'],
                $log['record_id'],
                $log['ip_address'],
                $log['user_agent']
            ];
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return $filepath;
    }
    
    /**
     * Export to JSON
     */
    private function exportToJSON($logs) {
        $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = TEMP_PATH . $filename;
        
        // Create temp directory if it doesn't exist
        if (!is_dir(TEMP_PATH)) {
            mkdir(TEMP_PATH, 0755, true);
        }
        
        $data = [
            'export_date' => date('Y-m-d H:i:s'),
            'total_records' => count($logs),
            'logs' => $logs
        ];
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($filepath, $json) === false) {
            throw new Exception("ไม่สามารถสร้างไฟล์ส่งออกได้");
        }
        
        return $filepath;
    }
    
    /**
     * Clean old audit logs
     */
    public function cleanOldLogs($retentionDays = 365) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));
        
        $query = "DELETE FROM activity_logs WHERE created_at < ?";
        $stmt = $this->db->execute($query, [$cutoffDate]);
        
        $deletedCount = $stmt->rowCount();
        
        // Log the cleanup action
        $this->logActivity(
            'audit_cleanup',
            'activity_logs',
            null,
            null,
            ['deleted_count' => $deletedCount, 'cutoff_date' => $cutoffDate]
        );
        
        return $deletedCount;
    }
    
    /**
     * Get audit statistics
     */
    public function getAuditStatistics($days = 30) {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = [];
        
        // Total activities
        $query = "SELECT COUNT(*) as total FROM activity_logs WHERE created_at >= ?";
        $result = $this->db->fetch($query, [$startDate]);
        $stats['total_activities'] = $result['total'] ?? 0;
        
        // Activities by action
        $query = "SELECT action, COUNT(*) as count 
                  FROM activity_logs 
                  WHERE created_at >= ?
                  GROUP BY action 
                  ORDER BY count DESC";
        $stats['by_action'] = $this->db->fetchAll($query, [$startDate]);
        
        // Activities by user
        $query = "SELECT 
                    al.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    COUNT(*) as count
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.created_at >= ?
                  GROUP BY al.user_id, user_name
                  ORDER BY count DESC
                  LIMIT 10";
        $stats['by_user'] = $this->db->fetchAll($query, [$startDate]);
        
        // Activities by IP
        $query = "SELECT ip_address, COUNT(*) as count
                  FROM activity_logs
                  WHERE created_at >= ?
                  GROUP BY ip_address
                  ORDER BY count DESC
                  LIMIT 10";
        $stats['by_ip'] = $this->db->fetchAll($query, [$startDate]);
        
        // Daily activity trend
        $query = "SELECT 
                    DATE(created_at) as activity_date,
                    COUNT(*) as count
                  FROM activity_logs
                  WHERE created_at >= ?
                  GROUP BY DATE(created_at)
                  ORDER BY activity_date DESC";
        $stats['daily_trend'] = $this->db->fetchAll($query, [$startDate]);
        
        return $stats;
    }
    
    /**
     * Monitor for suspicious patterns
     */
    public function monitorSuspiciousPatterns() {
        $alerts = [];
        
        // Check for rapid successive actions from same IP
        $query = "SELECT ip_address, action, COUNT(*) as count
                  FROM activity_logs
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                  GROUP BY ip_address, action
                  HAVING count >= 100";
        
        $rapidActions = $this->db->fetchAll($query);
        if (!empty($rapidActions)) {
            $alerts[] = [
                'type' => 'rapid_actions',
                'severity' => 'high',
                'description' => 'ตรวจพบกิจกรรมที่เร็วผิดปกติจาก IP เดียวกัน',
                'data' => $rapidActions
            ];
        }
        
        // Check for access to sensitive data outside business hours
        $query = "SELECT 
                    al.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    COUNT(*) as count
                  FROM activity_logs al
                  JOIN users u ON al.user_id = u.id
                  WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    AND al.action IN ('read', 'download')
                    AND (HOUR(al.created_at) < 8 OR HOUR(al.created_at) > 18)
                  GROUP BY al.user_id, user_name
                  HAVING count >= 10";
        
        $afterHoursAccess = $this->db->fetchAll($query);
        if (!empty($afterHoursAccess)) {
            $alerts[] = [
                'type' => 'after_hours_access',
                'severity' => 'medium',
                'description' => 'ตรวจพบการเข้าถึงข้อมูลนอกเวลาทำการ',
                'data' => $afterHoursAccess
            ];
        }
        
        return $alerts;
    }
}