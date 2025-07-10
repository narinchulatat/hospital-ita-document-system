<?php
$pageTitle = 'รายงานและสถิติ';
$pageSubtitle = 'ดูรายงานและข้อมูลสถิติของระบบ';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Reports Navigation -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">รายงาน</h3>
            </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="<?= BASE_URL ?>/admin/reports/users.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-users mr-3"></i>
                            รายงานผู้ใช้
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/reports/documents.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-alt mr-3"></i>
                            รายงานเอกสาร
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/reports/activities.php" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-history mr-3"></i>
                            รายงานกิจกรรม
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
                <i class="fas fa-chart-bar text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">รายงานและสถิติ</h3>
                <p class="text-gray-500 mb-6">เลือกประเภทรายงานที่ต้องการจากเมนูด้านซ้าย</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-users text-blue-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">รายงานผู้ใช้</h4>
                        <p class="text-sm text-gray-500">สถิติผู้ใช้และการใช้งาน</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-file-alt text-green-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">รายงานเอกสาร</h4>
                        <p class="text-sm text-gray-500">สถิติเอกสารและดาวน์โหลด</p>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <i class="fas fa-history text-purple-500 text-2xl mb-2"></i>
                        <h4 class="font-medium">รายงานกิจกรรม</h4>
                        <p class="text-sm text-gray-500">บันทึกกิจกรรมและการใช้งาน</p>
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