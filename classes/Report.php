<?php
/**
 * Report Class
 * Handles system reporting and analytics
 */

class Report {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate document statistics report
     */
    public function getDocumentStatistics($startDate = null, $endDate = null) {
        $whereClause = "";
        $params = [];
        
        if ($startDate && $endDate) {
            $whereClause = " WHERE d.created_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $report = [];
        
        // Total documents
        $query = "SELECT COUNT(*) as total FROM documents d{$whereClause}";
        $result = $this->db->fetch($query, $params);
        $report['total_documents'] = $result['total'] ?? 0;
        
        // Documents by status
        $query = "SELECT status, COUNT(*) as count 
                  FROM documents d{$whereClause} 
                  GROUP BY status";
        $results = $this->db->fetchAll($query, $params);
        $report['by_status'] = [];
        foreach ($results as $row) {
            $report['by_status'][$row['status']] = $row['count'];
        }
        
        // Documents by category
        $query = "SELECT c.name, COUNT(d.id) as count 
                  FROM categories c 
                  LEFT JOIN documents d ON c.id = d.category_id{$whereClause}
                  GROUP BY c.id, c.name 
                  ORDER BY count DESC";
        $report['by_category'] = $this->db->fetchAll($query, $params);
        
        // Documents by uploader
        $query = "SELECT CONCAT(u.first_name, ' ', u.last_name) as uploader_name, 
                         COUNT(d.id) as count
                  FROM users u 
                  LEFT JOIN documents d ON u.id = d.uploaded_by{$whereClause}
                  GROUP BY u.id, uploader_name 
                  ORDER BY count DESC 
                  LIMIT 10";
        $report['by_uploader'] = $this->db->fetchAll($query, $params);
        
        // Most downloaded documents
        $downloadWhere = $whereClause ? str_replace('WHERE', 'AND', $whereClause) : '';
        $query = "SELECT d.title, d.download_count, c.name as category_name
                  FROM documents d 
                  JOIN categories c ON d.category_id = c.id
                  WHERE d.status = 'approved'{$downloadWhere}
                  ORDER BY d.download_count DESC 
                  LIMIT 10";
        $report['most_downloaded'] = $this->db->fetchAll($query, $params);
        
        return $report;
    }
    
    /**
     * Generate user activity report
     */
    public function getUserActivityReport($startDate = null, $endDate = null) {
        $whereClause = "";
        $params = [];
        
        if ($startDate && $endDate) {
            $whereClause = " WHERE al.created_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $report = [];
        
        // Total activities
        $query = "SELECT COUNT(*) as total FROM activity_logs al{$whereClause}";
        $result = $this->db->fetch($query, $params);
        $report['total_activities'] = $result['total'] ?? 0;
        
        // Activities by action
        $query = "SELECT action, COUNT(*) as count 
                  FROM activity_logs al{$whereClause} 
                  GROUP BY action 
                  ORDER BY count DESC";
        $report['by_action'] = $this->db->fetchAll($query, $params);
        
        // Activities by user
        $query = "SELECT CONCAT(u.first_name, ' ', u.last_name) as user_name, 
                         COUNT(al.id) as count
                  FROM users u 
                  LEFT JOIN activity_logs al ON u.id = al.user_id{$whereClause}
                  GROUP BY u.id, user_name 
                  ORDER BY count DESC 
                  LIMIT 10";
        $report['by_user'] = $this->db->fetchAll($query, $params);
        
        // Daily activity trend
        $query = "SELECT DATE(created_at) as activity_date, COUNT(*) as count
                  FROM activity_logs al{$whereClause}
                  GROUP BY DATE(created_at)
                  ORDER BY activity_date DESC
                  LIMIT 30";
        $report['daily_trend'] = $this->db->fetchAll($query, $params);
        
        return $report;
    }
    
    /**
     * Generate system overview report
     */
    public function getSystemOverview() {
        $report = [];
        
        // User statistics
        $report['users'] = [
            'total' => $this->db->getRowCount('users'),
            'active' => $this->db->getRowCount('users', ['status' => 'active']),
            'locked' => $this->db->getRowCount('users', ['status' => 'locked']),
            'by_role' => $this->db->fetchAll(
                "SELECT r.name, COUNT(u.id) as count 
                 FROM roles r 
                 LEFT JOIN users u ON r.id = u.role_id 
                 GROUP BY r.id, r.name"
            )
        ];
        
        // Document statistics
        $report['documents'] = [
            'total' => $this->db->getRowCount('documents'),
            'approved' => $this->db->getRowCount('documents', ['status' => 'approved']),
            'pending' => $this->db->getRowCount('documents', ['status' => 'pending']),
            'public' => $this->db->getRowCount('documents', ['is_public' => 1])
        ];
        
        // Category statistics
        $report['categories'] = [
            'total' => $this->db->getRowCount('categories'),
            'active' => $this->db->getRowCount('categories', ['is_active' => 1]),
            'by_level' => $this->db->fetchAll(
                "SELECT level, COUNT(*) as count 
                 FROM categories 
                 WHERE is_active = 1 
                 GROUP BY level"
            )
        ];
        
        // Storage statistics
        $query = "SELECT SUM(file_size) as total_size FROM documents";
        $result = $this->db->fetch($query);
        $report['storage'] = [
            'total_files_size' => $result['total_size'] ?? 0,
            'database_size' => $this->db->getDatabaseSize()
        ];
        
        // Recent activities
        $report['recent_activities'] = $this->db->fetchAll(
            "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC
             LIMIT 20"
        );
        
        return $report;
    }
    
    /**
     * Generate backup report
     */
    public function getBackupReport($days = 30) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $report = [];
        
        // Total backups
        $report['total_backups'] = $this->db->getRowCount('backups');
        
        // Recent backups
        $query = "SELECT COUNT(*) as count FROM backups WHERE created_at >= ?";
        $result = $this->db->fetch($query, [$cutoffDate]);
        $report['recent_backups'] = $result['count'] ?? 0;
        
        // Backup status distribution
        $report['by_status'] = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count 
             FROM backups 
             GROUP BY status"
        );
        
        // Backup size statistics
        $query = "SELECT 
                    SUM(file_size) as total_size,
                    AVG(file_size) as avg_size,
                    MAX(file_size) as max_size
                  FROM backups 
                  WHERE status = 'completed'";
        $result = $this->db->fetch($query);
        $report['size_stats'] = $result;
        
        // Recent backup history
        $report['recent_history'] = $this->db->fetchAll(
            "SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name
             FROM backups b
             LEFT JOIN users u ON b.created_by = u.id
             ORDER BY b.created_at DESC
             LIMIT 10"
        );
        
        return $report;
    }
    
