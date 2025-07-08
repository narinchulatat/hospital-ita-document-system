<?php
$pageTitle = 'แดชบอร์ดผู้ดูแลระบบ';
require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

try {
    $db = Database::getInstance();
    $user = new User();
    $document = new Document();
    $category = new Category();
    
    // Get dashboard statistics
    $stats = [
        'total_users' => $user->getTotalCount(),
        'total_documents' => $document->getTotalCount(),
        'pending_documents' => $document->getTotalCount(['status' => DOC_STATUS_PENDING]),
        'total_categories' => count($category->getAll(false)),
        'total_downloads' => 0,
        'storage_used' => 0
    ];
    
    // Get total downloads
    $downloadStats = $db->fetch("SELECT SUM(download_count) as total FROM documents");
    $stats['total_downloads'] = $downloadStats['total'] ?? 0;
    
    // Calculate storage usage
    $storageStats = $db->fetch("SELECT SUM(file_size) as total FROM documents");
    $stats['storage_used'] = $storageStats['total'] ?? 0;
    
    // Get recent activities
    $recentActivities = $db->fetchAll(
        "SELECT al.*, u.first_name, u.last_name 
         FROM activity_logs al 
         LEFT JOIN users u ON al.user_id = u.id 
         ORDER BY al.created_at DESC 
         LIMIT 10"
    );
    
    // Get recent documents
    $recentDocuments = $document->getAll([], 1, 5);
    
    // Get pending approvals
    $pendingApprovals = $document->getPendingDocuments(1, 5);
    
    // Get user statistics by role
    $usersByRole = $db->fetchAll(
        "SELECT r.name as role_name, COUNT(u.id) as count
         FROM roles r
         LEFT JOIN users u ON r.id = u.role_id AND u.status = 'active'
         GROUP BY r.id, r.name
         ORDER BY r.id"
    );
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $stats = array_fill_keys(['total_users', 'total_documents', 'pending_documents', 'total_categories', 'total_downloads', 'storage_used'], 0);
    $recentActivities = [];
    $recentDocuments = [];
    $pendingApprovals = [];
    $usersByRole = [];
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-tachometer-alt mr-3"></i>แดชบอร์ดผู้ดูแลระบบ
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                ภาพรวมและการจัดการระบบ
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/admin/backups/" 
               class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-database mr-2"></i>สำรองข้อมูล
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Users -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-3xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ผู้ใช้ทั้งหมด</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_users']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/admin/users/" class="font-medium text-blue-600 hover:text-blue-500">
                        จัดการผู้ใช้ <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Documents -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-alt text-3xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">เอกสารทั้งหมด</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_documents']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/admin/documents/" class="font-medium text-green-600 hover:text-green-500">
                        จัดการเอกสาร <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Documents -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-3xl text-yellow-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">รออนุมัติ</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['pending_documents']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/admin/documents/?status=pending" class="font-medium text-yellow-600 hover:text-yellow-500">
                        ดูรายการ <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Storage Used -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-hdd text-3xl text-purple-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">พื้นที่ใช้งาน</dt>
                            <dd class="text-2xl font-bold text-gray-900"><?= formatFileSize($stats['storage_used']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/admin/reports/" class="font-medium text-purple-600 hover:text-purple-500">
                        ดูรายงาน <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activities -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-history mr-2"></i>กิจกรรมล่าสุด
                </h3>
            </div>
            <div class="p-6">
                <?php if (!empty($recentActivities)): ?>
                <div class="flow-root">
                    <ul class="-mb-8">
                        <?php foreach ($recentActivities as $index => $activity): ?>
                        <li>
                            <div class="relative pb-8">
                                <?php if ($index < count($recentActivities) - 1): ?>
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <?php endif; ?>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-<?= getActivityIcon($activity['action']) ?> text-blue-600 text-sm"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium text-gray-900">
                                                    <?= $activity['first_name'] ? htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) : 'ระบบ' ?>
                                                </span>
                                                <?= getActivityDescription($activity['action'], $activity['table_name']) ?>
                                            </p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <?= formatThaiDate($activity['created_at'], true) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">ไม่มีกิจกรรม</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-clock mr-2"></i>เอกสารรออนุมัติ
                </h3>
            </div>
            <div class="p-6">
                <?php if (!empty($pendingApprovals)): ?>
                <div class="space-y-4">
                    <?php foreach ($pendingApprovals as $doc): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900">
                                    <a href="<?= BASE_URL ?>/admin/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    อัปโหลดโดย: <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?= formatThaiDate($doc['created_at']) ?>
                                </p>
                            </div>
                            <div class="ml-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    รออนุมัติ
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="<?= BASE_URL ?>/admin/documents/?status=pending" 
                       class="text-blue-600 hover:text-blue-500 text-sm">
                        ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">ไม่มีเอกสารรออนุมัติ</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Users by Role Chart -->
    <div class="mt-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-chart-pie mr-2"></i>ผู้ใช้ตามบทบาท
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <?php 
                    $roleColors = ['bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500'];
                    foreach ($usersByRole as $index => $roleData): 
                    ?>
                    <div class="text-center">
                        <div class="<?= $roleColors[$index] ?? 'bg-gray-500' ?> rounded-lg p-4 text-white">
                            <div class="text-2xl font-bold"><?= number_format($roleData['count']) ?></div>
                            <div class="text-sm opacity-75"><?= htmlspecialchars($roleData['role_name']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function getActivityIcon($action) {
    $icons = [
        ACTION_CREATE => 'plus',
        ACTION_UPDATE => 'edit',
        ACTION_DELETE => 'trash',
        ACTION_LOGIN => 'sign-in-alt',
        ACTION_LOGOUT => 'sign-out-alt',
        ACTION_APPROVE => 'check',
        ACTION_REJECT => 'times',
        ACTION_DOWNLOAD => 'download'
    ];
    return $icons[$action] ?? 'circle';
}

function getActivityDescription($action, $table) {
    $descriptions = [
        ACTION_CREATE => 'สร้าง' . getTableName($table),
        ACTION_UPDATE => 'แก้ไข' . getTableName($table),
        ACTION_DELETE => 'ลบ' . getTableName($table),
        ACTION_LOGIN => 'เข้าสู่ระบบ',
        ACTION_LOGOUT => 'ออกจากระบบ',
        ACTION_APPROVE => 'อนุมัติเอกสาร',
        ACTION_REJECT => 'ไม่อนุมัติเอกสาร',
        ACTION_DOWNLOAD => 'ดาวน์โหลดเอกสาร'
    ];
    return $descriptions[$action] ?? 'ดำเนินการ';
}

function getTableName($table) {
    $names = [
        'users' => 'ผู้ใช้',
        'documents' => 'เอกสาร',
        'categories' => 'หมวดหมู่',
        'settings' => 'การตั้งค่า'
    ];
    return $names[$table] ?? $table;
}
?>

<?php require_once '../includes/footer.php'; ?>