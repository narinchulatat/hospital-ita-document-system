<?php
$pageTitle = 'ศูนย์การแจ้งเตือน';
require_once '../includes/header.php';
requireRole(ROLE_ADMIN);
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">ศูนย์การแจ้งเตือน</h1>
            <p class="text-gray-600">จัดการการแจ้งเตือนและประกาศ</p>
        </div>
        <div class="flex space-x-3">
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>สร้างการแจ้งเตือน
            </a>
            <a href="broadcast.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-bullhorn mr-2"></i>ส่งประกาศ
            </a>
        </div>
    </div>

    <!-- Notification Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-bell text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">การแจ้งเตือนทั้งหมด</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">ส่งแล้ว</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">รอส่ง</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-eye text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">อ่านแล้ว</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Notice -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex">
            <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
            <div class="text-blue-800">
                <h3 class="font-medium">โมดูลการแจ้งเตือน</h3>
                <p class="text-sm mt-1">
                    โมดูลนี้อยู่ในขั้นตอนการพัฒนา จะประกอบด้วยฟีเจอร์:
                </p>
                <ul class="text-sm mt-2 space-y-1 list-disc list-inside">
                    <li>ส่งการแจ้งเตือนแบบ Real-time</li>
                    <li>การแจ้งเตือนผ่านอีเมล</li>
                    <li>ระบบประกาศทั่วไป</li>
                    <li>การจัดกลุ่มผู้รับ</li>
                    <li>ตั้งเวลาส่งการแจ้งเตือน</li>
                    <li>ติดตามสถานะการอ่าน</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">รายการการแจ้งเตือน</h3>
        </div>
        <div class="p-6">
            <div class="text-center py-12">
                <i class="fas fa-bell text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">ยังไม่มีการแจ้งเตือน</p>
                <p class="text-gray-400 text-sm mt-2">โมดูลการแจ้งเตือนอยู่ในขั้นตอนการพัฒนา</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>