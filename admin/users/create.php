<?php
$pageTitle = 'เพิ่มผู้ใช้';
$pageSubtitle = 'สร้างบัญชีผู้ใช้ใหม่';
require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

$error = '';
$success = '';

try {
    $user = new User();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        // Validate input
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $role_id = (int)($_POST['role_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $phone = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? '');
        
        // Validation
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'กรุณากรอกชื่อผู้ใช้';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'ชื่อผู้ใช้ต้องประกอบด้วยตัวอักษร ตัวเลข และ _ เท่านั้น';
        } elseif ($user->isUsernameExists($username)) {
            $errors['username'] = 'ชื่อผู้ใช้นี้มีอยู่แล้ว';
        }
        
        if (empty($email)) {
            $errors['email'] = 'กรุณากรอกอีเมล';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'รูปแบบอีเมลไม่ถูกต้อง';
        } elseif ($user->isEmailExists($email)) {
            $errors['email'] = 'อีเมลนี้มีอยู่แล้ว';
        }
        
        if (empty($password)) {
            $errors['password'] = 'กรุณากรอกรหัสผ่าน';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        }
        
        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'รหัสผ่านไม่ตรงกัน';
        }
        
        if (empty($first_name)) {
            $errors['first_name'] = 'กรุณากรอกชื่อ';
        }
        
        if (empty($last_name)) {
            $errors['last_name'] = 'กรุณากรอกนามสกุล';
        }
        
        if (empty($role_id)) {
            $errors['role_id'] = 'กรุณาเลือกบทบาท';
        }
        
        if ($phone && !preg_match('/^[0-9]{9,10}$/', $phone)) {
            $errors['phone'] = 'หมายเลขโทรศัพท์ไม่ถูกต้อง';
        }
        
        if (empty($errors)) {
            // Create user
            $userData = [
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
            ];
            
            $userId = $user->create($userData);
            
            if ($userId) {
                // Log activity
                $activityLog = new ActivityLog();
                $activityLog->log($_SESSION['user_id'], ACTION_CREATE, 'users', $userId, 
                    json_encode(['username' => $username, 'email' => $email]));
                
                $_SESSION['success'] = 'สร้างผู้ใช้เรียบร้อยแล้ว';
                header('Location: index.php');
                exit;
            } else {
                $error = 'เกิดข้อผิดพลาดในการสร้างผู้ใช้';
            }
        }
    }
    
    // Get roles for dropdown
    $roles = $user->getAllRoles();
    
} catch (Exception $e) {
    error_log("User creation error: " . $e->getMessage());
    $error = 'เกิดข้อผิดพลาดในระบบ';
    $roles = [];
}
?>

