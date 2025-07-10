<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once 'auth.php';
require_once 'config.php';
require_once 'functions.php';

$pageTitle = $pageTitle ?? 'ระบบผู้ดูแล';
$currentPage = getCurrentPage();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= SITE_NAME ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/dataTables.responsive.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <!-- TailwindCSS Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sarabun': ['Sarabun', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#3498db',
                        'secondary': '#2c3e50',
                        'success': '#27ae60',
                        'warning': '#f39c12',
                        'danger': '#e74c3c',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sarabun bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-secondary to-gray-800 shadow-lg transition-all duration-300 ease-in-out" id="sidebar">
        <!-- Sidebar Brand -->
        <div class="flex items-center justify-center px-4 py-6 border-b border-white/10">
            <h4 class="text-white text-xl font-semibold">
                <i class="fas fa-hospital mr-2"></i>
                <span class="brand-text">Admin Panel</span>
            </h4>
        </div>
        
        <!-- Sidebar Navigation -->
        <nav class="mt-8 px-4">
            <?php 
            // Define admin menu structure
            $adminMenu = [
                'dashboard' => [
                    'title' => 'แดชบอร์ด',
                    'icon' => 'fa-tachometer-alt',
                    'url' => '/admin/',
                    'permission' => 'admin'
                ],
                'users' => [
                    'title' => 'จัดการผู้ใช้',
                    'icon' => 'fa-users',
                    'url' => '/admin/users/',
                    'permission' => 'admin'
                ],
                'documents' => [
                    'title' => 'จัดการเอกสาร',
                    'icon' => 'fa-file-alt',
                    'url' => '/admin/documents/',
                    'permission' => 'admin'
                ],
                'categories' => [
                    'title' => 'หมวดหมู่',
                    'icon' => 'fa-folder',
                    'url' => '/admin/categories/',
                    'permission' => 'admin'
                ],
                'settings' => [
                    'title' => 'ตั้งค่า',
                    'icon' => 'fa-cog',
                    'url' => '/admin/settings/',
                    'permission' => 'admin'
                ],
                'reports' => [
                    'title' => 'รายงาน',
                    'icon' => 'fa-chart-bar',
                    'url' => '/admin/reports/',
                    'permission' => 'admin'
                ]
            ];
            
            foreach ($adminMenu as $key => $menu): 
                $isActive = strpos($_SERVER['REQUEST_URI'], $menu['url']) !== false;
            ?>
                <a href="<?= BASE_URL . $menu['url'] ?>" 
                   class="flex items-center px-4 py-3 mb-1 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 <?= $isActive ? 'bg-white/20 text-white border-r-4 border-primary' : '' ?>">
                    <i class="fas <?= $menu['icon'] ?> w-5 text-center mr-3"></i>
                    <span class="nav-text"><?= $menu['title'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="ml-64 transition-all duration-300 ease-in-out min-h-screen" id="main-content">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between px-6 py-4">
                <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" id="sidebarToggle" type="button">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-bell"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                        </button>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span><?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?></span>
                            <i class="fas fa-chevron-down text-sm"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <a href="<?= BASE_URL ?>/admin/profile/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-user mr-2"></i>โปรไฟล์
                            </a>
                            <a href="<?= BASE_URL ?>/admin/profile/password.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-key mr-2"></i>เปลี่ยนรหัสผ่าน
                            </a>
                            <hr class="my-2">
                            <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-6">
            <?php if (isset($pageTitle)): ?>
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
                        <?php if (isset($pageSubtitle)): ?>
                        <p class="text-gray-600 mt-1"><?= htmlspecialchars($pageSubtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <!-- Breadcrumb -->
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2 text-gray-500">
                                <li><a href="<?= BASE_URL ?>/admin/" class="hover:text-gray-700">หน้าหลัก</a></li>
                                <?php if (isset($breadcrumbs)): ?>
                                    <?php foreach ($breadcrumbs as $crumb): ?>
                                    <li class="flex items-center">
                                        <i class="fas fa-chevron-right mx-2 text-xs"></i>
                                        <?php if (isset($crumb['url'])): ?>
                                        <a href="<?= $crumb['url'] ?>" class="hover:text-gray-700"><?= $crumb['title'] ?></a>
                                        <?php else: ?>
                                        <span class="text-gray-900"><?= $crumb['title'] ?></span>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start">
                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                <span class="text-green-700"><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2 mt-0.5"></i>
                <span class="text-red-700"><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); endif; ?>
            
            <!-- Page Content Starts Here -->