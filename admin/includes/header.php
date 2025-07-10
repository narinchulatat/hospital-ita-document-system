<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once 'auth.php';
require_once 'config.php';
require_once 'functions.php';

$pageTitle = $pageTitle ?? 'ระบบผู้ดูแล';
$currentPage = getCurrentPage();

// Define admin menu structure
$adminMenu = [
    'dashboard' => [
        'title' => 'แดชบอร์ด',
        'url' => '/admin/',
        'icon' => 'fa-tachometer-alt',
        'permission' => 'admin.dashboard'
    ],
    'users' => [
        'title' => 'จัดการผู้ใช้',
        'url' => '/admin/users/',
        'icon' => 'fa-users',
        'permission' => 'admin.users',
        'submenu' => [
            'list' => ['title' => 'รายการผู้ใช้', 'url' => '/admin/users/'],
            'create' => ['title' => 'เพิ่มผู้ใช้', 'url' => '/admin/users/create.php']
        ]
    ],
    'documents' => [
        'title' => 'จัดการเอกสาร',
        'url' => '/admin/documents/',
        'icon' => 'fa-file-alt',
        'permission' => 'admin.documents',
        'submenu' => [
            'list' => ['title' => 'รายการเอกสาร', 'url' => '/admin/documents/'],
            'create' => ['title' => 'เพิ่มเอกสาร', 'url' => '/admin/documents/create.php'],
            'approve' => ['title' => 'อนุมัติเอกสาร', 'url' => '/admin/documents/approve.php']
        ]
    ],
    'categories' => [
        'title' => 'จัดการหมวดหมู่',
        'url' => '/admin/categories/',
        'icon' => 'fa-folder',
        'permission' => 'admin.categories'
    ],
    'roles' => [
        'title' => 'จัดการบทบาท',
        'url' => '/admin/roles/',
        'icon' => 'fa-user-shield',
        'permission' => 'admin.roles'
    ],
    'reports' => [
        'title' => 'รายงาน',
        'url' => '/admin/reports/',
        'icon' => 'fa-chart-bar',
        'permission' => 'admin.reports'
    ],
    'settings' => [
        'title' => 'ตั้งค่าระบบ',
        'url' => '/admin/settings/',
        'icon' => 'fa-cog',
        'permission' => 'admin.settings'
    ]
];

// Function to check menu permissions
function hasMenuPermission($permission) {
    // For now, just check if user is admin
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Function to check if menu is active
function isMenuActive($url, $submenu = []) {
    $currentUrl = $_SERVER['REQUEST_URI'];
    if (strpos($currentUrl, $url) !== false) {
        return true;
    }
    foreach ($submenu as $item) {
        if (strpos($currentUrl, $item['url']) !== false) {
            return true;
        }
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= SITE_NAME ?></title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    'sans': ['Sarabun', 'sans-serif'],
                }
            }
        }
    </script>
    
    <!-- Google Fonts - Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables TailwindCSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <style>
        .sidebar-collapsed .nav-text { display: none; }
        .sidebar-collapsed .brand-text { display: none; }
        .sidebar-collapsed { width: 4rem !important; }
        .main-expanded { margin-left: 4rem !important; }
        
        /* Custom Select2 TailwindCSS styling */
        .select2-container--default .select2-selection--single {
            @apply border border-gray-300 rounded-md;
            height: 2.5rem;
        }
        .select2-container--default .select2-selection--multiple {
            @apply border border-gray-300 rounded-md min-h-[2.5rem];
        }
        .select2-dropdown {
            @apply border border-gray-300 rounded-md;
        }
        
        /* DataTables TailwindCSS overrides */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            @apply px-3 py-1 mx-1 text-sm border border-gray-300 rounded;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            @apply bg-blue-500 text-white border-blue-500;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content ml-64 transition-all duration-300 ease-in-out" id="main-content">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button class="text-gray-500 hover:text-gray-700 focus:outline-none lg:hidden" id="sidebarToggle">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <button class="text-gray-500 hover:text-gray-700 focus:outline-none hidden lg:block ml-4" id="sidebarCollapseToggle">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="text-gray-500 hover:text-gray-700 relative focus:outline-none" id="notificationBtn">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden" id="notificationBadge">0</span>
                            </button>
                            <!-- Notification Dropdown -->
                            <div class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 hidden" id="notificationDropdown">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900">การแจ้งเตือน</h3>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <div class="p-4 text-center text-gray-500">
                                        ไม่มีการแจ้งเตือนใหม่
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="relative">
                            <button class="flex items-center text-sm text-gray-500 hover:text-gray-700 focus:outline-none" id="userMenuBtn">
                                <i class="fas fa-user-circle text-2xl mr-2"></i>
                                <span class="hidden md:block"><?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?></span>
                                <i class="fas fa-chevron-down ml-2"></i>
                            </button>
                            
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden" id="userMenuDropdown">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm text-gray-900"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['username']) ?></p>
                                </div>
                                <div class="py-1">
                                    <a href="<?= BASE_URL ?>/admin/profile/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-2"></i>โปรไฟล์
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/settings/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-2"></i>ตั้งค่า
                                    </a>
                                    <div class="border-t border-gray-200"></div>
                                    <a href="<?= BASE_URL ?>/admin/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="p-6">
            <?php if (isset($pageTitle)): ?>
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
                        <?php if (isset($pageSubtitle)): ?>
                        <p class="text-gray-600 mt-1"><?= htmlspecialchars($pageSubtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if (file_exists(__DIR__ . '/breadcrumb.php')): ?>
                            <?php include 'breadcrumb.php'; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Flash Messages -->
            <?php if (file_exists(__DIR__ . '/alerts.php')): ?>
                <?php include 'alerts.php'; ?>
            <?php endif; ?>