<?php
/**
 * Reports Functions
 */

// Include main report class
require_once __DIR__ . '/../../classes/Report.php';

/**
 * Get report data based on type and parameters
 */
function getReportData($type, $params = []) {
    $report = new Report();
    
    switch ($type) {
        case 'dashboard_summary':
            return $report->getSystemSummary();
            
        case 'document_summary':
            return $report->getDocumentStats($params['date_range'] ?? null);
            
        case 'user_activity':
            return $report->getUserActivity($params['date_range'] ?? null, $params['user_id'] ?? null);
            
        case 'download_stats':
            return $report->getDownloadStats($params['date_range'] ?? null);
            
        case 'top_documents':
            return $report->getTopDocuments($params['limit'] ?? 10, $params['order_by'] ?? 'download_count');
            
        case 'top_users':
            return $report->getTopUsers($params['limit'] ?? 10, $params['date_range'] ?? null);
            
        case 'top_categories':
            return $report->getTopCategories($params['limit'] ?? 10);
            
        default:
            return [];
    }
}

/**
 * Generate chart data for reports
 */
function generateChartData($type, $data) {
    switch ($type) {
        case 'line':
            return generateLineChartData($data);
        case 'bar':
            return generateBarChartData($data);
        case 'pie':
            return generatePieChartData($data);
        case 'doughnut':
            return generateDoughnutChartData($data);
        default:
            return $data;
    }
}

/**
 * Generate line chart data
 */
function generateLineChartData($data) {
    $labels = [];
    $values = [];
    
    foreach ($data as $item) {
        $labels[] = $item['label'] ?? $item['date'] ?? '';
        $values[] = $item['value'] ?? $item['count'] ?? 0;
    }
    
    return [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'ข้อมูล',
                'data' => $values,
                'borderColor' => CHART_COLORS['primary'],
                'backgroundColor' => CHART_COLORS['primary'] . '20',
                'fill' => false
            ]
        ]
    ];
}

/**
 * Generate bar chart data
 */
function generateBarChartData($data) {
    $labels = [];
    $values = [];
    
    foreach ($data as $item) {
        $labels[] = $item['label'] ?? $item['name'] ?? '';
        $values[] = $item['value'] ?? $item['count'] ?? 0;
    }
    
    return [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'จำนวน',
                'data' => $values,
                'backgroundColor' => CHART_COLORS['primary'],
                'borderColor' => CHART_COLORS['primary'],
                'borderWidth' => 1
            ]
        ]
    ];
}

/**
 * Generate pie chart data
 */
function generatePieChartData($data) {
    $labels = [];
    $values = [];
    $colors = [];
    
    $colorKeys = array_keys(CHART_COLORS);
    
    foreach ($data as $index => $item) {
        $labels[] = $item['label'] ?? $item['name'] ?? '';
        $values[] = $item['value'] ?? $item['count'] ?? 0;
        $colors[] = CHART_COLORS[$colorKeys[$index % count($colorKeys)]];
    }
    
    return [
        'labels' => $labels,
        'datasets' => [
            [
                'data' => $values,
                'backgroundColor' => $colors,
                'borderWidth' => 1
            ]
        ]
    ];
}

/**
 * Generate doughnut chart data
 */
function generateDoughnutChartData($data) {
    return generatePieChartData($data); // Same as pie chart
}

/**
 * Export report data to specified format
 */
function exportReport($data, $format, $filename = null) {
    $report = new Report();
    
    switch ($format) {
        case 'csv':
            return $report->exportToCSV($data, $filename);
        case 'pdf':
            return exportToPDF($data, $filename);
        case 'excel':
            return exportToExcel($data, $filename);
        case 'json':
            return exportToJSON($data, $filename);
        case 'xml':
            return exportToXML($data, $filename);
        default:
            return false;
    }
}

/**
 * Export to PDF
 */
function exportToPDF($data, $filename = null) {
    // This would require TCPDF library
    // For now, return false
    return false;
}

