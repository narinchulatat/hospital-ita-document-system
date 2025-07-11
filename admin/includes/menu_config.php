<?php
/**
 * Unified Menu Configuration
 * Defines the hierarchical menu structure for the admin sidebar
 */

// Define menu configuration with proper grouping
$menu_config = [
    'dashboard' => [
        'title' => 'แดชบอร์ด',
        'icon' => 'fa-tachometer-alt',
        'url' => '/admin/',
        'permission' => null,
        'group' => 'main'
    ],
    'documents' => [
        'title' => 'จัดการเอกสาร',
        'icon' => 'fa-file-alt',
        'url' => '#',
        'permission' => PERM_DOCUMENT_VIEW,
        'group' => 'main',
        'children' => [
            'list' => [
                'title' => 'รายการเอกสาร',
                'icon' => 'fa-list',
                'url' => '/admin/documents/',
                'permission' => PERM_DOCUMENT_VIEW
            ],
            'create' => [
                'title' => 'เพิ่มเอกสารใหม่',
                'icon' => 'fa-plus',
                'url' => '/admin/documents/create.php',
                'permission' => PERM_DOCUMENT_CREATE
            ],
            'categories' => [
                'title' => 'หมวดหมู่เอกสาร',
                'icon' => 'fa-folder',
                'url' => '/admin/categories/',
                'permission' => PERM_CATEGORY_VIEW
            ],
            'approval' => [
                'title' => 'อนุมัติเอกสาร',
                'icon' => 'fa-check',
                'url' => '/admin/documents/approve.php',
                'permission' => PERM_DOCUMENT_APPROVE
            ]
        ]
    ],
    'users' => [
        'title' => 'จัดการผู้ใช้',
        'icon' => 'fa-users',
        'url' => '#',
        'permission' => PERM_USER_VIEW,
        'group' => 'main',
        'children' => [
            'list' => [
                'title' => 'รายการผู้ใช้',
                'icon' => 'fa-user',
                'url' => '/admin/users/',
                'permission' => PERM_USER_VIEW
            ],
            'create' => [
                'title' => 'เพิ่มผู้ใช้ใหม่',
                'icon' => 'fa-user-plus',
                'url' => '/admin/users/create.php',
                'permission' => PERM_USER_CREATE
            ],
            'roles' => [
                'title' => 'บทบาทและสิทธิ์',
                'icon' => 'fa-user-shield',
                'url' => '/admin/roles/',
                'permission' => PERM_ROLE_VIEW
            ],
            'groups' => [
                'title' => 'กลุ่มผู้ใช้',
                'icon' => 'fa-users-cog',
                'url' => '/admin/groups/',
                'permission' => PERM_USER_VIEW
            ]
        ]
    ],
    'settings' => [
        'title' => 'การตั้งค่า',
        'icon' => 'fa-cog',
        'url' => '#',
        'permission' => PERM_SETTING_VIEW,
        'group' => 'main',
        'children' => [
            'general' => [
                'title' => 'ตั้งค่าระบบ',
                'icon' => 'fa-cogs',
                'url' => '/admin/settings/general.php',
                'permission' => PERM_SETTING_EDIT
            ],
            'backup' => [
                'title' => 'สำรองข้อมูล',
                'icon' => 'fa-database',
                'url' => '/admin/backups/',
                'permission' => PERM_BACKUP_VIEW
            ],
            'notifications' => [
                'title' => 'ประกาศและแจ้งเตือน',
                'icon' => 'fa-bell',
                'url' => '/admin/notifications/',
                'permission' => null
            ],
            'logs' => [
                'title' => 'บันทึกกิจกรรม',
                'icon' => 'fa-history',
                'url' => '/admin/logs/',
                'permission' => PERM_LOG_VIEW
            ]
        ]
    ],
    'reports' => [
        'title' => 'รายงาน',
        'icon' => 'fa-chart-bar',
        'url' => '#',
        'permission' => PERM_REPORT_VIEW,
        'group' => 'main',
        'children' => [
            'usage' => [
                'title' => 'รายงานการใช้งาน',
                'icon' => 'fa-chart-line',
                'url' => '/admin/reports/usage.php',
                'permission' => PERM_REPORT_VIEW
            ],
            'documents' => [
                'title' => 'รายงานเอกสาร',
                'icon' => 'fa-file-chart',
                'url' => '/admin/reports/documents.php',
                'permission' => PERM_REPORT_VIEW
            ],
            'users' => [
                'title' => 'รายงานผู้ใช้',
                'icon' => 'fa-user-chart',
                'url' => '/admin/reports/users.php',
                'permission' => PERM_REPORT_VIEW
            ],
            'export' => [
                'title' => 'ส่งออกข้อมูล',
                'icon' => 'fa-download',
                'url' => '/admin/reports/export.php',
                'permission' => PERM_REPORT_VIEW
            ]
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
function isMenuActive($url, $children = []) {
    $currentPage = getCurrentPage();
    
    // Direct match
    if ($currentPage === $url) {
        return true;
    }
    
    // Check children
    foreach ($children as $child) {
        if ($currentPage === $child['url']) {
            return true;
        }
    }
    
    // Check if current page starts with the menu URL (for nested pages)
    if ($url !== '#' && strpos($currentPage, $url) === 0) {
        return true;
    }
    
    return false;
}

/**
 * Check if menu should be expanded (has active children)
 */
function shouldExpandMenu($children) {
    foreach ($children as $child) {
        if (isMenuActive($child['url'])) {
            return true;
        }
    }
    return false;
}

/**
 * Generate breadcrumb for current page
 */
function generateBreadcrumb($menu_config) {
    $currentPage = getCurrentPage();
    $breadcrumb = [];
    
    foreach ($menu_config as $key => $menu) {
        if (isMenuActive($menu['url'], $menu['children'] ?? [])) {
            $breadcrumb[] = [
                'title' => $menu['title'],
                'url' => $menu['url'] === '#' ? null : $menu['url']
            ];
            
            // Check children
            if (!empty($menu['children'])) {
                foreach ($menu['children'] as $childKey => $child) {
                    if (isMenuActive($child['url'])) {
                        $breadcrumb[] = [
                            'title' => $child['title'],
                            'url' => $child['url']
                        ];
                        break;
                    }
                }
            }
            break;
        }
    }
    
    return $breadcrumb;
}

/**
 * Get filtered menu based on permissions
 */
function getFilteredMenu($menu_config) {
    $filteredMenu = [];
    
    foreach ($menu_config as $key => $menu) {
        if (hasMenuPermission($menu['permission'])) {
            $filteredMenu[$key] = $menu;
            
            // Filter children
            if (!empty($menu['children'])) {
                $filteredChildren = [];
                foreach ($menu['children'] as $childKey => $child) {
                    if (hasMenuPermission($child['permission'])) {
                        $filteredChildren[$childKey] = $child;
                    }
                }
                $filteredMenu[$key]['children'] = $filteredChildren;
            }
        }
    }
    
    return $filteredMenu;
}
?>