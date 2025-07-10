<?php
require_once '../includes/header.php';

$pageTitle = 'จัดการเอกสาร';
$pageSubtitle = 'รายการเอกสารทั้งหมดในระบบ';

// Check permission
if (!hasPermission('documents.view')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

// Initialize database and classes
$db = new Database();

// Get filters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';

// Build query
$where_conditions = ['1=1'];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(d.title LIKE :search OR d.description LIKE :search OR d.document_number LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category_filter)) {
    $where_conditions[] = "d.category_id = :category";
    $params[':category'] = $category_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "d.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($type_filter)) {
    $where_conditions[] = "d.document_type = :type";
    $params[':type'] = $type_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM documents d 
              LEFT JOIN categories c ON d.category_id = c.id 
              WHERE $where_clause";
$count_stmt = $db->query($count_sql, $params);
$total_documents = $count_stmt->fetch()['total'];

// Pagination
$page = (int)($_GET['page'] ?? 1);
$per_page = 25;
$total_pages = ceil($total_documents / $per_page);
$offset = ($page - 1) * $per_page;

// Get documents
$sql = "SELECT d.*, c.name as category_name, 
               creator.first_name as creator_first_name, creator.last_name as creator_last_name,
               approver.first_name as approver_first_name, approver.last_name as approver_last_name
        FROM documents d 
        LEFT JOIN categories c ON d.category_id = c.id 
        LEFT JOIN users creator ON d.created_by = creator.id
        LEFT JOIN users approver ON d.approved_by = approver.id
        WHERE $where_clause
        ORDER BY d.created_at DESC 
        LIMIT :limit OFFSET :offset";

$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$stmt = $db->query($sql, $params);
$documents = $stmt->fetchAll();

// Get categories for filter
$categories_sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories_stmt = $db->query($categories_sql);
$categories = $categories_stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
            <?php if (hasPermission('documents.create')): ?>
            <a href="<?= BASE_URL ?>/admin/documents/create.php" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                เพิ่มเอกสารใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="ชื่อเอกสาร, เลขที่เอกสาร, คำอธิบาย"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                <select id="category" 
                        name="category" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select id="status" 
                        name="status" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">ทั้งหมด</option>
                    <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>ร่าง</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                    <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>อนุมัติแล้ว</option>
                    <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                    <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>เก็บถาวร</option>
                </select>
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">ประเภท</label>
                <select id="type" 
                        name="type" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">ทั้งหมด</option>
                    <option value="internal" <?= $type_filter === 'internal' ? 'selected' : '' ?>>ภายใน</option>
                    <option value="external" <?= $type_filter === 'external' ? 'selected' : '' ?>>ภายนอก</option>
                    <option value="confidential" <?= $type_filter === 'confidential' ? 'selected' : '' ?>>ความลับ</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" 
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-search mr-2"></i>ค้นหา
                </button>
                <a href="<?= BASE_URL ?>/admin/documents/" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <?php
        // Get quick stats
        $draft_count = $db->query("SELECT COUNT(*) as count FROM documents WHERE status = 'draft'")->fetch()['count'];
        $pending_count = $db->query("SELECT COUNT(*) as count FROM documents WHERE status = 'pending'")->fetch()['count'];
        $approved_count = $db->query("SELECT COUNT(*) as count FROM documents WHERE status = 'approved'")->fetch()['count'];
        $rejected_count = $db->query("SELECT COUNT(*) as count FROM documents WHERE status = 'rejected'")->fetch()['count'];
        $archived_count = $db->query("SELECT COUNT(*) as count FROM documents WHERE status = 'archived'")->fetch()['count'];
        ?>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">เอกสารทั้งหมด</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($total_documents) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-edit text-gray-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ร่าง</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($draft_count) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">รอดำเนินการ</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($pending_count) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">อนุมัติแล้ว</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($approved_count) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ไม่อนุมัติ</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($rejected_count) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">รายการเอกสาร</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เอกสาร</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมวดหมู่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้สร้าง</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การกระทำ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($documents as $doc): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-file-alt text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </div>
                                    <?php if ($doc['document_number']): ?>
                                    <div class="text-sm text-gray-500">
                                        เลขที่: <?= htmlspecialchars($doc['document_number']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <?= htmlspecialchars($doc['category_name'] ?? 'ไม่ระบุ') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $status_classes = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'archived' => 'bg-purple-100 text-purple-800'
                            ];
                            $status_labels = [
                                'draft' => 'ร่าง',
                                'pending' => 'รอดำเนินการ',
                                'approved' => 'อนุมัติแล้ว',
                                'rejected' => 'ไม่อนุมัติ',
                                'archived' => 'เก็บถาวร'
                            ];
                            $status_class = $status_classes[$doc['status']] ?? 'bg-gray-100 text-gray-800';
                            $status_label = $status_labels[$doc['status']] ?? $doc['status'];
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $status_class ?>">
                                <?= $status_label ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars(($doc['creator_first_name'] ?? '') . ' ' . ($doc['creator_last_name'] ?? '')) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= formatThaiDate($doc['created_at']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <?php if (hasPermission('documents.view')): ?>
                            <a href="<?= BASE_URL ?>/admin/documents/view.php?id=<?= $doc['id'] ?>" 
                               class="text-blue-600 hover:text-blue-900"
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('documents.edit')): ?>
                            <a href="<?= BASE_URL ?>/admin/documents/edit.php?id=<?= $doc['id'] ?>" 
                               class="text-indigo-600 hover:text-indigo-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('documents.approve') && $doc['status'] === 'pending'): ?>
                            <a href="<?= BASE_URL ?>/admin/documents/approve.php?id=<?= $doc['id'] ?>" 
                               class="text-green-600 hover:text-green-900"
                               data-tooltip="อนุมัติ">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('documents.delete')): ?>
                            <a href="<?= BASE_URL ?>/admin/documents/delete.php?id=<?= $doc['id'] ?>" 
                               class="text-red-600 hover:text-red-900 btn-delete"
                               data-title="ยืนยันการลบเอกสาร"
                               data-text="คุณแน่ใจหรือไม่ที่จะลบเอกสาร <?= htmlspecialchars($doc['title']) ?>?"
                               data-tooltip="ลบ">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($documents)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-file-alt text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium mb-1">ไม่พบเอกสาร</p>
                            <p class="text-sm">ลองเปลี่ยนเงื่อนไขการค้นหาหรือเพิ่มเอกสารใหม่</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (same as users module) -->
        <?php if ($total_pages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <!-- Pagination code here (same as users/index.php) -->
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-tooltip]').each(function() {
        const $this = $(this);
        const title = $this.data('tooltip');
        
        $this.on('mouseenter', function() {
            const tooltip = $('<div class="absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg whitespace-nowrap">')
                .text(title)
                .appendTo('body');
            
            const offset = $this.offset();
            tooltip.css({
                top: offset.top - tooltip.outerHeight() - 5,
                left: offset.left + ($this.outerWidth() - tooltip.outerWidth()) / 2
            });
        }).on('mouseleave', function() {
            $('.absolute.z-50').remove();
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>