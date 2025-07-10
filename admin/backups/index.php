<?php
$pageTitle = 'จัดการการสำรองข้อมูล';
require_once '../includes/header.php';
requireRole(ROLE_ADMIN);
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">จัดการการสำรองข้อมูล</h1>
            <p class="text-gray-600">สำรองและกู้คืนข้อมูลระบบ</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="createBackup()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>สร้างสำรองข้อมูล
            </button>
        </div>
    </div>

    <!-- Backup Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-database text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">สำรองฐานข้อมูล</h3>
                <p class="text-sm text-gray-600 mt-2 mb-4">สำรองข้อมูลฐานข้อมูลทั้งหมด</p>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                    สำรองข้อมูล
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-file-archive text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">สำรองไฟล์</h3>
                <p class="text-sm text-gray-600 mt-2 mb-4">สำรองไฟล์เอกสารทั้งหมด</p>
                <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                    สำรองไฟล์
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-archive text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">สำรองข้อมูลเต็ม</h3>
                <p class="text-sm text-gray-600 mt-2 mb-4">สำรองข้อมูลและไฟล์ทั้งหมด</p>
                <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">
                    สำรองทั้งหมด
                </button>
            </div>
        </div>
    </div>

    <!-- Backup History -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">ประวัติการสำรองข้อมูล</h3>
        </div>
        <div class="p-6">
            <div class="text-center py-12">
                <i class="fas fa-database text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">ยังไม่มีการสำรองข้อมูล</p>
                <p class="text-gray-400 text-sm mt-2">โมดูลสำรองข้อมูลอยู่ในขั้นตอนการพัฒนา</p>
            </div>
        </div>
    </div>
</div>

<script>
function createBackup() {
    showAlert('ฟีเจอร์สำรองข้อมูลอยู่ในขั้นตอนการพัฒนา', 'info');
}
</script>

<?php require_once '../includes/footer.php'; ?>