<?php
$pageTitle = 'จัดการหมวดหมู่';
$pageSubtitle = 'จัดการหมวดหมู่เอกสารและการจัดเรียง';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

try {
    $database = Database::getInstance();
    
    // Get categories with document count
    $categoriesQuery = "
        SELECT c.*, 
               COUNT(d.id) as document_count,
               parent.name as parent_name
        FROM categories c 
        LEFT JOIN documents d ON c.id = d.category_id 
        LEFT JOIN categories parent ON c.parent_id = parent.id
        GROUP BY c.id
        ORDER BY c.parent_id ASC, c.sort_order ASC, c.name ASC
    ";
    
    $categories = $database->fetchAll($categoriesQuery);
    
    // Get statistics
    $stats = [
        'total_categories' => count($categories),
        'root_categories' => count(array_filter($categories, function($cat) { return is_null($cat['parent_id']); })),
        'subcategories' => count(array_filter($categories, function($cat) { return !is_null($cat['parent_id']); }))
    ];
    
} catch (Exception $e) {
    error_log("Categories index error: " . $e->getMessage());
    $categories = [];
    $stats = ['total_categories' => 0, 'root_categories' => 0, 'subcategories' => 0];
}
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-folder text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">หมวดหมู่ทั้งหมด</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_categories']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-folder-open text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">หมวดหมู่หลัก</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['root_categories']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-sitemap text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">หมวดหมู่ย่อย</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['subcategories']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="flex flex-col sm:flex-row gap-4 mb-6">
    <a href="<?= BASE_URL ?>/admin/categories/create.php" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>
        เพิ่มหมวดหมู่ใหม่
    </a>
    
    <a href="<?= BASE_URL ?>/admin/categories/tree.php" 
       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
        <i class="fas fa-sitemap mr-2"></i>
        ดูแบบต้นไม้
    </a>
</div>

<!-- Categories Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        หมวดหมู่
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        หมวดหมู่หลัก
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        จำนวนเอกสาร
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ลำดับ
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        สร้างเมื่อ
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        การดำเนินการ
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-folder-open text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg">ไม่มีหมวดหมู่</p>
                        <p class="text-sm">
                            <a href="<?= BASE_URL ?>/admin/categories/create.php" class="text-blue-600 hover:text-blue-800">เพิ่มหมวดหมู่ใหม่</a>
                        </p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($categories as $category): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="text-sm font-medium text-gray-900">
                                <?= str_repeat('└─ ', ($category['parent_id'] ? 1 : 0)) ?>
                                <?= htmlspecialchars($category['name']) ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '-' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?= number_format($category['document_count']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= $category['sort_order'] ?? 0 ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= formatThaiDate($category['created_at']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-3">
                            <a href="<?= BASE_URL ?>/admin/categories/view.php?id=<?= $category['id'] ?>" 
                               class="text-blue-600 hover:text-blue-900" 
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/categories/edit.php?id=<?= $category['id'] ?>" 
                               class="text-green-600 hover:text-green-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/categories/delete.php?id=<?= $category['id'] ?>" 
                               class="text-red-600 hover:text-red-900 btn-delete"
                               data-tooltip="ลบ"
                               data-title="ยืนยันการลบหมวดหมู่"
                               data-text="คุณแน่ใจหรือไม่ที่จะลบหมวดหมู่ '<?= htmlspecialchars($category['name']) ?>'">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>