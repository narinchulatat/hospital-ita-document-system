<?php
require_once '../../includes/auth.php';

$documentId = (int)($_GET['id'] ?? 0);
$preview = isset($_GET['preview']);

if (!$documentId) {
    header('HTTP/1.0 400 Bad Request');
    die('Invalid document ID');
}

try {
    $document = new Document();
    $doc = $document->getById($documentId);
    
    // Check if document exists and is public
    if (!$doc || !$doc['is_public'] || $doc['status'] !== DOC_STATUS_APPROVED) {
        header('HTTP/1.0 404 Not Found');
        die('Document not found');
    }
    
    // Check if file exists
    if (!file_exists($doc['file_path'])) {
        header('HTTP/1.0 404 Not Found');
        die('File not found');
    }
    
    // Increment download count (except for preview)
    if (!$preview) {
        $document->incrementDownloadCount($documentId);
        
        // Log download activity
        logActivity(ACTION_DOWNLOAD, 'documents', $documentId);
    }
    
    // Get MIME type
    $mimeType = $doc['mime_type'];
    if (!$mimeType) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $doc['file_path']);
        finfo_close($finfo);
    }
    
    // Set headers
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . $doc['file_size']);
    
    if ($preview) {
        // For preview, display inline
        header('Content-Disposition: inline; filename="' . $doc['original_filename'] . '"');
    } else {
        // For download, force download
        header('Content-Disposition: attachment; filename="' . $doc['original_filename'] . '"');
    }
    
    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    
    // Output file
    readfile($doc['file_path']);
    
} catch (Exception $e) {
    error_log("Document download error: " . $e->getMessage());
    header('HTTP/1.0 500 Internal Server Error');
    die('Internal server error');
}
?>