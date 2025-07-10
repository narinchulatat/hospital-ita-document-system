<?php
/**
 * Reports Menu
 * Navigation menu for reports section
 */

// Page configuration
$pageTitle = 'เมนูรายงาน';
$pageDescription = 'เลือกประเภทรายงานที่ต้องการดู';
$pageIcon = 'fas fa-bars';
$breadcrumb = generateReportBreadcrumb([
    ['name' => 'เมนูรายงาน']
]);

// Include header
include_once __DIR__ . '/includes/header.php';

// Get report permissions
$permissions = getReportPermissions();
$reportsMenu = getReportsMenu();
?>

<div class="space-y-8">
    <!-- Reports Navigation -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Dashboard -->
        <div class="report-card p-6 cursor-pointer hover:shadow-lg transition-shadow" 
             onclick="window.location='<?php echo REPORTS_URL; ?>/dashboard.php'">
            <div class="flex items-center mb-4">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-tachometer-alt text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Dashboard</h3>
                    <p class="text-sm text-gray-600">ภาพรวมสถิติและรายงาน</p>
                </div>
            </div>
            <p class="text-gray-600 mb-4">
                แสดงสถิติสำคัญ กราฟ และข้อมูลเชิงลึกของระบบ
            </p>
            <div class="flex items-center text-blue-600">
                <span class="text-sm font-medium">ดู Dashboard</span>
                <i class="fas fa-arrow-right ml-2"></i>
            </div>
        </div>

        <?php foreach ($reportsMenu as $categoryKey => $category): ?>
        <!-- <?php echo ucfirst($categoryKey); ?> Reports -->
        <div class="report-card p-6 cursor-pointer hover:shadow-lg transition-shadow" 
             onclick="window.location='<?php echo REPORTS_URL; ?>/<?php echo $categoryKey; ?>/'">
            <div class="flex items-center mb-4">
                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="<?php echo $category['icon']; ?> text-gray-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900"><?php echo $category['name']; ?></h3>
                    <p class="text-sm text-gray-600"><?php echo count($category['reports']); ?> รายงาน</p>
                </div>
            </div>
            <p class="text-gray-600 mb-4">
                <?php echo $category['description']; ?>
            </p>
            <div class="space-y-2">
                <?php foreach (array_slice($category['reports'], 0, 3) as $reportName): ?>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-circle text-xs mr-2"></i>
                    <?php echo $reportName; ?>
                </div>
                <?php endforeach; ?>
                <?php if (count($category['reports']) > 3): ?>
                <div class="flex items-center text-sm text-blue-600">
                    <i class="fas fa-plus text-xs mr-2"></i>
                    อีก <?php echo count($category['reports']) - 3; ?> รายงาน
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Additional Tools -->
    <?php if (hasReportPermission('export') || hasReportPermission('scheduled') || hasReportPermission('custom')): ?>
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-tools mr-2 text-blue-600"></i>
            เครื่องมือเพิ่มเติม
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if (hasReportPermission('export')): ?>
            <a href="<?php echo REPORTS_URL; ?>/export/" 
               class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-download text-blue-600 mr-3"></i>
                <div>
                    <div class="font-medium text-blue-900">ส่งออกรายงาน</div>
                    <div class="text-sm text-blue-600">PDF, Excel, CSV</div>
                </div>
            </a>
            <?php endif; ?>
            
            <?php if (hasReportPermission('scheduled')): ?>
            <a href="<?php echo REPORTS_URL; ?>/scheduled/" 
               class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-clock text-green-600 mr-3"></i>
                <div>
                    <div class="font-medium text-green-900">รายงานตามกำหนดการ</div>
                    <div class="text-sm text-green-600">อัตโนมัติ</div>
                </div>
            </a>
            <?php endif; ?>
            
            <?php if (hasReportPermission('custom')): ?>
            <a href="<?php echo REPORTS_URL; ?>/custom/" 
               class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-magic text-purple-600 mr-3"></i>
                <div>
                    <div class="font-medium text-purple-900">รายงานกำหนดเอง</div>
                    <div class="text-sm text-purple-600">สร้างเอง</div>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Reports -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-history mr-2 text-green-600"></i>
            รายงานล่าสุด
        </h3>
        
        <div class="space-y-4">
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-chart-bar text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-900">สรุปรายงานเอกสาร</div>
                    <div class="text-sm text-gray-600">Dashboard • <?php echo formatThaiDate(date('Y-m-d')); ?></div>
                </div>
                <a href="<?php echo REPORTS_URL; ?>/documents/summary.php" 
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    ดูรายงาน
                </a>
            </div>
            
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-star text-green-600"></i>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-900">เอกสารยอดนิยม</div>
                    <div class="text-sm text-gray-600">เอกสาร • <?php echo formatThaiDate(date('Y-m-d')); ?></div>
                </div>
                <a href="<?php echo REPORTS_URL; ?>/documents/popular.php" 
                   class="text-green-600 hover:text-green-800 text-sm font-medium">
                    ดูรายงาน
                </a>
            </div>
            
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-folder text-purple-600"></i>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-900">รายงานตามหมวดหมู่</div>
                    <div class="text-sm text-gray-600">เอกสาร • <?php echo formatThaiDate(date('Y-m-d')); ?></div>
                </div>
                <a href="<?php echo REPORTS_URL; ?>/documents/by-category.php" 
                   class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                    ดูรายงาน
                </a>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-question-circle mr-2 text-orange-600"></i>
            ความช่วยเหลือ
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-2">วิธีใช้งานรายงาน</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• เลือกประเภทรายงานที่ต้องการ</li>
                    <li>• ใช้ตัวกรองเพื่อจำกัดข้อมูล</li>
                    <li>• ส่งออกรายงานในรูปแบบต่างๆ</li>
                    <li>• ดูกราฟและสถิติแบบเรียลไทม์</li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-900 mb-2">เคล็ดลับ</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• ใช้ช่วงวันที่เพื่อดูแนวโน้ม</li>
                    <li>• เปรียบเทียบข้อมูลหลายช่วงเวลา</li>
                    <li>• บันทึกรายงานที่ใช้บ่อย</li>
                    <li>• ตั้งค่าการส่งรายงานอัตโนมัติ</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/includes/footer.php';
?>