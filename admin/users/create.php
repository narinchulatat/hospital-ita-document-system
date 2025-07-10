<?php
$pageTitle = 'เพิ่มผู้ใช้ใหม่';
$pageSubtitle = 'สร้างบัญชีผู้ใช้ใหม่ในระบบ';

require_once '../includes/header.php';
require_once '../../classes/User.php';
require_once '../../classes/Role.php';

// Check permission
requirePermission(PERM_USER_CREATE);

$error = '';
$success = '';

try {
    $user = new User();
    $role = new Role();
    
    // Get roles for dropdown
    $roles = $role->getAll();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        checkCSRF();
        
        $userData = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'status' => $_POST['status'] ?? STATUS_ACTIVE,
            'role_id' => (int)($_POST['role_id'] ?? 0)
        ];
        
        // Validate required fields
        $errors = [];
        if (empty($userData['username'])) {
            $errors[] = 'กรุณากรอกชื่อผู้ใช้';
        }
        if (empty($userData['email'])) {
            $errors[] = 'กรุณากรอกอีเมล';
        }
        if (empty($userData['password'])) {
            $errors[] = 'กรุณากรอกรหัสผ่าน';
        }
        if ($userData['password'] !== $userData['confirm_password']) {
            $errors[] = 'รหัสผ่านไม่ตรงกัน';
        }
        if (empty($userData['first_name'])) {
            $errors[] = 'กรุณากรอกชื่อ';
        }
        if (empty($userData['last_name'])) {
            $errors[] = 'กรุณากรอกนามสกุล';
        }
        if ($userData['role_id'] <= 0) {
            $errors[] = 'กรุณาเลือกบทบาท';
        }
        
        // Validate email format
        if (!empty($userData['email']) && !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }
        
        // Validate password strength
        if (!empty($userData['password']) && strlen($userData['password']) < 6) {
            $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        }
        
        // Check if username already exists
        if (!empty($userData['username']) && $user->usernameExists($userData['username'])) {
            $errors[] = 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว';
        }
        
        // Check if email already exists
        if (!empty($userData['email']) && $user->emailExists($userData['email'])) {
            $errors[] = 'อีเมลนี้มีอยู่ในระบบแล้ว';
        }
        
        if (empty($errors)) {
            // Handle profile image upload
            $profileImage = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../uploads/profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 2 * 1024 * 1024; // 2MB
                
                $uploadErrors = validateFileUpload($_FILES['profile_image'], $allowedTypes, $maxSize);
                
                if (empty($uploadErrors)) {
                    $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                    $profileImage = generateUniqueFilename('profile_' . $userData['username'] . '.' . $extension);
                    
                    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $profileImage)) {
                        $errors[] = 'เกิดข้อผิดพลาดในการอัปโหลดรูปโปรไฟล์';
                    }
                } else {
                    $errors = array_merge($errors, $uploadErrors);
                }
            }
            
            if (empty($errors)) {
                // Hash password
                $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
                unset($userData['password'], $userData['confirm_password']);
                
                // Add profile image if uploaded
                if ($profileImage) {
                    $userData['profile_image'] = $profileImage;
                }
                
                // Create user
                $userId = $user->create($userData);
                
                if ($userId) {
                    // Assign role
                    $user->assignRole($userId, $userData['role_id']);
                    
                    // Log activity
                    logAdminActivity(ACTION_CREATE, 'users', $userId, null, $userData);
                    
                    setFlashMessage('success', 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว');
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'เกิดข้อผิดพลาดในการสร้างผู้ใช้';
                }
            }
        }
        
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        }
    }
    
} catch (Exception $e) {
    error_log("Create user error: " . $e->getMessage());
    $error = 'เกิดข้อผิดพลาดในระบบ';
}

