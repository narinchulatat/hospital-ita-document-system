<?php
$pageTitle = 'ดูข้อมูลผู้ใช้';
require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
    header('Location: index.php');
    exit;
}

try {
    $user = new User();
    $userData = $user->getById($userId);
    
    if (!$userData) {
        $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
        header('Location: index.php');
        exit;
    }
    
    // Get user activity logs
    $activityLog = new ActivityLog();
    $activities = $activityLog->getUserActivities($userId, 1, 10);
    
} catch (Exception $e) {
    error_log("User view error: " . $e->getMessage());
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
    header('Location: index.php');
    exit;
}
?>

<div class="max-w-6xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="index.php" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?>
                    </h1>
                    <p class="text-gray-600">ข้อมูลผู้ใช้ในระบบ</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="edit.php?id=<?= $userData['id'] ?>" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>แก้ไข
                </a>
                <?php if ($userData['id'] != $_SESSION['user_id']): ?>
                <a href="delete.php?id=<?= $userData['id'] ?>" 
                   class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors btn-delete"
                   data-title="ยืนยันการลบผู้ใช้"
                   data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ <?= htmlspecialchars($userData['username']) ?>?">
                    <i class="fas fa-trash mr-2"></i>ลบ
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">ข้อมูลพื้นฐาน</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">ชื่อผู้ใช้</label>
                            <p class="mt-1 text-lg text-gray-900"><?= htmlspecialchars($userData['username']) ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">อีเมล</label>
                            <p class="mt-1 text-lg text-gray-900">
                                <a href="mailto:<?= htmlspecialchars($userData['email']) ?>" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($userData['email']) ?>
                                </a>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">ชื่อ</label>
                            <p class="mt-1 text-lg text-gray-900"><?= htmlspecialchars($userData['first_name']) ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">นามสกุล</label>
                            <p class="mt-1 text-lg text-gray-900"><?= htmlspecialchars($userData['last_name']) ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">หมายเลขโทรศัพท์</label>
                            <p class="mt-1 text-lg text-gray-900">
                                <?= $userData['phone'] ? htmlspecialchars($userData['phone']) : '-' ?>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">แผนก/หน่วยงาน</label>
                            <p class="mt-1 text-lg text-gray-900">
                                <?= $userData['department'] ? htmlspecialchars($userData['department']) : '-' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">กิจกรรมล่าสุด</h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($activities)): ?>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <?php foreach ($activities as $index => $activity): ?>
                            <li>
                                <div class="relative pb-8">
                                    <?php if ($index < count($activities) - 1): ?>
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <?php endif; ?>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                <?= getActivityColor($activity['action']) ?>">
                                                <i class="fas fa-<?= getActivityIcon($activity['action']) ?> text-white text-sm"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <?= getActivityDescription($activity['action'], $activity['table_name']) ?>
                                                    <?php if ($activity['details']): ?>
                                                    <span class="text-gray-400">- <?= htmlspecialchars($activity['details']) ?></span>
                                                    <?php endif; ?>
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
                    
                    <div class="mt-6 text-center">
                        <a href="../logs/activities.php?user_id=<?= $userData['id'] ?>" 
                           class="text-blue-600 hover:text-blue-800 text-sm">
                            ดูกิจกรรมทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 text-center py-8">ไม่มีกิจกรรม</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">สถานะ</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">สถานะการใช้งาน</label>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                <?= $userData['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <i class="fas fa-circle mr-2 text-xs"></i>
                                <?= $userData['status'] === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน' ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">บทบาท</label>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-user-tag mr-2"></i>
                                <?= htmlspecialchars($userData['role_name']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">เข้าสู่ระบบล่าสุด</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?= $userData['last_login'] ? formatThaiDate($userData['last_login'], true) : 'ไม่เคยเข้าใช้' ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">สร้างเมื่อ</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?= formatThaiDate($userData['created_at'], true) ?>
                        </p>
                    </div>
                    
                    <?php if ($userData['updated_at']): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">แก้ไขล่าสุด</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?= formatThaiDate($userData['updated_at'], true) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">การดำเนินการ</h3>
                </div>
                <div class="p-6 space-y-3">
                    <?php if ($userData['status'] === 'active'): ?>
                    <button onclick="toggleUserStatus(<?= $userData['id'] ?>, 'inactive')" 
                            class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors">
                        <i class="fas fa-user-slash mr-2"></i>ปิดใช้งาน
                    </button>
                    <?php else: ?>
                    <button onclick="toggleUserStatus(<?= $userData['id'] ?>, 'active')" 
                            class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-user-check mr-2"></i>เปิดใช้งาน
                    </button>
                    <?php endif; ?>
                    
                    <button onclick="resetPassword(<?= $userData['id'] ?>)" 
                            class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-key mr-2"></i>รีเซ็ตรหัสผ่าน
                    </button>
                    
                    <button onclick="sendNotification(<?= $userData['id'] ?>)" 
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>ส่งการแจ้งเตือน
                    </button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">สถิติ</h3>
                </div>
                <div class="p-6 space-y-4">
                    <?php
                    // Get user statistics
                    $loginCount = $activityLog->getUserLoginCount($userId);
                    $documentCount = 0; // This would come from Document class
                    $lastActivity = $activityLog->getUserLastActivity($userId);
                    ?>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">จำนวนครั้งที่เข้าใช้</span>
                        <span class="text-sm font-medium text-gray-900"><?= number_format($loginCount) ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">เอกสารที่อัปโหลด</span>
                        <span class="text-sm font-medium text-gray-900"><?= number_format($documentCount) ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">กิจกรรมล่าสุด</span>
                        <span class="text-sm font-medium text-gray-900">
                            <?= $lastActivity ? formatTimeAgo($lastActivity['created_at']) : 'ไม่มี' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleUserStatus(userId, status) {
    const statusText = status === 'active' ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
    
    Swal.fire({
        title: `ยืนยันการ${statusText}`,
        text: `คุณต้องการ${statusText}ผู้ใช้นี้หรือไม่?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'actions.php',
                method: 'POST',
                data: {
                    action: 'toggle_status',
                    user_id: userId,
                    status: status,
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

function resetPassword(userId) {
    Swal.fire({
        title: 'รีเซ็ตรหัสผ่าน',
        text: 'คุณต้องการรีเซ็ตรหัสผ่านของผู้ใช้นี้หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'รีเซ็ต',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'actions.php',
                method: 'POST',
                data: {
                    action: 'reset_password',
                    user_id: userId,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'รีเซ็ตรหัสผ่านสำเร็จ',
                            html: `รหัสผ่านใหม่: <strong class="text-blue-600">${response.new_password}</strong><br>
                                   <small class="text-gray-500">กรุณาแจ้งรหัสผ่านใหม่ให้กับผู้ใช้</small>`,
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        });
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

function sendNotification(userId) {
    Swal.fire({
        title: 'ส่งการแจ้งเตือน',
        input: 'textarea',
        inputLabel: 'ข้อความ',
        inputPlaceholder: 'กรอกข้อความที่ต้องการส่ง...',
        inputAttributes: {
            'aria-label': 'ข้อความการแจ้งเตือน'
        },
        showCancelButton: true,
        confirmButtonText: 'ส่ง',
        cancelButtonText: 'ยกเลิก',
        inputValidator: (value) => {
            if (!value) {
                return 'กรุณากรอกข้อความ'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'actions.php',
                method: 'POST',
                data: {
                    action: 'send_notification',
                    user_id: userId,
                    message: result.value,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('ส่งการแจ้งเตือนเรียบร้อยแล้ว', 'success');
                    } else {
                        showAlert(response.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                },
                error: function() {
                    showAlert('เกิดข้อผิดพลาดในการส่งการแจ้งเตือน', 'error');
                }
            });
        }
    });
}
</script>

<?php
function getActivityColor($action) {
    $colors = [
        ACTION_CREATE => 'bg-green-500',
        ACTION_UPDATE => 'bg-blue-500',
        ACTION_DELETE => 'bg-red-500',
        ACTION_LOGIN => 'bg-green-500',
        ACTION_LOGOUT => 'bg-gray-500',
        ACTION_APPROVE => 'bg-green-500',
        ACTION_REJECT => 'bg-red-500',
        ACTION_DOWNLOAD => 'bg-blue-500'
    ];
    return $colors[$action] ?? 'bg-gray-500';
}

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

function formatTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'เมื่อสักครู่';
    if ($time < 3600) return floor($time / 60) . ' นาทีที่แล้ว';
    if ($time < 86400) return floor($time / 3600) . ' ชั่วโมงที่แล้ว';
    if ($time < 2592000) return floor($time / 86400) . ' วันที่แล้ว';
    if ($time < 31536000) return floor($time / 2592000) . ' เดือนที่แล้ว';
    
    return floor($time / 31536000) . ' ปีที่แล้ว';
}
?>

<?php require_once '../includes/footer.php'; ?>