    /**
     * Generate download report
     */
    public function getDownloadReport($startDate = null, $endDate = null, $limit = 20) {
        $whereClause = "";
        $params = [];
        
        if ($startDate && $endDate) {
            $whereClause = " WHERE al.created_at BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $report = [];
        
        // Total downloads
        $downloadCondition = $whereClause ? ' AND' . substr($whereClause, 6) : '';
        $query = "SELECT COUNT(*) as total 
                  FROM activity_logs al 
                  WHERE al.action = 'download'{$downloadCondition}";
        $result = $this->db->fetch($query, $params);
        $report['total_downloads'] = $result['total'] ?? 0;
        
        // Most downloaded documents
        $query = "SELECT d.title, d.download_count, c.name as category_name,
                         CONCAT(u.first_name, ' ', u.last_name) as uploader_name
                  FROM documents d
                  JOIN categories c ON d.category_id = c.id
                  JOIN users u ON d.uploaded_by = u.id
                  WHERE d.status = 'approved'
                  ORDER BY d.download_count DESC
                  LIMIT ?";
        $report['most_downloaded'] = $this->db->fetchAll($query, [$limit]);
        
        // Downloads by category
        $query = "SELECT c.name, SUM(d.download_count) as total_downloads
                  FROM categories c
                  JOIN documents d ON c.id = d.category_id
                  WHERE d.status = 'approved'
                  GROUP BY c.id, c.name
                  ORDER BY total_downloads DESC";
        $report['by_category'] = $this->db->fetchAll($query);
        
        // Download activity by date
        $query = "SELECT DATE(al.created_at) as download_date, COUNT(*) as count
                  FROM activity_logs al
                  WHERE al.action = 'download'{$downloadCondition}
                  GROUP BY DATE(al.created_at)
                  ORDER BY download_date DESC
                  LIMIT 30";
        $report['daily_downloads'] = $this->db->fetchAll($query, $params);
        
        return $report;
    }
    
    /**
     * Export report to CSV
     */
    public function exportToCSV($reportData, $filename = null) {
        if (!$filename) {
            $filename = 'report_' . date('Y-m-d_H-i-s') . '.csv';
        }
        
        $filepath = TEMP_PATH . $filename;
        
        // Create temp directory if it doesn't exist
        if (!is_dir(TEMP_PATH)) {
            mkdir(TEMP_PATH, 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        if (!$file) {
            throw new Exception("Cannot create export file");
        }
        
        // Write UTF-8 BOM for Excel compatibility
        fwrite($file, "\xEF\xBB\xBF");
        
        foreach ($reportData as $section => $data) {
            // Write section header
            fputcsv($file, [$section]);
            fputcsv($file, []);
            
            if (is_array($data) && !empty($data)) {
                if (isset($data[0]) && is_array($data[0])) {
                    // Table data
                    $headers = array_keys($data[0]);
                    fputcsv($file, $headers);
                    
                    foreach ($data as $row) {
                        fputcsv($file, $row);
                    }
                } else {
                    // Key-value data
                    foreach ($data as $key => $value) {
                        fputcsv($file, [$key, $value]);
                    }
                }
            }
            
            fputcsv($file, []);
        }
        
        fclose($file);
        
        return $filepath;
    }
    
    /**
     * Generate custom report
     */
    public function generateCustomReport($query, $params = []) {
        try {
            return $this->db->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Custom report generation failed: " . $e->getMessage());
            throw new Exception("รายงานไม่สามารถสร้างได้");
        }
    }
    
    /**
     * Get report templates
     */
    public function getReportTemplates() {
        return [
            'document_stats' => [
                'name' => 'สถิติเอกสาร',
                'description' => 'รายงานสถิติเอกสารทั้งหมดในระบบ',
                'method' => 'getDocumentStatistics'
            ],
            'user_activity' => [
                'name' => 'กิจกรรมผู้ใช้',
                'description' => 'รายงานกิจกรรมของผู้ใช้ในระบบ',
                'method' => 'getUserActivityReport'
            ],
            'system_overview' => [
                'name' => 'ภาพรวมระบบ',
                'description' => 'รายงานภาพรวมการใช้งานระบบ',
                'method' => 'getSystemOverview'
            ],
            'backup_report' => [
                'name' => 'รายงานการสำรองข้อมูล',
                'description' => 'รายงานสถานะการสำรองข้อมูล',
                'method' => 'getBackupReport'
            ],
            'download_report' => [
                'name' => 'รายงานการดาวน์โหลด',
                'description' => 'รายงานการดาวน์โหลดเอกสาร',
                'method' => 'getDownloadReport'
            ]
        ];
    }
}