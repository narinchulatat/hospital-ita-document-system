<?php
/**
 * Staff Notifications Page
 * Display and manage notifications
 */

$pageTitle = 'การแจ้งเตือน';
require_once '../includes/header.php';

// Require staff role
requireRole(ROLE_STAFF);

$error = '';
$success = '';

try {
    $notification = new Notification();
    $currentUserId = getCurrentUserId();
    
    // Get filters from URL
    $status = sanitizeInput($_GET['status'] ?? '');
    $type = sanitizeInput($_GET['type'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    
    // Build filters
    $filters = ['user_id' => $currentUserId];
    
    if (!empty($status)) {
        $filters['is_read'] = ($status === 'read') ? 1 : 0;
    }
    
    if (!empty($type)) {
        $filters['type'] = $type;
    }
    
    // Get notifications and total count
    $notifications = $notification->getAll($filters, $page, $perPage);
    $totalCount = $notification->getTotalCount($filters);
    $totalPages = ceil($totalCount / $perPage);
    
    // Get unread count
    $unreadCount = $notification->getTotalCount(['user_id' => $currentUserId, 'is_read' => 0]);
    
    // Handle actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        $action = sanitizeInput($_POST['action'] ?? '');
        
        if ($action === 'mark_read') {
            $notificationId = (int)($_POST['notification_id'] ?? 0);
            if ($notificationId) {
                $result = $notification->markAsRead($notificationId, $currentUserId);
                if ($result) {
                    $success = 'ทำเครื่องหมายว่าอ่านแล้ว';
                    // Refresh page to update counts
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                }
            }
        } elseif ($action === 'mark_all_read') {
            $result = $notification->markAllAsRead($currentUserId);
            if ($result) {
                $success = 'ทำเครื่องหมายทั้งหมดว่าอ่านแล้ว';
                // Refresh page to update counts
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }
        } elseif ($action === 'delete') {
            $notificationId = (int)($_POST['notification_id'] ?? 0);
            if ($notificationId) {
                $result = $notification->delete($notificationId, $currentUserId);
                if ($result) {
                    $success = 'ลบการแจ้งเตือนแล้ว';
                    // Refresh page
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                }
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Notifications error: " . $e->getMessage());
    $notifications = [];
    $totalCount = 0;
    $totalPages = 0;
    $unreadCount = 0;
    $error = 'ไม่สามารถโหลดการแจ้งเตือนได้';
}
?>

<div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="<?= BASE_URL ?>/staff/" 
                   class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-bell mr-3"></i>การแจ้งเตือน
                        <?php if ($unreadCount > 0): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                            <?= number_format($unreadCount) ?>
                        </span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-gray-600 mt-1">จัดการการแจ้งเตือนและข้อความต่าง ๆ</p>
                </div>
            </div>
            
            <?php if ($unreadCount > 0): ?>
            <form method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" 
                        onclick="return confirm('ทำเครื่องหมายการแจ้งเตือนทั้งหมดว่าอ่านแล้วหรือไม่?')"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-check-double mr-2"></i>อ่านทั้งหมด
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">เกิดข้อผิดพลาด</h3>
                <div class="mt-2 text-sm text-red-700">
                    <?= $error ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">สำเร็จ</h3>
                <div class="mt-2 text-sm text-green-700">
                    <?= $success ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-bell text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ทั้งหมด</dt>
                            <dd class="text-lg font-semibold text-gray-900"><?= number_format($totalCount) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-envelope text-2xl text-red-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ยังไม่อ่าน</dt>
                            <dd class="text-lg font-semibold text-gray-900"><?= number_format($unreadCount) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-envelope-open text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">อ่านแล้ว</dt>
                            <dd class="text-lg font-semibold text-gray-900"><?= number_format($totalCount - $unreadCount) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                    <select name="status" 
                            id="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">ทุกสถานะ</option>
                        <option value="unread" <?= $status === 'unread' ? 'selected' : '' ?>>ยังไม่อ่าน</option>
                        <option value="read" <?= $status === 'read' ? 'selected' : '' ?>>อ่านแล้ว</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">ประเภท</label>
                    <select name="type" 
                            id="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">ทุกประเภท</option>
                        <?php 
                        global $NOTIF_TYPE_NAMES;
                        foreach ($NOTIF_TYPE_NAMES as $typeKey => $typeName): ?>
                        <option value="<?= $typeKey ?>" <?= $type === $typeKey ? 'selected' : '' ?>>
                            <?= $typeName ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-1"></i>กรอง
                    </button>
                    <a href="<?= BASE_URL ?>/staff/notifications.php" 
                       class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-times mr-1"></i>ล้าง
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>รายการการแจ้งเตือน
                <?php if ($totalCount > 0): ?>
                <span class="text-sm text-gray-500 font-normal">
                    (<?= number_format($totalCount) ?> รายการ)
                </span>
                <?php endif; ?>
            </h3>
        </div>

        <?php if (!empty($notifications)): ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($notifications as $notif): ?>
            <div class="px-6 py-4 hover:bg-gray-50 <?= $notif['is_read'] ? '' : 'bg-blue-50' ?>">
                <div class="flex items-start space-x-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <?php
                        $typeIcons = [
                            NOTIF_TYPE_INFO => 'fas fa-info-circle text-blue-500',
                            NOTIF_TYPE_SUCCESS => 'fas fa-check-circle text-green-500',
                            NOTIF_TYPE_WARNING => 'fas fa-exclamation-triangle text-yellow-500',
                            NOTIF_TYPE_ERROR => 'fas fa-times-circle text-red-500'
                        ];
                        ?>
                        <div class="w-8 h-8 flex items-center justify-center rounded-full <?= $notif['is_read'] ? 'bg-gray-100' : 'bg-white shadow-sm' ?>">
                            <i class="<?= $typeIcons[$notif['type']] ?? 'fas fa-bell text-gray-500' ?>"></i>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 <?= $notif['is_read'] ? '' : 'font-semibold' ?>">
                                    <?= htmlspecialchars($notif['title']) ?>
                                    <?php if (!$notif['is_read']): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                                        ใหม่
                                    </span>
                                    <?php endif; ?>
                                </h4>
                                <p class="mt-1 text-sm text-gray-700">
                                    <?= htmlspecialchars($notif['message']) ?>
                                </p>
                                <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                    <span>
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= formatThaiDate($notif['created_at'], true) ?>
                                    </span>
                                    <?php if (!empty($notif['link'])): ?>
                                    <a href="<?= BASE_URL . htmlspecialchars($notif['link']) ?>" 
                                       class="text-blue-600 hover:text-blue-500">
                                        <i class="fas fa-external-link-alt mr-1"></i>ดูรายละเอียด
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2 ml-4">
                                <?php if (!$notif['is_read']): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                    <button type="submit" 
                                            title="ทำเครื่องหมายว่าอ่านแล้ว"
                                            class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-check text-sm"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <button onclick="confirmDelete(<?= $notif['id'] ?>)" 
                                        title="ลบการแจ้งเตือน"
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
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
                        แสดง <span class="font-medium"><?= number_format(($page - 1) * $perPage + 1) ?></span> ถึง 
                        <span class="font-medium"><?= number_format(min($page * $perPage, $totalCount)) ?></span> จาก 
                        <span class="font-medium"><?= number_format($totalCount) ?></span> รายการ
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 <?= $i == $page ? 'bg-blue-50 border-blue-500 text-blue-600' : '' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
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

        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-bell-slash text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่มีการแจ้งเตือน</h3>
            <?php if (!empty($status) || !empty($type)): ?>
            <p class="text-gray-500 mb-4">
                ลองปรับเปลี่ยนเงื่อนไขการกรองหรือ
                <a href="<?= BASE_URL ?>/staff/notifications.php" class="text-blue-600 hover:text-blue-500">ล้างตัวกรอง</a>
            </p>
            <?php else: ?>
            <p class="text-gray-500 mb-4">
                ยังไม่มีการแจ้งเตือนในขณะนี้
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900">ยืนยันการลบ</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    คุณแน่ใจหรือไม่ที่จะลบการแจ้งเตือนนี้?
                    การกระทำนี้ไม่สามารถย้อนกลับได้
                </p>
            </div>
            <div class="flex justify-center space-x-4 px-4 py-3">
                <button id="confirmDelete" 
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    ลบ
                </button>
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteNotificationId = null;

function confirmDelete(id) {
    deleteNotificationId = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    deleteNotificationId = null;
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteNotificationId) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'notification_id';
        idInput.value = deleteNotificationId;
        form.appendChild(idInput);
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        form.appendChild(actionInput);
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = '<?= generateCSRFToken() ?>';
        form.appendChild(tokenInput);
        
        document.body.appendChild(form);
        form.submit();
    }
});

// Close modal on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Auto-refresh unread count (optional)
setTimeout(function() {
    // You could implement WebSocket or polling here for real-time updates
}, 30000);
</script>

<?php require_once '../includes/footer.php'; ?>