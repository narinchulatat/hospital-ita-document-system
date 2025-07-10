<?php
$pageTitle = 'จัดการผู้ใช้';
$pageSubtitle = 'จัดการข้อมูลผู้ใช้ในระบบ';
require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

try {
    $user = new User();
    
    // Handle search and filters
    $search = $_GET['search'] ?? '';
    $role_filter = $_GET['role'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = 25;
    
    // Build where conditions
    $where = [];
    $params = [];
    
    if ($search) {
        $where[] = "(u.username LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
        $params['search'] = "%$search%";
    }
    
    if ($role_filter) {
        $where[] = "u.role_id = :role_id";
        $params['role_id'] = $role_filter;
    }
    
    if ($status_filter) {
        $where[] = "u.status = :status";
        $params['status'] = $status_filter;
    }
    
    // Get users with pagination
    $users = $user->getPaginated($where, $params, $page, $limit);
    $totalUsers = $user->getTotalCount($where, $params);
    $totalPages = ceil($totalUsers / $limit);
    
    // Get roles for filter
    $roles = $user->getAllRoles();
    
} catch (Exception $e) {
    error_log("Users index error: " . $e->getMessage());
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการโหลดข้อมูลผู้ใช้';
    $users = [];
    $roles = [];
    $totalUsers = 0;
    $totalPages = 1;
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">จัดการผู้ใช้</h1>
            <p class="text-gray-600">จัดการข้อมูลผู้ใช้และสิทธิ์การเข้าถึง</p>
        </div>
        <div class="flex space-x-3">
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>เพิ่มผู้ใช้
            </a>
            <button onclick="exportUsers()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-download mr-2"></i>ส่งออก
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">ผู้ใช้ทั้งหมด</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($totalUsers) ?></p>
                </div>
            </div>
        </div>
        
        <?php
        $activeUsers = $user->getTotalCount(['u.status = :status'], ['status' => 'active']);
        $inactiveUsers = $user->getTotalCount(['u.status = :status'], ['status' => 'inactive']);
        $adminUsers = $user->getTotalCount(['r.name = :role'], ['role' => 'admin']);
        ?>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">ใช้งานอยู่</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($activeUsers) ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-user-times text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">ไม่ใช้งาน</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($inactiveUsers) ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-crown text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">ผู้ดูแลระบบ</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($adminUsers) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ค้นหา</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="ชื่อผู้ใช้, อีเมล, ชื่อ-นามสกุล"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">บทบาท</label>
                <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">ทุกบทบาท</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= $role_filter == $role['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">ทุกสถานะ</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>ค้นหา
                </button>
                <a href="?" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                    <i class="fas fa-times mr-2"></i>ล้าง
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">รายการผู้ใช้</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">
                        แสดง <?= count($users) ?> จาก <?= number_format($totalUsers) ?> รายการ
                    </span>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="users-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="select-all-checkbox">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ผู้ใช้
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            บทบาท
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            สถานะ
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            เข้าสู่ระบบล่าสุด
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            การดำเนินการ
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                            <p>ไม่พบข้อมูลผู้ใช้</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($users as $userData): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="row-checkbox" value="<?= $userData['id'] ?>">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($userData['username']) ?> • <?= htmlspecialchars($userData['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?= $userData['role_name'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                        ($userData['role_name'] === 'staff' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') ?>">
                                    <?= htmlspecialchars($userData['role_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?= $userData['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $userData['status'] === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $userData['last_login'] ? formatThaiDate($userData['last_login'], true) : 'ไม่เคยเข้าใช้' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="view.php?id=<?= $userData['id'] ?>" 
                                       class="text-blue-600 hover:text-blue-900" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $userData['id'] ?>" 
                                       class="text-yellow-600 hover:text-yellow-900" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($userData['id'] != $_SESSION['user_id']): ?>
                                    <a href="delete.php?id=<?= $userData['id'] ?>" 
                                       class="text-red-600 hover:text-red-900 btn-delete" 
                                       title="ลบ"
                                       data-title="ยืนยันการลบผู้ใช้"
                                       data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ <?= htmlspecialchars($userData['username']) ?>?">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
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
                            แสดง <span class="font-medium"><?= (($page - 1) * $limit) + 1 ?></span> 
                            ถึง <span class="font-medium"><?= min($page * $limit, $totalUsers) ?></span> 
                            จาก <span class="font-medium"><?= number_format($totalUsers) ?></span> รายการ
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                                    <?= $i ?>
                                </span>
                                <?php else: ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <?= $i ?>
                                </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Export users function
function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.location.href = '?' + params.toString();
}

// Bulk actions
$(document).ready(function() {
    // Select all checkbox
    $('.select-all-checkbox').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });
    
    // Individual checkboxes
    $('.row-checkbox').on('change', function() {
        updateBulkActions();
    });
    
    function updateBulkActions() {
        const checkedCount = $('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            showBulkActions(checkedCount);
        } else {
            hideBulkActions();
        }
    }
    
    function showBulkActions(count) {
        if ($('#bulk-actions').length === 0) {
            const bulkActions = `
                <div id="bulk-actions" class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg border p-4 z-50">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">เลือก <span class="font-medium">${count}</span> รายการ</span>
                        <div class="flex space-x-2">
                            <button onclick="bulkActivate()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                เปิดใช้งาน
                            </button>
                            <button onclick="bulkDeactivate()" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                ปิดใช้งาน
                            </button>
                            <button onclick="bulkDelete()" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">
                                ลบ
                            </button>
                        </div>
                        <button onclick="hideBulkActions()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('body').append(bulkActions);
        } else {
            $('#bulk-actions span').html(`เลือก <span class="font-medium">${count}</span> รายการ`);
        }
    }
    
    window.hideBulkActions = function() {
        $('#bulk-actions').remove();
        $('.select-all-checkbox, .row-checkbox').prop('checked', false);
    }
    
    window.bulkActivate = function() {
        performBulkAction('activate', 'เปิดใช้งาน');
    }
    
    window.bulkDeactivate = function() {
        performBulkAction('deactivate', 'ปิดใช้งาน');
    }
    
    window.bulkDelete = function() {
        performBulkAction('delete', 'ลบ');
    }
    
    function performBulkAction(action, actionText) {
        const selectedIds = $('.row-checkbox:checked').map(function() {
            return this.value;
        }).get();
        
        if (selectedIds.length === 0) return;
        
        Swal.fire({
            title: `ยืนยันการ${actionText}`,
            text: `คุณต้องการ${actionText}ผู้ใช้ ${selectedIds.length} รายการหรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: action === 'delete' ? '#ef4444' : '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'bulk-actions.php',
                    method: 'POST',
                    data: {
                        action: action,
                        ids: selectedIds,
                        csrf_token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert(response.message || 'เกิดข้อผิดพลาด', 'error');
                        }
                    },
                    error: function() {
                        showAlert('เกิดข้อผิดพลาดในการดำเนินการ', 'error');
                    }
                });
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>