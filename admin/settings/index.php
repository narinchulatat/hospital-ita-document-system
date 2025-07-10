<?php
$pageTitle = 'ตั้งค่าระบบ';
$pageSubtitle = 'จัดการการตั้งค่าทั่วไปของระบบ';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Settings Navigation -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">ตั้งค่าระบบ</h3>
            </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="<?= BASE_URL ?>/admin/settings/general.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-cog mr-3"></i>
                            ตั้งค่าทั่วไป
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/settings/upload.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-upload mr-3"></i>
                            ตั้งค่าการอัปโหลด
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/settings/security.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-shield-alt mr-3"></i>
                            ตั้งค่าความปลอดภัย
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/settings/backup.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-database mr-3"></i>
                            ตั้งค่าสำรองข้อมูล
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="lg:col-span-3">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center py-12">
                <i class="fas fa-cogs text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">ตั้งค่าระบบ</h3>
                <p class="text-gray-500 mb-6">เลือกหมวดการตั้งค่าที่ต้องการจากเมนูด้านซ้าย</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-cog text-blue-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">ตั้งค่าทั่วไป</h4>
                        <p class="text-sm text-gray-500">ชื่อเว็บไซต์, โลโก้, ติดต่อ</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-upload text-green-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">ตั้งค่าการอัปโหลด</h4>
                        <p class="text-sm text-gray-500">ขนาดไฟล์, ประเภทไฟล์</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-shield-alt text-yellow-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">ตั้งค่าความปลอดภัย</h4>
                        <p class="text-sm text-gray-500">รหัสผ่าน, การเข้าถึง</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-database text-purple-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">ตั้งค่าสำรองข้อมูล</h4>
                        <p class="text-sm text-gray-500">กำหนดการสำรองอัตโนมัติ</p>
                    </div>
                </div>
                
                <div class="mt-8">
                    <span class="text-sm text-gray-400">โมดูลนี้อยู่ระหว่างการพัฒนา</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>