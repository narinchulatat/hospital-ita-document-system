<?php
$pageTitle = 'จัดการโปรไฟล์';
require_once '../includes/header.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('โทเค็นความปลอดภัยไม่ถูกต้อง');
        }
        
        $action = $_POST['action'] ?? '';
        $currentUserId = getCurrentUserId();
        
        if ($action === 'update_profile') {
            // Update profile information
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            
            if (empty($firstName) || empty($lastName) || empty($email)) {
                throw new Exception('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('รูปแบบอีเมลไม่ถูกต้อง');
            }
            
            $db = Database::getInstance();
            
            // Check if email is already used by another user
            $existingUser = $db->fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$email, $currentUserId]
            );
            
            if ($existingUser) {
                throw new Exception('อีเมลนี้ถูกใช้โดยผู้ใช้คนอื่นแล้ว');
            }
            
            // Update user data
            $updateData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->update('users', $updateData, ['id' => $currentUserId]);
            
            // Update session data
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            
            logActivity(ACTION_UPDATE, 'users', $currentUserId);
            
            $success = 'อัปเดตข้อมูลโปรไฟล์สำเร็จ';
            
        } elseif ($action === 'change_password') {
            // Change password
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('กรุณากรอกข้อมูลรหัสผ่านให้ครบถ้วน');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('รหัสผ่านใหม่และการยืนยันไม่ตรงกัน');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
            }
            
            $db = Database::getInstance();
            
            // Verify current password
            $user = $db->fetch("SELECT password FROM users WHERE id = ?", [$currentUserId]);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                throw new Exception('รหัสผ่านปัจจุบันไม่ถูกต้อง');
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->update('users', 
                ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')], 
                ['id' => $currentUserId]
            );
            
            logActivity(ACTION_UPDATE, 'users', $currentUserId, null, ['action' => 'password_changed']);
            
            $success = 'เปลี่ยนรหัสผ่านสำเร็จ';
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get current user data
try {
    $db = Database::getInstance();
    $userData = $db->fetch(
        "SELECT u.*, r.name as role_name 
         FROM users u 
         JOIN roles r ON u.role_id = r.id 
         WHERE u.id = ?",
        [getCurrentUserId()]
    );
    
    if (!$userData) {
        throw new Exception('ไม่พบข้อมูลผู้ใช้');
    }
    
    // Get recent login history
    $loginHistory = $db->fetchAll(
        "SELECT ip_address, user_agent, created_at 
         FROM activity_logs 
         WHERE user_id = ? AND action = 'login' 
         ORDER BY created_at DESC 
         LIMIT 10",
        [getCurrentUserId()]
    );
    
} catch (Exception $e) {
    error_log("Profile page error: " . $e->getMessage());
    $userData = [];
    $loginHistory = [];
}
?>

