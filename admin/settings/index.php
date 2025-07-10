<?php
$pageTitle = 'ตั้งค่าระบบ';
require_once '../includes/header.php';
requireRole(ROLE_ADMIN);
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">ตั้งค่าระบบ</h1>
            <p class="text-gray-600">กำหนดค่าการทำงานของระบบ</p>
        </div>
    </div>

    <!-- Settings Navigation -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="general.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-cog text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">ตั้งค่าทั่วไป</h3>
                    <p class="text-sm text-gray-600">ข้อมูลเว็บไซต์และการตั้งค่าพื้นฐาน</p>
                </div>
            </div>
        </a>

        <a href="upload.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-upload text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">ตั้งค่าการอัปโหลด</h3>
                    <p class="text-sm text-gray-600">กำหนดขนาดและประเภทไฟล์</p>
                </div>
            </div>
        </a>

        <a href="security.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">ตั้งค่าความปลอดภัย</h3>
                    <p class="text-sm text-gray-600">การรักษาความปลอดภัยระบบ</p>
                </div>
            </div>
        </a>

        <a href="backup.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-database text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">ตั้งค่าสำรองข้อมูล</h3>
                    <p class="text-sm text-gray-600">การสำรองและกู้คืนข้อมูล</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>