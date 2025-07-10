<?php
// Sidebar component for admin panel
?>
<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white transform transition-transform duration-300 ease-in-out z-50" id="sidebar">
    <!-- Sidebar Header -->
    <div class="flex items-center justify-center h-16 border-b border-gray-700">
        <div class="flex items-center">
            <i class="fas fa-hospital text-2xl text-blue-400"></i>
            <span class="ml-2 text-xl font-bold brand-text">Admin Panel</span>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="mt-8">
        <div class="px-4 space-y-2">
            <?php foreach ($adminMenu as $key => $menu): ?>
                <?php if (hasMenuPermission($menu['permission'])): ?>
                    <div class="nav-group">
                        <a href="<?= BASE_URL . $menu['url'] ?>" 
                           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 group <?= isMenuActive($menu['url'], $menu['submenu'] ?? []) ? 'bg-gray-700 text-white' : '' ?>"
                           <?php if (!empty($menu['submenu'])): ?>
                           onclick="toggleSubmenu(event, '<?= $key ?>')"
                           <?php endif; ?>>
                            <i class="fas <?= $menu['icon'] ?> w-5 h-5"></i>
                            <span class="ml-3 nav-text"><?= $menu['title'] ?></span>
                            <?php if (!empty($menu['submenu'])): ?>
                            <i class="fas fa-chevron-down ml-auto transition-transform duration-200" id="chevron-<?= $key ?>"></i>
                            <?php endif; ?>
                        </a>
                        
                        <?php if (!empty($menu['submenu'])): ?>
                        <div class="ml-8 mt-2 space-y-1 <?= isMenuActive($menu['url'], $menu['submenu']) ? '' : 'hidden' ?>" id="submenu-<?= $key ?>">
                            <?php foreach ($menu['submenu'] as $subkey => $submenu): ?>
                            <a href="<?= BASE_URL . $submenu['url'] ?>" 
                               class="flex items-center px-4 py-2 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-white transition-colors duration-200 text-sm <?= getCurrentPage() === $submenu['url'] ? 'bg-gray-700 text-white' : '' ?>">
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
        
        <!-- Bottom Navigation -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
            <div class="space-y-2">
                <a href="<?= BASE_URL ?>/admin/backups/" 
                   class="flex items-center px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                    <i class="fas fa-database w-5 h-5"></i>
                    <span class="ml-3 nav-text">สำรองข้อมูล</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/logs/" 
                   class="flex items-center px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                    <i class="fas fa-history w-5 h-5"></i>
                    <span class="ml-3 nav-text">บันทึกกิจกรรม</span>
                </a>
            </div>
        </div>
    </nav>
</div>

<!-- Mobile Sidebar Overlay -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden" id="sidebarOverlay"></div>

<script>
// Sidebar toggle functionality
function toggleSubmenu(event, menuKey) {
    event.preventDefault();
    const submenu = document.getElementById('submenu-' + menuKey);
    const chevron = document.getElementById('chevron-' + menuKey);
    
    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    } else {
        submenu.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }
}

// Initialize sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('main-content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarCollapseToggle = document.getElementById('sidebarCollapseToggle');
    
    // Mobile sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (window.innerWidth < 1024) { // lg breakpoint
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }
        });
    }
    
    // Desktop sidebar collapse
    if (sidebarCollapseToggle) {
        sidebarCollapseToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('main-expanded');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    }
    
    // Overlay click to close mobile sidebar
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }
    
    // Restore sidebar state on desktop
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true' && window.innerWidth >= 1024) {
        sidebar.classList.add('sidebar-collapsed');
        mainContent.classList.add('main-expanded');
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            // Desktop view
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        } else {
            // Mobile view
            sidebar.classList.remove('sidebar-collapsed');
            mainContent.classList.remove('main-expanded');
            sidebar.classList.add('-translate-x-full');
        }
    });
    
    // Initialize mobile state
    if (window.innerWidth < 1024) {
        sidebar.classList.add('-translate-x-full');
    }
});

// Dropdown toggles
document.addEventListener('DOMContentLoaded', function() {
    // Notification dropdown
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('hidden');
            // Close user menu if open
            const userMenuDropdown = document.getElementById('userMenuDropdown');
            if (userMenuDropdown) {
                userMenuDropdown.classList.add('hidden');
            }
        });
    }
    
    // User menu dropdown
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenuDropdown = document.getElementById('userMenuDropdown');
    
    if (userMenuBtn && userMenuDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('hidden');
            // Close notification dropdown if open
            if (notificationDropdown) {
                notificationDropdown.classList.add('hidden');
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (notificationDropdown) {
            notificationDropdown.classList.add('hidden');
        }
        if (userMenuDropdown) {
            userMenuDropdown.classList.add('hidden');
        }
    });
});
</script>