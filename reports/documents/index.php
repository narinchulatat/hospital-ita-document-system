<?php
/**
 * Documents Reports Index
 */

// Page configuration
$pageTitle = 'รายงานเอกสาร';
$pageDescription = 'รายงานเกี่ยวกับเอกสารในระบบ';
$pageIcon = 'fas fa-file-alt';
$breadcrumb = generateReportBreadcrumb([
    ['name' => 'รายงานเอกสาร', 'url' => REPORTS_URL . '/documents/']
]);

// Include header
include_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

// Check permission
if (!hasReportPermission('documents')) {
    echo '<div class="alert alert-danger">คุณไม่มีสิทธิ์เข้าถึงรายงานเอกสาร</div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Get report data
$report = new Report();
$documentStats = $report->getDocumentStats();
?>

<div class="space-y-8">
    <!-- Document Reports Menu -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="report-card p-6 cursor-pointer" onclick="window.location='<?php echo REPORTS_URL; ?>/documents/summary.php'">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-chart-bar text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">สรุปเอกสาร</h3>
                    <p class="text-sm text-gray-600">ภาพรวมสถิติเอกสาร</p>
                </div>
            </div>
            <p class="text-gray-600">รายงานสรุปข้อมูลเอกสารทั้งหมดในระบบ</p>
        </div>
        
        <div class="report-card p-6 cursor-pointer" onclick="window.location='<?php echo REPORTS_URL; ?>/documents/by-category.php'">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-folder text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">รายงานตามหมวดหมู่</h3>
                    <p class="text-sm text-gray-600">จำแนกตามหมวดหมู่</p>
                </div>
            </div>
            <p class="text-gray-600">รายงานเอกสารแยกตามหมวดหมู่ต่างๆ</p>
        </div>
        
        <div class="report-card p-6 cursor-pointer" onclick="window.location='<?php echo REPORTS_URL; ?>/documents/by-status.php'">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-tag text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">รายงานตามสถานะ</h3>
                    <p class="text-sm text-gray-600">จำแนกตามสถานะ</p>
                </div>
            </div>
            <p class="text-gray-600">รายงานเอกสารแยกตามสถานะการอนุมัติ</p>
        </div>
        
        <div class="report-card p-6 cursor-pointer" onclick="window.location='<?php echo REPORTS_URL; ?>/documents/by-date.php'">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-calendar text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">รายงานตามวันที่</h3>
                    <p class="text-sm text-gray-600">จำแนกตามช่วงเวลา</p>
                </div>
            </div>
            <p class="text-gray-600">รายงานเอกสารแยกตามวันที่สร้างหรืออัปเดต</p>
        </div>
        
        <div class="report-card p-6 cursor-pointer" onclick="window.location='<?php echo REPORTS_URL; ?>/documents/popular.php'">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-star text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">เอกสารยอดนิยม</h3>
                    <p class="text-sm text-gray-600">เอกสารที่ได้รับความนิยม</p>
                </div>
            </div>
            <p class="text-gray-600">รายงานเอกสารที่มีการเข้าชมและดาวน์โหลดมาก</p>
        </div>
        
        <div class="report-card p-6 cursor-pointer" onclick="window.location='<?php echo REPORTS_URL; ?>/documents/downloads.php'">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-download text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">รายงานการดาวน์โหลด</h3>
                    <p class="text-sm text-gray-600">สถิติการดาวน์โหลด</p>
                </div>
            </div>
            <p class="text-gray-600">รายงานสถิติการดาวน์โหลดเอกสาร</p>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
            สถิติเอกสารด่วน
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-blue-600">
                        <?php echo formatNumber($documentStats['total'] ?? 0); ?>
                    </span>
                </div>
                <h4 class="text-sm font-medium text-gray-900">เอกสารทั้งหมด</h4>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-green-600">
                        <?php echo formatNumber($documentStats['approved'] ?? 0); ?>
                    </span>
                </div>
                <h4 class="text-sm font-medium text-gray-900">อนุมัติแล้ว</h4>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-yellow-600">
                        <?php echo formatNumber($documentStats['pending'] ?? 0); ?>
                    </span>
                </div>
                <h4 class="text-sm font-medium text-gray-900">รออนุมัติ</h4>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                    <span class="text-2xl font-bold text-red-600">
                        <?php echo formatNumber($documentStats['rejected'] ?? 0); ?>
                    </span>
                </div>
                <h4 class="text-sm font-medium text-gray-900">ไม่อนุมัติ</h4>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>