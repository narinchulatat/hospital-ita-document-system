<?php
require_once '../includes/header.php';

$pageTitle = 'ศูนย์การแจ้งเตือน';
$pageSubtitle = 'จัดการการแจ้งเตือนและประกาศ';

// Check permission
if (!hasPermission('notifications.view')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
            <?php if (hasPermission('notifications.create')): ?>
            <a href="<?= BASE_URL ?>/admin/notifications/create.php" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                ส่งการแจ้งเตือนใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="text-center py-12">
            <i class="fas fa-bell text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">ศูนย์การแจ้งเตือน</h3>
            <p class="text-gray-500 mb-6">จัดการการแจ้งเตือนและประกาศให้กับผู้ใช้ในระบบ</p>
            <div class="space-y-2 text-sm text-gray-600">
                <p>• ส่งการแจ้งเตือนแบบเรียลไทม์</p>
                <p>• สร้างประกาศสำหรับกลุ่มผู้ใช้</p>
                <p>• กำหนดเวลาส่งการแจ้งเตือน</p>
                <p>• ติดตามสถานะการอ่านข้อความ</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>