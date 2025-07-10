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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link href="<?= BASE_URL ?>/admin/assets/css/admin.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/dashboard.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/tables.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/forms.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: 70px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-bottom: 1px solid #e9ecef;
        }
        
        .sidebar-brand {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
            border-right: 3px solid #3498db;
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }
        
        .submenu {
            background-color: rgba(0,0,0,0.2);
        }
        
        .submenu .nav-link {
            padding-left: 3rem;
            font-size: 0.9rem;
        }
        
        .page-header {
            background: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .page-subtitle {
            color: #6c757d;
            margin: 0.25rem 0 0 0;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
        }
        
        .alert {
            border: none;
            border-radius: 8px;
        }
        
        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h4><i class="fas fa-hospital me-2"></i><span class="brand-text">Admin Panel</span></h4>
        </div>
        
        <div class="sidebar-nav">
            <?php foreach ($adminMenu as $key => $menu): ?>
                <?php if (hasMenuPermission($menu['permission'])): ?>
                    <div class="nav-item">
                        <a href="<?= BASE_URL . $menu['url'] ?>" 
                           class="nav-link <?= isMenuActive($menu['url'], $menu['submenu'] ?? []) ? 'active' : '' ?>">
                            <i class="fas <?= $menu['icon'] ?>"></i>
                            <span class="nav-text"><?= $menu['title'] ?></span>
                        </a>
                        
                        <?php if (!empty($menu['submenu']) && isMenuActive($menu['url'], $menu['submenu'])): ?>
                            <div class="submenu">
                                <?php foreach ($menu['submenu'] as $subkey => $submenu): ?>
                                    <a href="<?= BASE_URL . $submenu['url'] ?>" 
                                       class="nav-link <?= getCurrentPage() === $submenu['url'] ? 'active' : '' ?>">
                                        <i class="fas fa-circle"></i>
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
    <div class="main-content" id="main-content">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <button class="btn btn-outline-secondary me-3" id="sidebarToggle" type="button">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="navbar-nav ms-auto">
                    <!-- Notifications -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger rounded-pill">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text">ไม่มีการแจ้งเตือน</span></li>
                        </ul>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/profile/"><i class="fas fa-user me-2"></i>โปรไฟล์</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/profile/password.php"><i class="fas fa-key me-2"></i>เปลี่ยนรหัสผ่าน</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="container-fluid py-4">
            <?php if (isset($pageTitle)): ?>
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
                        <?php if (isset($pageSubtitle)): ?>
                        <p class="page-subtitle"><?= htmlspecialchars($pageSubtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-auto">
                        <?php include 'breadcrumb.php'; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Flash Messages -->
            <?php include 'alerts.php'; ?>