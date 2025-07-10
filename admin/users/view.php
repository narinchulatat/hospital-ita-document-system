<?php
$pageTitle = 'ดูรายละเอียดผู้ใช้';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

$userId = intval($_GET['id'] ?? 0);

if ($userId <= 0) {
    $_SESSION['error_message'] = 'ไม่พบผู้ใช้ที่ต้องการดู';
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

try {
    $database = Database::getInstance();
    
    // Get user details with role information
    $userQuery = "
        SELECT u.*, r.name as role_name, r.display_name as role_display_name,
               creator.first_name as creator_first_name, creator.last_name as creator_last_name,
               CONCAT(u.first_name, ' ', u.last_name) as full_name
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        LEFT JOIN users creator ON u.created_by = creator.id
        WHERE u.id = ?
    ";
    
    $user = $database->fetch($userQuery, [$userId]);
    
    if (!$user) {
        $_SESSION['error_message'] = 'ไม่พบผู้ใช้ที่ต้องการดู';
        header('Location: ' . BASE_URL . '/admin/users/');
        exit;
    }
    
    // Get user activity logs
    $activityLogs = $database->fetchAll(
        "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
        [$userId]
    );
    
    // Get user statistics
    $stats = [
        'documents_uploaded' => $database->fetch("SELECT COUNT(*) as count FROM documents WHERE uploaded_by = ?", [$userId])['count'],
        'last_login' => $database->fetch("SELECT created_at FROM activity_logs WHERE user_id = ? AND action = ? ORDER BY created_at DESC LIMIT 1", [$userId, ACTION_LOGIN])['created_at'] ?? null,
        'total_activities' => $database->fetch("SELECT COUNT(*) as count FROM activity_logs WHERE user_id = ?", [$userId])['count']
    ];
    
} catch (Exception $e) {
    error_log("View user error: " . $e->getMessage());
    $_SESSION['error_message'] = 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้';
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

$pageTitle = 'ข้อมูลผู้ใช้: ' . $user['full_name'];
?>

<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="<?= BASE_URL ?>/admin/" class="text-gray-700 hover:text-blue-600">
                    <i class="fas fa-home mr-1"></i>
                    หน้าหลัก
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="<?= BASE_URL ?>/admin/users/" class="text-gray-700 hover:text-blue-600">จัดการผู้ใช้</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-gray-500"><?= htmlspecialchars($user['full_name']) ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- User Header -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center mr-4">
                        <i class="fas fa-user text-gray-600 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($user['full_name']) ?></h1>
                        <p class="text-gray-600">@<?= htmlspecialchars($user['username']) ?></p>
                        <div class="flex items-center mt-2">
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
                            $statusClass = $statusClasses[$user['status']] ?? 'bg-gray-100 text-gray-800';
                            $statusText = $statusTexts[$user['status']] ?? $user['status'];
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?> mr-2">
                                <?= $statusText ?>
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($user['role_display_name'] ?? $user['role_name']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $user['id'] ?>" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-edit mr-2"></i>
                        แก้ไขข้อมูล
                    </a>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <a href="<?= BASE_URL ?>/admin/users/delete.php?id=<?= $user['id'] ?>" 
                       class="inline-flex items-center px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 btn-delete"
                       data-title="ยืนยันการลบผู้ใช้"
                       data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ '<?= htmlspecialchars($user['full_name']) ?>'">
                        <i class="fas fa-trash mr-2"></i>
                        ลบผู้ใช้
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-file-upload text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">เอกสารที่อัปโหลด</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['documents_uploaded']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-history text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">กิจกรรมทั้งหมด</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_activities']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">เข้าสู่ระบบล่าสุด</p>
                    <p class="text-sm font-bold text-gray-900">
                        <?= $stats['last_login'] ? formatThaiDate($stats['last_login'], true) : 'ไม่เคยเข้าสู่ระบบ' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Information -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">ข้อมูลส่วนตัว</h3>
            </div>
            <div class="p-6">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ชื่อ-นามสกุล</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['full_name']) ?></dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ชื่อผู้ใช้</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['username']) ?></dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">อีเมล</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-blue-600 hover:text-blue-800">
                                <?= htmlspecialchars($user['email']) ?>
                            </a>
                        </dd>
                    </div>
                    
                    <?php if ($user['phone']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">เบอร์โทรศัพท์</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['phone']) ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user['department']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">แผนก/หน่วยงาน</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['department']) ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">บทบาท</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($user['role_display_name'] ?? $user['role_name']) ?>
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">สถานะ</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">วันที่สร้าง</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= formatThaiDate($user['created_at'], true) ?></dd>
                    </div>
                    
                    <?php if ($user['creator_first_name']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">สร้างโดย</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?= htmlspecialchars($user['creator_first_name'] . ' ' . $user['creator_last_name']) ?>
                        </dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user['updated_at']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">อัปเดตล่าสุด</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= formatThaiDate($user['updated_at'], true) ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">กิจกรรมล่าสุด</h3>
            </div>
            <div class="p-6">
                <?php if (!empty($activityLogs)): ?>
                <div class="flow-root">
                    <ul class="-mb-8">
                        <?php foreach ($activityLogs as $index => $activity): ?>
                        <li>
                            <div class="relative pb-8">
                                <?php if ($index < count($activityLogs) - 1): ?>
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <?php endif; ?>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <?php
                                        $actionIcons = [
                                            ACTION_CREATE => 'fa-plus text-green-600',
                                            ACTION_UPDATE => 'fa-edit text-blue-600',
                                            ACTION_DELETE => 'fa-trash text-red-600',
                                            ACTION_LOGIN => 'fa-sign-in-alt text-purple-600',
                                            ACTION_LOGOUT => 'fa-sign-out-alt text-gray-600'
                                        ];
                                        $iconClass = $actionIcons[$activity['action']] ?? 'fa-circle text-gray-600';
                                        ?>
                                        <span class="h-8 w-8 rounded-full bg-white flex items-center justify-center ring-8 ring-white border-2 border-gray-200">
                                            <i class="fas <?= $iconClass ?>"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-900">
                                                <?= getActivityDescription($activity['action'], $activity['table_name']) ?>
                                            </p>
                                            <p class="text-xs text-gray-500"><?= formatThaiDate($activity['created_at'], true) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="mt-6">
                    <a href="<?= BASE_URL ?>/admin/logs/activities.php?user_id=<?= $user['id'] ?>" 
                       class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                        ดูกิจกรรมทั้งหมด
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-8">ไม่มีกิจกรรม</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
function getActivityDescription($action, $table) {
    $descriptions = [
        ACTION_CREATE => 'สร้าง' . getTableName($table),
        ACTION_UPDATE => 'แก้ไข' . getTableName($table),
        ACTION_DELETE => 'ลบ' . getTableName($table),
        ACTION_LOGIN => 'เข้าสู่ระบบ',
        ACTION_LOGOUT => 'ออกจากระบบ'
    ];
    return $descriptions[$action] ?? 'ดำเนินการ';
}

function getTableName($table) {
    $names = [
        'users' => 'ผู้ใช้',
        'documents' => 'เอกสาร',
        'categories' => 'หมวดหมู่'
    ];
    return $names[$table] ?? $table;
}
?>

<?php require_once '../includes/footer.php'; ?>