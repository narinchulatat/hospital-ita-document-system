<?php
/**
 * Reports Main Index Page
 */

// Page configuration
$pageTitle = 'ระบบรายงาน';
$pageDescription = 'ระบบรายงานครบถ้วนสำหรับโรงพยาบาล ITA';
$pageIcon = 'fas fa-chart-bar';
require_once __DIR__ . '/includes/functions.php';
$breadcrumb = generateReportBreadcrumb();

// Include header
include_once __DIR__ . '/includes/header.php';

// Get report permissions
$permissions = getReportPermissions();
$reportsMenu = getReportsMenu();
?>

<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-8 text-white">
        <div class="max-w-4xl">
            <h1 class="text-3xl font-bold mb-4">
                <i class="fas fa-chart-line mr-3"></i>
                ยินดีต้อนรับสู่ระบบรายงาน
            </h1>
            <p class="text-xl mb-6 text-blue-100">
                รายงานครบถ้วนสำหรับการบริหารจัดการระบบเอกสาร ITA โรงพยาบาล
            </p>
            <div class="flex items-center space-x-4">
                <a href="<?php echo REPORTS_URL; ?>/dashboard.php" 
                   class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </a>
                <?php if (hasReportPermission('custom')): ?>
                <a href="<?php echo REPORTS_URL; ?>/custom/" 
                   class="bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-400 transition-colors">
                    <i class="fas fa-magic mr-2"></i>
                    สร้างรายงานกำหนดเอง
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reports Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($reportsMenu as $categoryKey => $category): ?>
        <div class="report-card p-6 cursor-pointer" onclick="window.location='<?php echo REPORTS_URL; ?>/<?php echo $categoryKey; ?>/'">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="<?php echo $category['icon']; ?> text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900"><?php echo $category['name']; ?></h3>
                    <p class="text-sm text-gray-600"><?php echo count($category['reports']); ?> รายงาน</p>
                </div>
            </div>
            <p class="text-gray-600 mb-4"><?php echo $category['description']; ?></p>
            <div class="flex flex-wrap gap-2">
                <?php foreach (array_slice($category['reports'], 0, 3) as $reportKey => $reportName): ?>
                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                    <?php echo $reportName; ?>
                </span>
                <?php endforeach; ?>
                <?php if (count($category['reports']) > 3): ?>
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                    +<?php echo count($category['reports']) - 3; ?> เพิ่มเติม
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Stats -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
            สถิติด่วน
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            // Get quick statistics
            $report = new Report();
            $systemSummary = $report->getSystemSummary();
            ?>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-blue-600">
                        <?php echo formatNumber($systemSummary['documents']['total']); ?>
                    </span>
                </div>
                <h4 class="text-sm font-medium text-gray-900">เอกสารทั้งหมด</h4>
                <p class="text-xs text-gray-500 mt-1">
                    อนุมัติแล้ว: <?php echo formatNumber($systemSummary['documents']['approved']); ?>
                </p>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-green-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-green-600">
                        <?php echo formatNumber($systemSummary['users']['total']); ?>
                    </span>
                </div>
                <h4 class="text-sm font-medium text-gray-900">ผู้ใช้งาน</h4>
                <p class="text-xs text-gray-500 mt-1">
                    ใช้งาน: <?php echo formatNumber($systemSummary['users']['active']); ?>
                </p>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-yellow-600">
                        <?php echo formatNumber($systemSummary['documents']['pending']); ?>
                    </span>
                </div>
                <h4 class="text-sm font-medium text-gray-900">รออนุมัติ</h4>
                <p class="text-xs text-gray-500 mt-1">
                    ต้องดำเนินการ
                </p>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder text-purple-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-purple-600">
                        <?php echo formatNumber($systemSummary['categories']['total']); ?>
                    </span>
                </div>
                <h4 class="text-sm font-medium text-gray-900">หมวดหมู่</h4>
                <p class="text-xs text-gray-500 mt-1">
                    ใช้งาน: <?php echo formatNumber($systemSummary['categories']['active']); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="report-card p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history mr-2 text-blue-600"></i>
                กิจกรรมล่าสุด
            </h3>
            <a href="<?php echo REPORTS_URL; ?>/users/activity.php" 
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                ดูทั้งหมด
            </a>
        </div>
        
        <div class="space-y-4">
            <?php foreach (array_slice($systemSummary['recent_activity'], 0, 5) as $activity): ?>
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">
                        <strong><?php echo htmlspecialchars($activity['username'] ?? 'ระบบ'); ?></strong>
                        <?php echo htmlspecialchars($activity['action']); ?>
                    </p>
                    <p class="text-xs text-gray-500">
                        <?php echo formatThaiDate($activity['created_at'], 'j F Y เวลา H:i'); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-bolt mr-2 text-blue-600"></i>
            การดำเนินการด่วน
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="<?php echo REPORTS_URL; ?>/dashboard.php" 
               class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-tachometer-alt text-blue-600 mr-3"></i>
                <span class="text-sm font-medium text-blue-900">Dashboard</span>
            </a>
            
            <?php if (hasReportPermission('documents')): ?>
            <a href="<?php echo REPORTS_URL; ?>/documents/summary.php" 
               class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-file-alt text-green-600 mr-3"></i>
                <span class="text-sm font-medium text-green-900">สรุปเอกสาร</span>
            </a>
            <?php endif; ?>
            
            <?php if (hasReportPermission('users')): ?>
            <a href="<?php echo REPORTS_URL; ?>/users/activity.php" 
               class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                <i class="fas fa-users text-yellow-600 mr-3"></i>
                <span class="text-sm font-medium text-yellow-900">กิจกรรมผู้ใช้</span>
            </a>
            <?php endif; ?>
            
            <?php if (hasReportPermission('export')): ?>
            <a href="<?php echo REPORTS_URL; ?>/export/" 
               class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-download text-purple-600 mr-3"></i>
                <span class="text-sm font-medium text-purple-900">ส่งออกรายงาน</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/includes/footer.php';
?>