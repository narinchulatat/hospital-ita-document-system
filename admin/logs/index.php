<?php
$pageTitle = 'ประวัติการทำงาน';
require_once '../includes/header.php';
requireRole(ROLE_ADMIN);
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">ประวัติการทำงาน</h1>
            <p class="text-gray-600">บันทึกกิจกรรมและการใช้งานระบบ</p>
        </div>
    </div>

    <!-- Logs Navigation -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="activities.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-history text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">กิจกรรมผู้ใช้</h3>
                <p class="text-sm text-gray-600 mt-2">ประวัติการทำงานของผู้ใช้</p>
            </div>
        </a>

        <a href="errors.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">บันทึกข้อผิดพลาด</h3>
                <p class="text-sm text-gray-600 mt-2">รายการข้อผิดพลาดของระบบ</p>
            </div>
        </a>

        <a href="logins.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-sign-in-alt text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">ประวัติการเข้าสู่ระบบ</h3>
                <p class="text-sm text-gray-600 mt-2">บันทึกการเข้าสู่ระบบ</p>
            </div>
        </a>

        <a href="system.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-server text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">บันทึกระบบ</h3>
                <p class="text-sm text-gray-600 mt-2">กิจกรรมของระบบ</p>
            </div>
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>