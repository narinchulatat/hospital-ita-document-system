<?php
/**
 * Documents REST API
 * Provides JSON API for document data
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../includes/header.php';

try {
    $document = new Document();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Parse query parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(100, max(1, intval($_GET['limit'] ?? 20))); // Max 100 items per page
    $category_id = intval($_GET['category_id'] ?? 0);
    $search = trim($_GET['search'] ?? '');
    $sort = $_GET['sort'] ?? 'created_at';
    $order = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
    $status = $_GET['status'] ?? 'approved';
    
    // Validate sort field
    $allowedSortFields = ['id', 'title', 'created_at', 'updated_at', 'download_count', 'view_count'];
    if (!in_array($sort, $allowedSortFields)) {
        $sort = 'created_at';
    }
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single document
                $id = intval($_GET['id']);
                $doc = $document->getById($id);
                
                if (!$doc || ($doc['status'] !== 'approved' || !$doc['is_public'])) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Document not found'
                    ]);
                    exit;
                }
                
                // Increment view count
                $document->incrementViewCount($id);
                
                echo json_encode([
                    'success' => true,
                    'data' => $doc
                ]);
                
            } else {
                // Get document list
                $conditions = [
                    'status' => $status,
                    'is_public' => 1
                ];
                
                if ($category_id > 0) {
                    $conditions['category_id'] = $category_id;
                }
                
                if (!empty($search)) {
                    $conditions['search'] = $search;
                }
                
                $documents = $document->getAll($conditions, $page, $limit, $sort, $order);
                $totalCount = $document->getTotalCount($conditions);
                $totalPages = ceil($totalCount / $limit);
                
                echo json_encode([
                    'success' => true,
                    'data' => $documents,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total_items' => $totalCount,
                        'items_per_page' => $limit,
                        'has_next' => $page < $totalPages,
                        'has_prev' => $page > 1
                    ]
                ]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Documents API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>