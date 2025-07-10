<?php
/**
 * Document Class
 * Handles document management operations
 */

class Document {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create new document with proper field mapping
     */
    public function create($data) {
        $this->db->beginTransaction();
        
        try {
            // Ensure correct field names and defaults
            if (!isset($data['file_name'])) {
                $data['file_name'] = $data['filename'] ?? '';
            }
            if (!isset($data['version'])) {
                $data['version'] = '1.0';
            }
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }
            if (!isset($data['visibility'])) {
                $data['visibility'] = 'public';
            }
            if (!isset($data['download_count'])) {
                $data['download_count'] = 0;
            }
            if (!isset($data['view_count'])) {
                $data['view_count'] = 0;
            }
            if (!isset($data['is_featured'])) {
                $data['is_featured'] = 0;
            }
            if (!isset($data['virus_scan_status'])) {
                $data['virus_scan_status'] = 'pending';
            }
            
            // Clean up old field names
            unset($data['fiscal_years'], $data['quarters'], $data['filename']);
            
            // Insert document
            $documentId = $this->db->insert('documents', $data);
            
            $this->db->commit();
            
            return $documentId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get document by ID with proper field mapping
     */
    public function getById($id) {
        $query = "SELECT d.*, c.name as category_name, 
                         u1.first_name as uploader_first_name, u1.last_name as uploader_last_name,
                         u2.first_name as approver_first_name, u2.last_name as approver_last_name
                  FROM documents d
                  JOIN categories c ON d.category_id = c.id
                  JOIN users u1 ON d.uploaded_by = u1.id
                  LEFT JOIN users u2 ON d.approved_by = u2.id
                  WHERE d.id = ?";
        
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Update document with proper field mapping
     */
    public function update($id, $data) {
        $oldDocument = $this->getById($id);
        
        $this->db->beginTransaction();
        
        try {
            // Clean up old field names
            unset($data['fiscal_years'], $data['quarters']);
            
            // Set updated_at
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Update document
            $this->db->update('documents', $data, ['id' => $id]);
            
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Delete document
     */
    public function delete($id) {
        $document = $this->getById($id);
        
        if ($document) {
            // Delete file
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            
            // Delete from database (cascade will handle related records)
            $this->db->delete('documents', ['id' => $id]);
            
            // Log activity
            logActivity(ACTION_DELETE, 'documents', $id, $document);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all documents with filters and pagination
     */
    public function getAll($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT d.*, c.name as category_name,
                         u1.first_name as uploader_first_name, u1.last_name as uploader_last_name
                  FROM documents d
                  JOIN categories c ON d.category_id = c.id
                  JOIN users u1 ON d.uploaded_by = u1.id";
        
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $whereConditions[] = "d.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "d.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(d.title LIKE ? OR d.description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['uploaded_by'])) {
            $whereConditions[] = "d.uploaded_by = ?";
            $params[] = $filters['uploaded_by'];
        }
        
        if (isset($filters['is_public'])) {
            $whereConditions[] = "d.is_public = ?";
            $params[] = $filters['is_public'];
        }
        
        if (!empty($filters['fiscal_year_id'])) {
            $query .= " JOIN document_fiscal_years dfy ON d.id = dfy.document_id";
            $whereConditions[] = "dfy.fiscal_year_id = ?";
            $params[] = $filters['fiscal_year_id'];
        }
        
        if (!empty($filters['quarter_id'])) {
            $query .= " JOIN document_quarters dq ON d.id = dq.document_id";
            $whereConditions[] = "dq.quarter_id = ?";
            $params[] = $filters['quarter_id'];
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        // Add ordering
        $query .= " ORDER BY d.created_at DESC";
        
        // Add pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get total document count with filters
     */
    public function getTotalCount($filters = []) {
        $query = "SELECT COUNT(DISTINCT d.id) as count FROM documents d";
        $params = [];
        $whereConditions = [];
        
        // Apply same filters as getAll
        if (!empty($filters['status'])) {
            $whereConditions[] = "d.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "d.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(d.title LIKE ? OR d.description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['uploaded_by'])) {
            $whereConditions[] = "d.uploaded_by = ?";
            $params[] = $filters['uploaded_by'];
        }
        
        if (isset($filters['is_public'])) {
            $whereConditions[] = "d.is_public = ?";
            $params[] = $filters['is_public'];
        }
        
        if (!empty($filters['fiscal_year_id'])) {
            $query .= " JOIN document_fiscal_years dfy ON d.id = dfy.document_id";
            $whereConditions[] = "dfy.fiscal_year_id = ?";
            $params[] = $filters['fiscal_year_id'];
        }
        
        if (!empty($filters['quarter_id'])) {
            $query .= " JOIN document_quarters dq ON d.id = dq.document_id";
            $whereConditions[] = "dq.quarter_id = ?";
            $params[] = $filters['quarter_id'];
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $result = $this->db->fetch($query, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Approve document using correct field names
     */
    public function approve($id, $approverId, $notes = '') {
        $data = [
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'approval_notes' => $notes,
            'visibility' => 'public'
        ];
        
        return $this->db->update('documents', $data, ['id' => $id]);
    }
    
    /**
     * Reject document using correct field names
     */
    public function reject($id, $approverId, $notes = '') {
        $data = [
            'status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'approval_notes' => $notes
        ];
        
        return $this->db->update('documents', $data, ['id' => $id]);
    }
    
    /**
     * Increment view count
     */
    public function incrementViewCount($id) {
        $this->db->execute("UPDATE documents SET view_count = view_count + 1 WHERE id = ?", [$id]);
    }
    
    /**
     * Increment download count
     */
    public function incrementDownloadCount($id) {
        $this->db->execute("UPDATE documents SET download_count = download_count + 1 WHERE id = ?", [$id]);
        
        // Log download activity
        logActivity(ACTION_DOWNLOAD, 'documents', $id);
    }
    
    /**
     * Get document versions
     */
    public function getVersions($documentId) {
        $query = "SELECT dv.*, u.first_name, u.last_name
                  FROM document_versions dv
                  JOIN users u ON dv.created_by = u.id
                  WHERE dv.document_id = ?
                  ORDER BY dv.created_at DESC";
        
        return $this->db->fetchAll($query, [$documentId]);
    }
    
    /**
     * Create new version
     */
    public function createVersion($documentId, $versionData) {
        $this->db->beginTransaction();
        
        try {
            // Insert new version
            $versionData['document_id'] = $documentId;
            $this->db->insert('document_versions', $versionData);
            
            // Update document with new version info
            $documentData = [
                'version' => $versionData['version'],
                'filename' => $versionData['filename'],
                'file_path' => $versionData['file_path'],
                'file_size' => $versionData['file_size'],
                'status' => DOC_STATUS_PENDING // Reset to pending for approval
            ];
            $this->db->update('documents', $documentData, ['id' => $documentId]);
            
            $this->db->commit();
            
            // Log activity
            logActivity(ACTION_UPDATE, 'documents', $documentId, null, $versionData);
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get pending documents for approval
     */
    public function getPendingDocuments($page = 1, $limit = 20) {
        $filters = ['status' => 'pending'];
        return $this->getAll($filters, $page, $limit);
    }
    
    /**
     * Update virus scan status
     */
    public function updateVirusScanStatus($id, $status, $scanDate = null) {
        $data = [
            'virus_scan_status' => $status,
            'virus_scan_date' => $scanDate ?: date('Y-m-d H:i:s')
        ];
        
        return $this->db->update('documents', $data, ['id' => $id]);
    }
    
    /**
     * Calculate and update file checksum
     */
    public function updateChecksum($id, $filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $checksum = hash_file('sha256', $filePath);
        return $this->db->update('documents', ['checksum' => $checksum], ['id' => $id]);
    }
    
    /**
     * Get documents by tag
     */
    public function getByTag($tag, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT d.*, c.name as category_name
                  FROM documents d
                  JOIN categories c ON d.category_id = c.id
                  WHERE d.tags LIKE ? AND d.status = 'approved' AND d.visibility = 'public'
                  ORDER BY d.created_at DESC
                  LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($query, ["%{$tag}%", $limit, $offset]);
    }
    
    /**
     * Get expiring documents
     */
    public function getExpiringDocuments($days = 30) {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $query = "SELECT d.*, c.name as category_name
                  FROM documents d
                  JOIN categories c ON d.category_id = c.id
                  WHERE d.expiry_date IS NOT NULL 
                  AND d.expiry_date <= ? 
                  AND d.status = 'approved'
                  ORDER BY d.expiry_date ASC";
        
        return $this->db->fetchAll($query, [$expiryDate]);
    }
    
    /**
     * Get featured documents
     */
    public function getFeaturedDocuments($limit = 10) {
        $query = "SELECT d.*, c.name as category_name
                  FROM documents d
                  JOIN categories c ON d.category_id = c.id
                  WHERE d.is_featured = 1 
                  AND d.status = 'approved' 
                  AND d.visibility = 'public'
                  ORDER BY d.created_at DESC
                  LIMIT ?";
        
        return $this->db->fetchAll($query, [$limit]);
    }
    
    /**
     * Set document as featured
     */
    public function setFeatured($id, $featured = true) {
        return $this->db->update('documents', ['is_featured' => $featured ? 1 : 0], ['id' => $id]);
    }
    
    /**
     * Archive document
     */
    public function archive($id) {
        return $this->db->update('documents', ['status' => 'archived'], ['id' => $id]);
    }
    
    /**
     * Get document statistics
     */
    public function getDocumentStats() {
        $stats = [];
        
        // Total documents
        $stats['total'] = $this->db->getRowCount('documents');
        
        // Documents by status
        foreach (['draft', 'pending', 'approved', 'rejected', 'archived'] as $status) {
            $stats["status_{$status}"] = $this->db->getRowCount('documents', ['status' => $status]);
        }
        
        // Documents by visibility
        foreach (['public', 'private', 'restricted'] as $visibility) {
            $stats["visibility_{$visibility}"] = $this->db->getRowCount('documents', ['visibility' => $visibility]);
        }
        
        // Featured documents
        $stats['featured'] = $this->db->getRowCount('documents', ['is_featured' => 1]);
        
        // Documents with expiry dates
        $stats['with_expiry'] = $this->db->execute("SELECT COUNT(*) as count FROM documents WHERE expiry_date IS NOT NULL")->fetch()['count'];
        
        // Total file size
        $sizeResult = $this->db->fetch("SELECT SUM(file_size) as total_size FROM documents");
        $stats['total_size'] = $sizeResult['total_size'] ?? 0;
        
        return $stats;
    }
}