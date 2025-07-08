<?php
/**
 * Common Header Template
 */

// Include configuration and authentication
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Auto-load classes
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Get current user if logged in
$currentUser = getCurrentUser();
$siteName = getSetting('site_name', SITE_NAME);
$siteDescription = getSetting('site_description', SITE_DESCRIPTION);

// Determine current page title
$pageTitle = $pageTitle ?? $siteName;
if ($pageTitle !== $siteName) {
    $pageTitle = $pageTitle . ' - ' . $siteName;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($siteDescription) ?>">
    
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/custom.css">
    
    <style>
        .swal2-popup {
            font-family: 'Sarabun', sans-serif !important;
        }
        
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }
        
        .loading-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            text-align: center;
        }
        
        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .tree-item {
            margin-left: 1.5rem;
        }
        
        .tree-toggle {
            cursor: pointer;
            user-select: none;
        }
        
        .tree-toggle:hover {
            background-color: #f3f4f6;
        }
        
        .tree-children {
            display: none;
        }
        
        .tree-children.show {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Loading overlay -->
    <div id="loading" class="loading">
        <div class="loading-content">
            <div class="spinner"></div>
            <p class="text-gray-600">กำลังโหลด...</p>
        </div>
    </div>

    <?php if (isLoggedIn()): ?>
    <!-- Navigation for logged in users -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="<?= getRoleRedirectUrl(getCurrentUserRole()) ?>" class="text-xl font-bold text-blue-600">
                            <i class="fas fa-hospital mr-2"></i>
                            <?= htmlspecialchars($siteName) ?>
                        </a>
                    </div>
                    
                    <!-- Main Navigation -->
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <?php 
                        global $DASHBOARD_MENUS;
                        $userRole = getCurrentUserRole();
                        if (isset($DASHBOARD_MENUS[$userRole])):
                            foreach ($DASHBOARD_MENUS[$userRole] as $menu):
                        ?>
                        <a href="<?= BASE_URL . $menu['url'] ?>" 
                           class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="<?= $menu['icon'] ?> mr-2"></i>
                            <?= htmlspecialchars($menu['title']) ?>
                        </a>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
                
                <!-- User menu -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notificationBtn" class="text-gray-500 hover:text-gray-700 relative">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="notificationCount" class="hidden absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                        </button>
                    </div>
                    
                    <!-- User dropdown -->
                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center text-sm text-gray-500 hover:text-gray-700">
                            <i class="fas fa-user-circle text-2xl mr-2"></i>
                            <span><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                        
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <div class="px-4 py-2 text-xs text-gray-500 border-b">
                                <?= htmlspecialchars($currentUser['username']) ?><br>
                                <span class="text-blue-600"><?= htmlspecialchars($currentUser['role_name']) ?></span>
                            </div>
                            <a href="<?= BASE_URL ?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>โปรไฟล์
                            </a>
                            <a href="<?= BASE_URL ?>/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>ตั้งค่า
                            </a>
                            <div class="border-t">
                                <a href="<?= BASE_URL ?>/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobileMenu" class="hidden md:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <?php 
                if (isset($DASHBOARD_MENUS[$userRole])):
                    foreach ($DASHBOARD_MENUS[$userRole] as $menu):
                ?>
                <a href="<?= BASE_URL . $menu['url'] ?>" 
                   class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="<?= $menu['icon'] ?> mr-2"></i>
                    <?= htmlspecialchars($menu['title']) ?>
                </a>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </nav>
    <?php else: ?>
    <!-- Public navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="<?= BASE_URL ?>/public/" class="text-xl font-bold text-blue-600">
                            <i class="fas fa-hospital mr-2"></i>
                            <?= htmlspecialchars($siteName) ?>
                        </a>
                    </div>
                    
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="<?= BASE_URL ?>/public/" class="text-gray-500 hover:text-gray-700 inline-flex items-center px-1 pt-1 text-sm font-medium">
                            <i class="fas fa-home mr-2"></i>หน้าหลัก
                        </a>
                        <a href="<?= BASE_URL ?>/public/documents/" class="text-gray-500 hover:text-gray-700 inline-flex items-center px-1 pt-1 text-sm font-medium">
                            <i class="fas fa-file-alt mr-2"></i>เอกสาร
                        </a>
                        <a href="<?= BASE_URL ?>/public/search.php" class="text-gray-500 hover:text-gray-700 inline-flex items-center px-1 pt-1 text-sm font-medium">
                            <i class="fas fa-search mr-2"></i>ค้นหา
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>เข้าสู่ระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main content container -->
    <main class="flex-1"><?php
// The main content will be inserted here by individual pages
?>