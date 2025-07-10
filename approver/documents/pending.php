<?php
$pageTitle = 'เอกสารรออนุมัติ';
require_once '../includes/header.php';

// Get filter parameters
$urgentOnly = isset($_GET['urgent']) && $_GET['urgent'] == '1';
$categoryFilter = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

try {
    $document = new Document();
    
    // Build filter conditions for pending documents
    $filters = ['status' => DOC_STATUS_PENDING];
    
    if ($urgentOnly) {
        $filters['urgent'] = true;
    }
    if ($categoryFilter) {
        $filters['category_id'] = $categoryFilter;
    }
    if ($searchQuery) {
        $filters['search'] = $searchQuery;
    }
    
    // Get pending documents
    $documents = $document->getPendingDocuments($page, $perPage, $filters);
    $totalDocuments = $document->getTotalCount($filters);
    $totalPages = ceil($totalDocuments / $perPage);
    
    // Get categories for filter
    $categoryModel = new Category();
    $categories = $categoryModel->getAll();
    
    // Get statistics
    $stats = [
        'total_pending' => $document->getTotalCount(['status' => DOC_STATUS_PENDING]),
        'urgent_pending' => $document->getTotalCount(['status' => DOC_STATUS_PENDING, 'urgent' => true]),
        'overdue_pending' => $document->getTotalCount(['status' => DOC_STATUS_PENDING, 'overdue' => true])
    ];
    
} catch (Exception $e) {
    error_log("Pending documents error: " . $e->getMessage());
    $documents = [];
    $totalDocuments = 0;
    $totalPages = 0;
    $categories = [];
    $stats = ['total_pending' => 0, 'urgent_pending' => 0, 'overdue_pending' => 0];
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-hourglass-half mr-3 text-yellow-600"></i>เอกสารรออนุมัติ
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                เอกสารที่รอการอนุมัติจากคุณ (<?= number_format($totalDocuments) ?> รายการ)
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/approver/documents/" class="btn-secondary">
                <i class="fas fa-list mr-2"></i>ทั้งหมด
            </a>
            <a href="<?= BASE_URL ?>/approver/approval/bulk.php" class="btn-primary">
                <i class="fas fa-check-double mr-2"></i>อนุมัติหลายรายการ
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <!-- Total Pending -->
        <div class="card stat-card pending">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hourglass-half text-lg text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">รออนุมัติทั้งหมด</div>
                        <div class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_pending']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Urgent Pending -->
        <div class="card stat-card rejected">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-lg text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">เอกสารด่วน</div>
                        <div class="text-2xl font-bold text-gray-900"><?= number_format($stats['urgent_pending']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue -->
        <div class="card stat-card info">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-lg text-orange-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">เกินกำหนด</div>
                        <div class="text-2xl font-bold text-gray-900"><?= number_format($stats['overdue_pending']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?= htmlspecialchars($searchQuery) ?>"
                           placeholder="ชื่อเอกสาร, คำอธิบาย..."
                           class="form-input">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Urgent Filter -->
                <div>
                    <label for="urgent" class="block text-sm font-medium text-gray-700 mb-1">ประเภท</label>
                    <select id="urgent" name="urgent" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="1" <?= $urgentOnly ? 'selected' : '' ?>>เอกสารด่วนเท่านั้น</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search mr-2"></i>ค้นหา
                    </button>
                    <a href="<?= BASE_URL ?>/approver/documents/pending.php" class="btn-secondary">
                        <i class="fas fa-times mr-2"></i>ล้าง
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div id="bulkActions" class="bulk-actions hidden mb-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-square text-blue-600 mr-2"></i>
                    <span class="text-sm font-medium text-blue-900">
                        เลือกแล้ว <span class="selected-count">0</span> รายการ
                    </span>
                </div>
                <div class="flex space-x-2">
                    <button class="bulk-approve btn-success btn-sm">
                        <i class="fas fa-check mr-1"></i>อนุมัติทั้งหมด
                    </button>
                    <button class="bulk-reject btn-danger btn-sm">
                        <i class="fas fa-times mr-1"></i>ไม่อนุมัติทั้งหมด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (!empty($documents)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-8">
                                <input type="checkbox" class="select-all form-checkbox">
                            </th>
                            <th>เอกสาร</th>
                            <th class="text-center">หมวดหมู่</th>
                            <th class="text-center">ผู้อัปโหลด</th>
                            <th class="text-center">วันที่อัปโหลด</th>
                            <th class="text-center">ระยะเวลารอ</th>
                            <th class="text-center">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                        <?php
                        $daysPending = floor((time() - strtotime($doc['created_at'])) / 86400);
                        $isUrgent = $doc['is_urgent'] ?? false;
                        $isOverdue = $daysPending > 7; // Consider overdue after 7 days
                        ?>
                        <tr data-document-id="<?= $doc['id'] ?>" class="<?= $isUrgent ? 'bg-red-50' : ($isOverdue ? 'bg-orange-50' : '') ?>">
                            <td>
                                <input type="checkbox" class="select-item form-checkbox">
                            </td>
                            <td>
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="file-icon <?= getFileTypeClass($doc['file_type']) ?>">
                                            <i class="<?= getFileTypeIcon($doc['file_type']) ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-sm font-medium text-gray-900">
                                                <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $doc['id'] ?>" 
                                                   class="hover:text-blue-600">
                                                    <?= htmlspecialchars($doc['title']) ?>
                                                </a>
                                            </h4>
                                            <?php if ($isUrgent): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>ด่วน
                                            </span>
                                            <?php endif; ?>
                                            <?php if ($isOverdue): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-clock mr-1"></i>เกินกำหนด
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($doc['description']): ?>
                                        <p class="text-xs text-gray-500 mb-2">
                                            <?= htmlspecialchars(substr($doc['description'], 0, 120)) ?>
                                            <?= strlen($doc['description']) > 120 ? '...' : '' ?>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <div class="flex items-center space-x-4 text-xs text-gray-400">
                                            <span><i class="fas fa-file mr-1"></i><?= strtoupper($doc['file_type']) ?></span>
                                            <span><i class="fas fa-weight mr-1"></i><?= formatFileSize($doc['file_size']) ?></span>
                                            <span><i class="fas fa-eye mr-1"></i><?= number_format($doc['view_count']) ?> ครั้ง</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary"><?= htmlspecialchars($doc['category_name']) ?></span>
                            </td>
                            <td class="text-center text-sm">
                                <div>
                                    <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= htmlspecialchars($doc['uploader_email']) ?>
                                </div>
                            </td>
                            <td class="text-center text-sm">
                                <div><?= formatThaiDate($doc['created_at']) ?></div>
                                <div class="text-xs text-gray-500">
                                    <?= formatThaiDate($doc['created_at'], true) ?>
                                </div>
                            </td>
                            <td class="text-center text-sm">
                                <div class="<?= $isOverdue ? 'text-red-600 font-medium' : ($daysPending > 3 ? 'text-orange-600' : 'text-gray-900') ?>">
                                    <?= $daysPending ?> วัน
                                </div>
                                <?php if ($daysPending > 0): ?>
                                <div class="text-xs text-gray-500">
                                    <?= $daysPending === 1 ? 'เมื่อวาน' : $daysPending . ' วันที่แล้ว' ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center space-x-1">
                                    <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="btn-sm btn-info" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <button onclick="approverPanel.showApprovalModal(<?= $doc['id'] ?>, 'approve')" 
                                            class="btn-sm btn-success" title="อนุมัติ">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    
                                    <button onclick="approverPanel.showApprovalModal(<?= $doc['id'] ?>, 'reject')" 
                                            class="btn-sm btn-danger" title="ไม่อนุมัติ">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    
                                    <a href="<?= BASE_URL ?>/approver/documents/approve.php?id=<?= $doc['id'] ?>" 
                                       class="btn-sm btn-primary" title="หน้าอนุมัติแบบเต็ม">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        แสดง <?= number_format(($page - 1) * $perPage + 1) ?> ถึง <?= number_format(min($page * $perPage, $totalDocuments)) ?> 
                        จาก <?= number_format($totalDocuments) ?> รายการ
                    </div>
                    
                    <div class="flex space-x-1">
                        <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                           class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                           class="px-3 py-2 text-sm border rounded-md <?= $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-300 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                           class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-check-circle text-6xl text-green-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่มีเอกสารรออนุมัติ</h3>
                <p class="text-gray-500">
                    <?php if ($searchQuery || $categoryFilter || $urgentOnly): ?>
                    ไม่พบเอกสารรออนุมัติที่ตรงกับเงื่อนไขการค้นหา
                    <?php else: ?>
                    ยอดเยียม! ไม่มีเอกสารที่รอการอนุมัติจากคุณ
                    <?php endif; ?>
                </p>
                <?php if ($searchQuery || $categoryFilter || $urgentOnly): ?>
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/approver/documents/pending.php" class="btn-primary">
                        <i class="fas fa-times mr-2"></i>ล้างตัวกรอง
                    </a>
                </div>
                <?php else: ?>
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/approver/documents/" class="btn-secondary">
                        <i class="fas fa-list mr-2"></i>ดูเอกสารทั้งหมด
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize approver panel
    window.approverPanel = window.approverPanel || new ApproverPanel();
    
    // Auto-submit form on filter change
    $('#category, #urgent').change(function() {
        $(this).closest('form').submit();
    });
    
    // Highlight urgent and overdue documents
    $('tr[data-document-id]').each(function() {
        const row = $(this);
        if (row.hasClass('bg-red-50')) {
            row.find('td:first').prepend('<i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>');
        } else if (row.hasClass('bg-orange-50')) {
            row.find('td:first').prepend('<i class="fas fa-clock text-orange-500 mr-2"></i>');
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>