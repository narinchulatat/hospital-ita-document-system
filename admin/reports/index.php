<?php
require_once '../includes/header.php';

$pageTitle = 'รายงานและสถิติ';
$pageSubtitle = 'ดูรายงานและสถิติการใช้งานระบบ';

// Check permission
if (!hasPermission('reports.view')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <a href="<?= BASE_URL ?>/admin/reports/users.php" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">รายงานผู้ใช้</h3>
                    <p class="text-sm text-gray-500">สถิติและกิจกรรมผู้ใช้</p>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/reports/documents.php" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">รายงานเอกสาร</h3>
                    <p class="text-sm text-gray-500">สถิติการใช้งานเอกสาร</p>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/reports/activities.php" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">รายงานกิจกรรม</h3>
                    <p class="text-sm text-gray-500">การใช้งานระบบทั่วไป</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <h3 class="text-lg font-medium text-gray-900 mb-6">ภาพรวมระบบ</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600 mb-2">1,234</div>
                <div class="text-sm text-gray-500">ผู้ใช้ทั้งหมด</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600 mb-2">5,678</div>
                <div class="text-sm text-gray-500">เอกสารทั้งหมด</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600 mb-2">12,345</div>
                <div class="text-sm text-gray-500">การดาวน์โหลด</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-orange-600 mb-2">89%</div>
                <div class="text-sm text-gray-500">อัตราการอนุมัติ</div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>