<?php
/**
 * Report Class
 * Handles report generation and analytics
 */

class Report {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get user activity report
     */
    public function getUserActivity($dateRange = null, $userId = null) {
        $whereConditions = [];
        $params = [];
        
        if ($dateRange) {
            $whereConditions[] = "al.created_at >= ? AND al.created_at <= ?";
            $params[] = $dateRange['start'];
            $params[] = $dateRange['end'];
        }
        
        if ($userId) {
            $whereConditions[] = "al.user_id = ?";
            $params[] = $userId;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $query = "SELECT al.*, u.username, u.first_name, u.last_name
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  {$whereClause}
                  ORDER BY al.created_at DESC
                  LIMIT 1000";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get document statistics
     */
    public function getDocumentStats($dateRange = null) {
        $document = new Document();
        return $document->getDocumentStats();
    }
    
    /**
     * Get download statistics
     */
    public function getDownloadStats($dateRange = null) {
        $download = new Download();
        return $download->getStats($dateRange);
    }
    
    /**
     * Get top documents
     */
    public function getTopDocuments($limit = 10, $orderBy = 'download_count') {
        $allowedOrderBy = ['download_count', 'view_count', 'created_at'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'download_count';
        }
        
        $query = "SELECT d.*, c.name as category_name
                  FROM documents d
                  JOIN categories c ON d.category_id = c.id
                  WHERE d.status = 'approved'
                  ORDER BY d.{$orderBy} DESC
                  LIMIT ?";
        
        return $this->db->fetchAll($query, [$limit]);
    }
    
    /**
     * Get top users by activity
     */
    public function getTopUsers($limit = 10, $dateRange = null) {
        $whereClause = "";
        $params = [];
        
        if ($dateRange) {
            $whereClause = "WHERE al.created_at >= ? AND al.created_at <= ?";
            $params[] = $dateRange['start'];
            $params[] = $dateRange['end'];
        }
        
        $query = "SELECT u.id, u.username, u.first_name, u.last_name,
                         COUNT(al.id) as activity_count
                  FROM users u
                  LEFT JOIN activity_logs al ON u.id = al.user_id
                  {$whereClause}
                  GROUP BY u.id
                  ORDER BY activity_count DESC
                  LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get top categories
     */
    public function getTopCategories($limit = 10) {
        $query = "SELECT c.*, COUNT(d.id) as document_count
                  FROM categories c
                  LEFT JOIN documents d ON c.id = d.category_id
                  GROUP BY c.id
                  ORDER BY document_count DESC
                  LIMIT ?";
        
        return $this->db->fetchAll($query, [$limit]);
    }
    
    /**
     * Export data to CSV format
     */
    public function exportToCSV($data, $filename = null) {
        if (empty($data)) {
            return false;
        }
        
        $filename = $filename ?: 'report_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Write header
        fputcsv($output, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        return true;
    }
    
    /**
     * Generate system summary report
     */
    public function getSystemSummary() {
        $summary = [];
        
        // User statistics
        $summary['users'] = [
            'total' => $this->db->getRowCount('users'),
            'active' => $this->db->getRowCount('users', ['is_active' => 1])
        ];
        
        // Document statistics
        $summary['documents'] = [
            'total' => $this->db->getRowCount('documents'),
            'approved' => $this->db->getRowCount('documents', ['status' => 'approved']),
            'pending' => $this->db->getRowCount('documents', ['status' => 'pending'])
        ];
        
        // Category statistics
        $summary['categories'] = [
            'total' => $this->db->getRowCount('categories'),
            'active' => $this->db->getRowCount('categories', ['is_active' => 1])
        ];
        
        // Recent activity
        $summary['recent_activity'] = $this->db->fetchAll(
            "SELECT al.*, u.username 
             FROM activity_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             ORDER BY al.created_at DESC 
             LIMIT 10"
        );
        
        return $summary;
    }
}