<?php
require_once '../includes/header.php';

// Check permission
if (!hasPermission('users.view')) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

// Get user ID
$user_id = (int)($_GET['id'] ?? 0);
if ($user_id <= 0) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

// Initialize database and classes
$db = new Database();

// Get user data with detailed information
$user_sql = "SELECT u.*, r.name as role_name, r.display_name as role_display_name, 
                   d.name as department_name, d.description as department_description,
                   creator.first_name as creator_first_name, creator.last_name as creator_last_name,
                   updater.first_name as updater_first_name, updater.last_name as updater_last_name
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN departments d ON u.department_id = d.id 
            LEFT JOIN users creator ON u.created_by = creator.id
            LEFT JOIN users updater ON u.updated_by = updater.id
            WHERE u.id = :id";
$user_stmt = $db->query($user_sql, [':id' => $user_id]);
$user_data = $user_stmt->fetch();

if (!$user_data) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

$pageTitle = 'รายละเอียดผู้ใช้';
$pageSubtitle = $user_data['first_name'] . ' ' . $user_data['last_name'];

// Get user's recent activities
$activities_sql = "SELECT al.*, u.first_name, u.last_name 
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.user_id = :user_id 
                  ORDER BY al.created_at DESC 
                  LIMIT 10";
$activities_stmt = $db->query($activities_sql, [':user_id' => $user_id]);
$activities = $activities_stmt->fetchAll();

// Get user's document statistics
$doc_stats_sql = "SELECT 
                    COUNT(*) as total_documents,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_documents,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_documents,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_documents
                  FROM documents 
                  WHERE created_by = :user_id OR assigned_to = :user_id";
$doc_stats_stmt = $db->query($doc_stats_sql, [':user_id' => $user_id]);
$doc_stats = $doc_stats_stmt->fetch();

// Get login history
$login_history_sql = "SELECT * FROM user_login_history 
                     WHERE user_id = :user_id 
                     ORDER BY login_time DESC 
                     LIMIT 5";
