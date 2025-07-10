<?php
$pageTitle = 'บันทึกกิจกรรมระบบ';
$pageSubtitle = 'ดูประวัติการทำงานและบันทึกการใช้งาน';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Logs Navigation -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">บันทึกระบบ</h3>
            </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="<?= BASE_URL ?>/admin/logs/activities.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-history mr-3"></i>
                            กิจกรรมผู้ใช้
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/logs/errors.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            บันทึกข้อผิดพลาด
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/logs/logins.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-sign-in-alt mr-3"></i>
                            ประวัติการเข้าสู่ระบบ
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
                <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">บันทึกกิจกรรมระบบ</h3>
                <p class="text-gray-500 mb-6">เลือกประเภทบันทึกที่ต้องการดูจากเมนูด้านซ้าย</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-history text-blue-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">กิจกรรมผู้ใช้</h4>
                        <p class="text-sm text-gray-500">บันทึกการกระทำของผู้ใช้</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">บันทึกข้อผิดพลาด</h4>
                        <p class="text-sm text-gray-500">บันทึกข้อผิดพลาดของระบบ</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-sign-in-alt text-green-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">ประวัติการเข้าสู่ระบบ</h4>
                        <p class="text-sm text-gray-500">บันทึกการเข้า-ออกระบบ</p>
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