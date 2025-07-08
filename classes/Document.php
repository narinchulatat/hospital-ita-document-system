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
     * Create new document
     */
    public function create($data) {
        $this->db->beginTransaction();
        
        try {
            // Insert document
            $documentId = $this->db->insert('documents', $data);
            
            // Insert fiscal years associations if provided
            if (isset($data['fiscal_years']) && is_array($data['fiscal_years'])) {
                foreach ($data['fiscal_years'] as $fiscalYearId) {
                    $this->db->insert('document_fiscal_years', [
                        'document_id' => $documentId,
                        'fiscal_year_id' => $fiscalYearId
                    ]);
                }
            }
            
            // Insert quarters associations if provided
            if (isset($data['quarters']) && is_array($data['quarters'])) {
                foreach ($data['quarters'] as $quarterId) {
                    $this->db->insert('document_quarters', [
                        'document_id' => $documentId,
                        'quarter_id' => $quarterId
                    ]);
                }
            }
            
            // Create initial version
            $versionData = [
                'document_id' => $documentId,
                'version' => $data['version'],
                'filename' => $data['filename'],
                'file_path' => $data['file_path'],
                'file_size' => $data['file_size'],
                'change_notes' => 'เริ่มต้นเอกสาร',
                'created_by' => $data['uploaded_by']
            ];
            $this->db->insert('document_versions', $versionData);
            
            $this->db->commit();
            
            // Log activity
            logActivity(ACTION_CREATE, 'documents', $documentId, null, $data);
            
            return $documentId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get document by ID
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
        
        $document = $this->db->fetch($query, [$id]);
        
        if ($document) {
            // Get fiscal years
            $fiscalYears = $this->db->fetchAll(
                "SELECT fy.* FROM fiscal_years fy
                 JOIN document_fiscal_years dfy ON fy.id = dfy.fiscal_year_id
                 WHERE dfy.document_id = ?", [$id]
            );
            $document['fiscal_years'] = $fiscalYears;
            
            // Get quarters
            $quarters = $this->db->fetchAll(
                "SELECT q.* FROM quarters q
                 JOIN document_quarters dq ON q.id = dq.quarter_id
                 WHERE dq.document_id = ?", [$id]
            );
            $document['quarters'] = $quarters;
        }
        
        return $document;
    }
    
    /**
     * Update document
     */
    public function update($id, $data) {
        $oldDocument = $this->getById($id);
        
        $this->db->beginTransaction();
        
        try {
            // Update document
            $this->db->update('documents', $data, ['id' => $id]);
            
            // Update fiscal years if provided
            if (isset($data['fiscal_years'])) {
                $this->db->delete('document_fiscal_years', ['document_id' => $id]);
                foreach ($data['fiscal_years'] as $fiscalYearId) {
                    $this->db->insert('document_fiscal_years', [
                        'document_id' => $id,
                        'fiscal_year_id' => $fiscalYearId
                    ]);
                }
            }
            
            // Update quarters if provided
            if (isset($data['quarters'])) {
                $this->db->delete('document_quarters', ['document_id' => $id]);
                foreach ($data['quarters'] as $quarterId) {
                    $this->db->insert('document_quarters', [
                        'document_id' => $id,
                        'quarter_id' => $quarterId
                    ]);
                }
            }
            
            $this->db->commit();
            
            // Log activity
            logActivity(ACTION_UPDATE, 'documents', $id, $oldDocument, $data);
            
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
     * Approve document
     */
    public function approve($id, $approverId, $comment = '') {
        $data = [
            'status' => DOC_STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'approval_comment' => $comment,
            'is_public' => 1
        ];
        
        $result = $this->db->update('documents', $data, ['id' => $id]);
        
        if ($result) {
            // Log activity
            logActivity(ACTION_APPROVE, 'documents', $id, null, $data);
            
            // Send notification to uploader
            $document = $this->getById($id);
            if ($document) {
                sendNotification(
                    $document['uploaded_by'],
                    'เอกสารได้รับการอนุมัติ',
                    "เอกสาร \"{$document['title']}\" ได้รับการอนุมัติแล้ว",
                    NOTIF_TYPE_SUCCESS,
                    "/documents/view.php?id={$id}"
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Reject document
     */
    public function reject($id, $approverId, $comment = '') {
        $data = [
            'status' => DOC_STATUS_REJECTED,
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'approval_comment' => $comment
        ];
        
        $result = $this->db->update('documents', $data, ['id' => $id]);
        
        if ($result) {
            // Log activity
            logActivity(ACTION_REJECT, 'documents', $id, null, $data);
            
            // Send notification to uploader
            $document = $this->getById($id);
            if ($document) {
                sendNotification(
                    $document['uploaded_by'],
                    'เอกสารไม่ได้รับการอนุมัติ',
                    "เอกสาร \"{$document['title']}\" ไม่ได้รับการอนุมัติ: {$comment}",
                    NOTIF_TYPE_WARNING,
                    "/documents/view.php?id={$id}"
                );
            }
        }
        
        return $result;
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
    public function getPendingDocuments($page = 1, $limit = ITEMS_PER_PAGE) {
        $filters = ['status' => DOC_STATUS_PENDING];
        return $this->getAll($filters, $page, $limit);
    }
}