$login_history_stmt = $db->query($login_history_sql, [':user_id' => $user_id]);
$login_history = $login_history_stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="<?= BASE_URL ?>/admin/" class="text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="<?= BASE_URL ?>/admin/users/" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">จัดการผู้ใช้</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-gray-500 md:ml-2">รายละเอียดผู้ใช้</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
            <div class="flex space-x-3">
                <?php if (hasPermission('users.edit')): ?>
                <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $user_id ?>" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    แก้ไข
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/admin/users/" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    กลับ
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center">
                                <span class="text-2xl font-bold text-gray-700">
                                    <?= strtoupper(substr($user_data['first_name'], 0, 1) . substr($user_data['last_name'], 0, 1)) ?>
                                </span>
                            </div>
                        </div>
                        <div class="ml-4 text-white">
                            <h3 class="text-xl font-bold"><?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?></h3>
                            <p class="text-blue-100">@<?= htmlspecialchars($user_data['username']) ?></p>
                            <?php
                            $status_colors = [
                                'active' => 'bg-green-500',
                                'inactive' => 'bg-yellow-500',
                                'banned' => 'bg-red-500'
                            ];
                            $status_labels = [
                                'active' => 'ใช้งาน',
                                'inactive' => 'ระงับ',
                                'banned' => 'ห้ามใช้งาน'
                            ];
                            $status_color = $status_colors[$user_data['status']] ?? 'bg-gray-500';
                            $status_label = $status_labels[$user_data['status']] ?? $user_data['status'];
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white <?= $status_color ?> mt-2">
                                <?= $status_label ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">อีเมล</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user_data['email']) ?></dd>
                    </div>
                    
                    <?php if ($user_data['phone']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">โทรศัพท์</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user_data['phone']) ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">บทบาท</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($user_data['role_display_name'] ?? $user_data['role_name'] ?? 'ไม่ระบุ') ?>
                            </span>
                        </dd>
                    </div>
                    
                    <?php if ($user_data['department_name']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">หน่วยงาน</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user_data['department_name']) ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user_data['position']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ตำแหน่ง</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user_data['position']) ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user_data['employee_id']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">รหัสพนักงาน</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user_data['employee_id']) ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">สร้างเมื่อ</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= formatThaiDate($user_data['created_at'], true) ?></dd>
                    </div>
                    
                    <?php if ($user_data['updated_at']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">แก้ไขล่าสุด</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= formatThaiDate($user_data['updated_at'], true) ?></dd>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">การกระทำ</h4>
                <div class="space-y-3">
                    <?php if (hasPermission('users.edit')): ?>
                    <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $user_id ?>" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        แก้ไขข้อมูล
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('users.password') && $user_data['id'] != $_SESSION['user_id']): ?>
                    <button type="button" 
                            onclick="resetPassword(<?= $user_id ?>)"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        <i class="fas fa-key mr-2"></i>
                        รีเซ็ตรหัสผ่าน
                    </button>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('users.delete') && $user_data['id'] != $_SESSION['user_id']): ?>
                    <a href="<?= BASE_URL ?>/admin/users/delete.php?id=<?= $user_id ?>" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200 btn-delete"
                       data-title="ยืนยันการลบผู้ใช้"
                       data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ <?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?>?">
                        <i class="fas fa-trash mr-2"></i>
                        ลบผู้ใช้
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-alt text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">เอกสารทั้งหมด</dt>
                                <dd class="text-lg font-medium text-gray-900"><?= number_format($doc_stats['total_documents'] ?? 0) ?></dd>
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
                                <dt class="text-sm font-medium text-gray-500 truncate">อนุมัติแล้ว</dt>
                                <dd class="text-lg font-medium text-gray-900"><?= number_format($doc_stats['approved_documents'] ?? 0) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">รอดำเนินการ</dt>
                                <dd class="text-lg font-medium text-gray-900"><?= number_format($doc_stats['pending_documents'] ?? 0) ?></dd>
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
                                <dt class="text-sm font-medium text-gray-500 truncate">ไม่อนุมัติ</dt>
                                <dd class="text-lg font-medium text-gray-900"><?= number_format($doc_stats['rejected_documents'] ?? 0) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">กิจกรรมล่าสุด</h3>
                </div>
                <div class="px-6 py-4">
                    <?php if (!empty($activities)): ?>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <?php foreach ($activities as $index => $activity): ?>
                            <li>
                                <div class="relative pb-8">
                                    <?php if ($index < count($activities) - 1): ?>
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    <?php endif; ?>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-user text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <?= htmlspecialchars($activity['description']) ?>
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
                    <p class="text-gray-500 text-center py-8">ไม่พบกิจกรรมล่าสุด</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Login History -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">ประวัติการเข้าสู่ระบบ</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php if (!empty($login_history)): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เวลา</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Agent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($login_history as $login): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= formatThaiDate($login['login_time'], true) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($login['ip_address']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    <?= htmlspecialchars($login['user_agent']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($login['success']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        สำเร็จ
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ล้มเหลว
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-gray-500 text-center py-8">ไม่พบประวัติการเข้าสู่ระบบ</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">ข้อมูลระบบ</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">สร้างโดย</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($user_data['creator_first_name']): ?>
                                <?= htmlspecialchars($user_data['creator_first_name'] . ' ' . $user_data['creator_last_name']) ?>
                                <?php else: ?>
                                <span class="text-gray-400">ไม่ระบุ</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">แก้ไขโดย</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($user_data['updater_first_name']): ?>
                                <?= htmlspecialchars($user_data['updater_first_name'] . ' ' . $user_data['updater_last_name']) ?>
                                <?php else: ?>
                                <span class="text-gray-400">ไม่ระบุ</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">วันที่สร้าง</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= formatThaiDate($user_data['created_at'], true) ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">วันที่แก้ไขล่าสุด</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($user_data['updated_at']): ?>
                                <?= formatThaiDate($user_data['updated_at'], true) ?>
                                <?php else: ?>
                                <span class="text-gray-400">ไม่เคยแก้ไข</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetPassword(userId) {
    Swal.fire({
        title: 'รีเซ็ตรหัสผ่าน',
        text: 'คุณต้องการรีเซ็ตรหัสผ่านให้ผู้ใช้นี้หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3B82F6',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'ใช่, รีเซ็ต',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading('กำลังรีเซ็ตรหัสผ่าน...');
            
            $.ajax({
                url: '<?= BASE_URL ?>/admin/api/reset-password.php',
                method: 'POST',
                data: {
                    user_id: userId,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        Swal.fire({
                            title: 'สำเร็จ!',
                            html: `รีเซ็ตรหัสผ่านเรียบร้อยแล้ว<br>รหัสผ่านใหม่: <strong>${response.new_password}</strong>`,
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        });
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด!', response.message || 'ไม่สามารถรีเซ็ตรหัสผ่านได้', 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
                }
            });
        }
    });
}

$(document).ready(function() {
    // Initialize tooltips if any
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