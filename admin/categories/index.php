<?php
$pageTitle = 'จัดการหมวดหมู่';
$pageSubtitle = 'รายการหมวดหมู่เอกสารทั้งหมดในระบบ';

require_once '../includes/header.php';
require_once '../../classes/Category.php';
require_once '../../classes/Document.php';

// Check permission
requirePermission(PERM_CATEGORY_VIEW);

try {
    $category = new Category();
    $document = new Document();
    
    // Get search term
    $search = $_GET['search'] ?? '';
    
    // Get categories with document count
    $categories = $category->getAllWithDocumentCount($search);
    
    // Get category statistics
    $stats = [
        'total_categories' => count($categories),
        'active_categories' => count(array_filter($categories, fn($cat) => $cat['is_active'])),
        'inactive_categories' => count(array_filter($categories, fn($cat) => !$cat['is_active'])),
        'total_documents' => array_sum(array_column($categories, 'document_count'))
    ];
    
} catch (Exception $e) {
    error_log("Category management error: " . $e->getMessage());
    setFlashMessage('error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
    $categories = [];
    $stats = array_fill_keys(['total_categories', 'active_categories', 'inactive_categories', 'total_documents'], 0);
}
?>

<!-- Category Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['total_categories']) ?></div>
                    <div class="stats-label">หมวดหมู่ทั้งหมด</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card success">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['active_categories']) ?></div>
                    <div class="stats-label">หมวดหมู่ใช้งาน</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card warning">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-folder-minus"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['inactive_categories']) ?></div>
                    <div class="stats-label">หมวดหมู่ปิดใช้</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card info">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['total_documents']) ?></div>
                    <div class="stats-label">เอกสารทั้งหมด</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page Actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="text-muted">พบหมวดหมู่ทั้งหมด <?= number_format($stats['total_categories']) ?> หมวดหมู่</span>
    </div>
    <div class="btn-group">
        <?php if (hasMenuPermission(PERM_CATEGORY_CREATE)): ?>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>เพิ่มหมวดหมู่
        </a>
        <?php endif; ?>
        
        <a href="tree.php" class="btn btn-outline-info">
            <i class="fas fa-sitemap me-2"></i>ดูแบบต้นไม้
        </a>
        
        <button class="btn btn-outline-secondary" id="sort-categories">
            <i class="fas fa-sort me-2"></i>จัดเรียงลำดับ
        </button>
    </div>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">ค้นหาหมวดหมู่</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="ชื่อหมวดหมู่, คำอธิบาย">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>ค้นหา
                    </button>
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>รีเซ็ต
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">รายการหมวดหมู่</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($categories)): ?>
        <div class="table-responsive">
            <table class="table table-hover" id="categories-table">
                <thead>
                    <tr>
                        <th>หมวดหมู่</th>
                        <th>คำอธิบาย</th>
                        <th>หมวดหมู่แม่</th>
                        <th>จำนวนเอกสาร</th>
                        <th>ลำดับ</th>
                        <th>สถานะ</th>
                        <th>วันที่สร้าง</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="sortable-categories">
                    <?php foreach ($categories as $cat): ?>
                    <tr data-id="<?= $cat['id'] ?>" data-level="<?= $cat['level'] ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="margin-left: <?= ($cat['level'] - 1) * 20 ?>px;">
                                    <?php if ($cat['level'] > 1): ?>
                                    <i class="fas fa-level-up-alt text-muted me-1" style="transform: rotate(90deg);"></i>
                                    <?php endif; ?>
                                    <i class="fas fa-folder<?= $cat['is_active'] ? '-open' : '' ?> text-<?= $cat['is_active'] ? 'primary' : 'muted' ?> me-2"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </div>
                                    <?php if ($cat['level'] > 1): ?>
                                    <small class="text-muted">ระดับ <?= $cat['level'] ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($cat['description']): ?>
                            <span class="text-truncate-2 d-block" style="max-width: 200px;">
                                <?= htmlspecialchars($cat['description']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($cat['parent_name']): ?>
                            <span class="badge bg-light text-dark">
                                <?= htmlspecialchars($cat['parent_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-info">หมวดหมู่หลัก</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-medium me-2"><?= number_format($cat['document_count']) ?></span>
                                <?php if ($cat['document_count'] > 0): ?>
                                <a href="<?= BASE_URL ?>/admin/documents/?category=<?= $cat['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="tooltip" title="ดูเอกสาร">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2"><?= $cat['sort_order'] ?></span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary move-up" data-id="<?= $cat['id'] ?>" 
                                            data-bs-toggle="tooltip" title="เลื่อนขึ้น">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary move-down" data-id="<?= $cat['id'] ?>" 
                                            data-bs-toggle="tooltip" title="เลื่อนลง">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input status-toggle" type="checkbox" 
                                       data-id="<?= $cat['id'] ?>" <?= $cat['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label">
                                    <?= $cat['is_active'] ? 'ใช้งาน' : 'ปิดใช้' ?>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <?= formatThaiDate($cat['created_at']) ?>
                                <div class="text-muted">
                                    <?= formatThaiDate($cat['created_at'], true) ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="view.php?id=<?= $cat['id'] ?>" 
                                   class="btn btn-sm btn-outline-info" 
                                   data-bs-toggle="tooltip" title="ดูรายละเอียด">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if (hasMenuPermission(PERM_CATEGORY_EDIT)): ?>
                                <a href="edit.php?id=<?= $cat['id'] ?>" 
                                   class="btn btn-sm btn-outline-warning" 
                                   data-bs-toggle="tooltip" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (hasMenuPermission(PERM_CATEGORY_CREATE)): ?>
                                <a href="create.php?parent=<?= $cat['id'] ?>" 
                                   class="btn btn-sm btn-outline-success" 
                                   data-bs-toggle="tooltip" title="เพิ่มหมวดหมู่ย่อย">
                                    <i class="fas fa-plus"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (hasMenuPermission(PERM_CATEGORY_DELETE) && $cat['document_count'] == 0): ?>
                                <a href="delete.php?id=<?= $cat['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" 
                                   data-bs-toggle="tooltip" title="ลบ"
                                   data-title="ยืนยันการลบหมวดหมู่"
                                   data-text="คุณแน่ใจหรือไม่ที่จะลบหมวดหมู่ <?= htmlspecialchars($cat['name']) ?>?">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php elseif ($cat['document_count'] > 0): ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled 
                                        data-bs-toggle="tooltip" title="ไม่สามารถลบได้ มีเอกสาร <?= $cat['document_count'] ?> รายการ">
                                    <i class="fas fa-lock"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">ไม่พบข้อมูลหมวดหมู่</h5>
            <p class="text-muted">
                <?php if ($search): ?>
                ลองเปลี่ยนเงื่อนไขการค้นหา หรือ <a href="index.php">ดูทั้งหมด</a>
                <?php else: ?>
                <a href="create.php">เพิ่มหมวดหมู่แรก</a> เพื่อเริ่มต้นจัดระเบียบเอกสาร
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Sort Categories Modal -->
<div class="modal fade" id="sortModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">จัดเรียงลำดับหมวดหมู่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    ลากและวางเพื่อจัดเรียงลำดับหมวดหมู่ หมวดหมู่ที่อยู่ด้านบนจะแสดงก่อน
                </div>
                <ul id="sortable-list" class="list-group">
                    <?php foreach ($categories as $cat): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center" 
                        data-id="<?= $cat['id'] ?>" style="margin-left: <?= ($cat['level'] - 1) * 20 ?>px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-grip-vertical text-muted me-3"></i>
                            <i class="fas fa-folder<?= $cat['is_active'] ? '-open' : '' ?> text-<?= $cat['is_active'] ? 'primary' : 'muted' ?> me-2"></i>
                            <?= htmlspecialchars($cat['name']) ?>
                        </div>
                        <span class="badge bg-secondary"><?= $cat['document_count'] ?> เอกสาร</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="save-sort">บันทึกการจัดเรียง</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Status toggle
    $('.status-toggle').change(function() {
        const categoryId = $(this).data('id');
        const isActive = $(this).prop('checked');
        const label = $(this).siblings('label');
        
        $.ajax({
            url: '../api/toggle_category_status.php',
            method: 'POST',
            data: {
                id: categoryId,
                is_active: isActive ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    label.text(isActive ? 'ใช้งาน' : 'ปิดใช้');
                    AdminJS.showAlert(response.message || 'อัปเดตสถานะเรียบร้อย', 'success', 2000);
                } else {
                    // Revert checkbox
                    $('.status-toggle[data-id="' + categoryId + '"]').prop('checked', !isActive);
                    AdminJS.showAlert(response.message || 'เกิดข้อผิดพลาด', 'error');
                }
            },
            error: function() {
                // Revert checkbox
                $('.status-toggle[data-id="' + categoryId + '"]').prop('checked', !isActive);
                AdminJS.showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    });
    
    // Move up/down buttons
    $('.move-up, .move-down').click(function() {
        const categoryId = $(this).data('id');
        const direction = $(this).hasClass('move-up') ? 'up' : 'down';
        
        $.ajax({
            url: '../api/move_category.php',
            method: 'POST',
            data: {
                id: categoryId,
                direction: direction
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    AdminJS.showAlert(response.message || 'เกิดข้อผิดพลาด', 'error');
                }
            },
            error: function() {
                AdminJS.showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    });
    
    // Sort modal
    $('#sort-categories').click(function() {
        $('#sortModal').modal('show');
    });
    
    // Initialize sortable
    if (typeof Sortable !== 'undefined') {
        const sortableList = document.getElementById('sortable-list');
        const sortable = Sortable.create(sortableList, {
            handle: '.fa-grip-vertical',
            animation: 150,
            ghostClass: 'sortable-ghost'
        });
    }
    
    // Save sort order
    $('#save-sort').click(function() {
        const order = [];
        $('#sortable-list li').each(function(index) {
            order.push({
                id: $(this).data('id'),
                sort_order: index + 1
            });
        });
        
        $.ajax({
            url: '../api/sort_categories.php',
            method: 'POST',
            data: {
                order: JSON.stringify(order)
            },
            success: function(response) {
                if (response.success) {
                    $('#sortModal').modal('hide');
                    AdminJS.showAlert('จัดเรียงลำดับเรียบร้อย', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    AdminJS.showAlert(response.message || 'เกิดข้อผิดพลาด', 'error');
                }
            },
            error: function() {
                AdminJS.showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    });
    
    // Initialize DataTable
    if ($.fn.DataTable) {
        $('#categories-table').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
            },
            columnDefs: [
                { orderable: false, targets: [-1] }, // Disable sorting on actions column
                { searchable: false, targets: [-1] }
            ],
            order: [[4, 'asc']] // Sort by sort_order ascending
        });
    }
});
</script>

<style>
.sortable-ghost {
    opacity: 0.4;
}

.move-up, .move-down {
    border: none !important;
    padding: 0.25rem 0.5rem;
}

.status-toggle {
    cursor: pointer;
}

tr[data-level="1"] {
    font-weight: 500;
}

tr[data-level="2"] {
    background-color: rgba(0,0,0,0.02);
}

tr[data-level="3"] {
    background-color: rgba(0,0,0,0.04);
}
</style>

<?php require_once '../includes/footer.php'; ?>