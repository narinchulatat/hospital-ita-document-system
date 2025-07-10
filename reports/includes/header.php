<?php
// Include reports config
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Get current user info
$currentUser = getCurrentUser();
$userRole = getCurrentUserRole();
$permissions = getReportPermissions();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'รายงาน'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/favicon.ico">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo REPORTS_ASSETS_URL; ?>/css/reports.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom styles -->
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8fafc;
        }
        .report-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #3B82F6;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }
        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 4px;
        }
        .sidebar-reports {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .sidebar-reports .nav-item {
            margin-bottom: 8px;
        }
        .sidebar-reports .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #64748b;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .sidebar-reports .nav-link:hover,
        .sidebar-reports .nav-link.active {
            background: #f1f5f9;
            color: #3B82F6;
        }
        .sidebar-reports .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .export-btn {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            color: #374151;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        .export-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        .filter-group button {
            padding: 8px 16px;
            background: #3B82F6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background 0.3s ease;
        }
        .filter-group button:hover {
            background: #2563eb;
        }
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }
            .filter-group {
                min-width: 100%;
            }
            .export-buttons {
                flex-wrap: wrap;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>" class="flex items-center">
                        <i class="fas fa-hospital text-2xl text-blue-600 mr-3"></i>
                        <span class="text-xl font-bold text-gray-900">
                            <?php echo SITE_NAME; ?>
                        </span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="<?php echo BASE_URL; ?>" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-home mr-2"></i>หน้าหลัก
                    </a>
                    
                    <?php if ($userRole === ROLE_ADMIN): ?>
                    <a href="<?php echo BASE_URL; ?>/admin" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-cog mr-2"></i>จัดการระบบ
                    </a>
                    <?php endif; ?>
                    
                    <div class="relative">
                        <button class="flex items-center text-gray-600 hover:text-blue-600 transition-colors" onclick="toggleUserMenu()">
                            <i class="fas fa-user-circle mr-2"></i>
                            <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                        
                        <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden z-50">
                            <div class="py-1">
                                <a href="<?php echo BASE_URL; ?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>โปรไฟล์
                                </a>
                                <a href="<?php echo BASE_URL; ?>/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm">
                <?php foreach ($breadcrumb as $index => $item): ?>
                    <?php if ($index > 0): ?>
                        <li class="text-gray-400">/</li>
                    <?php endif; ?>
                    <li>
                        <?php if (isset($item['url']) && $index < count($breadcrumb) - 1): ?>
                            <a href="<?php echo $item['url']; ?>" class="text-blue-600 hover:text-blue-800">
                                <?php echo $item['name']; ?>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-900"><?php echo $item['name']; ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
        
        <!-- Page Header -->
        <?php if (isset($pageTitle)): ?>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <?php if (isset($pageIcon)): ?>
                    <i class="<?php echo $pageIcon; ?> mr-3 text-blue-600"></i>
                <?php endif; ?>
                <?php echo $pageTitle; ?>
            </h1>
            <?php if (isset($pageDescription)): ?>
                <p class="text-gray-600"><?php echo $pageDescription; ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Main Content -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <div class="lg:w-64 flex-shrink-0">
                <?php include __DIR__ . '/sidebar.php'; ?>
            </div>
            
            <!-- Content Area -->
            <div class="flex-1">