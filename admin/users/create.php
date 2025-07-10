<?php
$pageTitle = 'เพิ่มผู้ใช้ใหม่';
$pageSubtitle = 'สร้างบัญชีผู้ใช้ใหม่ในระบบ';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

$errors = [];
$success = '';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token';
    } else {
        try {
            $user = new User();
            $database = Database::getInstance();
            
            // Validate input
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $role_id = intval($_POST['role_id'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $phone = trim($_POST['phone'] ?? '');
            $department = trim($_POST['department'] ?? '');
            
            // Validation
            if (empty($username)) $errors[] = 'กรุณากรอกชื่อผู้ใช้';
            if (empty($email)) $errors[] = 'กรุณากรอกอีเมล';
            if (empty($password)) $errors[] = 'กรุณากรอกรหัสผ่าน';
            if (empty($first_name)) $errors[] = 'กรุณากรอกชื่อ';
            if (empty($last_name)) $errors[] = 'กรุณากรอกนามสกุล';
            if ($role_id <= 0) $errors[] = 'กรุณาเลือกบทบาท';
            
            // Email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
            }
            
            // Password validation
            if (strlen($password) < 6) {
                $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
            }
            
            if ($password !== $confirm_password) {
                $errors[] = 'รหัสผ่านไม่ตรงกัน';
            }
            
            // Username validation
            if (strlen($username) < 3) {
                $errors[] = 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร';
            }
            
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
                $errors[] = 'ชื่อผู้ใช้ต้องประกอบด้วยตัวอักษร ตัวเลข - และ _ เท่านั้น';
            }
            
            // Check if username exists
            if (empty($errors)) {
                $existingUser = $database->fetch("SELECT id FROM users WHERE username = ?", [$username]);
                if ($existingUser) {
                    $errors[] = 'ชื่อผู้ใช้นี้ถูกใช้แล้ว';
                }
            }
            
            // Check if email exists
            if (empty($errors)) {
                $existingEmail = $database->fetch("SELECT id FROM users WHERE email = ?", [$email]);
                if ($existingEmail) {
                    $errors[] = 'อีเมลนี้ถูกใช้แล้ว';
                }
            }
            
            // Validate role exists
            if (empty($errors)) {
                $roleExists = $database->fetch("SELECT id FROM roles WHERE id = ?", [$role_id]);
                if (!$roleExists) {
                    $errors[] = 'บทบาทที่เลือกไม่ถูกต้อง';
                }
            }
            
            // Phone validation
            if ($phone && !preg_match('/^[0-9]{9,10}$/', $phone)) {
                $errors[] = 'หมายเลขโทรศัพท์ไม่ถูกต้อง';
            }
            
            if (empty($errors)) {
                // Create user
                $userId = $user->create([
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'role_id' => $role_id,
                    'status' => $status,
                    'phone' => $phone,
                    'department' => $department,
                    'created_by' => $_SESSION['user_id']
                ]);
                
                if ($userId) {
                    // Log activity
                    $activityLog = new ActivityLog();
                    $activityLog->log($_SESSION['user_id'], ACTION_CREATE, 'users', $userId, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                    
                    $_SESSION['success_message'] = 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว';
                    header('Location: ' . BASE_URL . '/admin/users/');
                    exit;
                } else {
                    $errors[] = 'เกิดข้อผิดพลาดในการสร้างผู้ใช้';
                }
            }
            
        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            $errors[] = 'เกิดข้อผิดพลาดในการสร้างผู้ใช้';
        }
    }
}

// Get roles for dropdown
try {
    $database = Database::getInstance();
    $roles = $database->fetchAll("SELECT * FROM roles ORDER BY name");
} catch (Exception $e) {
    $roles = [];
}
?>

