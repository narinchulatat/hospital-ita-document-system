<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';

requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$type = $_GET['type'] ?? 'documents';
$days = (int)($_GET['days'] ?? 7);

try {
    $db = Database::getInstance();
    $data = [];
    
    switch ($type) {
        case 'documents':
            // Documents created per day
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                   FROM documents 
                   WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                   GROUP BY DATE(created_at)
                   ORDER BY date";
            
            $results = $db->fetchAll($sql, [$days]);
            
            $data = [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'เอกสารใหม่',
                        'data' => [],
                        'borderColor' => 'rgb(52, 152, 219)',
                        'backgroundColor' => 'rgba(52, 152, 219, 0.1)',
                        'tension' => 0.4
                    ]
                ]
            ];
            
            foreach ($results as $row) {
                $data['labels'][] = formatThaiDate($row['date']);
                $data['datasets'][0]['data'][] = (int)$row['count'];
            }
            break;
            
        case 'downloads':
            // Downloads per day
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                   FROM downloads 
                   WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                   GROUP BY DATE(created_at)
                   ORDER BY date";
            
            $results = $db->fetchAll($sql, [$days]);
            
            $data = [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'การดาวน์โหลด',
                        'data' => [],
                        'borderColor' => 'rgb(39, 174, 96)',
                        'backgroundColor' => 'rgba(39, 174, 96, 0.1)',
                        'tension' => 0.4
                    ]
                ]
            ];
            
            foreach ($results as $row) {
                $data['labels'][] = formatThaiDate($row['date']);
                $data['datasets'][0]['data'][] = (int)$row['count'];
            }
            break;
            
        case 'users':
            // Users by role
            $sql = "SELECT r.name as role_name, COUNT(ur.user_id) as count
                   FROM roles r
                   LEFT JOIN user_roles ur ON r.id = ur.role_id
                   LEFT JOIN users u ON ur.user_id = u.id AND u.status = 'active'
                   GROUP BY r.id, r.name
                   ORDER BY r.id";
            
            $results = $db->fetchAll($sql);
            
            $data = [
                'labels' => [],
                'datasets' => [
                    [
                        'data' => [],
                        'backgroundColor' => [
                            'rgb(52, 152, 219)',
                            'rgb(39, 174, 96)',
                            'rgb(243, 156, 18)',
                            'rgb(155, 89, 182)',
                            'rgb(231, 76, 60)'
                        ]
                    ]
                ]
            ];
            
            foreach ($results as $row) {
                $data['labels'][] = $row['role_name'];
                $data['datasets'][0]['data'][] = (int)$row['count'];
            }
            break;
            
        case 'document_status':
            // Document status distribution
            $sql = "SELECT status, COUNT(*) as count 
                   FROM documents 
                   GROUP BY status
                   ORDER BY FIELD(status, 'draft', 'pending', 'approved', 'rejected', 'archived')";
            
            $results = $db->fetchAll($sql);
            
            $statusLabels = [
                'draft' => 'แบบร่าง',
                'pending' => 'รออนุมัติ',
                'approved' => 'อนุมัติแล้ว',
                'rejected' => 'ไม่อนุมัติ',
                'archived' => 'เก็บถาวร'
            ];
            
            $statusColors = [
                'draft' => 'rgb(108, 117, 125)',
                'pending' => 'rgb(243, 156, 18)',
                'approved' => 'rgb(39, 174, 96)',
                'rejected' => 'rgb(231, 76, 60)',
                'archived' => 'rgb(52, 58, 64)'
            ];
            
            $data = [
                'labels' => [],
                'datasets' => [
                    [
                        'data' => [],
                        'backgroundColor' => []
                    ]
                ]
            ];
            
            foreach ($results as $row) {
                $data['labels'][] = $statusLabels[$row['status']] ?? $row['status'];
                $data['datasets'][0]['data'][] = (int)$row['count'];
                $data['datasets'][0]['backgroundColor'][] = $statusColors[$row['status']] ?? 'rgb(108, 117, 125)';
            }
            break;
            
        default:
            throw new Exception('Invalid chart type');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    error_log("Charts data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'เกิดข้อผิดพลาดเซิร์ฟเวอร์'
    ]);
}

function formatThaiDate($date) {
    $thaiMonths = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $thaiMonths[date('n', $timestamp)];
    
    return $day . ' ' . $month;
}
?>