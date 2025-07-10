<?php
$pageTitle = 'จัดการเอกสาร';
$pageSubtitle = 'รายการเอกสารทั้งหมดในระบบ';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

try {
    $database = Database::getInstance();
    
    // Get filter parameters
    $status = $_GET['status'] ?? '';
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if ($status) {
        $conditions[] = "d.status = ?";
        $params[] = $status;
    }
    
    if ($category) {
        $conditions[] = "d.category_id = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $conditions[] = "(d.title LIKE ? OR d.description LIKE ? OR d.filename LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    }
    
    // Get documents with pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 25;
    $offset = ($page - 1) * $limit;
    
    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    
    $documentsQuery = "
        SELECT d.*, c.name as category_name, c.color as category_color,
               u.first_name as uploader_first_name, u.last_name as uploader_last_name,
               a.first_name as approver_first_name, a.last_name as approver_last_name
        FROM documents d 
        LEFT JOIN categories c ON d.category_id = c.id 
        LEFT JOIN users u ON d.uploaded_by = u.id
        LEFT JOIN users a ON d.approved_by = a.id
        {$whereClause}
        ORDER BY d.created_at DESC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $documents = $database->fetchAll($documentsQuery, $params);
    
    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM documents d 
        LEFT JOIN categories c ON d.category_id = c.id 
        {$whereClause}
    ";
    $totalCount = $database->fetch($countQuery, $params)['total'];
    $totalPages = ceil($totalCount / $limit);
    
    // Get categories for filter
    $categories = $database->fetchAll("SELECT * FROM categories ORDER BY name");
    
    // Get statistics
    $stats = [
        'total' => $database->fetch("SELECT COUNT(*) as count FROM documents")['count'],
        'approved' => $database->fetch("SELECT COUNT(*) as count FROM documents WHERE status = 'approved'")['count'],
        'pending' => $database->fetch("SELECT COUNT(*) as count FROM documents WHERE status = 'pending'")['count'],
        'rejected' => $database->fetch("SELECT COUNT(*) as count FROM documents WHERE status = 'rejected'")['count'],
        'total_size' => $database->fetch("SELECT SUM(file_size) as size FROM documents")['size'] ?? 0,
        'total_downloads' => $database->fetch("SELECT SUM(download_count) as downloads FROM documents")['downloads'] ?? 0
    ];
    
} catch (Exception $e) {
    error_log("Documents index error: " . $e->getMessage());
    $documents = [];
    $totalPages = 0;
    $categories = [];
    $stats = ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0, 'total_size' => 0, 'total_downloads' => 0];
}
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-file-alt text-blue-600 text-lg"></i>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">ทั้งหมด</p>
                <p class="text-lg font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">อนุมัติแล้ว</p>
                <p class="text-lg font-bold text-gray-900"><?= number_format($stats['approved']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
                <i class="fas fa-clock text-yellow-600 text-lg"></i>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">รออนุมัติ</p>
                <p class="text-lg font-bold text-gray-900"><?= number_format($stats['pending']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
                <i class="fas fa-times-circle text-red-600 text-lg"></i>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">ไม่อนุมัติ</p>
                <p class="text-lg font-bold text-gray-900"><?= number_format($stats['rejected']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-hdd text-purple-600 text-lg"></i>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">ขนาดรวม</p>
                <p class="text-sm font-bold text-gray-900"><?= formatFileSize($stats['total_size']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 bg-indigo-100 rounded-lg">
                <i class="fas fa-download text-indigo-600 text-lg"></i>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">ดาวน์โหลด</p>
                <p class="text-lg font-bold text-gray-900"><?= number_format($stats['total_downloads']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col sm:flex-row gap-4 mb-4 lg:mb-0">
                <!-- Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <form method="GET" class="inline">
                        <input type="text" 
                               name="search" 
                               value="<?= htmlspecialchars($search) ?>"
                               placeholder="ค้นหาเอกสาร..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                        <?php if ($status): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>"><?php endif; ?>
                        <?php if ($category): ?><input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>"><?php endif; ?>
                    </form>
                </div>
                
                <!-- Status Filter -->
                <select name="status" class="table-filter border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">สถานะทั้งหมด</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>อนุมัติแล้ว</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>รออนุมัติ</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>ฉบับร่าง</option>
                </select>
                
                <!-- Category Filter -->
                <select name="category" class="table-filter border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">หมวดหมู่ทั้งหมด</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Reset Filters -->
                <a href="<?= BASE_URL ?>/admin/documents/" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-undo mr-2"></i>
                    รีเซ็ต
                </a>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="<?= BASE_URL ?>/admin/documents/create.php" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>
                    อัปโหลดเอกสารใหม่
                </a>
                
                <div class="flex gap-2">
                    <button type="button" class="export-excel inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-file-excel mr-2"></i>
                        Excel
                    </button>
                    <button type="button" class="export-pdf inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-file-pdf mr-2"></i>
                        PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Documents Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="documentsTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" class="select-all rounded border-gray-300">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        เอกสาร
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        หมวดหมู่
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ขนาดไฟล์
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        สถานะ
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        อัปโหลดโดย
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        วันที่อัปโหลด
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        การดำเนินการ
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($documents)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-file-alt text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg">ไม่พบเอกสาร</p>
                        <p class="text-sm">ลองปรับเปลี่ยนเงื่อนไขการค้นหาหรือ<a href="<?= BASE_URL ?>/admin/documents/create.php" class="text-blue-600 hover:text-blue-800">อัปโหลดเอกสารใหม่</a></p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($documents as $document): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" class="select-row rounded border-gray-300" value="<?= $document['id'] ?>">
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-file-<?= getFileIcon($document['file_type']) ?> text-gray-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                                    <?= htmlspecialchars($document['title']) ?>
                                </div>
                                <div class="text-sm text-gray-500 max-w-xs truncate">
                                    <?= htmlspecialchars($document['filename']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($document['category_name']): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <?= htmlspecialchars($document['category_name']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-400 text-sm">ไม่มีหมวดหมู่</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= formatFileSize($document['file_size']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php
                        $statusClasses = [
                            'approved' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'draft' => 'bg-gray-100 text-gray-800'
                        ];
                        $statusTexts = [
                            'approved' => 'อนุมัติแล้ว',
                            'pending' => 'รออนุมัติ',
                            'rejected' => 'ไม่อนุมัติ',
                            'draft' => 'ฉบับร่าง'
                        ];
                        $statusClass = $statusClasses[$document['status']] ?? 'bg-gray-100 text-gray-800';
                        $statusText = $statusTexts[$document['status']] ?? $document['status'];
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                            <?= $statusText ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php if ($document['uploader_first_name']): ?>
                            <?= htmlspecialchars($document['uploader_first_name'] . ' ' . $document['uploader_last_name']) ?>
                        <?php else: ?>
                            <span class="text-gray-400">ไม่ทราบ</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= formatThaiDate($document['created_at']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-3">
                            <a href="<?= BASE_URL ?>/admin/documents/view.php?id=<?= $document['id'] ?>" 
                               class="text-blue-600 hover:text-blue-900" 
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/public/documents/download.php?id=<?= $document['id'] ?>" 
                               class="text-green-600 hover:text-green-900"
                               data-tooltip="ดาวน์โหลด">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php if ($document['status'] === 'pending'): ?>
                            <a href="<?= BASE_URL ?>/admin/documents/approve.php?id=<?= $document['id'] ?>" 
                               class="text-yellow-600 hover:text-yellow-900"
                               data-tooltip="อนุมัติ">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/admin/documents/edit.php?id=<?= $document['id'] ?>" 
                               class="text-purple-600 hover:text-purple-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/documents/delete.php?id=<?= $document['id'] ?>" 
                               class="text-red-600 hover:text-red-900 btn-delete"
                               data-tooltip="ลบ"
                               data-title="ยืนยันการลบเอกสาร"
                               data-text="คุณแน่ใจหรือไม่ที่จะลบเอกสาร '<?= htmlspecialchars($document['title']) ?>'">
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

<!-- Bulk Actions -->
<div class="bulk-actions hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <div class="flex items-center justify-between">
        <div class="text-sm text-blue-800">
            เลือกแล้ว <span class="selected-count font-semibold">0</span> รายการ
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" class="bulk-approve px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-check mr-2"></i>
                อนุมัติที่เลือก
            </button>
            <button type="button" class="bulk-delete px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>
                ลบที่เลือก
            </button>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="mt-6 flex items-center justify-between">
    <div class="text-sm text-gray-700">
        แสดงผลลัพธ์ <?= number_format(($page - 1) * $limit + 1) ?> ถึง <?= number_format(min($page * $limit, $totalCount)) ?> 
        จากทั้งหมด <?= number_format($totalCount) ?> รายการ
    </div>
    
    <nav class="flex items-center space-x-2">
        <!-- Previous Page -->
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= $status ? '&status=' . $status : '' ?><?= $category ? '&category=' . $category : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            ก่อนหน้า
        </a>
        <?php endif; ?>
        
        <!-- Page Numbers -->
        <?php 
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        
        for ($i = $startPage; $i <= $endPage; $i++): 
        ?>
        <a href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?><?= $category ? '&category=' . $category : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
           class="px-3 py-2 text-sm font-medium <?= $i === $page ? 'text-blue-600 bg-blue-50 border-blue-500' : 'text-gray-500 bg-white border-gray-300' ?> border rounded-lg hover:bg-gray-50">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <!-- Next Page -->
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?><?= $status ? '&status=' . $status : '' ?><?= $category ? '&category=' . $category : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            ถัดไป
        </a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<script>
// Initialize table filters
document.addEventListener('DOMContentLoaded', function() {
    // Status filter change
    document.querySelector('select[name="status"]').addEventListener('change', function() {
        updateFilters();
    });
    
    // Category filter change
    document.querySelector('select[name="category"]').addEventListener('change', function() {
        updateFilters();
    });
    
    // Search form submit
    document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            updateFilters();
        }
    });
});

function updateFilters() {
    const params = new URLSearchParams();
    
    const search = document.querySelector('input[name="search"]').value;
    const status = document.querySelector('select[name="status"]').value;
    const category = document.querySelector('select[name="category"]').value;
    
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    if (category) params.set('category', category);
    
    window.location.href = '?' + params.toString();
}

function getFileIcon(fileType) {
    const icons = {
        'pdf': 'pdf',
        'doc': 'word',
        'docx': 'word',
        'xls': 'excel',
        'xlsx': 'excel',
        'ppt': 'powerpoint',
        'pptx': 'powerpoint',
        'jpg': 'image',
        'jpeg': 'image',
        'png': 'image',
        'gif': 'image'
    };
    return icons[fileType] || 'alt';
}
</script>

<?php
function getFileIcon($fileType) {
    $icons = [
        'pdf' => 'pdf',
        'doc' => 'word',
        'docx' => 'word', 
        'xls' => 'excel',
        'xlsx' => 'excel',
        'ppt' => 'powerpoint',
        'pptx' => 'powerpoint',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image'
    ];
    return $icons[$fileType] ?? 'alt';
}
?>

<?php require_once '../includes/footer.php'; ?>