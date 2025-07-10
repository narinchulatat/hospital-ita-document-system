<?php
$pageTitle = 'จัดการเอกสาร';
$pageSubtitle = 'รายการเอกสารทั้งหมดในระบบ';

require_once '../includes/header.php';
require_once '../../classes/Document.php';
require_once '../../classes/Category.php';
require_once '../../classes/User.php';

// Check permission
requirePermission(PERM_DOCUMENT_VIEW);

try {
    $document = new Document();
    $category = new Category();
    $user = new User();
    
    // Get filters
    $search = $_GET['search'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $statusFilter = $_GET['status'] ?? '';
    $uploaderFilter = $_GET['uploader'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // Build filters
    $filters = [];
    if ($search) {
        $filters['search'] = $search;
    }
    if ($categoryFilter) {
        $filters['category'] = $categoryFilter;
    }
    if ($statusFilter) {
        $filters['status'] = $statusFilter;
    }
    if ($uploaderFilter) {
        $filters['uploader'] = $uploaderFilter;
    }
    
    // Get documents
    $documents = $document->getAll($filters, $page, $limit);
    $totalDocuments = $document->getTotalCount($filters);
    $totalPages = ceil($totalDocuments / $limit);
    
    // Get categories for filter
    $categories = $category->getAll(false);
    
    // Get users for filter
    $uploaders = $user->getUploaders();
    
    // Get document statistics
    $stats = [
        'total' => $document->getTotalCount(),
        'pending' => $document->getTotalCount(['status' => DOC_STATUS_PENDING]),
        'approved' => $document->getTotalCount(['status' => DOC_STATUS_APPROVED]),
        'rejected' => $document->getTotalCount(['status' => DOC_STATUS_REJECTED])
    ];
    
} catch (Exception $e) {
    error_log("Document management error: " . $e->getMessage());
    setFlashMessage('error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
    $documents = [];
    $totalDocuments = 0;
    $totalPages = 0;
    $categories = [];
    $uploaders = [];
    $stats = array_fill_keys(['total', 'pending', 'approved', 'rejected'], 0);
}
?>

<!-- Document Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['total']) ?></div>
                    <div class="stats-label">เอกสารทั้งหมด</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card warning">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['pending']) ?></div>
                    <div class="stats-label">รออนุมัติ</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card success">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['approved']) ?></div>
                    <div class="stats-label">อนุมัติแล้ว</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card danger">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-times"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['rejected']) ?></div>
                    <div class="stats-label">ไม่อนุมัติ</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page Actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="text-muted">พบเอกสารทั้งหมด <?= number_format($totalDocuments) ?> รายการ</span>
    </div>
    <div class="btn-group">
        <?php if (hasMenuPermission(PERM_DOCUMENT_CREATE)): ?>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>เพิ่มเอกสาร
        </a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission(PERM_DOCUMENT_APPROVE) && $stats['pending'] > 0): ?>
        <a href="approve.php" class="btn btn-warning">
            <i class="fas fa-clock me-2"></i>อนุมัติเอกสาร (<?= number_format($stats['pending']) ?>)
        </a>
        <?php endif; ?>
        
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#bulkActionsModal" id="bulk-actions-btn" style="display: none;">
            <i class="fas fa-tasks me-2"></i>การจัดการแบบกลุ่ม
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">ค้นหา</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="ชื่อเอกสาร, คำอธิบาย">
            </div>
            
            <div class="col-md-2">
                <label for="category" class="form-label">หมวดหมู่</label>
                <select class="form-select" id="category" name="category">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                        <?= str_repeat('&nbsp;&nbsp;', $cat['level'] - 1) ?>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">สถานะ</label>
                <select class="form-select" id="status" name="status">
                    <option value="">ทั้งหมด</option>
                    <option value="<?= DOC_STATUS_DRAFT ?>" <?= $statusFilter === DOC_STATUS_DRAFT ? 'selected' : '' ?>>แบบร่าง</option>
                    <option value="<?= DOC_STATUS_PENDING ?>" <?= $statusFilter === DOC_STATUS_PENDING ? 'selected' : '' ?>>รออนุมัติ</option>
                    <option value="<?= DOC_STATUS_APPROVED ?>" <?= $statusFilter === DOC_STATUS_APPROVED ? 'selected' : '' ?>>อนุมัติแล้ว</option>
                    <option value="<?= DOC_STATUS_REJECTED ?>" <?= $statusFilter === DOC_STATUS_REJECTED ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                    <option value="<?= DOC_STATUS_ARCHIVED ?>" <?= $statusFilter === DOC_STATUS_ARCHIVED ? 'selected' : '' ?>>เก็บถาวร</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="uploader" class="form-label">ผู้อัปโหลด</label>
                <select class="form-select" id="uploader" name="uploader">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($uploaders as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $uploaderFilter == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>ค้นหา
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Documents Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">รายการเอกสาร</h5>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="select_all">
                <label class="form-check-label" for="select_all">เลือกทั้งหมด</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($documents)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="select_all_header" class="form-check-input">
                        </th>
                        <th>เอกสาร</th>
                        <th>หมวดหมู่</th>
                        <th>ผู้อัปโหลด</th>
                        <th>สถานะ</th>
                        <th>ดาวน์โหลด</th>
                        <th>วันที่อัปโหลด</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input document-checkbox" 
                                   value="<?= $doc['id'] ?>">
                        </td>
                        <td>
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="fas <?= getFileIcon($doc['file_name']) ?> fa-2x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="view.php?id=<?= $doc['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($doc['title']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted d-block">
                                        <?= htmlspecialchars($doc['file_name']) ?>
                                        (<?= formatFileSize($doc['file_size']) ?>)
                                    </small>
                                    <?php if ($doc['description']): ?>
                                    <small class="text-muted text-truncate-2 d-block mt-1">
                                        <?= htmlspecialchars($doc['description']) ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($doc['category_name']): ?>
                            <span class="badge bg-light text-dark">
                                <?= htmlspecialchars($doc['category_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">ไม่ระบุ</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($doc['uploader_profile_image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($doc['uploader_profile_image']) ?>" 
                                     class="rounded-circle me-2" width="30" height="30" alt="Profile">
                                <?php else: ?>
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 30px; height: 30px;">
                                    <i class="fas fa-user text-white small"></i>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-medium small">
                                        <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?>
                                    </div>
                                    <small class="text-muted">@<?= htmlspecialchars($doc['uploader_username']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?= getStatusBadge($doc['status'], 'document') ?>
                        </td>
                        <td>
                            <span class="fw-medium"><?= number_format($doc['download_count']) ?></span>
                            <small class="text-muted d-block">ครั้ง</small>
                        </td>
                        <td>
                            <div class="small">
                                <?= formatThaiDate($doc['created_at']) ?>
                                <div class="text-muted">
                                    <?= formatThaiDate($doc['created_at'], true) ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="view.php?id=<?= $doc['id'] ?>" 
                                   class="btn btn-sm btn-outline-info" 
                                   data-bs-toggle="tooltip" title="ดูรายละเอียด">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if (hasMenuPermission(PERM_DOCUMENT_EDIT)): ?>
                                <a href="edit.php?id=<?= $doc['id'] ?>" 
                                   class="btn btn-sm btn-outline-warning" 
                                   data-bs-toggle="tooltip" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (hasMenuPermission(PERM_DOCUMENT_APPROVE) && $doc['status'] === DOC_STATUS_PENDING): ?>
                                <a href="approve.php?id=<?= $doc['id'] ?>" 
                                   class="btn btn-sm btn-outline-success" 
                                   data-bs-toggle="tooltip" title="อนุมัติ">
                                    <i class="fas fa-check"></i>
                                </a>
                                <?php endif; ?>
                                
                                <a href="<?= BASE_URL ?>/downloads.php?id=<?= $doc['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="tooltip" title="ดาวน์โหลด" target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                                
                                <?php if (hasMenuPermission(PERM_DOCUMENT_DELETE)): ?>
                                <a href="delete.php?id=<?= $doc['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" 
                                   data-bs-toggle="tooltip" title="ลบ"
                                   data-title="ยืนยันการลบเอกสาร"
                                   data-text="คุณแน่ใจหรือไม่ที่จะลบเอกสาร <?= htmlspecialchars($doc['title']) ?>?">
                                    <i class="fas fa-trash"></i>
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
        <div class="d-flex justify-content-center mt-4">
            <?= generatePagination($page, $totalPages, 'index.php', $_GET) ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">ไม่พบข้อมูลเอกสาร</h5>
            <p class="text-muted">ลองเปลี่ยนเงื่อนไขการค้นหา หรือ <a href="create.php">เพิ่มเอกสารใหม่</a></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">การจัดการแบบกลุ่ม</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="bulk_actions.php">
                <div class="modal-body">
                    <?= getCSRFInput() ?>
                    <div class="mb-3">
                        <label for="bulk_action" class="form-label">เลือกการดำเนินการ</label>
                        <select class="form-select" id="bulk_action" name="action" required>
                            <option value="">-- เลือกการดำเนินการ --</option>
                            <?php if (hasMenuPermission(PERM_DOCUMENT_APPROVE)): ?>
                            <option value="approve">อนุมัติ</option>
                            <option value="reject">ไม่อนุมัติ</option>
                            <?php endif; ?>
                            <option value="change_category">เปลี่ยนหมวดหมู่</option>
                            <option value="archive">เก็บถาวร</option>
                            <?php if (hasMenuPermission(PERM_DOCUMENT_DELETE)): ?>
                            <option value="delete">ลบ</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="category_selection" style="display: none;">
                        <label for="new_category" class="form-label">หมวดหมู่ใหม่</label>
                        <select class="form-select" id="new_category" name="new_category">
                            <option value="">ไม่ระบุหมวดหมู่</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= str_repeat('&nbsp;&nbsp;', $cat['level'] - 1) ?>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="rejection_reason" style="display: none;">
                        <label for="reason" class="form-label">เหตุผลในการไม่อนุมัติ</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                    </div>
                    
                    <input type="hidden" id="selected_documents" name="documents">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">ดำเนินการ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Bulk actions functionality
    $('#bulk_action').change(function() {
        const action = $(this).val();
        $('#category_selection, #rejection_reason').hide();
        
        if (action === 'change_category') {
            $('#category_selection').show();
        } else if (action === 'reject') {
            $('#rejection_reason').show();
        }
    });
    
    // Select all checkboxes functionality
    $('#select_all, #select_all_header').change(function() {
        const isChecked = $(this).prop('checked');
        $('.document-checkbox').prop('checked', isChecked);
        updateBulkActionsButton();
    });
    
    $('.document-checkbox').change(function() {
        updateBulkActionsButton();
        
        // Update select all checkbox
        const totalCheckboxes = $('.document-checkbox').length;
        const checkedCheckboxes = $('.document-checkbox:checked').length;
        $('#select_all, #select_all_header').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    function updateBulkActionsButton() {
        const checkedBoxes = $('.document-checkbox:checked').length;
        if (checkedBoxes > 0) {
            $('#bulk-actions-btn').show();
        } else {
            $('#bulk-actions-btn').hide();
        }
    }
    
    // Submit bulk actions
    $('#bulkActionsModal form').on('submit', function() {
        const selectedDocuments = [];
        $('.document-checkbox:checked').each(function() {
            selectedDocuments.push($(this).val());
        });
        $('#selected_documents').val(selectedDocuments.join(','));
    });
    
    // Initialize DataTable for better sorting
    if ($.fn.DataTable) {
        $('.table').DataTable({
            responsive: true,
            pageLength: <?= ADMIN_ITEMS_PER_PAGE ?>,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
            },
            columnDefs: [
                { orderable: false, targets: [0, -1] }, // Disable sorting on checkbox and actions columns
                { searchable: false, targets: [0, -1] }
            ],
            order: [[6, 'desc']] // Sort by upload date descending
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>