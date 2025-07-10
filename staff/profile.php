<?php
/**
 * Staff Profile Management Page
 * Manage profile information and change password
 */

$pageTitle = 'จัดการโปรไฟล์';
require_once '../includes/header.php';

// Require staff role
requireRole(ROLE_STAFF);

$error = '';
$success = '';
$user = null;

try {
    $userObj = new User();
    $currentUserId = getCurrentUserId();
    
    // Get current user data
    $user = $userObj->getById($currentUserId);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
} catch (Exception $e) {
    error_log("Profile load error: " . $e->getMessage());
    $error = 'ไม่สามารถโหลดข้อมูลโปรไฟล์ได้';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        $action = sanitizeInput($_POST['action'] ?? '');
        
        if ($action === 'update_profile') {
            // Update profile information
            $firstName = sanitizeInput($_POST['first_name'] ?? '');
            $lastName = sanitizeInput($_POST['last_name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $phone = sanitizeInput($_POST['phone'] ?? '');
            $department = sanitizeInput($_POST['department'] ?? '');
            $position = sanitizeInput($_POST['position'] ?? '');
            
            // Validation
            $errors = [];
            
            if (empty($firstName)) {
                $errors[] = 'กรุณาระบุชื่อ';
            }
            
            if (empty($lastName)) {
                $errors[] = 'กรุณาระบุนามสกุล';
            }
            
            if (empty($email)) {
                $errors[] = 'กรุณาระบุอีเมล';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
            }
            
            // Check if email is already used by another user
            if ($email !== $user['email']) {
                $existingUser = $userObj->getByEmail($email);
                if ($existingUser && $existingUser['id'] != $currentUserId) {
                    $errors[] = 'อีเมลนี้ถูกใช้งานแล้ว';
                }
            }
            
            if (!empty($errors)) {
                $error = implode('<br>', $errors);
            } else {
                // Update user profile
                $updateData = [
                    'id' => $currentUserId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'department' => $department,
                    'position' => $position,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $result = $userObj->update($updateData);
                
                if ($result) {
                    // Log activity
                    $activityLog = new ActivityLog();
                    $activityLog->log(ACTION_UPDATE, 'profile', $currentUserId, 'Updated profile information');
                    
                    $success = 'อัปเดตข้อมูลโปรไฟล์เรียบร้อยแล้ว';
                    
                    // Refresh user data
                    $user = $userObj->getById($currentUserId);
                } else {
                    $error = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
                }
            }
            
        } elseif ($action === 'change_password') {
            // Change password
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            $errors = [];
            
            if (empty($currentPassword)) {
                $errors[] = 'กรุณาระบุรหัสผ่านปัจจุบัน';
            }
            
            if (empty($newPassword)) {
                $errors[] = 'กรุณาระบุรหัสผ่านใหม่';
            }
            
            if (strlen($newPassword) < 6) {
                $errors[] = 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน';
            }
            
            // Verify current password
            if (!empty($currentPassword) && !password_verify($currentPassword, $user['password'])) {
                $errors[] = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
            }
            
            if (!empty($errors)) {
                $error = implode('<br>', $errors);
            } else {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $result = $userObj->update([
                    'id' => $currentUserId,
                    'password' => $hashedPassword,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                if ($result) {
                    // Log activity
                    $activityLog = new ActivityLog();
                    $activityLog->log(ACTION_UPDATE, 'password', $currentUserId, 'Changed password');
                    
                    $success = 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว';
                } else {
                    $error = 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน';
                }
            }
        }
        
    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        $error = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage();
    }
}

// Get activity log
try {
    $activityLog = new ActivityLog();
    $activities = $activityLog->getByUser($currentUserId, 1, 10);
} catch (Exception $e) {
    $activities = [];
}
?>

<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="<?= BASE_URL ?>/staff/" 
               class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-user mr-3"></i>จัดการโปรไฟล์
                </h1>
                <p class="text-gray-600 mt-1">แก้ไขข้อมูลส่วนตัวและเปลี่ยนรหัสผ่าน</p>
            </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-info-circle mr-2"></i>ข้อมูลส่วนตัว
                    </h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                ชื่อ <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? $user['first_name']) ?>"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                นามสกุล <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? $user['last_name']) ?>"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                อีเมล <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                เบอร์โทรศัพท์
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? $user['phone']) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">
                                แผนก
                            </label>
                            <input type="text" 
                                   id="department" 
                                   name="department" 
                                   value="<?= htmlspecialchars($_POST['department'] ?? $user['department']) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Position -->
                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-700 mb-1">
                                ตำแหน่ง
                            </label>
                            <input type="text" 
                                   id="position" 
                                   name="position" 
                                   value="<?= htmlspecialchars($_POST['position'] ?? $user['position']) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-key mr-2"></i>เปลี่ยนรหัสผ่าน
                    </h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="space-y-6">
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                รหัสผ่านปัจจุบัน <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                                รหัสผ่านใหม่ <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   required
                                   minlength="6"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                                ยืนยันรหัสผ่านใหม่ <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-key mr-2"></i>เปลี่ยนรหัสผ่าน
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Profile Summary -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-user-circle mr-2"></i>สรุปโปรไฟล์
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user text-3xl text-blue-600"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        </h4>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                        
                        <?php if (!empty($user['department']) || !empty($user['position'])): ?>
                        <div class="mt-4 space-y-1">
                            <?php if (!empty($user['position'])): ?>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($user['position']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($user['department'])): ?>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($user['department']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <dl class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">สถานะ</dt>
                                <dd class="text-green-600 font-medium">ใช้งาน</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">บทบาท</dt>
                                <dd class="text-gray-900">เจ้าหน้าที่</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">เข้าร่วมเมื่อ</dt>
                                <dd class="text-gray-900"><?= formatThaiDate($user['created_at']) ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <?php if (!empty($activities)): ?>
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-history mr-2"></i>กิจกรรมล่าสุด
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <?php
                                $iconClasses = [
                                    ACTION_CREATE => 'fas fa-plus text-green-500',
                                    ACTION_UPDATE => 'fas fa-edit text-yellow-500',
                                    ACTION_DELETE => 'fas fa-trash text-red-500',
                                    ACTION_LOGIN => 'fas fa-sign-in-alt text-blue-500',
                                    ACTION_LOGOUT => 'fas fa-sign-out-alt text-gray-500'
                                ];
                                ?>
                                <i class="<?= $iconClasses[$activity['action']] ?? 'fas fa-circle text-gray-500' ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    <?= htmlspecialchars($activity['description']) ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?= formatThaiDate($activity['created_at'], true) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('รหัสผ่านไม่ตรงกัน');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('new_password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword.value) {
        confirmPassword.dispatchEvent(new Event('input'));
    }
});

// Form validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังบันทึก...';
        submitBtn.disabled = true;
        
        // Re-enable button after a delay (in case validation fails)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>