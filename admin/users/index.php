<?php
$pageTitle = 'จัดการผู้ใช้';
$pageSubtitle = 'รายการผู้ใช้ทั้งหมดในระบบ';

require_once '../includes/header.php';
require_once '../../classes/User.php';
require_once '../../classes/Role.php';

// Check permission
requirePermission(PERM_USER_VIEW);

try {
    $user = new User();
    $role = new Role();
    
    // Get filters
    $search = $_GET['search'] ?? '';
    $roleFilter = $_GET['role'] ?? '';
    $statusFilter = $_GET['status'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // Build filters
    $filters = [];
    if ($search) {
        $filters['search'] = $search;
    }
    if ($roleFilter) {
        $filters['role'] = $roleFilter;
    }
    if ($statusFilter) {
        $filters['status'] = $statusFilter;
    }
    
    // Get users
    $users = $user->getAll($filters, $page, $limit);
    $totalUsers = $user->getTotalCount($filters);
    $totalPages = ceil($totalUsers / $limit);
    
    // Get roles for filter
    $roles = $role->getAll();
    
} catch (Exception $e) {
    error_log("User management error: " . $e->getMessage());
    setFlashMessage('error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
    $users = [];
    $totalUsers = 0;
    $totalPages = 0;
    $roles = [];
}
?>

<!-- Page Actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="text-muted">พบผู้ใช้ทั้งหมด <?= number_format($totalUsers) ?> คน</span>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>เพิ่มผู้ใช้ใหม่
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">ค้นหา</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="ชื่อ, อีเมล, หรือชื่อผู้ใช้">
            </div>
            
            <div class="col-md-3">
                <label for="role" class="form-label">บทบาท</label>
                <select class="form-select" id="role" name="role">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $roleFilter == $r['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">สถานะ</label>
                <select class="form-select" id="status" name="status">
                    <option value="">ทั้งหมด</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                    <option value="locked" <?= $statusFilter === 'locked' ? 'selected' : '' ?>>ถูกล็อค</option>
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

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">รายการผู้ใช้</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($users)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>รูปโปรไฟล์</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>อีเมล</th>
                        <th>แผนก</th>
                        <th>บทบาท</th>
                        <th>สถานะ</th>
                        <th>เข้าใช้ล่าสุด</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <?php if ($u['profile_image']): ?>
                            <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($u['profile_image']) ?>" 
                                 class="rounded-circle" width="40" height="40" alt="Profile">
                            <?php else: ?>
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($u['username']) ?></strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                        </td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($u['email']) ?>">
                                <?= htmlspecialchars($u['email']) ?>
                            </a>
                        </td>
                        <td>
                            <?= htmlspecialchars($u['department'] ?? '-') ?>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?= htmlspecialchars($u['role_name'] ?? '-') ?>
                            </span>
                        </td>
                        <td>
                            <?= getStatusBadge($u['status'], 'user') ?>
                        </td>
                        <td>
                            <?= $u['last_login'] ? formatThaiDate($u['last_login'], true) : '-' ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="view.php?id=<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-outline-info" 
                                   data-bs-toggle="tooltip" title="ดูรายละเอียด">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if (hasMenuPermission(PERM_USER_EDIT)): ?>
                                <a href="edit.php?id=<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-outline-warning" 
                                   data-bs-toggle="tooltip" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (hasMenuPermission(PERM_USER_DELETE) && $u['id'] != $_SESSION['user_id']): ?>
                                <a href="delete.php?id=<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" 
                                   data-bs-toggle="tooltip" title="ลบ"
                                   data-title="ยืนยันการลบผู้ใช้"
                                   data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>?">
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
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">ไม่พบข้อมูลผู้ใช้</h5>
            <p class="text-muted">ลองเปลี่ยนเงื่อนไขการค้นหา หรือ <a href="create.php">เพิ่มผู้ใช้ใหม่</a></p>
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
                            <option value="activate">เปิดใช้งาน</option>
                            <option value="deactivate">ปิดใช้งาน</option>
                            <option value="change_role">เปลี่ยนบทบาท</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="role_selection" style="display: none;">
                        <label for="new_role" class="form-label">บทบาทใหม่</label>
                        <select class="form-select" id="new_role" name="new_role">
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <input type="hidden" id="selected_users" name="users">
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
        if ($(this).val() === 'change_role') {
            $('#role_selection').show();
        } else {
            $('#role_selection').hide();
        }
    });
    
    // Select all checkboxes functionality
    $('#select_all').change(function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActionsButton();
    });
    
    $('.user-checkbox').change(function() {
        updateBulkActionsButton();
    });
    
    function updateBulkActionsButton() {
        const checkedBoxes = $('.user-checkbox:checked').length;
        if (checkedBoxes > 0) {
            $('#bulk-actions-btn').removeClass('d-none');
        } else {
            $('#bulk-actions-btn').addClass('d-none');
        }
    }
    
    // Initialize DataTable for better sorting and searching
    if ($.fn.DataTable) {
        $('.table').DataTable({
            responsive: true,
            pageLength: <?= ADMIN_ITEMS_PER_PAGE ?>,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
            },
            columnDefs: [
                { orderable: false, targets: [0, -1] } // Disable sorting on profile image and actions columns
            ]
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>