<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">เพิ่มผู้ใช้</h1>
                <p class="text-gray-600">สร้างบัญชีผู้ใช้ใหม่ในระบบ</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">ข้อมูลผู้ใช้</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Error Alert -->
                <?php if ($error): ?>
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-0.5"></i>
                        <div class="text-red-700"><?= htmlspecialchars($error) ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Validation Errors -->
                <?php if (!empty($errors)): ?>
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="text-red-800 font-medium">กรุณาแก้ไขข้อผิดพลาดต่อไปนี้:</h4>
                            <ul class="mt-2 text-red-700 list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            ชื่อผู้ใช้ <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               placeholder="ชื่อผู้ใช้สำหรับเข้าสู่ระบบ"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['username']) ? 'border-red-500' : '' ?>"
                               required>
                        <?php if (isset($errors['username'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['username']) ?></p>
                        <?php endif; ?>
                        <p class="mt-1 text-sm text-gray-500">ใช้ตัวอักษร ตัวเลข และ _ เท่านั้น</p>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            อีเมล <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="อีเมลสำหรับติดต่อ"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['email']) ? 'border-red-500' : '' ?>"
                               required>
                        <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            รหัสผ่าน <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="รหัสผ่านอย่างน้อย 6 ตัวอักษร"
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['password']) ? 'border-red-500' : '' ?>"
                                   required>
                            <button type="button" 
                                    onclick="togglePassword('password')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['password']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                            ยืนยันรหัสผ่าน <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="ยืนยันรหัสผ่าน"
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['confirm_password']) ? 'border-red-500' : '' ?>"
                                   data-confirm="#password"
                                   required>
                            <button type="button" 
                                    onclick="togglePassword('confirm_password')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="confirm_password-icon"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['confirm_password']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Personal Information -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">ข้อมูลส่วนตัว</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                ชื่อ <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                   placeholder="ชื่อจริง"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['first_name']) ? 'border-red-500' : '' ?>"
                                   required>
                            <?php if (isset($errors['first_name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['first_name']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                นามสกุล <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                                   placeholder="นามสกุล"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['last_name']) ? 'border-red-500' : '' ?>"
                                   required>
                            <?php if (isset($errors['last_name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['last_name']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">หมายเลขโทรศัพท์</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                   placeholder="0xx-xxx-xxxx"
                                   data-validate="phone"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['phone']) ? 'border-red-500' : '' ?>">
                            <?php if (isset($errors['phone'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-2">แผนก/หน่วยงาน</label>
                            <input type="text" 
                                   id="department" 
                                   name="department" 
                                   value="<?= htmlspecialchars($_POST['department'] ?? '') ?>"
                                   placeholder="แผนกที่สังกัด"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                <!-- Role and Status -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">บทบาทและสิทธิ์</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                                บทบาท <span class="text-red-500">*</span>
                            </label>
                            <select id="role_id" 
                                    name="role_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['role_id']) ? 'border-red-500' : '' ?>"
                                    required>
                                <option value="">เลือกบทบาท</option>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= ($_POST['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['name']) ?>
                                    <?php if ($role['description']): ?>
                                    - <?= htmlspecialchars($role['description']) ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['role_id'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['role_id']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                            <select id="status" 
                                    name="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                                <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                <div class="flex items-center justify-end space-x-3">
                    <a href="index.php" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        ยกเลิก
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors form-loading">
                        <i class="fas fa-save mr-2"></i>บันทึก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Real-time password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.classList.add('border-red-500');
        this.classList.remove('border-gray-300');
        
        // Remove existing error message
        const existingError = this.parentNode.parentNode.querySelector('.password-error');
        if (existingError) existingError.remove();
        
        // Add error message
        const errorMsg = document.createElement('p');
        errorMsg.className = 'mt-1 text-sm text-red-600 password-error';
        errorMsg.textContent = 'รหัสผ่านไม่ตรงกัน';
        this.parentNode.parentNode.appendChild(errorMsg);
    } else {
        this.classList.remove('border-red-500');
        this.classList.add('border-gray-300');
        
        // Remove error message
        const existingError = this.parentNode.parentNode.querySelector('.password-error');
        if (existingError) existingError.remove();
    }
});

// Username validation
document.getElementById('username').addEventListener('blur', function() {
    const username = this.value;
    if (username.length >= 3) {
        // Check username availability
        $.ajax({
            url: '../api/check-username.php',
            method: 'POST',
            data: {
                username: username,
                csrf_token: $('input[name="csrf_token"]').val()
            },
            success: function(response) {
                const usernameField = document.getElementById('username');
                const existingError = usernameField.parentNode.querySelector('.username-error');
                
                if (existingError) existingError.remove();
                
                if (!response.available) {
                    usernameField.classList.add('border-red-500');
                    usernameField.classList.remove('border-gray-300');
                    
                    const errorMsg = document.createElement('p');
                    errorMsg.className = 'mt-1 text-sm text-red-600 username-error';
                    errorMsg.textContent = 'ชื่อผู้ใช้นี้มีอยู่แล้ว';
                    usernameField.parentNode.appendChild(errorMsg);
                } else {
                    usernameField.classList.remove('border-red-500');
                    usernameField.classList.add('border-gray-300');
                }
            }
        });
    }
});

// Email validation
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value;
    if (email && isValidEmail(email)) {
        // Check email availability
        $.ajax({
            url: '../api/check-email.php',
            method: 'POST',
            data: {
                email: email,
                csrf_token: $('input[name="csrf_token"]').val()
            },
            success: function(response) {
                const emailField = document.getElementById('email');
                const existingError = emailField.parentNode.querySelector('.email-error');
                
                if (existingError) existingError.remove();
                
                if (!response.available) {
                    emailField.classList.add('border-red-500');
                    emailField.classList.remove('border-gray-300');
                    
                    const errorMsg = document.createElement('p');
                    errorMsg.className = 'mt-1 text-sm text-red-600 email-error';
                    errorMsg.textContent = 'อีเมลนี้มีอยู่แล้ว';
                    emailField.parentNode.appendChild(errorMsg);
                } else {
                    emailField.classList.remove('border-red-500');
                    emailField.classList.add('border-gray-300');
                }
            }
        });
    }
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
</script>

<?php require_once '../includes/footer.php'; ?>