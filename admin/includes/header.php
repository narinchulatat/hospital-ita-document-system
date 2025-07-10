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
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS for TailwindCSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        .sidebar-transition {
            transition: all 0.3s ease;
        }
        
        .nav-link-active {
            border-right: 3px solid #3b82f6;
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .sidebar-collapsed {
            width: 70px;
        }
        
        .main-content-expanded {
            margin-left: 70px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 50;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Sidebar -->
    <nav class="sidebar sidebar-transition fixed top-0 left-0 w-64 h-screen bg-gradient-to-b from-gray-800 to-gray-900 shadow-xl z-50" id="sidebar">
        <!-- Brand -->
        <div class="p-4 text-center border-b border-white border-opacity-10">
            <h4 class="text-white font-semibold text-lg">
                <i class="fas fa-hospital mr-2"></i>
                <span class="brand-text">Admin Panel</span>
            </h4>
        </div>
        
        <!-- Navigation -->
        <div class="py-4">
            <?php foreach ($adminMenu as $key => $menu): ?>
                <?php if (hasMenuPermission($menu['permission'])): ?>
                    <div class="nav-item">
                        <a href="<?= BASE_URL . $menu['url'] ?>" 
                           class="nav-link flex items-center px-4 py-3 text-gray-300 hover:bg-white hover:bg-opacity-10 hover:text-white transition-all <?= isMenuActive($menu['url'], $menu['submenu'] ?? []) ? 'nav-link-active text-white' : '' ?>">
                            <i class="fas <?= $menu['icon'] ?> w-5 mr-3 text-center"></i>
                            <span class="nav-text"><?= $menu['title'] ?></span>
                        </a>
                        
                        <?php if (!empty($menu['submenu']) && isMenuActive($menu['url'], $menu['submenu'])): ?>
                            <div class="submenu bg-black bg-opacity-20">
                                <?php foreach ($menu['submenu'] as $subkey => $submenu): ?>
                                    <a href="<?= BASE_URL . $submenu['url'] ?>" 
                                       class="flex items-center px-12 py-2 text-gray-400 hover:bg-white hover:bg-opacity-10 hover:text-white transition-all text-sm <?= getCurrentPage() === $submenu['url'] ? 'nav-link-active text-white' : '' ?>">
                                        <i class="fas fa-circle text-xs mr-3"></i>
                                        <span class="nav-text"><?= $submenu['title'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content sidebar-transition ml-64 min-h-screen" id="main-content">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="px-6 py-3">
                <div class="flex items-center justify-between">
                    <button class="px-3 py-2 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            id="sidebarToggle" 
                            type="button">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-600 hover:text-gray-900 relative">
                                <i class="fas fa-bell text-lg"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                            </button>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                                <i class="fas fa-user-circle text-2xl"></i>
                                <span class="text-sm font-medium"><?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <a href="<?= BASE_URL ?>/admin/profile/" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>โปรไฟล์
                                </a>
                                <a href="<?= BASE_URL ?>/admin/profile/password.php" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-key mr-2"></i>เปลี่ยนรหัสผ่าน
                                </a>
                                <hr class="my-1">
                                <a href="<?= BASE_URL ?>/admin/logout.php" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="p-6">
            <?php if (isset($pageTitle)): ?>
            <div class="bg-white border-b border-gray-200 mb-8 pb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
                        <?php if (isset($pageSubtitle)): ?>
                        <p class="text-gray-600 mt-1"><?= htmlspecialchars($pageSubtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php include 'breadcrumb.php'; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Flash Messages -->
            <?php include 'alerts.php'; ?>