<?php
/**
 * Approver Panel Header
 */

// Include global configuration and authentication
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auto-load classes
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/../../classes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Require approver role
requireRole(ROLE_APPROVER);

// Get current user
$currentUser = getCurrentUser();
$siteName = getSetting('site_name', SITE_NAME);

// Determine current page title
$pageTitle = $pageTitle ?? 'แดชบอร์ดผู้อนุมัติ';
if (strpos($pageTitle, $siteName) === false) {
    $pageTitle = $pageTitle . ' - ' . $siteName;
}

// Get current path for active menu
$currentPath = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="ระบบจัดการเอกสารโรงพยาบาล - แผงผู้อนุมัติ">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/approver/assets/css/approver.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-lg border-b border-blue-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo and Main Navigation -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="<?= BASE_URL ?>/approver/" class="flex items-center">
                            <i class="fas fa-hospital text-blue-600 text-2xl mr-3"></i>
                            <div>
                                <div class="text-lg font-bold text-gray-900">ระบบผู้อนุมัติ</div>
                                <div class="text-xs text-gray-500">Hospital Document System</div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <a href="<?= BASE_URL ?>/approver/" 
                           class="<?= strpos($currentPath, '/approver/index') !== false || $currentPath === '/approver/' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>แดชบอร์ด
                        </a>
                        
                        <a href="<?= BASE_URL ?>/approver/documents/" 
                           class="<?= strpos($currentPath, '/approver/documents') !== false ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-file-alt mr-2"></i>เอกสาร
                        </a>
                        
                        <a href="<?= BASE_URL ?>/approver/approval/" 
                           class="<?= strpos($currentPath, '/approver/approval') !== false ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-check-circle mr-2"></i>อนุมัติ
                        </a>
                        
                        <a href="<?= BASE_URL ?>/approver/reports/" 
                           class="<?= strpos($currentPath, '/approver/reports') !== false ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-chart-bar mr-2"></i>รายงาน
                        </a>
                    </div>
                </div>
                
                <!-- Right Navigation -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notificationBtn" class="p-2 text-gray-400 hover:text-gray-500 relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span id="notificationBadge" class="hidden absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">0</span>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1" id="notificationList">
                                <div class="px-4 py-3 text-sm text-gray-500 text-center">ไม่มีการแจ้งเตือน</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <div class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">ผู้อนุมัติ</div>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="<?= BASE_URL ?>/approver/profile/" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-3"></i>โปรไฟล์
                                </a>
                                <a href="<?= BASE_URL ?>/approver/profile/settings.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-3"></i>ตั้งค่า
                                </a>
                                <a href="<?= BASE_URL ?>/public/" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-3"></i>ดูเว็บไซต์สาธารณะ
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="<?= BASE_URL ?>/logout.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-3"></i>ออกจากระบบ
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button id="mobileMenuBtn" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="hidden md:hidden">
            <div class="pt-2 pb-3 space-y-1 bg-white border-t border-gray-200">
                <a href="<?= BASE_URL ?>/approver/" 
                   class="<?= strpos($currentPath, '/approver/index') !== false || $currentPath === '/approver/' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-3"></i>แดชบอร์ด
                </a>
                
                <a href="<?= BASE_URL ?>/approver/documents/" 
                   class="<?= strpos($currentPath, '/approver/documents') !== false ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-file-alt mr-3"></i>เอกสาร
                </a>
                
                <a href="<?= BASE_URL ?>/approver/approval/" 
                   class="<?= strpos($currentPath, '/approver/approval') !== false ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-check-circle mr-3"></i>อนุมัติ
                </a>
                
                <a href="<?= BASE_URL ?>/approver/reports/" 
                   class="<?= strpos($currentPath, '/approver/reports') !== false ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-chart-bar mr-3"></i>รายงาน
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">