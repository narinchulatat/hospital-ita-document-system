<?php
/**
 * Download Class
 * Handles download tracking and statistics
 */

class Download {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Log document download
     */
    public function log($documentId, $userId = null, $fileSize = null) {
        $data = [
            'document_id' => $documentId,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'file_size' => $fileSize
        ];
        
        // Insert download record
        $downloadId = $this->db->insert('downloads', $data);
        
        // Update document download count
        $this->db->execute(
            "UPDATE documents SET download_count = download_count + 1 WHERE id = ?",
            [$documentId]
        );
        
        return $downloadId;
    }
    
    /**
     * Get download statistics
     */
    public function getStats($dateRange = null) {
        $stats = [];
        
        $whereClause = "";
        $params = [];
        
        if ($dateRange) {
            $whereClause = "WHERE download_at >= ? AND download_at <= ?";
            $params = [$dateRange['start'], $dateRange['end']];
        }
        
        // Total downloads
        $query = "SELECT COUNT(*) as count FROM downloads {$whereClause}";
        $result = $this->db->fetch($query, $params);
        $stats['total_downloads'] = $result['count'];
        
        // Unique users
        $query = "SELECT COUNT(DISTINCT user_id) as count FROM downloads {$whereClause} AND user_id IS NOT NULL";
        $result = $this->db->fetch($query, $params);
        $stats['unique_users'] = $result['count'];
        
        // Total data transferred
        $query = "SELECT SUM(file_size) as total_size FROM downloads {$whereClause} AND file_size IS NOT NULL";
        $result = $this->db->fetch($query, $params);
        $stats['total_data_transferred'] = $result['total_size'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Get popular documents
     */
    public function getPopularDocuments($limit = 10, $dateRange = null) {
        $whereClause = "";
        $params = [];
        
        if ($dateRange) {
            $whereClause = "WHERE d.download_at >= ? AND d.download_at <= ?";
            $params = [$dateRange['start'], $dateRange['end']];
        }
        
        $query = "SELECT doc.id, doc.title, doc.file_name, c.name as category_name,
                         COUNT(d.id) as download_count,
                         COUNT(DISTINCT d.user_id) as unique_downloaders
                  FROM downloads d
                  JOIN documents doc ON d.document_id = doc.id
                  JOIN categories c ON doc.category_id = c.id
                  {$whereClause}
                  GROUP BY d.document_id
                  ORDER BY download_count DESC
                  LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get downloads by user
     */
    public function getByUser($userId, $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT d.*, doc.title, doc.file_name, c.name as category_name
                  FROM downloads d
                  JOIN documents doc ON d.document_id = doc.id
                  JOIN categories c ON doc.category_id = c.id
                  WHERE d.user_id = ?
                  ORDER BY d.download_at DESC
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, [$userId, $limit, $offset]);
    }
    
    /**
     * Get downloads by document
     */
    public function getByDocument($documentId, $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT d.*, u.username, u.first_name, u.last_name
                  FROM downloads d
                  LEFT JOIN users u ON d.user_id = u.id
                  WHERE d.document_id = ?
                  ORDER BY d.download_at DESC
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, [$documentId, $limit, $offset]);
    }
    
    /**
     * Get downloads by date range
     */
    public function getByDateRange($startDate, $endDate, $page = 1, $limit = 100) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT d.*, doc.title, doc.file_name, c.name as category_name,
                         u.username, u.first_name, u.last_name
                  FROM downloads d
                  JOIN documents doc ON d.document_id = doc.id
                  JOIN categories c ON doc.category_id = c.id
                  LEFT JOIN users u ON d.user_id = u.id
                  WHERE d.download_at >= ? AND d.download_at <= ?
                  ORDER BY d.download_at DESC
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, [$startDate, $endDate, $limit, $offset]);
    }
    
    /**
     * Get download trends by day
     */
    public function getTrendsByDay($days = 30) {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $query = "SELECT DATE(download_at) as date, COUNT(*) as downloads
                  FROM downloads
                  WHERE download_at >= ?
                  GROUP BY DATE(download_at)
                  ORDER BY date ASC";
        
        return $this->db->fetchAll($query, [$startDate]);
    }
    
    /**
     * Get download trends by hour
     */
    public function getTrendsByHour($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $query = "SELECT HOUR(download_at) as hour, COUNT(*) as downloads
                  FROM downloads
                  WHERE DATE(download_at) = ?
                  GROUP BY HOUR(download_at)
                  ORDER BY hour ASC";
        
        return $this->db->fetchAll($query, [$date]);
    }
    
    /**
     * Get top downloading users
     */
    public function getTopUsers($limit = 10, $dateRange = null) {
        $whereClause = "";
        $params = [];
        
        if ($dateRange) {
            $whereClause = "WHERE d.download_at >= ? AND d.download_at <= ?";
            $params = [$dateRange['start'], $dateRange['end']];
        }
        
        $query = "SELECT u.id, u.username, u.first_name, u.last_name,
                         COUNT(d.id) as download_count,
                         COUNT(DISTINCT d.document_id) as unique_documents
                  FROM downloads d
                  JOIN users u ON d.user_id = u.id
                  {$whereClause}
                  GROUP BY d.user_id
                  ORDER BY download_count DESC
                  LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get downloads by category
     */
    public function getByCategory($limit = 10, $dateRange = null) {
        $whereClause = "";
        $params = [];
        
        if ($dateRange) {
            $whereClause = "WHERE d.download_at >= ? AND d.download_at <= ?";
            $params = [$dateRange['start'], $dateRange['end']];
        }
        
        $query = "SELECT c.id, c.name, COUNT(d.id) as download_count
                  FROM downloads d
                  JOIN documents doc ON d.document_id = doc.id
                  JOIN categories c ON doc.category_id = c.id
                  {$whereClause}
                  GROUP BY c.id
                  ORDER BY download_count DESC
                  LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get downloads by file type
     */
    public function getByFileType($limit = 10, $dateRange = null) {
        $whereClause = "";
        $params = [];
        
        if ($dateRange) {
            $whereClause = "WHERE d.download_at >= ? AND d.download_at <= ?";
            $params = [$dateRange['start'], $dateRange['end']];
        }
        
        $query = "SELECT doc.file_type, COUNT(d.id) as download_count
                  FROM downloads d
                  JOIN documents doc ON d.document_id = doc.id
                  {$whereClause}
                  GROUP BY doc.file_type
                  ORDER BY download_count DESC
                  LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Clean old download logs
     */
    public function cleanup($daysOld = 365) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        
        $query = "DELETE FROM downloads WHERE download_at < ?";
        $stmt = $this->db->execute($query, [$cutoffDate]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Export download data to CSV
     */
    public function exportToCsv($filters = []) {
        $query = "SELECT d.*, doc.title, doc.file_name, doc.file_type,
                         c.name as category_name, u.username, u.first_name, u.last_name
                  FROM downloads d
                  JOIN documents doc ON d.document_id = doc.id
                  JOIN categories c ON doc.category_id = c.id
                  LEFT JOIN users u ON d.user_id = u.id";
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['document_id'])) {
            $whereConditions[] = "d.document_id = ?";
            $params[] = $filters['document_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "d.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "d.download_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "d.download_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $query .= " ORDER BY d.download_at DESC";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get download summary report
     */
    public function getSummaryReport($dateRange = null) {
        $report = [];
        
        // Basic stats
        $report['stats'] = $this->getStats($dateRange);
        
        // Popular documents
        $report['popular_documents'] = $this->getPopularDocuments(10, $dateRange);
        
        // Top users
        $report['top_users'] = $this->getTopUsers(10, $dateRange);
        
        // Category breakdown
        $report['by_category'] = $this->getByCategory(10, $dateRange);
        
        // File type breakdown
        $report['by_file_type'] = $this->getByFileType(10, $dateRange);
        
        // Daily trends (last 30 days)
        $report['daily_trends'] = $this->getTrendsByDay(30);
        
        return $report;
    }
}