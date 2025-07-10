<?php
/**
 * Staff Document Delete Script
 * Handle document deletion
 */

require_once '../../includes/header.php';

// Require staff role
requireRole(ROLE_STAFF);

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/staff/documents/');
    exit;
}

$error = '';
$success = '';

try {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }
    
    $documentId = (int)($_POST['id'] ?? 0);
    
    if (!$documentId) {
        throw new Exception('Document ID is required');
    }
    
    $documentObj = new Document();
    $fileManager = new FileManager();
    $currentUserId = getCurrentUserId();
    
    // Get document details
    $document = $documentObj->getById($documentId);
    
    if (!$document) {
        throw new Exception('Document not found');
    }
    
    // Check if document belongs to current user
    if ($document['uploaded_by'] != $currentUserId) {
        throw new Exception('You do not have permission to delete this document');
    }
    
    // Check if document can be deleted (only draft documents)
    if ($document['status'] !== DOC_STATUS_DRAFT) {
        throw new Exception('Only draft documents can be deleted');
    }
    
    // Delete the document file first
    if (!empty($document['file_path']) && file_exists($document['file_path'])) {
        if (!unlink($document['file_path'])) {
            error_log("Failed to delete file: " . $document['file_path']);
            // Continue with database deletion even if file deletion fails
        }
    }
    
    // Delete document from database
    $result = $documentObj->delete($documentId);
    
    if (!$result) {
        throw new Exception('Failed to delete document from database');
    }
    
    // Log activity
    $activityLog = new ActivityLog();
    $activityLog->log(ACTION_DELETE, 'document', $documentId, 'Deleted document: ' . $document['title']);
    
    // Set success message and redirect
    $success = 'ลบเอกสารเรียบร้อยแล้ว';
    header('Location: ' . BASE_URL . '/staff/documents/?success=' . urlencode($success));
    exit;
    
} catch (Exception $e) {
    error_log("Document delete error: " . $e->getMessage());
    $error = $e->getMessage();
    
    // Redirect back with error
    header('Location: ' . BASE_URL . '/staff/documents/?error=' . urlencode($error));
    exit;
}
?>