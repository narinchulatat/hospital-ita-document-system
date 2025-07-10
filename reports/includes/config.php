<?php
/**
 * Reports Configuration
 */

// Include main config
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// For now, allow all logged-in users to access reports
// In the future, you can implement specific permission checks
$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Reports specific configuration
define('REPORTS_PATH', __DIR__ . '/../');
define('REPORTS_URL', BASE_URL . '/reports');
define('REPORTS_ASSETS_URL', REPORTS_URL . '/assets');

// Chart colors
define('CHART_COLORS', [
    'primary' => '#3B82F6',
    'secondary' => '#64748B',
    'success' => '#10B981',
    'warning' => '#F59E0B',
    'danger' => '#EF4444',
    'info' => '#06B6D4',
    'light' => '#F1F5F9',
    'dark' => '#1E293B'
]);

// Date range options
define('DATE_RANGES', [
    'today' => 'วันนี้',
    'yesterday' => 'เมื่อวาน',
    'last_7_days' => '7 วันที่ผ่านมา',
    'last_30_days' => '30 วันที่ผ่านมา',
    'this_month' => 'เดือนนี้',
    'last_month' => 'เดือนที่แล้ว',
    'this_quarter' => 'ไตรมาสนี้',
    'last_quarter' => 'ไตรมาสที่แล้ว',
    'this_year' => 'ปีนี้',
    'last_year' => 'ปีที่แล้ว',
    'custom' => 'กำหนดเอง'
]);

// Export formats
define('EXPORT_FORMATS', [
    'pdf' => 'PDF',
    'excel' => 'Excel',
    'csv' => 'CSV',
    'json' => 'JSON',
    'xml' => 'XML'
]);

// Report categories
define('REPORT_CATEGORIES', [
    'documents' => [
        'name' => 'รายงานเอกสาร',
        'icon' => 'fas fa-file-alt',
        'description' => 'รายงานเกี่ยวกับเอกสารในระบบ',
        'reports' => [
            'summary' => 'สรุปเอกสาร',
            'by-category' => 'ตามหมวดหมู่',
            'by-status' => 'ตามสถานะ',
            'by-date' => 'ตามวันที่',
            'popular' => 'เอกสารยอดนิยม',
            'downloads' => 'การดาวน์โหลด',
            'views' => 'การเข้าชม'
        ]
    ],
    'users' => [
        'name' => 'รายงานผู้ใช้',
        'icon' => 'fas fa-users',
        'description' => 'รายงานเกี่ยวกับผู้ใช้งานระบบ',
        'reports' => [
            'activity' => 'กิจกรรมผู้ใช้',
            'login' => 'การเข้าใช้งาน',
            'registration' => 'การลงทะเบียน',
            'role-distribution' => 'การกระจายบทบาท',
            'active-users' => 'ผู้ใช้งานที่ใช้งาน'
        ]
    ],
    'approvals' => [
        'name' => 'รายงานการอนุมัติ',
        'icon' => 'fas fa-check-circle',
        'description' => 'รายงานเกี่ยวกับการอนุมัติเอกสาร',
        'reports' => [
            'pending' => 'รออนุมัติ',
            'processed' => 'ที่ประมวลผลแล้ว',
            'by-approver' => 'ตามผู้อนุมัติ',
            'timeline' => 'เส้นเวลาการอนุมัติ',
            'performance' => 'ประสิทธิภาพการอนุมัติ'
        ]
    ],
    'system' => [
        'name' => 'รายงานระบบ',
        'icon' => 'fas fa-cog',
        'description' => 'รายงานเกี่ยวกับการใช้งานระบบ',
        'reports' => [
            'usage' => 'การใช้งานระบบ',
            'storage' => 'การใช้พื้นที่',
            'errors' => 'ข้อผิดพลาด',
            'security' => 'ความปลอดภัย',
            'performance' => 'ประสิทธิภาพ'
        ]
    ],
    'analytics' => [
        'name' => 'รายงานการวิเคราะห์',
        'icon' => 'fas fa-chart-line',
        'description' => 'รายงานการวิเคราะห์ข้อมูล',
        'reports' => [
            'trends' => 'แนวโน้ม',
            'growth' => 'การเติบโต',
            'insights' => 'ข้อมูลเชิงลึก',
            'predictions' => 'การพยากรณ์'
        ]
    ]
]);

// Helper function to get date range
function getDateRange($range) {
    switch ($range) {
        case 'today':
            return [
                'start' => date('Y-m-d 00:00:00'),
                'end' => date('Y-m-d 23:59:59')
            ];
        case 'yesterday':
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('-1 day')),
                'end' => date('Y-m-d 23:59:59', strtotime('-1 day'))
            ];
        case 'last_7_days':
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('-7 days')),
                'end' => date('Y-m-d 23:59:59')
            ];
        case 'last_30_days':
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('-30 days')),
                'end' => date('Y-m-d 23:59:59')
            ];
        case 'this_month':
            return [
                'start' => date('Y-m-01 00:00:00'),
                'end' => date('Y-m-t 23:59:59')
            ];
        case 'last_month':
            return [
                'start' => date('Y-m-01 00:00:00', strtotime('first day of last month')),
                'end' => date('Y-m-t 23:59:59', strtotime('last day of last month'))
            ];
        case 'this_year':
            return [
                'start' => date('Y-01-01 00:00:00'),
                'end' => date('Y-12-31 23:59:59')
            ];
        case 'last_year':
            return [
                'start' => date('Y-01-01 00:00:00', strtotime('-1 year')),
                'end' => date('Y-12-31 23:59:59', strtotime('-1 year'))
            ];
        default:
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('-30 days')),
                'end' => date('Y-m-d 23:59:59')
            ];
    }
}

// Helper function to format numbers
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, '.', ',');
}

// Helper function to format percentages
function formatPercentage($value, $total, $decimals = 1) {
    if ($total == 0) return '0%';
    return number_format(($value / $total) * 100, $decimals) . '%';
}

// Helper function to get file size in human readable format
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}