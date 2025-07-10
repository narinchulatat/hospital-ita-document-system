<?php
require_once '../includes/header.php';

$pageTitle = 'จัดการผู้ใช้';
$pageSubtitle = 'รายการผู้ใช้ทั้งหมดในระบบ';

// Check permission
if (!hasPermission('users.view')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

// Initialize database and classes
$db = new Database();
$user = new User();

// Get filters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$where_conditions = ['1=1'];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.username LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($role_filter)) {
    $where_conditions[] = "r.name = :role";
    $params[':role'] = $role_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "u.status = :status";
    $params[':status'] = $status_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users u 
              LEFT JOIN roles r ON u.role_id = r.id 
              WHERE $where_clause";
$count_stmt = $db->query($count_sql, $params);
$total_users = $count_stmt->fetch()['total'];

// Pagination
$page = (int)($_GET['page'] ?? 1);
$per_page = 25;
$total_pages = ceil($total_users / $per_page);
$offset = ($page - 1) * $per_page;

// Get users
$sql = "SELECT u.*, r.name as role_name, r.display_name as role_display_name
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE $where_clause
        ORDER BY u.created_at DESC 
        LIMIT :limit OFFSET :offset";

$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$stmt = $db->query($sql, $params);
$users = $stmt->fetchAll();

// Get roles for filter
$roles_sql = "SELECT * FROM roles WHERE status = 'active' ORDER BY display_name";
$roles_stmt = $db->query($roles_sql);
$roles = $roles_stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
            <?php if (hasPermission('users.create')): ?>
            <a href="<?= BASE_URL ?>/admin/users/create.php" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                เพิ่มผู้ใช้ใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="ชื่อผู้ใช้, ชื่อ-นามสกุล, อีเมล"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">บทบาท</label>
                <select id="role" 
                        name="role" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?= htmlspecialchars($role['name']) ?>" <?= $role_filter === $role['name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role['display_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select id="status" 
                        name="status" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">ทั้งหมด</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>ระงับ</option>
                    <option value="banned" <?= $status_filter === 'banned' ? 'selected' : '' ?>>ห้ามใช้งาน</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" 
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-search mr-2"></i>ค้นหา
                </button>
                <a href="<?= BASE_URL ?>/admin/users/" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ผู้ใช้ทั้งหมด</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($total_users) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <?php
        // Get quick stats
        $active_count = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch()['count'];
        $inactive_count = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'")->fetch()['count'];
        $banned_count = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'banned'")->fetch()['count'];
        ?>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ใช้งาน</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($active_count) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ระงับ</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($inactive_count) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-slash text-red-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ห้ามใช้งาน</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($banned_count) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">รายการผู้ใช้</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้ใช้</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อีเมล</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">บทบาท</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การกระทำ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user_item): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            <?= strtoupper(substr($user_item['first_name'], 0, 1) . substr($user_item['last_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($user_item['first_name'] . ' ' . $user_item['last_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @<?= htmlspecialchars($user_item['username']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars($user_item['email']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($user_item['role_display_name'] ?? $user_item['role_name'] ?? 'ไม่ระบุ') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $status_classes = [
                                'active' => 'bg-green-100 text-green-800',
                                'inactive' => 'bg-yellow-100 text-yellow-800', 
                                'banned' => 'bg-red-100 text-red-800'
                            ];
                            $status_labels = [
                                'active' => 'ใช้งาน',
                                'inactive' => 'ระงับ',
                                'banned' => 'ห้ามใช้งาน'
                            ];
                            $status_class = $status_classes[$user_item['status']] ?? 'bg-gray-100 text-gray-800';
                            $status_label = $status_labels[$user_item['status']] ?? $user_item['status'];
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $status_class ?>">
                                <?= $status_label ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= formatThaiDate($user_item['created_at']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <?php if (hasPermission('users.view')): ?>
                            <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $user_item['id'] ?>" 
                               class="text-blue-600 hover:text-blue-900"
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('users.edit')): ?>
                            <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $user_item['id'] ?>" 
                               class="text-indigo-600 hover:text-indigo-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('users.delete') && $user_item['id'] != $_SESSION['user_id']): ?>
                            <a href="<?= BASE_URL ?>/admin/users/delete.php?id=<?= $user_item['id'] ?>" 
                               class="text-red-600 hover:text-red-900 btn-delete"
                               data-title="ยืนยันการลบผู้ใช้"
                               data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ <?= htmlspecialchars($user_item['first_name'] . ' ' . $user_item['last_name']) ?>?"
                               data-tooltip="ลบ">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium mb-1">ไม่พบข้อมูลผู้ใช้</p>
                            <p class="text-sm">ลองเปลี่ยนเงื่อนไขการค้นหาหรือเพิ่มผู้ใช้ใหม่</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    ก่อนหน้า
                </a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                   class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    ถัดไป
                </a>
                <?php endif; ?>
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        แสดง <span class="font-medium"><?= number_format($offset + 1) ?></span> 
                        ถึง <span class="font-medium"><?= number_format(min($offset + $per_page, $total_users)) ?></span> 
                        จาก <span class="font-medium"><?= number_format($total_users) ?></span> รายการ
                    </p>
                </div>
                
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                           class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $i === $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
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
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-tooltip]').each(function() {
        const $this = $(this);
        const title = $this.data('tooltip');
        
        $this.on('mouseenter', function() {
            const tooltip = $('<div class="absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg whitespace-nowrap">')
                .text(title)
                .appendTo('body');
            
            const offset = $this.offset();
            tooltip.css({
                top: offset.top - tooltip.outerHeight() - 5,
                left: offset.left + ($this.outerWidth() - tooltip.outerWidth()) / 2
            });
        }).on('mouseleave', function() {
            $('.absolute.z-50').remove();
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>