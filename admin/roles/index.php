<?php
require_once '../includes/header.php';
require_once 'functions.php';

$pageTitle = 'จัดการบทบาทและสิทธิ์';
$pageSubtitle = 'กำหนดบทบาทผู้ใช้และสิทธิ์การเข้าถึง';

// Check permission
if (!hasPermission('roles.view')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

// Initialize database and classes
$db = new Database();
$role = new Role();

// Get filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$where_conditions = ['1=1'];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(r.name LIKE :search OR r.display_name LIKE :search OR r.description LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "r.is_active = :status";
    $params[':status'] = $status_filter === 'active' ? 1 : 0;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM roles r WHERE $where_clause";
$count_stmt = $db->query($count_sql, $params);
$total_roles = $count_stmt->fetch()['total'];

// Pagination
$page = (int)($_GET['page'] ?? 1);
$per_page = 25;
$total_pages = ceil($total_roles / $per_page);
$offset = ($page - 1) * $per_page;

// Get roles with user counts
$sql = "SELECT r.*, 
               (SELECT COUNT(*) FROM users WHERE role_id = r.id) as user_count
        FROM roles r 
        WHERE $where_clause
        ORDER BY r.name ASC
        LIMIT :limit OFFSET :offset";

$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$stmt = $db->query($sql, $params);
$roles = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $db->query("SELECT COUNT(*) as count FROM roles")->fetch()['count'],
    'active' => $db->query("SELECT COUNT(*) as count FROM roles WHERE is_active = 1")->fetch()['count'],
    'inactive' => $db->query("SELECT COUNT(*) as count FROM roles WHERE is_active = 0")->fetch()['count'],
    'with_users' => $db->query("SELECT COUNT(DISTINCT role_id) as count FROM users WHERE role_id IS NOT NULL")->fetch()['count']
];
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
            <?php if (hasPermission('roles.create')): ?>
            <a href="<?= BASE_URL ?>/admin/roles/add.php" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                เพิ่มบทบาทใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="ชื่อบทบาท, ชื่อที่แสดง, คำอธิบาย"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select id="status" 
                        name="status" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">ทั้งหมด</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" 
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-search mr-2"></i>ค้นหา
                </button>
                <a href="<?= BASE_URL ?>/admin/roles/" 
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
                        <i class="fas fa-user-shield text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ทั้งหมด</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($stats['total']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ใช้งาน</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($stats['active']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">ไม่ใช้งาน</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($stats['inactive']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">มีผู้ใช้</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= number_format($stats['with_users']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">รายการบทบาท</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">บทบาท</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อระบบ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จำนวนผู้ใช้</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การกระทำ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($roles as $roleItem): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center <?= getRoleBadgeClass($roleItem['name']) ?>">
                                        <i class="fas <?= getRoleIcon($roleItem['name']) ?>"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($roleItem['display_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($roleItem['description'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            <?= htmlspecialchars($roleItem['name']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusBadgeClass($roleItem['is_active']) ?>">
                                <?= getStatusLabel($roleItem['is_active']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <span class="mr-2"><?= number_format($roleItem['user_count']) ?></span>
                                <?php if ($roleItem['user_count'] > 0): ?>
                                <a href="<?= BASE_URL ?>/admin/users/?role=<?= urlencode($roleItem['name']) ?>" 
                                   class="text-blue-600 hover:text-blue-900 text-xs"
                                   title="ดูผู้ใช้ในบทบาทนี้">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= formatThaiDate($roleItem['created_at']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <?php if (hasPermission('roles.view')): ?>
                            <a href="<?= BASE_URL ?>/admin/roles/view.php?id=<?= $roleItem['id'] ?>" 
                               class="text-blue-600 hover:text-blue-900"
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('roles.edit')): ?>
                            <a href="<?= BASE_URL ?>/admin/roles/edit.php?id=<?= $roleItem['id'] ?>" 
                               class="text-indigo-600 hover:text-indigo-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('roles.delete') && canDeleteRole($roleItem['id'])): ?>
                            <a href="<?= BASE_URL ?>/admin/roles/delete.php?id=<?= $roleItem['id'] ?>" 
                               class="text-red-600 hover:text-red-900"
                               data-tooltip="ลบ">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php elseif (hasPermission('roles.delete')): ?>
                            <span class="text-gray-400" data-tooltip="ไม่สามารถลบได้">
                                <i class="fas fa-trash"></i>
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-user-shield text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium mb-1">ไม่พบข้อมูลบทบาท</p>
                            <p class="text-sm">ลองเปลี่ยนเงื่อนไขการค้นหาหรือเพิ่มบทบาทใหม่</p>
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
                        ถึง <span class="font-medium"><?= number_format(min($offset + $per_page, $total_roles)) ?></span> 
                        จาก <span class="font-medium"><?= number_format($total_roles) ?></span> รายการ
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