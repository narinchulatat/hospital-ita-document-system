<?php
/**
 * Application Constants
 */

// User roles
define('ROLE_ADMIN', 1);
define('ROLE_STAFF', 2);
define('ROLE_APPROVER', 3);
define('ROLE_VISITOR', 4);

// User status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_LOCKED', 'locked');

// Document status
define('DOC_STATUS_DRAFT', 'draft');
define('DOC_STATUS_PENDING', 'pending');
define('DOC_STATUS_APPROVED', 'approved');
define('DOC_STATUS_REJECTED', 'rejected');
define('DOC_STATUS_ARCHIVED', 'archived');

// Notification types
define('NOTIF_TYPE_INFO', 'info');
define('NOTIF_TYPE_SUCCESS', 'success');
define('NOTIF_TYPE_WARNING', 'warning');
define('NOTIF_TYPE_ERROR', 'error');

// Activity log actions
define('ACTION_CREATE', 'create');
define('ACTION_READ', 'read');
define('ACTION_UPDATE', 'update');
define('ACTION_DELETE', 'delete');
define('ACTION_LOGIN', 'login');
define('ACTION_LOGOUT', 'logout');
define('ACTION_APPROVE', 'approve');
define('ACTION_REJECT', 'reject');
define('ACTION_DOWNLOAD', 'download');

// Backup types
define('BACKUP_MANUAL', 'manual');
define('BACKUP_SCHEDULED', 'scheduled');

// Backup status
define('BACKUP_CREATING', 'creating');
define('BACKUP_COMPLETED', 'completed');
define('BACKUP_FAILED', 'failed');

// Setting types
define('SETTING_STRING', 'string');
define('SETTING_INTEGER', 'integer');
define('SETTING_BOOLEAN', 'boolean');
define('SETTING_JSON', 'json');

// Category levels
define('CATEGORY_LEVEL_1', 1);
define('CATEGORY_LEVEL_2', 2);
define('CATEGORY_LEVEL_3', 3);

// Role names for display
$ROLE_NAMES = [
    ROLE_ADMIN => 'ผู้ดูแลระบบ',
    ROLE_STAFF => 'เจ้าหน้าที่',
    ROLE_APPROVER => 'ผู้อนุมัติ',
    ROLE_VISITOR => 'ผู้เยี่ยมชม'
];

// Status names for display
$STATUS_NAMES = [
    STATUS_ACTIVE => 'ใช้งาน',
    STATUS_INACTIVE => 'ไม่ใช้งาน',
    STATUS_LOCKED => 'ถูกล็อค'
];

// Document status names for display
$DOC_STATUS_NAMES = [
    DOC_STATUS_DRAFT => 'ร่าง',
    DOC_STATUS_PENDING => 'รออนุมัติ',
    DOC_STATUS_APPROVED => 'อนุมัติแล้ว',
    DOC_STATUS_REJECTED => 'ไม่อนุมัติ',
    DOC_STATUS_ARCHIVED => 'เก็บถาวร'
];

// Notification type names for display
$NOTIF_TYPE_NAMES = [
    NOTIF_TYPE_INFO => 'ข้อมูล',
    NOTIF_TYPE_SUCCESS => 'สำเร็จ',
    NOTIF_TYPE_WARNING => 'คำเตือน',
    NOTIF_TYPE_ERROR => 'ข้อผิดพลาด'
];

// File type icons
$FILE_TYPE_ICONS = [
    'pdf' => 'fas fa-file-pdf text-red-500',
    'doc' => 'fas fa-file-word text-blue-500',
    'docx' => 'fas fa-file-word text-blue-500',
    'xls' => 'fas fa-file-excel text-green-500',
    'xlsx' => 'fas fa-file-excel text-green-500',
    'jpg' => 'fas fa-file-image text-purple-500',
    'jpeg' => 'fas fa-file-image text-purple-500',
    'png' => 'fas fa-file-image text-purple-500',
    'default' => 'fas fa-file text-gray-500'
];

// Dashboard menu items by role
$DASHBOARD_MENUS = [
    ROLE_ADMIN => [
        ['title' => 'หน้าหลัก', 'url' => '/admin/', 'icon' => 'fas fa-tachometer-alt'],
        ['title' => 'จัดการผู้ใช้', 'url' => '/admin/users/', 'icon' => 'fas fa-users'],
        ['title' => 'จัดการเอกสาร', 'url' => '/admin/documents/', 'icon' => 'fas fa-file-alt'],
        ['title' => 'จัดการหมวดหมู่', 'url' => '/admin/categories/', 'icon' => 'fas fa-folder'],
        ['title' => 'รายงาน', 'url' => '/admin/reports/', 'icon' => 'fas fa-chart-bar'],
        ['title' => 'สำรองข้อมูล', 'url' => '/admin/backups/', 'icon' => 'fas fa-database'],
        ['title' => 'ตั้งค่า', 'url' => '/admin/settings/', 'icon' => 'fas fa-cog']
    ],
    ROLE_STAFF => [
        ['title' => 'หน้าหลัก', 'url' => '/staff/', 'icon' => 'fas fa-tachometer-alt'],
        ['title' => 'จัดการเอกสาร', 'url' => '/staff/documents/', 'icon' => 'fas fa-file-alt']
    ],
    ROLE_APPROVER => [
        ['title' => 'หน้าหลัก', 'url' => '/approver/', 'icon' => 'fas fa-tachometer-alt'],
        ['title' => 'อนุมัติเอกสาร', 'url' => '/approver/approval/', 'icon' => 'fas fa-check-circle']
    ]
];