/**
 * Export to Excel
 */
function exportToExcel($data, $filename = null) {
    // This would require PhpSpreadsheet library
    // For now, return false
    return false;
}

/**
 * Export to JSON
 */
function exportToJSON($data, $filename = null) {
    if (empty($data)) {
        return false;
    }
    
    $filename = $filename ?: 'report_' . date('Y-m-d_H-i-s') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return true;
}

/**
 * Export to XML
 */
function exportToXML($data, $filename = null) {
    if (empty($data)) {
        return false;
    }
    
    $filename = $filename ?: 'report_' . date('Y-m-d_H-i-s') . '.xml';
    
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $xml = new SimpleXMLElement('<report/>');
    
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $item = $xml->addChild('item');
            foreach ($value as $k => $v) {
                $item->addChild($k, htmlspecialchars($v));
            }
        } else {
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
    
    echo $xml->asXML();
    return true;
}

/**
 * Get report permissions for current user
 */
function getReportPermissions() {
    $permissions = [];
    
    // Get current user role
    $userRole = getCurrentUserRole();
    
    switch ($userRole) {
        case ROLE_ADMIN:
            $permissions = [
                'dashboard' => true,
                'documents' => true,
                'users' => true,
                'approvals' => true,
                'system' => true,
                'analytics' => true,
                'export' => true,
                'scheduled' => true,
                'custom' => true
            ];
            break;
            
        case ROLE_STAFF:
            $permissions = [
                'dashboard' => true,
                'documents' => true,
                'users' => false,
                'approvals' => false,
                'system' => false,
                'analytics' => true,
                'export' => true,
                'scheduled' => false,
                'custom' => true
            ];
            break;
            
        case ROLE_APPROVER:
            $permissions = [
                'dashboard' => true,
                'documents' => true,
                'users' => false,
                'approvals' => true,
                'system' => false,
                'analytics' => true,
                'export' => true,
                'scheduled' => false,
                'custom' => true
            ];
            break;
            
        default:
            $permissions = [
                'dashboard' => false,
                'documents' => false,
                'users' => false,
                'approvals' => false,
                'system' => false,
                'analytics' => false,
                'export' => false,
                'scheduled' => false,
                'custom' => false
            ];
    }
    
    return $permissions;
}

/**
 * Check if user has permission for specific report
 */
function hasReportPermission($permission) {
    $permissions = getReportPermissions();
    return $permissions[$permission] ?? false;
}

/**
 * Get reports menu for current user
 */
function getReportsMenu() {
    $permissions = getReportPermissions();
    $menu = [];
    
    foreach (REPORT_CATEGORIES as $key => $category) {
        if ($permissions[$key] ?? false) {
            $menu[$key] = $category;
        }
    }
    
    return $menu;
}

/**
 * Generate report breadcrumb
 */
function generateReportBreadcrumb($sections = []) {
    $breadcrumb = [
        ['name' => 'หน้าหลัก', 'url' => BASE_URL],
        ['name' => 'รายงาน', 'url' => REPORTS_URL]
    ];
    
    foreach ($sections as $section) {
        $breadcrumb[] = $section;
    }
    
    return $breadcrumb;
}

/**
 * Format Thai date
 */
function formatThaiDate($date, $format = 'j F Y') {
    $thaiMonths = [
        'January' => 'มกราคม',
        'February' => 'กุมภาพันธ์',
        'March' => 'มีนาคม',
        'April' => 'เมษายน',
        'May' => 'พฤษภาคม',
        'June' => 'มิถุนายน',
        'July' => 'กรกฎาคม',
        'August' => 'สิงหาคม',
        'September' => 'กันยายน',
        'October' => 'ตุลาคม',
        'November' => 'พฤศจิกายน',
        'December' => 'ธันวาคม'
    ];
    
    $formatted = date($format, strtotime($date));
    
    foreach ($thaiMonths as $en => $th) {
        $formatted = str_replace($en, $th, $formatted);
    }
    
    return $formatted;
}