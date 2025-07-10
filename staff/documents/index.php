<?php
/**
 * Staff Documents Management Page
 * Display and manage documents uploaded by staff
 */

$pageTitle = 'จัดการเอกสาร';
require_once '../../includes/header.php';

// Require staff role
requireRole(ROLE_STAFF);

try {
    $document = new Document();
    $category = new Category();
    $currentUserId = getCurrentUserId();
    
    // Get filters from URL
    $search = sanitizeInput($_GET['search'] ?? '');
    $categoryFilter = sanitizeInput($_GET['category'] ?? '');
    $statusFilter = sanitizeInput($_GET['status'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 15;
    
    // Build filters for the current user's documents only
    $filters = ['uploaded_by' => $currentUserId];
    
    if (!empty($search)) {
        $filters['search'] = $search;
    }
    
    if (!empty($categoryFilter)) {
        $filters['category_id'] = $categoryFilter;
    }
    
    if (!empty($statusFilter)) {
        $filters['status'] = $statusFilter;
    }
    
    // Get documents and total count
    $documents = $document->getAll($filters, $page, $perPage);
    $totalCount = $document->getTotalCount($filters);
    $totalPages = ceil($totalCount / $perPage);
    
    // Get categories for filter dropdown
    $categories = $category->getAll(['status' => 'active']);
    
    // Get statistics
    $stats = [
        'total' => $document->getTotalCount(['uploaded_by' => $currentUserId]),
        'draft' => $document->getTotalCount(['uploaded_by' => $currentUserId, 'status' => DOC_STATUS_DRAFT]),
        'pending' => $document->getTotalCount(['uploaded_by' => $currentUserId, 'status' => DOC_STATUS_PENDING]),
        'approved' => $document->getTotalCount(['uploaded_by' => $currentUserId, 'status' => DOC_STATUS_APPROVED]),
        'rejected' => $document->getTotalCount(['uploaded_by' => $currentUserId, 'status' => DOC_STATUS_REJECTED])
    ];
    
} catch (Exception $e) {
    error_log("Staff documents error: " . $e->getMessage());
    $documents = [];
    $totalCount = 0;
    $totalPages = 0;
    $categories = [];
    $stats = array_fill_keys(['total', 'draft', 'pending', 'approved', 'rejected'], 0);
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-file-alt mr-3"></i>จัดการเอกสาร
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                จัดการเอกสารทั้งหมดของคุณ
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/staff/documents/upload.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-upload mr-2"></i>อัปโหลดเอกสารใหม่
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-alt text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ทั้งหมด</dt>
                            <dd class="text-lg font-semibold text-gray-900"><?= number_format($stats['total']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-edit text-2xl text-gray-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ร่าง</dt>
                            <dd class="text-lg font-semibold text-gray-900"><?= number_format($stats['draft']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">รออนุมัติ</dt>
                            <dd class="text-lg font-semibold text-gray-900"><?= number_format($stats['pending']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">อนุมัติแล้ว</dt>
                            <dd class="text-lg font-semibold text-gray-900"><?= number_format($stats['approved']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-2xl text-red-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ไม่อนุมัติ</dt>
                            <dd class="text-lg font-semibold text-gray-900"><?= number_format($stats['rejected']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="ชื่อเอกสาร, รายละเอียด..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                    <select name="category" 
                            id="category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">ทุกหมวดหมู่</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                    <select name="status" 
                            id="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">ทุกสถานะ</option>
                        <?php 
                        global $DOC_STATUS_NAMES;
                        foreach ($DOC_STATUS_NAMES as $statusKey => $statusName): ?>
                        <option value="<?= $statusKey ?>" <?= $statusFilter == $statusKey ? 'selected' : '' ?>>
                            <?= $statusName ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-search mr-1"></i>ค้นหา
                    </button>
                    <a href="<?= BASE_URL ?>/staff/documents/" 
                       class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-times mr-1"></i>ล้าง
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>รายการเอกสาร
                <?php if ($totalCount > 0): ?>
                <span class="text-sm text-gray-500 font-normal">
                    (<?= number_format($totalCount) ?> รายการ)
                </span>
                <?php endif; ?>
            </h3>
        </div>

        <?php if (!empty($documents)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            เอกสาร
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            หมวดหมู่
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            สถานะ
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            วันที่อัปโหลด
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            การจัดการ
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($documents as $doc): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <i class="<?= getFileTypeIcon($doc['file_type']) ?>"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="<?= BASE_URL ?>/staff/documents/view.php?id=<?= $doc['id'] ?>" 
                                           class="hover:text-blue-600">
                                            <?= htmlspecialchars($doc['title']) ?>
                                        </a>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars(substr($doc['description'], 0, 100)) ?>
                                        <?= strlen($doc['description']) > 100 ? '...' : '' ?>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        <i class="fas fa-file mr-1"></i><?= strtoupper($doc['file_type']) ?>
                                        <i class="fas fa-weight ml-2 mr-1"></i><?= formatFileSize($doc['file_size']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <?= htmlspecialchars($doc['category_name']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusClasses = [
                                DOC_STATUS_DRAFT => 'bg-gray-100 text-gray-800',
                                DOC_STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
                                DOC_STATUS_APPROVED => 'bg-green-100 text-green-800',
                                DOC_STATUS_REJECTED => 'bg-red-100 text-red-800'
                            ];
                            global $DOC_STATUS_NAMES;
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClasses[$doc['status']] ?>">
                                <?= $DOC_STATUS_NAMES[$doc['status']] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= formatThaiDate($doc['created_at']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="<?= BASE_URL ?>/staff/documents/view.php?id=<?= $doc['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-900" title="ดูรายละเอียด">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($doc['status'] == DOC_STATUS_DRAFT || $doc['status'] == DOC_STATUS_REJECTED): ?>
                                <a href="<?= BASE_URL ?>/staff/documents/edit.php?id=<?= $doc['id'] ?>" 
                                   class="text-yellow-600 hover:text-yellow-900" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($doc['status'] == DOC_STATUS_DRAFT): ?>
                                <button onclick="confirmDelete(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['title']) ?>')" 
                                        class="text-red-600 hover:text-red-900" title="ลบ">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    ก่อนหน้า
                </a>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                   class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    ถัดไป
                </a>
                <?php endif; ?>
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        แสดง <span class="font-medium"><?= number_format(($page - 1) * $perPage + 1) ?></span> ถึง 
                        <span class="font-medium"><?= number_format(min($page * $perPage, $totalCount)) ?></span> จาก 
                        <span class="font-medium"><?= number_format($totalCount) ?></span> รายการ
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 <?= $i == $page ? 'bg-blue-50 border-blue-500 text-blue-600' : '' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่พบเอกสาร</h3>
            <?php if (!empty($search) || !empty($categoryFilter) || !empty($statusFilter)): ?>
            <p class="text-gray-500 mb-4">
                ลองปรับเปลี่ยนเงื่อนไขการค้นหาหรือ
                <a href="<?= BASE_URL ?>/staff/documents/" class="text-blue-600 hover:text-blue-500">ล้างตัวกรอง</a>
            </p>
            <?php else: ?>
            <p class="text-gray-500 mb-4">
                คุณยังไม่มีเอกสารในระบบ เริ่มต้นด้วยการอัปโหลดเอกสารแรก
            </p>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/staff/documents/upload.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-upload mr-2"></i>อัปโหลดเอกสารใหม่
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900">ยืนยันการลบเอกสาร</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    คุณแน่ใจหรือไม่ที่จะลบเอกสาร "<span id="deleteDocTitle"></span>"?
                    การกระทำนี้ไม่สามารถย้อนกลับได้
                </p>
            </div>
            <div class="flex justify-center space-x-4 px-4 py-3">
                <button id="confirmDelete" 
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    ลบเอกสาร
                </button>
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteDocumentId = null;

function confirmDelete(id, title) {
    deleteDocumentId = id;
    document.getElementById('deleteDocTitle').textContent = title;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    deleteDocumentId = null;
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteDocumentId) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/staff/documents/delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteDocumentId;
        form.appendChild(idInput);
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = '<?= generateCSRFToken() ?>';
        form.appendChild(tokenInput);
        
        document.body.appendChild(form);
        form.submit();
    }
});

// Close modal on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>