// Set breadcrumb
$breadcrumbItems = [
    ['title' => 'หน้าหลัก', 'url' => BASE_URL . '/admin/'],
    ['title' => 'จัดการผู้ใช้', 'url' => BASE_URL . '/admin/users/'],
    ['title' => 'เพิ่มผู้ใช้ใหม่']
];
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>เพิ่มผู้ใช้ใหม่
                </h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $error ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= getCSRFInput() ?>
                    
                    <!-- Profile Image -->
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <div class="profile-image-upload">
                                <div class="profile-preview mb-3">
                                    <img id="profile-preview" src="#" alt="Profile Preview" 
                                         style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; display: none;">
                                    <div id="profile-placeholder" class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 120px; height: 120px;">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                </div>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" 
                                       accept="image/*" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('profile_image').click();">
                                    <i class="fas fa-camera me-2"></i>เลือกรูปโปรไฟล์
                                </button>
                                <small class="form-text text-muted d-block mt-2">
                                    รองรับไฟล์ JPG, PNG, GIF ขนาดไม่เกิน 2MB
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">ข้อมูลพื้นฐาน</h6>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                                       pattern="[a-zA-Z0-9_]+" required>
                                <div class="invalid-feedback">กรุณากรอกชื่อผู้ใช้ (ใช้ได้เฉพาะ a-z, A-Z, 0-9, _)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                <div class="invalid-feedback">กรุณากรอกอีเมลที่ถูกต้อง</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       data-confirm="#password" required>
                                <div class="invalid-feedback">รหัสผ่านไม่ตรงกัน</div>
                            </div>
                        </div>
                        
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">ข้อมูลส่วนตัว</h6>
                            
                            <div class="mb-3">
                                <label for="first_name" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                                <div class="invalid-feedback">กรุณากรอกชื่อ</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="last_name" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                                <div class="invalid-feedback">กรุณากรอกนามสกุล</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="department" class="form-label">แผนก</label>
                                <input type="text" class="form-control" id="department" name="department" 
                                       value="<?= htmlspecialchars($_POST['department'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="position" class="form-label">ตำแหน่ง</label>
                                <input type="text" class="form-control" id="position" name="position" 
                                       value="<?= htmlspecialchars($_POST['position'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                       pattern="[0-9]{9,10}">
                                <div class="invalid-feedback">หมายเลขโทรศัพท์ไม่ถูกต้อง</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Role and Status -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">บทบาทและสิทธิ์</h6>
                            
                            <div class="mb-3">
                                <label for="role_id" class="form-label">บทบาท <span class="text-danger">*</span></label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">-- เลือกบทบาท --</option>
                                    <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($_POST['role_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['name']) ?>
                                        <?php if ($r['description']): ?>
                                        - <?= htmlspecialchars($r['description']) ?>
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">กรุณาเลือกบทบาท</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">การตั้งค่า</h6>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">สถานะ</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= ($_POST['status'] ?? STATUS_ACTIVE) === STATUS_ACTIVE ? 'selected' : '' ?>>
                                        ใช้งาน
                                    </option>
                                    <option value="inactive" <?= ($_POST['status'] ?? '') === STATUS_INACTIVE ? 'selected' : '' ?>>
                                        ไม่ใช้งาน
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>กลับ
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Profile image preview
    $('#profile_image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profile-preview').attr('src', e.target.result).show();
                $('#profile-placeholder').hide();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const password = $('#password');
        const icon = $(this).find('i');
        
        if (password.attr('type') === 'password') {
            password.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            password.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Real-time username validation
    $('#username').on('input', function() {
        const username = $(this).val();
        if (username.length >= 3) {
            $.ajax({
                url: '../api/check_username.php',
                method: 'POST',
                data: { username: username },
                success: function(response) {
                    if (response.exists) {
                        $('#username').addClass('is-invalid');
                        $('#username').siblings('.invalid-feedback').text('ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว');
                    } else {
                        $('#username').removeClass('is-invalid');
                    }
                }
            });
        }
    });
    
    // Real-time email validation
    $('#email').on('input', function() {
        const email = $(this).val();
        if (email.length >= 3 && email.includes('@')) {
            $.ajax({
                url: '../api/check_email.php',
                method: 'POST',
                data: { email: email },
                success: function(response) {
                    if (response.exists) {
                        $('#email').addClass('is-invalid');
                        $('#email').siblings('.invalid-feedback').text('อีเมลนี้มีอยู่ในระบบแล้ว');
                    } else {
                        $('#email').removeClass('is-invalid');
                    }
                }
            });
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>