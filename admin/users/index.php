<?php
$pageTitle = 'จัดการผู้ใช้';
$pageSubtitle = 'รายการผู้ใช้ทั้งหมดในระบบ';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

try {
    $user = new User();
    $database = Database::getInstance();
    
    // Get filter parameters
    $status = $_GET['status'] ?? '';
    $role = $_GET['role'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if ($status) {
        $conditions[] = "u.status = ?";
        $params[] = $status;
    }
    
    if ($role) {
        $conditions[] = "r.name = ?";
        $params[] = $role;
    }
    
    if ($search) {
        $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    // Get users with pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 25;
    $offset = ($page - 1) * $limit;
    
    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    
    $usersQuery = "
        SELECT u.*, r.name as role_name, r.display_name as role_display_name,
               CONCAT(u.first_name, ' ', u.last_name) as full_name
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        {$whereClause}
        ORDER BY u.created_at DESC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $users = $database->fetchAll($usersQuery, $params);
    
    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        {$whereClause}
    ";
    $totalCount = $database->fetch($countQuery, $params)['total'];
    $totalPages = ceil($totalCount / $limit);
    
    // Get roles for filter
    $roles = $database->fetchAll("SELECT * FROM roles ORDER BY name");
    
    // Get statistics
    $stats = [
        'total' => $database->fetch("SELECT COUNT(*) as count FROM users")['count'],
        'active' => $database->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'],
        'inactive' => $database->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'")['count'],
        'pending' => $database->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'pending'")['count']
    ];
    
} catch (Exception $e) {
    error_log("Users index error: " . $e->getMessage());
    $users = [];
    $totalPages = 0;
    $roles = [];
    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'pending' => 0];
}
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">ทั้งหมด</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-user-check text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">ใช้งาน</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['active']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
                <i class="fas fa-user-times text-red-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">ไม่ใช้งาน</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['inactive']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
                <i class="fas fa-user-clock text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">รออนุมัติ</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['pending']) ?></p>
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
                               placeholder="ค้นหาผู้ใช้..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                        <?php if ($status): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>"><?php endif; ?>
                        <?php if ($role): ?><input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>"><?php endif; ?>
                    </form>
                </div>
                
                <!-- Status Filter -->
                <select name="status" class="table-filter border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" data-column="3">
                    <option value="">สถานะทั้งหมด</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>รออนุมัติ</option>
                </select>
                
                <!-- Role Filter -->
                <select name="role" class="table-filter border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" data-column="4">
                    <option value="">บทบาททั้งหมด</option>
                    <?php foreach ($roles as $roleItem): ?>
                    <option value="<?= htmlspecialchars($roleItem['name']) ?>" <?= $role === $roleItem['name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($roleItem['display_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Reset Filters -->
                <a href="<?= BASE_URL ?>/admin/users/" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-undo mr-2"></i>
                    รีเซ็ต
                </a>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="<?= BASE_URL ?>/admin/users/create.php" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    เพิ่มผู้ใช้ใหม่
                </a>
                
                <button type="button" 
                        class="export-excel inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-file-excel mr-2"></i>
                    ส่งออก Excel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="usersTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" class="select-all rounded border-gray-300">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ผู้ใช้
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        อีเมล
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        บทบาท
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        สถานะ
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        วันที่สร้าง
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        การดำเนินการ
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg">ไม่พบข้อมูลผู้ใช้</p>
                        <p class="text-sm">ลองปรับเปลี่ยนเงื่อนไขการค้นหาหรือ<a href="<?= BASE_URL ?>/admin/users/create.php" class="text-blue-600 hover:text-blue-800">เพิ่มผู้ใช้ใหม่</a></p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $userItem): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" class="select-row rounded border-gray-300" value="<?= $userItem['id'] ?>">
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
                                    <?= htmlspecialchars($userItem['full_name']) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    @<?= htmlspecialchars($userItem['username']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= htmlspecialchars($userItem['email']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?= htmlspecialchars($userItem['role_display_name'] ?? $userItem['role_name']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php
                        $statusClasses = [
                            'active' => 'bg-green-100 text-green-800',
                            'inactive' => 'bg-red-100 text-red-800', 
                            'pending' => 'bg-yellow-100 text-yellow-800'
                        ];
                        $statusTexts = [
                            'active' => 'ใช้งาน',
                            'inactive' => 'ไม่ใช้งาน',
                            'pending' => 'รออนุมัติ'
                        ];
                        $statusClass = $statusClasses[$userItem['status']] ?? 'bg-gray-100 text-gray-800';
                        $statusText = $statusTexts[$userItem['status']] ?? $userItem['status'];
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                            <?= $statusText ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= formatThaiDate($userItem['created_at']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-3">
                            <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $userItem['id'] ?>" 
                               class="text-blue-600 hover:text-blue-900" 
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $userItem['id'] ?>" 
                               class="text-green-600 hover:text-green-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($userItem['id'] != $_SESSION['user_id']): ?>
                            <a href="<?= BASE_URL ?>/admin/users/delete.php?id=<?= $userItem['id'] ?>" 
                               class="text-red-600 hover:text-red-900 btn-delete"
                               data-tooltip="ลบ"
                               data-title="ยืนยันการลบผู้ใช้"
                               data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ '<?= htmlspecialchars($userItem['full_name']) ?>'">>
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
</div>

<!-- Bulk Actions -->
<div class="bulk-actions hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <div class="flex items-center justify-between">
        <div class="text-sm text-blue-800">
            เลือกแล้ว <span class="selected-count font-semibold">0</span> รายการ
        </div>
        <div class="flex items-center space-x-2">
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
        <a href="?page=<?= $page - 1 ?><?= $status ? '&status=' . $status : '' ?><?= $role ? '&role=' . $role : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
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
        <a href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?><?= $role ? '&role=' . $role : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
           class="px-3 py-2 text-sm font-medium <?= $i === $page ? 'text-blue-600 bg-blue-50 border-blue-500' : 'text-gray-500 bg-white border-gray-300' ?> border rounded-lg hover:bg-gray-50">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <!-- Next Page -->
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?><?= $status ? '&status=' . $status : '' ?><?= $role ? '&role=' . $role : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
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
    
    // Role filter change
    document.querySelector('select[name="role"]').addEventListener('change', function() {
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
    const role = document.querySelector('select[name="role"]').value;
    
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    if (role) params.set('role', role);
    
    window.location.href = '?' + params.toString();
}
</script>

<?php require_once '../includes/footer.php'; ?>