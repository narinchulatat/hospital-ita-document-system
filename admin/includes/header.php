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
    
    <!-- Admin CSS -->
    <link href="<?= BASE_URL ?>/admin/assets/css/admin.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/sidebar.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/dashboard.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/tables.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/forms.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sarabun': ['Sarabun', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="font-sarabun bg-gray-50">
    <!-- Include Unified Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="px-6">
                <div class="flex items-center justify-between h-16">
                    <button class="text-gray-600 hover:text-gray-800 p-2 rounded-lg hover:bg-gray-100 transition-colors lg:hidden" id="mobileMenuToggle" type="button">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="text-gray-600 hover:text-gray-800 p-2 rounded-lg hover:bg-gray-100 transition-colors" id="notificationDropdown">
                                <i class="fas fa-bell"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                            </button>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="relative">
                            <button class="flex items-center text-gray-600 hover:text-gray-800 p-2 rounded-lg hover:bg-gray-100 transition-colors" id="userDropdown">
                                <i class="fas fa-user-circle mr-2"></i>
                                <?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?>
                                <i class="fas fa-chevron-down ml-2 text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50 hidden" id="userDropdownMenu">
                                <a href="<?= BASE_URL ?>/admin/profile/" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>โปรไฟล์
                                </a>
                                <a href="<?= BASE_URL ?>/admin/profile/password.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-key mr-2"></i>เปลี่ยนรหัสผ่าน
                                </a>
                                <hr class="my-1">
                                <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
            <div class="bg-white p-6 mb-8 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800"><?= htmlspecialchars($pageTitle) ?></h1>
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
            <?php include '../components/alerts.php'; ?>