<div class="max-w-4xl mx-auto">
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
                    <span class="text-gray-500">เพิ่มผู้ใช้ใหม่</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">ข้อมูลผู้ใช้ใหม่</h3>
            <p class="mt-1 text-sm text-gray-600">กรอกข้อมูลเพื่อสร้างบัญชีผู้ใช้ใหม่</p>
        </div>
        
        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
        <div class="m-6 mb-0 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <div class="flex">
                <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
                <div>
                    <h4 class="font-medium">เกิดข้อผิดพลาด:</h4>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="p-6 space-y-6 needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <!-- Basic Information -->
            <div>
                <h4 class="text-md font-medium text-gray-900 mb-4">ข้อมูลพื้นฐาน</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้ *</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               required
                               pattern="[a-zA-Z0-9_-]{3,}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">อย่างน้อย 3 ตัวอักษร ใช้ได้เฉพาะ a-z, A-Z, 0-9, _, -</p>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">อีเมล *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required
                               data-validate="email"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">ชื่อ *</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                               required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">นามสกุล *</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                               required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Security -->
            <div>
                <h4 class="text-md font-medium text-gray-900 mb-4">ความปลอดภัย</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน *</label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   minlength="6"
                                   class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" 
                                    class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    data-target="#password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">อย่างน้อย 6 ตัวอักษร</p>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">ยืนยันรหัสผ่าน *</label>
                        <div class="relative">
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required
                                   data-confirm="#password"
                                   class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" 
                                    class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    data-target="#confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Role and Status -->
            <div>
                <h4 class="text-md font-medium text-gray-900 mb-4">บทบาทและสถานะ</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Role -->
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700">บทบาท *</label>
                        <select id="role_id" 
                                name="role_id" 
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">เลือกบทบาท</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= (isset($_POST['role_id']) && $_POST['role_id'] == $role['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['display_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">สถานะ</label>
                        <select id="status" 
                                name="status" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="active" <?= (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : 'selected' ?>>ใช้งาน</option>
                            <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                            <option value="pending" <?= (isset($_POST['status']) && $_POST['status'] === 'pending') ? 'selected' : '' ?>>รออนุมัติ</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div>
                <h4 class="text-md font-medium text-gray-900 mb-4">ข้อมูลเพิ่มเติม</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">เบอร์โทรศัพท์</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                               pattern="[0-9]{9,10}"
                               data-validate="phone"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">ตัวเลข 9-10 หลัก</p>
                    </div>
                    
                    <!-- Department -->
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700">แผนก/หน่วยงาน</label>
                        <input type="text" 
                               id="department" 
                               name="department" 
                               value="<?= htmlspecialchars($_POST['department'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= BASE_URL ?>/admin/users/" 
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    ยกเลิก
                </a>
                <button type="submit" 
                        class="btn-loading px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Username validation
    document.getElementById('username').addEventListener('input', function() {
        const value = this.value;
        const isValid = /^[a-zA-Z0-9_-]{3,}$/.test(value);
        
        if (value && !isValid) {
            this.classList.add('border-red-500');
            showFieldError(this, 'ชื่อผู้ใช้ไม่ถูกต้อง');
        } else {
            this.classList.remove('border-red-500');
            hideFieldError(this);
        }
    });
    
    // Email validation
    document.getElementById('email').addEventListener('blur', function() {
        const value = this.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value && !emailRegex.test(value)) {
            this.classList.add('border-red-500');
            showFieldError(this, 'รูปแบบอีเมลไม่ถูกต้อง');
        } else {
            this.classList.remove('border-red-500');
            hideFieldError(this);
        }
    });
    
    // Password confirmation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && password !== confirmPassword) {
            this.classList.add('border-red-500');
            showFieldError(this, 'รหัสผ่านไม่ตรงกัน');
        } else {
            this.classList.remove('border-red-500');
            hideFieldError(this);
        }
    });
    
    // Phone validation
    document.getElementById('phone').addEventListener('input', function() {
        const value = this.value;
        const phoneRegex = /^[0-9]{9,10}$/;
        
        if (value && !phoneRegex.test(value)) {
            this.classList.add('border-red-500');
            showFieldError(this, 'หมายเลขโทรศัพท์ไม่ถูกต้อง');
        } else {
            this.classList.remove('border-red-500');
            hideFieldError(this);
        }
    });
});

function showFieldError(field, message) {
    hideFieldError(field);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'text-red-500 text-sm mt-1 field-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function hideFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>