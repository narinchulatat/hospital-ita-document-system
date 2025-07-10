<?php
/**
 * Admin Configuration
 */

// Admin specific constants
define('ADMIN_ITEMS_PER_PAGE', 25);
define('ADMIN_MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour

// Admin permissions
define('PERM_USER_VIEW', 'user_view');
define('PERM_USER_CREATE', 'user_create');
define('PERM_USER_EDIT', 'user_edit');
define('PERM_USER_DELETE', 'user_delete');

define('PERM_DOCUMENT_VIEW', 'document_view');
define('PERM_DOCUMENT_CREATE', 'document_create');
define('PERM_DOCUMENT_EDIT', 'document_edit');
define('PERM_DOCUMENT_DELETE', 'document_delete');
define('PERM_DOCUMENT_APPROVE', 'document_approve');

define('PERM_CATEGORY_VIEW', 'category_view');
define('PERM_CATEGORY_CREATE', 'category_create');
define('PERM_CATEGORY_EDIT', 'category_edit');
define('PERM_CATEGORY_DELETE', 'category_delete');

define('PERM_ROLE_VIEW', 'role_view');
define('PERM_ROLE_CREATE', 'role_create');
define('PERM_ROLE_EDIT', 'role_edit');
define('PERM_ROLE_DELETE', 'role_delete');

define('PERM_BACKUP_VIEW', 'backup_view');
define('PERM_BACKUP_CREATE', 'backup_create');
define('PERM_BACKUP_RESTORE', 'backup_restore');
define('PERM_BACKUP_DELETE', 'backup_delete');

define('PERM_SETTING_VIEW', 'setting_view');
define('PERM_SETTING_EDIT', 'setting_edit');

define('PERM_REPORT_VIEW', 'report_view');
define('PERM_LOG_VIEW', 'log_view');

// Admin menu structure
$adminMenu = [
    'dashboard' => [
        'title' => 'แดชบอร์ด',
        'icon' => 'fa-tachometer-alt',
        'url' => '/admin/',
        'permission' => null
    ],
    'users' => [
        'title' => 'จัดการผู้ใช้',
        'icon' => 'fa-users',
        'url' => '/admin/users/',
        'permission' => PERM_USER_VIEW,
        'submenu' => [
            'list' => ['title' => 'รายการผู้ใช้', 'url' => '/admin/users/'],
            'create' => ['title' => 'เพิ่มผู้ใช้', 'url' => '/admin/users/create.php'],
        ]
    ],
    'documents' => [
        'title' => 'จัดการเอกสาร',
        'icon' => 'fa-file-alt',
        'url' => '/admin/documents/',
        'permission' => PERM_DOCUMENT_VIEW,
        'submenu' => [
            'list' => ['title' => 'รายการเอกสาร', 'url' => '/admin/documents/'],
            'create' => ['title' => 'เพิ่มเอกสาร', 'url' => '/admin/documents/create.php'],
            'approve' => ['title' => 'อนุมัติเอกสาร', 'url' => '/admin/documents/approve.php'],
        ]
    ],
    'categories' => [
        'title' => 'จัดการหมวดหมู่',
        'icon' => 'fa-folder',
        'url' => '/admin/categories/',
        'permission' => PERM_CATEGORY_VIEW,
        'submenu' => [
            'list' => ['title' => 'รายการหมวดหมู่', 'url' => '/admin/categories/'],
            'create' => ['title' => 'เพิ่มหมวดหมู่', 'url' => '/admin/categories/create.php'],
            'tree' => ['title' => 'ดูแบบต้นไม้', 'url' => '/admin/categories/tree.php'],
        ]
    ],
    'roles' => [
        'title' => 'บทบาทและสิทธิ์',
        'icon' => 'fa-user-shield',
        'url' => '/admin/roles/',
        'permission' => PERM_ROLE_VIEW,
        'submenu' => [
            'roles' => ['title' => 'จัดการบทบาท', 'url' => '/admin/roles/'],
            'permissions' => ['title' => 'จัดการสิทธิ์', 'url' => '/admin/permissions/'],
        ]
    ],
    'backups' => [
        'title' => 'สำรองข้อมูล',
        'icon' => 'fa-database',
        'url' => '/admin/backups/',
        'permission' => PERM_BACKUP_VIEW,
        'submenu' => [
            'list' => ['title' => 'รายการสำรอง', 'url' => '/admin/backups/'],
            'create' => ['title' => 'สร้างสำรอง', 'url' => '/admin/backups/create.php'],
        ]
    ],
    'notifications' => [
        'title' => 'การแจ้งเตือน',
        'icon' => 'fa-bell',
        'url' => '/admin/notifications/',
        'permission' => null,
        'submenu' => [
            'list' => ['title' => 'รายการแจ้งเตือน', 'url' => '/admin/notifications/'],
            'create' => ['title' => 'ส่งแจ้งเตือน', 'url' => '/admin/notifications/create.php'],
            'broadcast' => ['title' => 'แจ้งเตือนทั่วไป', 'url' => '/admin/notifications/broadcast.php'],
        ]
    ],
    'settings' => [
        'title' => 'ตั้งค่าระบบ',
        'icon' => 'fa-cog',
        'url' => '/admin/settings/',
        'permission' => PERM_SETTING_VIEW,
        'submenu' => [
            'general' => ['title' => 'ตั้งค่าทั่วไป', 'url' => '/admin/settings/general.php'],
            'upload' => ['title' => 'ตั้งค่าการอัปโหลด', 'url' => '/admin/settings/upload.php'],
            'security' => ['title' => 'ตั้งค่าความปลอดภัย', 'url' => '/admin/settings/security.php'],
            'backup' => ['title' => 'ตั้งค่าสำรองข้อมูล', 'url' => '/admin/settings/backup.php'],
        ]
    ],
    'reports' => [
        'title' => 'รายงาน',
        'icon' => 'fa-chart-bar',
        'url' => '/admin/reports/',
        'permission' => PERM_REPORT_VIEW,
        'submenu' => [
            'users' => ['title' => 'รายงานผู้ใช้', 'url' => '/admin/reports/users.php'],
            'documents' => ['title' => 'รายงานเอกสาร', 'url' => '/admin/reports/documents.php'],
            'downloads' => ['title' => 'รายงานการดาวน์โหลด', 'url' => '/admin/reports/downloads.php'],
            'activities' => ['title' => 'รายงานกิจกรรม', 'url' => '/admin/reports/activities.php'],
        ]
    ],
    'logs' => [
        'title' => 'ประวัติการทำงาน',
        'icon' => 'fa-history',
        'url' => '/admin/logs/',
        'permission' => PERM_LOG_VIEW,
        'submenu' => [
            'activities' => ['title' => 'กิจกรรมผู้ใช้', 'url' => '/admin/logs/activities.php'],
            'errors' => ['title' => 'ข้อผิดพลาด', 'url' => '/admin/logs/errors.php'],
            'downloads' => ['title' => 'ประวัติดาวน์โหลด', 'url' => '/admin/logs/downloads.php'],
        ]
    ]
];

/**
 * Check if user has permission to access menu item
 */
function hasMenuPermission($permission) {
    if ($permission === null) {
        return true;
    }
    
    try {
        $user = new User();
        return $user->hasPermission($_SESSION['user_id'], $permission);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get current page for active menu highlighting
 */
function getCurrentPage() {
    $path = $_SERVER['REQUEST_URI'];
    $path = parse_url($path, PHP_URL_PATH);
    $path = str_replace(BASE_URL, '', $path);
    return $path;
}

/**
 * Check if menu item is active
 */
function isMenuActive($url, $submenu = []) {
    $currentPage = getCurrentPage();
    
    if ($currentPage === $url) {
        return true;
    }
    
    foreach ($submenu as $item) {
        if ($currentPage === $item['url']) {
            return true;
        }
    }
    
    return false;
}
?>