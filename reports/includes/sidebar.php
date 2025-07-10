<?php
// Get reports menu for current user
$reportsMenu = getReportsMenu();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>

<div class="sidebar-reports">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-chart-bar mr-2 text-blue-600"></i>รายงาน
    </h3>
    
    <nav class="space-y-2">
        <!-- Dashboard -->
        <div class="nav-item">
            <a href="<?php echo REPORTS_URL; ?>/dashboard.php" 
               class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <!-- Document Reports -->
        <?php if (isset($reportsMenu['documents'])): ?>
        <div class="nav-item">
            <div class="nav-category">
                <button class="nav-link w-full text-left" onclick="toggleSubmenu('documents')">
                    <i class="<?php echo $reportsMenu['documents']['icon']; ?>"></i>
                    <span><?php echo $reportsMenu['documents']['name']; ?></span>
                    <i class="fas fa-chevron-down ml-auto transform transition-transform" id="documents-arrow"></i>
                </button>
                <div id="documents-submenu" class="ml-6 mt-2 space-y-1 <?php echo ($currentDir === 'documents') ? '' : 'hidden'; ?>">
                    <?php foreach ($reportsMenu['documents']['reports'] as $key => $name): ?>
                    <a href="<?php echo REPORTS_URL; ?>/documents/<?php echo $key; ?>.php" 
                       class="nav-link text-sm <?php echo ($currentPage === $key) ? 'active' : ''; ?>">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span><?php echo $name; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- User Reports -->
        <?php if (isset($reportsMenu['users'])): ?>
        <div class="nav-item">
            <div class="nav-category">
                <button class="nav-link w-full text-left" onclick="toggleSubmenu('users')">
                    <i class="<?php echo $reportsMenu['users']['icon']; ?>"></i>
                    <span><?php echo $reportsMenu['users']['name']; ?></span>
                    <i class="fas fa-chevron-down ml-auto transform transition-transform" id="users-arrow"></i>
                </button>
                <div id="users-submenu" class="ml-6 mt-2 space-y-1 <?php echo ($currentDir === 'users') ? '' : 'hidden'; ?>">
                    <?php foreach ($reportsMenu['users']['reports'] as $key => $name): ?>
                    <a href="<?php echo REPORTS_URL; ?>/users/<?php echo $key; ?>.php" 
                       class="nav-link text-sm <?php echo ($currentPage === $key) ? 'active' : ''; ?>">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span><?php echo $name; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Approval Reports -->
        <?php if (isset($reportsMenu['approvals'])): ?>
        <div class="nav-item">
            <div class="nav-category">
                <button class="nav-link w-full text-left" onclick="toggleSubmenu('approvals')">
                    <i class="<?php echo $reportsMenu['approvals']['icon']; ?>"></i>
                    <span><?php echo $reportsMenu['approvals']['name']; ?></span>
                    <i class="fas fa-chevron-down ml-auto transform transition-transform" id="approvals-arrow"></i>
                </button>
                <div id="approvals-submenu" class="ml-6 mt-2 space-y-1 <?php echo ($currentDir === 'approvals') ? '' : 'hidden'; ?>">
                    <?php foreach ($reportsMenu['approvals']['reports'] as $key => $name): ?>
                    <a href="<?php echo REPORTS_URL; ?>/approvals/<?php echo $key; ?>.php" 
                       class="nav-link text-sm <?php echo ($currentPage === $key) ? 'active' : ''; ?>">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span><?php echo $name; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- System Reports -->
        <?php if (isset($reportsMenu['system'])): ?>
        <div class="nav-item">
            <div class="nav-category">
                <button class="nav-link w-full text-left" onclick="toggleSubmenu('system')">
                    <i class="<?php echo $reportsMenu['system']['icon']; ?>"></i>
                    <span><?php echo $reportsMenu['system']['name']; ?></span>
                    <i class="fas fa-chevron-down ml-auto transform transition-transform" id="system-arrow"></i>
                </button>
                <div id="system-submenu" class="ml-6 mt-2 space-y-1 <?php echo ($currentDir === 'system') ? '' : 'hidden'; ?>">
                    <?php foreach ($reportsMenu['system']['reports'] as $key => $name): ?>
                    <a href="<?php echo REPORTS_URL; ?>/system/<?php echo $key; ?>.php" 
                       class="nav-link text-sm <?php echo ($currentPage === $key) ? 'active' : ''; ?>">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span><?php echo $name; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Analytics Reports -->
        <?php if (isset($reportsMenu['analytics'])): ?>
        <div class="nav-item">
            <div class="nav-category">
                <button class="nav-link w-full text-left" onclick="toggleSubmenu('analytics')">
                    <i class="<?php echo $reportsMenu['analytics']['icon']; ?>"></i>
                    <span><?php echo $reportsMenu['analytics']['name']; ?></span>
                    <i class="fas fa-chevron-down ml-auto transform transition-transform" id="analytics-arrow"></i>
                </button>
                <div id="analytics-submenu" class="ml-6 mt-2 space-y-1 <?php echo ($currentDir === 'analytics') ? '' : 'hidden'; ?>">
                    <?php foreach ($reportsMenu['analytics']['reports'] as $key => $name): ?>
                    <a href="<?php echo REPORTS_URL; ?>/analytics/<?php echo $key; ?>.php" 
                       class="nav-link text-sm <?php echo ($currentPage === $key) ? 'active' : ''; ?>">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span><?php echo $name; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Export & Tools -->
        <?php if (hasReportPermission('export')): ?>
        <div class="nav-item border-t pt-4 mt-4">
            <div class="nav-category">
                <button class="nav-link w-full text-left" onclick="toggleSubmenu('tools')">
                    <i class="fas fa-tools"></i>
                    <span>เครื่องมือ</span>
                    <i class="fas fa-chevron-down ml-auto transform transition-transform" id="tools-arrow"></i>
                </button>
                <div id="tools-submenu" class="ml-6 mt-2 space-y-1 hidden">
                    <?php if (hasReportPermission('scheduled')): ?>
                    <a href="<?php echo REPORTS_URL; ?>/scheduled/" class="nav-link text-sm">
                        <i class="fas fa-clock"></i>
                        <span>รายงานตามกำหนดการ</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasReportPermission('custom')): ?>
                    <a href="<?php echo REPORTS_URL; ?>/custom/" class="nav-link text-sm">
                        <i class="fas fa-magic"></i>
                        <span>รายงานกำหนดเอง</span>
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo REPORTS_URL; ?>/export/" class="nav-link text-sm">
                        <i class="fas fa-download"></i>
                        <span>ส่งออกรายงาน</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </nav>
</div>

<script>
function toggleSubmenu(menuId) {
    const submenu = document.getElementById(menuId + '-submenu');
    const arrow = document.getElementById(menuId + '-arrow');
    
    submenu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

// Auto-expand active menu
document.addEventListener('DOMContentLoaded', function() {
    const activeLinks = document.querySelectorAll('.nav-link.active');
    activeLinks.forEach(link => {
        const submenu = link.closest('[id$="-submenu"]');
        if (submenu) {
            submenu.classList.remove('hidden');
            const menuId = submenu.id.replace('-submenu', '');
            const arrow = document.getElementById(menuId + '-arrow');
            if (arrow) {
                arrow.classList.add('rotate-180');
            }
        }
    });
});
</script>