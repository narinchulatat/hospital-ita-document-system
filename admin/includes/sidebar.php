<?php
/**
 * Unified Sidebar Component
 * Renders the main navigation sidebar with hierarchical menu structure
 */

// Include menu configuration
require_once 'menu_config.php';

// Get filtered menu based on user permissions
$filteredMenu = getFilteredMenu($menu_config);
$currentPage = getCurrentPage();
?>

<aside class="sidebar" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-hospital"></i>
            <span class="brand-text">Hospital ITA</span>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" type="button">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <!-- Sidebar Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-scroll">
            <?php foreach ($filteredMenu as $menuKey => $menu): ?>
                <?php
                $isActive = isMenuActive($menu['url'], $menu['children'] ?? []);
                $hasChildren = !empty($menu['children']);
                $shouldExpand = $hasChildren && shouldExpandMenu($menu['children']);
                ?>
                
                <div class="nav-item <?= $isActive ? 'active' : '' ?>">
                    <a href="<?= $menu['url'] === '#' ? 'javascript:void(0)' : BASE_URL . $menu['url'] ?>" 
                       class="nav-link <?= $hasChildren ? 'has-dropdown' : '' ?> <?= $isActive ? 'active' : '' ?>"
                       data-toggle="<?= $hasChildren ? 'dropdown' : '' ?>">
                        <i class="nav-icon fas <?= $menu['icon'] ?>"></i>
                        <span class="nav-text"><?= htmlspecialchars($menu['title']) ?></span>
                        <?php if ($hasChildren): ?>
                            <i class="nav-arrow fas fa-chevron-down"></i>
                        <?php endif; ?>
                    </a>
                    
                    <?php if ($hasChildren): ?>
                        <div class="nav-dropdown <?= $shouldExpand ? 'show' : '' ?>">
                            <?php foreach ($menu['children'] as $childKey => $child): ?>
                                <?php if (hasMenuPermission($child['permission'])): ?>
                                    <a href="<?= BASE_URL . $child['url'] ?>" 
                                       class="nav-dropdown-link <?= isMenuActive($child['url']) ? 'active' : '' ?>">
                                        <i class="nav-dropdown-icon fas <?= $child['icon'] ?>"></i>
                                        <span class="nav-dropdown-text"><?= htmlspecialchars($child['title']) ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?></div>
                <div class="user-role"><?= htmlspecialchars($_SESSION['role_name'] ?? 'ผู้ดูแลระบบ') ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>