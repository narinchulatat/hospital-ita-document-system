<?php
$pageTitle = 'รายงานและสถิติ';
require_once '../includes/header.php';
requireRole(ROLE_ADMIN);
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">รายงานและสถิติ</h1>
            <p class="text-gray-600">ข้อมูลสถิติและรายงานการใช้งานระบบ</p>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="users.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">รายงานผู้ใช้</h3>
                    <p class="text-sm text-gray-600">สถิติและข้อมูลผู้ใช้งาน</p>
                </div>
            </div>
        </a>

        <a href="documents.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-file-alt text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">รายงานเอกสาร</h3>
                    <p class="text-sm text-gray-600">สถิติการอัปโหลดและดาวน์โหลด</p>
                </div>
            </div>
        </a>

        <a href="activities.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">รายงานกิจกรรม</h3>
                    <p class="text-sm text-gray-600">ประวัติการใช้งานระบบ</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>