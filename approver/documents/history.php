<?php
$pageTitle = 'ประวัติการอนุมัติ';
require_once '../includes/header.php';

// Get filter parameters
$filterAction = $_GET['filter'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateRange = $_GET['date_range'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

try {
    $currentUserId = getCurrentUserId();
    $db = Database::getInstance();
    
    // Build query conditions
    $whereConditions = ['al.approver_id = ?'];
    $params = [$currentUserId];
    
    if ($filterAction === 'approved') {
        $whereConditions[] = "al.action = 'approve'";
    } elseif ($filterAction === 'rejected') {
        $whereConditions[] = "al.action = 'reject'";
    }
    
    if ($statusFilter) {
        $whereConditions[] = "d.status = ?";
        $params[] = $statusFilter;
    }
    
    if ($searchQuery) {
        $whereConditions[] = "(d.title LIKE ? OR d.description LIKE ? OR al.comments LIKE ?)";
        $searchTerm = "%{$searchQuery}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($dateRange) {
        switch ($dateRange) {
            case 'today':
                $whereConditions[] = "DATE(al.created_at) = CURDATE()";
                break;
            case 'week':
                $whereConditions[] = "al.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $whereConditions[] = "al.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'quarter':
                $whereConditions[] = "al.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
                break;
            case 'year':
                $whereConditions[] = "al.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as count 
                   FROM approval_logs al
                   JOIN documents d ON al.document_id = d.id
                   WHERE $whereClause";
    $totalRecords = $db->fetch($countQuery, $params)['count'] ?? 0;
    $totalPages = ceil($totalRecords / $perPage);
    
    // Get approval history with pagination
    $offset = ($page - 1) * $perPage;
    $query = "SELECT al.*, d.title, d.description, d.file_type, d.status as current_status,
                     c.name as category_name,
                     u.first_name as uploader_first_name, u.last_name as uploader_last_name
              FROM approval_logs al
              JOIN documents d ON al.document_id = d.id
              JOIN categories c ON d.category_id = c.id
              JOIN users u ON d.uploaded_by = u.id
              WHERE $whereClause
              ORDER BY al.created_at DESC
              LIMIT ? OFFSET ?";
    
    $params[] = $perPage;
    $params[] = $offset;
    
    $approvalHistory = $db->fetchAll($query, $params);
    
    // Get statistics for current user
    $stats = [
        'total_approvals' => $db->fetch("SELECT COUNT(*) as count FROM approval_logs WHERE approver_id = ? AND action = 'approve'", [$currentUserId])['count'] ?? 0,
        'total_rejections' => $db->fetch("SELECT COUNT(*) as count FROM approval_logs WHERE approver_id = ? AND action = 'reject'", [$currentUserId])['count'] ?? 0,
        'this_month' => $db->fetch("SELECT COUNT(*) as count FROM approval_logs WHERE approver_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)", [$currentUserId])['count'] ?? 0,
        'today' => $db->fetch("SELECT COUNT(*) as count FROM approval_logs WHERE approver_id = ? AND DATE(created_at) = CURDATE()", [$currentUserId])['count'] ?? 0
    ];
    
    $stats['total_actions'] = $stats['total_approvals'] + $stats['total_rejections'];
    $stats['approval_rate'] = $stats['total_actions'] > 0 ? round(($stats['total_approvals'] / $stats['total_actions']) * 100, 1) : 0;
    
} catch (Exception $e) {
    error_log("Approval history error: " . $e->getMessage());
    $approvalHistory = [];
    $totalRecords = 0;
    $totalPages = 0;
    $stats = array_fill_keys(['total_approvals', 'total_rejections', 'total_actions', 'this_month', 'today', 'approval_rate'], 0);
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-history mr-3"></i>ประวัติการอนุมัติ
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                ประวัติการอนุมัติและไม่อนุมัติเอกสารของคุณ (<?= number_format($totalRecords) ?> รายการ)
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/approver/documents/pending.php" class="btn-warning">
                <i class="fas fa-hourglass-half mr-2"></i>รออนุมัติ
            </a>
            <a href="<?= BASE_URL ?>/approver/reports/" class="btn-info">
                <i class="fas fa-chart-bar mr-2"></i>รายงาน
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-8">
        <div class="card stat-card approved">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-green-600"><?= number_format($stats['total_approvals']) ?></div>
                <div class="text-sm text-gray-500">อนุมัติทั้งหมด</div>
            </div>
        </div>
        
        <div class="card stat-card rejected">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-red-600"><?= number_format($stats['total_rejections']) ?></div>
                <div class="text-sm text-gray-500">ไม่อนุมัติทั้งหมด</div>
            </div>
        </div>
        
        <div class="card stat-card info">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-blue-600"><?= $stats['approval_rate'] ?>%</div>
                <div class="text-sm text-gray-500">อัตราอนุมัติ</div>
            </div>
        </div>
        
        <div class="card stat-card pending">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-yellow-600"><?= number_format($stats['this_month']) ?></div>
                <div class="text-sm text-gray-500">เดือนนี้</div>
            </div>
        </div>
        
        <div class="card stat-card info">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-purple-600"><?= number_format($stats['today']) ?></div>
                <div class="text-sm text-gray-500">วันนี้</div>
            </div>
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
                           placeholder="ชื่อเอกสาร, ความเห็น..."
                           class="form-input">
                </div>

                <!-- Action Filter -->
                <div>
                    <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">การดำเนินการ</label>
                    <select id="filter" name="filter" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="approved" <?= $filterAction === 'approved' ? 'selected' : '' ?>>อนุมัติ</option>
                        <option value="rejected" <?= $filterAction === 'rejected' ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะปัจจุบัน</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="<?= DOC_STATUS_APPROVED ?>" <?= $statusFilter === DOC_STATUS_APPROVED ? 'selected' : '' ?>>อนุมัติแล้ว</option>
                        <option value="<?= DOC_STATUS_REJECTED ?>" <?= $statusFilter === DOC_STATUS_REJECTED ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">ช่วงเวลา</label>
                    <select id="date_range" name="date_range" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="today" <?= $dateRange === 'today' ? 'selected' : '' ?>>วันนี้</option>
                        <option value="week" <?= $dateRange === 'week' ? 'selected' : '' ?>>สัปดาห์นี้</option>
                        <option value="month" <?= $dateRange === 'month' ? 'selected' : '' ?>>เดือนนี้</option>
                        <option value="quarter" <?= $dateRange === 'quarter' ? 'selected' : '' ?>>ไตรมาสนี้</option>
                        <option value="year" <?= $dateRange === 'year' ? 'selected' : '' ?>>ปีนี้</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search mr-2"></i>ค้นหา
                    </button>
                    <a href="<?= BASE_URL ?>/approver/documents/history.php" class="btn-secondary">
                        <i class="fas fa-times mr-2"></i>ล้าง
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Approval History List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (!empty($approvalHistory)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>เอกสาร</th>
                            <th class="text-center">การดำเนินการ</th>
                            <th class="text-center">สถานะปัจจุบัน</th>
                            <th class="text-center">ผู้อัปโหลด</th>
                            <th class="text-center">วันที่ดำเนินการ</th>
                            <th class="text-center">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvalHistory as $record): ?>
                        <tr>
                            <td>
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="file-icon <?= getFileTypeClass($record['file_type']) ?>">
                                            <i class="<?= getFileTypeIcon($record['file_type']) ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">
                                            <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $record['document_id'] ?>" 
                                               class="hover:text-blue-600">
                                                <?= htmlspecialchars($record['title']) ?>
                                            </a>
                                        </h4>
                                        <p class="text-xs text-gray-500 mb-2">
                                            หมวดหมู่: <?= htmlspecialchars($record['category_name']) ?>
                                        </p>
                                        <?php if ($record['comments']): ?>
                                        <div class="mt-2">
                                            <div class="bg-gray-50 rounded-md p-2">
                                                <div class="text-xs font-medium text-gray-700 mb-1">ความเห็น:</div>
                                                <p class="text-xs text-gray-600">
                                                    <?= htmlspecialchars(truncateText($record['comments'], 150)) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($record['action'] === 'approve'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>อนุมัติ
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>ไม่อนุมัติ
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $statusClass = match($record['current_status']) {
                                    DOC_STATUS_APPROVED => 'status-approved',
                                    DOC_STATUS_REJECTED => 'status-rejected',
                                    default => 'status-draft'
                                };
                                ?>
                                <span class="<?= $statusClass ?>">
                                    <?= $DOC_STATUS_NAMES[$record['current_status']] ?? $record['current_status'] ?>
                                </span>
                            </td>
                            <td class="text-center text-sm">
                                <?= htmlspecialchars($record['uploader_first_name'] . ' ' . $record['uploader_last_name']) ?>
                            </td>
                            <td class="text-center text-sm">
                                <div><?= formatThaiDate($record['created_at']) ?></div>
                                <div class="text-xs text-gray-500">
                                    <?= date('H:i น.', strtotime($record['created_at'])) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center space-x-1">
                                    <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $record['document_id'] ?>" 
                                       class="btn-sm btn-info" title="ดูเอกสาร">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($record['comments']): ?>
                                    <button onclick="showCommentModal('<?= addslashes($record['comments']) ?>', '<?= addslashes($record['title']) ?>')" 
                                            class="btn-sm btn-secondary" title="ดูความเห็นเต็ม">
                                        <i class="fas fa-comment"></i>
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
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        แสดง <?= number_format(($page - 1) * $perPage + 1) ?> ถึง <?= number_format(min($page * $perPage, $totalRecords)) ?> 
                        จาก <?= number_format($totalRecords) ?> รายการ
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
                <i class="fas fa-history text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่พบประวัติการอนุมัติ</h3>
                <p class="text-gray-500">
                    <?php if ($searchQuery || $filterAction || $statusFilter || $dateRange): ?>
                    ไม่พบประวัติที่ตรงกับเงื่อนไขการค้นหา
                    <?php else: ?>
                    คุณยังไม่มีประวัติการอนุมัติเอกสาร
                    <?php endif; ?>
                </p>
                <?php if ($searchQuery || $filterAction || $statusFilter || $dateRange): ?>
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/approver/documents/history.php" class="btn-primary">
                        <i class="fas fa-times mr-2"></i>ล้างตัวกรอง
                    </a>
                </div>
                <?php else: ?>
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/approver/documents/pending.php" class="btn-primary">
                        <i class="fas fa-hourglass-half mr-2"></i>ไปอนุมัติเอกสาร
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Comment Modal -->
<div id="commentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="commentModalTitle">ความเห็นเต็ม</h3>
                <button onclick="closeCommentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p id="commentModalContent" class="text-gray-700 whitespace-pre-wrap"></p>
            </div>
            <div class="mt-6 text-right">
                <button onclick="closeCommentModal()" class="btn-secondary">
                    ปิด
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#filter, #status, #date_range').change(function() {
        $(this).closest('form').submit();
    });
});

function showCommentModal(comment, documentTitle) {
    $('#commentModalTitle').text('ความเห็นสำหรับ: ' + documentTitle);
    $('#commentModalContent').text(comment);
    $('#commentModal').removeClass('hidden');
}

function closeCommentModal() {
    $('#commentModal').addClass('hidden');
}

// Close modal on outside click
$('#commentModal').click(function(e) {
    if (e.target === this) {
        closeCommentModal();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>