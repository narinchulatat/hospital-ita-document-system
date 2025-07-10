<?php
/**
 * Categories REST API  
 * Provides JSON API for category data
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../includes/header.php';

try {
    $category = new Category();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single category
                $id = intval($_GET['id']);
                $cat = $category->getById($id);
                
                if (!$cat || !$cat['is_active']) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Category not found'
                    ]);
                    exit;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $cat
                ]);
                
            } elseif (isset($_GET['tree'])) {
                // Get category tree
                $tree = $category->getTree();
                
                echo json_encode([
                    'success' => true,
                    'data' => $tree
                ]);
                
            } else {
                // Get all categories
                $includeCount = isset($_GET['include_count']) && $_GET['include_count'] === '1';
                $categories = $category->getAll($includeCount);
                
                echo json_encode([
                    'success' => true,
                    'data' => $categories
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
    error_log("Categories API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>