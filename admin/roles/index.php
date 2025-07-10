<?php
require_once '../includes/header.php';

$pageTitle = 'จัดการบทบาทและสิทธิ์';
$pageSubtitle = 'กำหนดบทบาทผู้ใช้และสิทธิ์การเข้าถึง';

// Check permission
if (!hasPermission('roles.view')) {
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
            <?php if (hasPermission('roles.create')): ?>
            <a href="<?= BASE_URL ?>/admin/roles/create.php" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                เพิ่มบทบาทใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="text-center py-12">
            <i class="fas fa-user-shield text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">จัดการบทบาทและสิทธิ์</h3>
            <p class="text-gray-500 mb-6">ควบคุมการเข้าถึงระบบด้วยระบบบทบาทและสิทธิ์ที่ยืดหยุ่น</p>
            <div class="space-y-2 text-sm text-gray-600">
                <p>• สร้างและแก้ไขบทบาทผู้ใช้</p>
                <p>• กำหนดสิทธิ์การเข้าถึงแต่ละฟีเจอร์</p>
                <p>• มอบหมายบทบาทให้กับผู้ใช้</p>
                <p>• ดูเมทริกซ์สิทธิ์แบบละเอียด</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>