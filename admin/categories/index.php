<?php
require_once '../includes/header.php';

$pageTitle = 'จัดการหมวดหมู่';
$pageSubtitle = 'จัดการหมวดหมู่เอกสารและการจัดกลุ่ม';

// Check permission
if (!hasPermission('categories.view')) {
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
            <?php if (hasPermission('categories.create')): ?>
            <a href="<?= BASE_URL ?>/admin/categories/create.php" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                เพิ่มหมวดหมู่ใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="text-center py-12">
            <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">จัดการหมวดหมู่เอกสาร</h3>
            <p class="text-gray-500 mb-6">จัดระเบียบเอกสารด้วยระบบหมวดหมู่ที่มีโครงสร้างแบบต้นไม้</p>
            <div class="space-y-2 text-sm text-gray-600">
                <p>• สร้างและจัดการหมวดหมู่หลักและหมวดหมู่ย่อย</p>
                <p>• ลากและวางเพื่อจัดเรียงหมวดหมู่</p>
                <p>• กำหนดสิทธิ์การเข้าถึงตามหมวดหมู่</p>
                <p>• ดูรายงานการใช้งานแต่ละหมวดหมู่</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>