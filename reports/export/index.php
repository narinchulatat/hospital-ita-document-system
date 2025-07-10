<?php
/**
 * Export Reports
 * Handles report export in various formats
 */

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

// Get export parameters
$format = $_GET['format'] ?? $_POST['format'] ?? 'csv';
$type = $_GET['type'] ?? $_POST['type'] ?? 'documents';
$filename = $_GET['filename'] ?? $_POST['filename'] ?? null;

// Validate format
$validFormats = ['csv', 'json', 'xml'];
if (!in_array($format, $validFormats)) {
    $format = 'csv';
}

// Get report data based on type
$data = [];
$headers = [];

try {
    $db = Database::getInstance();
    
    switch ($type) {
        case 'documents':
            $data = $db->fetchAll("
                SELECT d.id, d.title, d.description, c.name as category_name,
                       d.status, d.file_size, d.download_count, d.view_count,
                       d.created_at, CONCAT(u.first_name, ' ', u.last_name) as uploader_name
                FROM documents d
                JOIN categories c ON d.category_id = c.id
                JOIN users u ON d.user_id = u.id
                ORDER BY d.created_at DESC
            ");
            
            $headers = [
                'ID', 'ชื่อเอกสาร', 'คำอธิบาย', 'หมวดหมู่', 'สถานะ', 
                'ขนาดไฟล์', 'จำนวนดาวน์โหลด', 'จำนวนการเข้าชม', 
                'วันที่สร้าง', 'ผู้อัปโหลด'
            ];
            break;
            
        case 'users':
            $data = $db->fetchAll("
                SELECT u.id, u.username, u.email, u.first_name, u.last_name,
                       u.role_id, u.is_active, u.created_at
                FROM users u
                ORDER BY u.created_at DESC
            ");
            
            $headers = [
                'ID', 'ชื่อผู้ใช้', 'อีเมล', 'ชื่อ', 'นามสกุล', 
                'บทบาท', 'สถานะ', 'วันที่สร้าง'
            ];
            break;
            
        case 'categories':
            $data = $db->fetchAll("
                SELECT c.id, c.name, c.description, c.is_active, c.created_at,
                       COUNT(d.id) as document_count
                FROM categories c
                LEFT JOIN documents d ON c.id = d.category_id
                GROUP BY c.id
                ORDER BY c.created_at DESC
            ");
            
            $headers = [
                'ID', 'ชื่อหมวดหมู่', 'คำอธิบาย', 'สถานะ', 
                'วันที่สร้าง', 'จำนวนเอกสาร'
            ];
            break;
            
        case 'downloads':
            $data = $db->fetchAll("
                SELECT dl.id, d.title as document_title, 
                       CONCAT(u.first_name, ' ', u.last_name) as downloader_name,
                       dl.ip_address, dl.created_at
                FROM downloads dl
                JOIN documents d ON dl.document_id = d.id
                JOIN users u ON dl.user_id = u.id
                ORDER BY dl.created_at DESC
            ");
            
            $headers = [
                'ID', 'ชื่อเอกสาร', 'ผู้ดาวน์โหลด', 
                'IP Address', 'วันที่ดาวน์โหลด'
            ];
            break;
            
        case 'activity_logs':
            $data = $db->fetchAll("
                SELECT al.id, al.action, al.table_name, al.record_id,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       al.ip_address, al.created_at
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT 1000
            ");
            
            $headers = [
                'ID', 'การกระทำ', 'ตาราง', 'Record ID', 
                'ผู้ใช้', 'IP Address', 'วันที่'
            ];
            break;
            
        default:
            throw new Exception('Invalid report type');
    }
    
    // Generate filename if not provided
    if (!$filename) {
        $filename = $type . '_report_' . date('Y-m-d_H-i-s') . '.' . $format;
    } else {
        // Ensure filename has correct extension
        $filename = pathinfo($filename, PATHINFO_FILENAME) . '.' . $format;
    }
    
    // Export based on format
    switch ($format) {
        case 'csv':
            exportCSV($data, $headers, $filename);
            break;
            
        case 'json':
            exportJSON($data, $filename);
            break;
            
        case 'xml':
            exportXML($data, $headers, $filename);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}

/**
 * Export data as CSV
 */
function exportCSV($data, $headers, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, $headers);
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, array_values($row));
    }
    
    fclose($output);
}

/**
 * Export data as JSON
 */
function exportJSON($data, $filename) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo json_encode([
        'exported_at' => date('Y-m-d H:i:s'),
        'total_records' => count($data),
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Export data as XML
 */
function exportXML($data, $headers, $filename) {
    header('Content-Type: application/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><report></report>');
    $xml->addChild('exported_at', date('Y-m-d H:i:s'));
    $xml->addChild('total_records', count($data));
    
    $dataElement = $xml->addChild('data');
    
    foreach ($data as $row) {
        $item = $dataElement->addChild('item');
        foreach ($row as $key => $value) {
            $item->addChild($key, htmlspecialchars($value));
        }
    }
    
    echo $xml->asXML();
}