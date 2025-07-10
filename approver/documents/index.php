<?php
$pageTitle = 'รายการเอกสารทั้งหมด';
require_once '../includes/header.php';

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

try {
    $document = new Document();
    
    // Build filter conditions
    $filters = [];
    if ($statusFilter) {
        $filters['status'] = $statusFilter;
    }
    if ($categoryFilter) {
        $filters['category_id'] = $categoryFilter;
    }
    if ($searchQuery) {
        $filters['search'] = $searchQuery;
    }
    if ($dateFilter) {
        $filters['date'] = $dateFilter;
    }
    
    // Get documents with pagination
    $documents = $document->getAll($filters, $page, $perPage);
    $totalDocuments = $document->getTotalCount($filters);
    $totalPages = ceil($totalDocuments / $perPage);
    
    // Get categories for filter dropdown
    $categoryModel = new Category();
    $categories = $categoryModel->getAll();
    
} catch (Exception $e) {
    error_log("Document listing error: " . $e->getMessage());
    $documents = [];
    $totalDocuments = 0;
    $totalPages = 0;
    $categories = [];
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-file-alt mr-3"></i>รายการเอกสารทั้งหมด
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                จัดการและดูรายการเอกสารในระบบ (<?= number_format($totalDocuments) ?> รายการ)
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/approver/documents/pending.php" class="btn-warning">
                <i class="fas fa-hourglass-half mr-2"></i>รออนุมัติ
            </a>
            <a href="<?= BASE_URL ?>/approver/documents/history.php" class="btn-info">
                <i class="fas fa-history mr-2"></i>ประวัติ
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="<?= DOC_STATUS_DRAFT ?>" <?= $statusFilter === DOC_STATUS_DRAFT ? 'selected' : '' ?>>ร่าง</option>
                        <option value="<?= DOC_STATUS_PENDING ?>" <?= $statusFilter === DOC_STATUS_PENDING ? 'selected' : '' ?>>รออนุมัติ</option>
                        <option value="<?= DOC_STATUS_APPROVED ?>" <?= $statusFilter === DOC_STATUS_APPROVED ? 'selected' : '' ?>>อนุมัติแล้ว</option>
                        <option value="<?= DOC_STATUS_REJECTED ?>" <?= $statusFilter === DOC_STATUS_REJECTED ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                        <option value="<?= DOC_STATUS_ARCHIVED ?>" <?= $statusFilter === DOC_STATUS_ARCHIVED ? 'selected' : '' ?>>เก็บถาวร</option>
                    </select>
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

                <!-- Date Filter -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">ช่วงเวลา</label>
                    <select id="date" name="date" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="today" <?= $dateFilter === 'today' ? 'selected' : '' ?>>วันนี้</option>
                        <option value="week" <?= $dateFilter === 'week' ? 'selected' : '' ?>>สัปดาห์นี้</option>
                        <option value="month" <?= $dateFilter === 'month' ? 'selected' : '' ?>>เดือนนี้</option>
                        <option value="quarter" <?= $dateFilter === 'quarter' ? 'selected' : '' ?>>ไตรมาสนี้</option>
                        <option value="year" <?= $dateFilter === 'year' ? 'selected' : '' ?>>ปีนี้</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search mr-2"></i>ค้นหา
                    </button>
                    <a href="<?= BASE_URL ?>/approver/documents/" class="btn-secondary">
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
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">ผู้อัปโหลด</th>
                            <th class="text-center">วันที่อัปโหลด</th>
                            <th class="text-center">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                        <tr data-document-id="<?= $doc['id'] ?>">
                            <td>
                                <?php if ($doc['status'] === DOC_STATUS_PENDING): ?>
                                <input type="checkbox" class="select-item form-checkbox">
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="file-icon <?= getFileTypeClass($doc['file_type']) ?>">
                                            <i class="<?= getFileTypeIcon($doc['file_type']) ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">
                                            <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $doc['id'] ?>" 
                                               class="hover:text-blue-600">
                                                <?= htmlspecialchars($doc['title']) ?>
                                            </a>
                                        </h4>
                                        <?php if ($doc['description']): ?>
                                        <p class="text-xs text-gray-500 mb-2">
                                            <?= htmlspecialchars(substr($doc['description'], 0, 100)) ?>
                                            <?= strlen($doc['description']) > 100 ? '...' : '' ?>
                                        </p>
                                        <?php endif; ?>
                                        <div class="flex items-center space-x-4 text-xs text-gray-400">
                                            <span><i class="fas fa-file mr-1"></i><?= strtoupper($doc['file_type']) ?></span>
                                            <span><i class="fas fa-weight mr-1"></i><?= formatFileSize($doc['file_size']) ?></span>
                                            <span><i class="fas fa-download mr-1"></i><?= number_format($doc['download_count']) ?> ครั้ง</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary"><?= htmlspecialchars($doc['category_name']) ?></span>
                            </td>
                            <td class="text-center">
                                <?php 
                                $statusClass = match($doc['status']) {
                                    DOC_STATUS_PENDING => 'status-pending',
                                    DOC_STATUS_APPROVED => 'status-approved',
                                    DOC_STATUS_REJECTED => 'status-rejected',
                                    default => 'status-draft'
                                };
                                ?>
                                <span class="<?= $statusClass ?>">
                                    <?= $DOC_STATUS_NAMES[$doc['status']] ?? $doc['status'] ?>
                                </span>
                            </td>
                            <td class="text-center text-sm">
                                <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?>
                            </td>
                            <td class="text-center text-sm">
                                <?= formatThaiDate($doc['created_at']) ?>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center space-x-1">
                                    <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="btn-sm btn-info" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($doc['status'] === DOC_STATUS_PENDING): ?>
                                    <button onclick="approverPanel.showApprovalModal(<?= $doc['id'] ?>, 'approve')" 
                                            class="btn-sm btn-success" title="อนุมัติ">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="approverPanel.showApprovalModal(<?= $doc['id'] ?>, 'reject')" 
                                            class="btn-sm btn-danger" title="ไม่อนุมัติ">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($doc['status'], [DOC_STATUS_APPROVED, DOC_STATUS_REJECTED])): ?>
                                    <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $doc['id'] ?>#approval-history" 
                                       class="btn-sm btn-secondary" title="ดูประวัติการอนุมัติ">
                                        <i class="fas fa-history"></i>
                                    </a>
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
                <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่พบเอกสาร</h3>
                <p class="text-gray-500">
                    <?php if ($searchQuery || $statusFilter || $categoryFilter): ?>
                    ไม่พบเอกสารที่ตรงกับเงื่อนไขการค้นหา ลองเปลี่ยนตัวกรองหรือคำค้นหา
                    <?php else: ?>
                    ยังไม่มีเอกสารในระบบ
                    <?php endif; ?>
                </p>
                <?php if ($searchQuery || $statusFilter || $categoryFilter): ?>
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/approver/documents/" class="btn-primary">
                        <i class="fas fa-times mr-2"></i>ล้างตัวกรอง
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
    $('#status, #category, #date').change(function() {
        $(this).closest('form').submit();
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>