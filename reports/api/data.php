<?php
/**
 * Reports Data API
 * Provides JSON data for reports
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include required files
require_once __DIR__ . '/../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get request parameters
$type = $_GET['type'] ?? '';
$dateRange = $_GET['date_range'] ?? 'last_30_days';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$category = $_GET['category'] ?? '';
$limit = intval($_GET['limit'] ?? 10);

// Validate parameters
if (empty($type)) {
    http_response_code(400);
    echo json_encode(['error' => 'Type parameter is required']);
    exit();
}

// Apply date filter
$dateFilter = null;
if ($dateRange === 'custom' && $startDate && $endDate) {
    $dateFilter = [
        'start' => $startDate . ' 00:00:00',
        'end' => $endDate . ' 23:59:59'
    ];
} elseif ($dateRange !== 'all') {
    $dateFilter = getDateRange($dateRange);
}

try {
    $db = Database::getInstance();
    $data = [];
    
    switch ($type) {
        case 'dashboard_stats':
            // Get dashboard summary statistics
            $data = [
                'documents' => [
                    'total' => $db->getRowCount('documents'),
                    'approved' => $db->getRowCount('documents', ['status' => 'approved']),
                    'pending' => $db->getRowCount('documents', ['status' => 'pending']),
                    'rejected' => $db->getRowCount('documents', ['status' => 'rejected'])
                ],
                'users' => [
                    'total' => $db->getRowCount('users'),
                    'active' => $db->getRowCount('users', ['is_active' => 1])
                ],
                'categories' => [
                    'total' => $db->getRowCount('categories'),
                    'active' => $db->getRowCount('categories', ['is_active' => 1])
                ]
            ];
            break;
            
        case 'documents_by_category':
            // Get documents grouped by category
            $whereClause = '';
            $params = [];
            
            if ($dateFilter) {
                $whereClause = 'WHERE d.created_at >= ? AND d.created_at <= ?';
                $params = [$dateFilter['start'], $dateFilter['end']];
            }
            
            $results = $db->fetchAll("
                SELECT c.name as category_name, COUNT(d.id) as document_count
                FROM categories c
                LEFT JOIN documents d ON c.id = d.category_id {$whereClause}
                GROUP BY c.id, c.name
                ORDER BY document_count DESC
                LIMIT ?
            ", array_merge($params, [$limit]));
            
            $data = [
                'labels' => array_column($results, 'category_name'),
                'data' => array_column($results, 'document_count')
            ];
            break;
            
        case 'documents_by_status':
            // Get documents grouped by status
            $whereClause = '';
            $params = [];
            
            if ($dateFilter) {
                $whereClause = 'WHERE created_at >= ? AND created_at <= ?';
                $params = [$dateFilter['start'], $dateFilter['end']];
            }
            
            $results = $db->fetchAll("
                SELECT status, COUNT(*) as count
                FROM documents {$whereClause}
                GROUP BY status
                ORDER BY count DESC
            ", $params);
            
            $statusNames = [
                'approved' => 'อนุมัติแล้ว',
                'pending' => 'รออนุมัติ',
                'rejected' => 'ไม่อนุมัติ',
                'draft' => 'ร่าง'
            ];
            
            $data = [
                'labels' => array_map(function($row) use ($statusNames) {
                    return $statusNames[$row['status']] ?? $row['status'];
                }, $results),
                'data' => array_column($results, 'count')
            ];
            break;
            
        case 'documents_by_date':
            // Get documents grouped by date
            $whereClause = '';
            $params = [];
            
            if ($dateFilter) {
                $whereClause = 'WHERE created_at >= ? AND created_at <= ?';
                $params = [$dateFilter['start'], $dateFilter['end']];
            }
            
            $results = $db->fetchAll("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM documents {$whereClause}
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT ?
            ", array_merge($params, [$limit]));
            
            $data = [
                'labels' => array_column($results, 'date'),
                'data' => array_column($results, 'count')
            ];
            break;
            
        case 'top_documents':
            // Get top documents by downloads or views
            $orderBy = $_GET['order_by'] ?? 'download_count';
            $validOrderBy = ['download_count', 'view_count'];
            
            if (!in_array($orderBy, $validOrderBy)) {
                $orderBy = 'download_count';
            }
            
            $whereClause = "WHERE d.status = 'approved'";
            $params = [];
            
            if ($category) {
                $whereClause .= " AND d.category_id = ?";
                $params[] = $category;
            }
            
            if ($dateFilter) {
                $whereClause .= " AND d.created_at >= ? AND d.created_at <= ?";
                $params[] = $dateFilter['start'];
                $params[] = $dateFilter['end'];
            }
            
            $results = $db->fetchAll("
                SELECT d.title, d.{$orderBy} as value, c.name as category_name
                FROM documents d
                JOIN categories c ON d.category_id = c.id
                {$whereClause}
                ORDER BY d.{$orderBy} DESC
                LIMIT ?
            ", array_merge($params, [$limit]));
            
            $data = [
                'labels' => array_column($results, 'title'),
                'data' => array_column($results, 'value'),
                'categories' => array_column($results, 'category_name')
            ];
            break;
            
        case 'user_activity':
            // Get user activity over time
            $whereClause = '';
            $params = [];
            
            if ($dateFilter) {
                $whereClause = 'WHERE created_at >= ? AND created_at <= ?';
                $params = [$dateFilter['start'], $dateFilter['end']];
            }
            
            $results = $db->fetchAll("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM activity_logs {$whereClause}
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT ?
            ", array_merge($params, [$limit]));
            
            $data = [
                'labels' => array_column($results, 'date'),
                'data' => array_column($results, 'count')
            ];
            break;
            
        case 'downloads_by_date':
            // Get downloads grouped by date
            $whereClause = '';
            $params = [];
            
            if ($dateFilter) {
                $whereClause = 'WHERE dl.created_at >= ? AND dl.created_at <= ?';
                $params = [$dateFilter['start'], $dateFilter['end']];
            }
            
            $results = $db->fetchAll("
                SELECT DATE(dl.created_at) as date, COUNT(*) as count
                FROM downloads dl
                JOIN documents d ON dl.document_id = d.id
                {$whereClause}
                GROUP BY DATE(dl.created_at)
                ORDER BY date DESC
                LIMIT ?
            ", array_merge($params, [$limit]));
            
            $data = [
                'labels' => array_column($results, 'date'),
                'data' => array_column($results, 'count')
            ];
            break;
            
        case 'storage_usage':
            // Get storage usage by category
            $results = $db->fetchAll("
                SELECT c.name as category_name, SUM(d.file_size) as total_size
                FROM categories c
                JOIN documents d ON c.id = d.category_id
                WHERE d.status = 'approved'
                GROUP BY c.id, c.name
                ORDER BY total_size DESC
                LIMIT ?
            ", [$limit]);
            
            $data = [
                'labels' => array_column($results, 'category_name'),
                'data' => array_column($results, 'total_size')
            ];
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid type parameter']);
            exit();
    }
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => time(),
        'params' => [
            'type' => $type,
            'date_range' => $dateRange,
            'category' => $category,
            'limit' => $limit
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => DEBUG_MODE ? $e->getMessage() : 'An error occurred'
    ]);
}