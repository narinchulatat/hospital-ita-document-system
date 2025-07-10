<?php
$pageTitle = 'ศูนย์การแจ้งเตือน';
$pageSubtitle = 'จัดการการแจ้งเตือนและประกาศข่าวสาร';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);
?>

<div class="bg-white rounded-lg shadow p-6">
    <div class="text-center py-12">
        <i class="fas fa-bell text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">ศูนย์การแจ้งเตือน</h3>
        <p class="text-gray-500 mb-6">โมดูลนี้จะรวมถึงการส่งการแจ้งเตือน การประกาศ และการแจ้งข่าวสาร</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
            <div class="border border-gray-200 rounded-lg p-4">
                <i class="fas fa-list text-blue-500 text-2xl mb-2"></i>
                <h4 class="font-medium">รายการแจ้งเตือน</h4>
                <p class="text-sm text-gray-500">ดูการแจ้งเตือนทั้งหมด</p>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <i class="fas fa-plus text-green-500 text-2xl mb-2"></i>
                <h4 class="font-medium">ส่งการแจ้งเตือน</h4>
                <p class="text-sm text-gray-500">สร้างการแจ้งเตือนใหม่</p>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <i class="fas fa-broadcast-tower text-purple-500 text-2xl mb-2"></i>
                <h4 class="font-medium">ส่งประกาศ</h4>
                <p class="text-sm text-gray-500">ส่งประกาศให้ทุกคน</p>
            </div>
        </div>
        
        <div class="mt-8">
            <span class="text-sm text-gray-400">โมดูลนี้อยู่ระหว่างการพัฒนา</span>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>