<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            <i class="fas fa-user mr-3"></i>จัดการโปรไฟล์
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            จัดการข้อมูลส่วนตัวและการตั้งค่าบัญชีของคุณ
        </p>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($success) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Profile Form -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-id-card mr-2"></i>ข้อมูลส่วนตัว
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" id="profileForm">
                        <?= getCSRFTokenInput() ?>
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    ชื่อ <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>"
                                       class="form-input"
                                       required>
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    นามสกุล <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>"
                                       class="form-input"
                                       required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    อีเมล <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                                       class="form-input"
                                       required>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    เบอร์โทรศัพท์
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?= htmlspecialchars($userData['phone'] ?? '') ?>"
                                       class="form-input">
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save mr-2"></i>บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-lock mr-2"></i>เปลี่ยนรหัสผ่าน
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" id="passwordForm">
                        <?= getCSRFTokenInput() ?>
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="space-y-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    รหัสผ่านปัจจุบัน <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="form-input"
                                       required>
                            </div>
                            
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    รหัสผ่านใหม่ <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="form-input"
                                       minlength="6"
                                       required>
                                <p class="mt-1 text-sm text-gray-500">รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</p>
                            </div>
                            
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    ยืนยันรหัสผ่านใหม่ <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-input"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-warning">
                                <i class="fas fa-key mr-2"></i>เปลี่ยนรหัสผ่าน
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Account Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-info-circle mr-2"></i>ข้อมูลบัญชี
                    </h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ชื่อผู้ใช้</dt>
                            <dd class="text-sm text-gray-900"><?= htmlspecialchars($userData['username'] ?? '') ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ตำแหน่ง</dt>
                            <dd class="text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($userData['role_name'] ?? '') ?>
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">สถานะ</dt>
                            <dd class="text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle mr-1"></i>ใช้งาน
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">วันที่สมัครสมาชิก</dt>
                            <dd class="text-sm text-gray-900"><?= formatThaiDate($userData['created_at'] ?? '') ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">อัปเดตล่าสุด</dt>
                            <dd class="text-sm text-gray-900"><?= formatThaiDate($userData['updated_at'] ?? '') ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Recent Login History -->
            <?php if (!empty($loginHistory)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-history mr-2"></i>ประวัติการเข้าสู่ระบบ
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <?php foreach (array_slice($loginHistory, 0, 5) as $login): ?>
                        <div class="flex items-start space-x-3 text-sm">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-sign-in-alt text-gray-400"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-gray-900 font-medium">
                                    <?= formatThaiDate($login['created_at'], true) ?>
                                </p>
                                <p class="text-gray-500 text-xs">
                                    IP: <?= htmlspecialchars($login['ip_address']) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($loginHistory) > 5): ?>
                        <div class="text-center pt-3">
                            <button onclick="showAllLoginHistory()" class="text-blue-600 hover:text-blue-500 text-sm">
                                ดูทั้งหมด (<?= count($loginHistory) ?> รายการ)
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Links -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-external-link-alt mr-2"></i>ลิงก์ด่วน
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <a href="<?= BASE_URL ?>/approver/profile/settings.php" 
                           class="flex items-center text-sm text-gray-700 hover:text-blue-600">
                            <i class="fas fa-cog mr-3 text-gray-400"></i>ตั้งค่าทั่วไป
                        </a>
                        <a href="<?= BASE_URL ?>/approver/profile/notifications.php" 
                           class="flex items-center text-sm text-gray-700 hover:text-blue-600">
                            <i class="fas fa-bell mr-3 text-gray-400"></i>การแจ้งเตือน
                        </a>
                        <a href="<?= BASE_URL ?>/approver/documents/history.php" 
                           class="flex items-center text-sm text-gray-700 hover:text-blue-600">
                            <i class="fas fa-history mr-3 text-gray-400"></i>ประวัติการอนุมัติ
                        </a>
                        <a href="<?= BASE_URL ?>/approver/reports/" 
                           class="flex items-center text-sm text-gray-700 hover:text-blue-600">
                            <i class="fas fa-chart-bar mr-3 text-gray-400"></i>รายงาน
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Password confirmation validation
    $('#confirm_password').on('input', function() {
        const newPassword = $('#new_password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && newPassword !== confirmPassword) {
            this.setCustomValidity('รหัสผ่านไม่ตรงกัน');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Clear password fields after successful password change
    <?php if ($success && strpos($success, 'รหัสผ่าน') !== false): ?>
    $('#passwordForm')[0].reset();
    <?php endif; ?>
});

function showAllLoginHistory() {
    const allHistory = <?= json_encode($loginHistory) ?>;
    
    let html = '<div class="space-y-3">';
    allHistory.forEach(function(login) {
        const date = new Date(login.created_at);
        const formattedDate = date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        html += `
            <div class="flex items-start space-x-3 text-sm">
                <div class="flex-shrink-0 mt-1">
                    <i class="fas fa-sign-in-alt text-gray-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-gray-900 font-medium">${formattedDate}</p>
                    <p class="text-gray-500 text-xs">IP: ${login.ip_address}</p>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    Swal.fire({
        title: 'ประวัติการเข้าสู่ระบบทั้งหมด',
        html: html,
        width: '600px',
        confirmButtonText: 'ปิด'
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>