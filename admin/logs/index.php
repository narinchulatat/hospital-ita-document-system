<?php
require_once '../includes/header.php';

$pageTitle = 'บันทึกการทำงาน';
$pageSubtitle = 'ตรวจสอบบันทึกกิจกรรมและการทำงานของระบบ';

// Check permission
if (!hasPermission('logs.view')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$active_tab = $_GET['tab'] ?? 'activities';
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

    <!-- Log Navigation Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <a href="?tab=activities" 
                   class="<?= $active_tab === 'activities' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-history mr-2"></i>
                    กิจกรรมผู้ใช้
                </a>
                <a href="?tab=errors" 
                   class="<?= $active_tab === 'errors' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ข้อผิดพลาด
                </a>
                <a href="?tab=logins" 
                   class="<?= $active_tab === 'logins' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    การเข้าสู่ระบบ
                </a>
                <a href="?tab=system" 
                   class="<?= $active_tab === 'system' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-server mr-2"></i>
                    ระบบ
                </a>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <?php if ($active_tab === 'activities'): ?>
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">บันทึกกิจกรรมผู้ใช้</h3>
                <p class="text-sm text-gray-500">กิจกรรมการใช้งานของผู้ใช้ในระบบ</p>
            </div>
            
            <!-- Activity Log Content Here -->
            <div class="text-center py-12">
                <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                <h4 class="text-lg font-medium text-gray-900">บันทึกกิจกรรมผู้ใช้</h4>
                <p class="text-gray-500">แสดงรายการกิจกรรมทั้งหมดของผู้ใช้ในระบบ</p>
            </div>
        </div>
        
        <?php elseif ($active_tab === 'errors'): ?>
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">บันทึกข้อผิดพลาด</h3>
                <p class="text-sm text-gray-500">ข้อผิดพลาดที่เกิดขึ้นในระบบ</p>
            </div>
            
            <div class="text-center py-12">
                <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                <h4 class="text-lg font-medium text-gray-900">บันทึกข้อผิดพลาด</h4>
                <p class="text-gray-500">ตรวจสอบและแก้ไขข้อผิดพลาดในระบบ</p>
            </div>
        </div>
        
        <?php elseif ($active_tab === 'logins'): ?>
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">บันทึกการเข้าสู่ระบบ</h3>
                <p class="text-sm text-gray-500">ประวัติการเข้าสู่ระบบของผู้ใช้</p>
            </div>
            
            <div class="text-center py-12">
                <i class="fas fa-sign-in-alt text-4xl text-green-300 mb-4"></i>
                <h4 class="text-lg font-medium text-gray-900">บันทึกการเข้าสู่ระบบ</h4>
                <p class="text-gray-500">ติดตามการเข้าใช้งานระบบของผู้ใช้</p>
            </div>
        </div>
        
        <?php else: ?>
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">บันทึกระบบ</h3>
                <p class="text-sm text-gray-500">บันทึกการทำงานของระบบ</p>
            </div>
            
            <div class="text-center py-12">
                <i class="fas fa-server text-4xl text-purple-300 mb-4"></i>
                <h4 class="text-lg font-medium text-gray-900">บันทึกระบบ</h4>
                <p class="text-gray-500">ตรวจสอบสถานะและประสิทธิภาพระบบ</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>