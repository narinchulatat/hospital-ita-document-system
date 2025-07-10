<?php
$pageTitle = 'แก้ไขข้อมูลผู้ใช้';

require_once '../includes/header.php';
require_once '../../classes/User.php';
require_once '../../classes/Role.php';

// Check permission
requirePermission(PERM_USER_EDIT);

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    header('Location: index.php?error=not_found');
    exit;
}

$error = '';
$success = '';

try {
    $user = new User();
    $role = new Role();
    
    // Get user data
    $userData = $user->getById($userId);
    if (!$userData) {
        header('Location: index.php?error=not_found');
        exit;
    }
    
    $pageSubtitle = 'แก้ไขข้อมูล ' . htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']);
    
    // Get roles for dropdown
    $roles = $role->getAll();
    
    // Get user's current roles
    $userRoles = $user->getUserRoles($userId);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        checkCSRF();
        
        $updateData = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'status' => $_POST['status'] ?? STATUS_ACTIVE,
            'role_id' => (int)($_POST['role_id'] ?? 0)
        ];
        
        // Handle password change
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                $error = 'รหัสผ่านใหม่ไม่ตรงกัน';
            } elseif (strlen($_POST['new_password']) < 6) {
                $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
            } else {
                $updateData['password_hash'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            }
        }
        
        // Validate required fields
        $errors = [];
        if (empty($updateData['username'])) {
            $errors[] = 'กรุณากรอกชื่อผู้ใช้';
        }
        if (empty($updateData['email'])) {
            $errors[] = 'กรุณากรอกอีเมล';
        }
        if (empty($updateData['first_name'])) {
            $errors[] = 'กรุณากรอกชื่อ';
        }
        if (empty($updateData['last_name'])) {
            $errors[] = 'กรุณากรอกนามสกุล';
        }
        
        // Validate email format
        if (!empty($updateData['email']) && !filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }
        
        // Check if username already exists (exclude current user)
        if (!empty($updateData['username']) && $updateData['username'] !== $userData['username']) {
            if ($user->usernameExists($updateData['username'])) {
                $errors[] = 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว';
            }
        }
        
        // Check if email already exists (exclude current user)
        if (!empty($updateData['email']) && $updateData['email'] !== $userData['email']) {
            if ($user->emailExists($updateData['email'])) {
                $errors[] = 'อีเมลนี้มีอยู่ในระบบแล้ว';
            }
        }
        
        if (empty($errors) && empty($error)) {
            // Handle profile image upload
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
                    $profileImage = generateUniqueFilename('profile_' . $updateData['username'] . '.' . $extension);
                    
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $profileImage)) {
                        // Delete old profile image
                        if ($userData['profile_image'] && file_exists($uploadDir . $userData['profile_image'])) {
                            unlink($uploadDir . $userData['profile_image']);
                        }
                        $updateData['profile_image'] = $profileImage;
                    }
                } else {
                    $errors = array_merge($errors, $uploadErrors);
                }
            }
            
            if (empty($errors)) {
                // Get old data for logging
                $oldData = $userData;
                
                // Update user
                if ($user->update($userId, $updateData)) {
                    // Update role if changed
                    if ($updateData['role_id'] > 0 && !in_array($updateData['role_id'], array_column($userRoles, 'role_id'))) {
                        $user->removeAllRoles($userId);
                        $user->assignRole($userId, $updateData['role_id']);
                    }
                    
                    // Log activity
                    logAdminActivity(ACTION_UPDATE, 'users', $userId, $oldData, $updateData);
                    
                    setFlashMessage('success', 'อัปเดตข้อมูลผู้ใช้เรียบร้อยแล้ว');
                    header('Location: view.php?id=' . $userId);
                    exit;
                } else {
                    $error = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
                }
            }
        }
        
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        }
        
        // Update userData with posted values for form repopulation
        $userData = array_merge($userData, $updateData);
    }
    
} catch (Exception $e) {
    error_log("Edit user error: " . $e->getMessage());
    $error = 'เกิดข้อผิดพลาดในระบบ';
}

// Set breadcrumb
$breadcrumbItems = [
    ['title' => 'หน้าหลัก', 'url' => BASE_URL . '/admin/'],
    ['title' => 'จัดการผู้ใช้', 'url' => BASE_URL . '/admin/users/'],
    ['title' => 'แก้ไขข้อมูลผู้ใช้']
];
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>แก้ไขข้อมูลผู้ใช้
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
                                    <?php if ($userData['profile_image']): ?>
                                    <img id="profile-preview" src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($userData['profile_image']) ?>" 
                                         alt="Profile" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%;">
                                    <div id="profile-placeholder" class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 120px; height: 120px; display: none;">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                    <?php else: ?>
                                    <img id="profile-preview" src="#" alt="Profile Preview" 
                                         style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; display: none;">
                                    <div id="profile-placeholder" class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 120px; height: 120px;">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" 
                                       accept="image/*" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('profile_image').click();">
                                    <i class="fas fa-camera me-2"></i>เปลี่ยนรูปโปรไฟล์
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
                                       value="<?= htmlspecialchars($userData['username']) ?>" 
                                       pattern="[a-zA-Z0-9_]+" required>
                                <div class="invalid-feedback">กรุณากรอกชื่อผู้ใช้ (ใช้ได้เฉพาะ a-z, A-Z, 0-9, _)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($userData['email']) ?>" required>
                                <div class="invalid-feedback">กรุณากรอกอีเมลที่ถูกต้อง</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           minlength="6" placeholder="เว้นว่างหากไม่ต้องการเปลี่ยน">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       data-confirm="#new_password" placeholder="เว้นว่างหากไม่ต้องการเปลี่ยน">
                                <div class="invalid-feedback">รหัสผ่านไม่ตรงกัน</div>
                            </div>
                        </div>
                        
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">ข้อมูลส่วนตัว</h6>
                            
                            <div class="mb-3">
                                <label for="first_name" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($userData['first_name']) ?>" required>
                                <div class="invalid-feedback">กรุณากรอกชื่อ</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="last_name" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($userData['last_name']) ?>" required>
                                <div class="invalid-feedback">กรุณากรอกนามสกุล</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="department" class="form-label">แผนก</label>
                                <input type="text" class="form-control" id="department" name="department" 
                                       value="<?= htmlspecialchars($userData['department'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="position" class="form-label">ตำแหน่ง</label>
                                <input type="text" class="form-control" id="position" name="position" 
                                       value="<?= htmlspecialchars($userData['position'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($userData['phone'] ?? '') ?>"
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
                                    <option value="<?= $r['id'] ?>" 
                                            <?= (count($userRoles) > 0 && $userRoles[0]['role_id'] == $r['id']) ? 'selected' : '' ?>>
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
                                    <option value="active" <?= $userData['status'] === STATUS_ACTIVE ? 'selected' : '' ?>>
                                        ใช้งาน
                                    </option>
                                    <option value="inactive" <?= $userData['status'] === STATUS_INACTIVE ? 'selected' : '' ?>>
                                        ไม่ใช้งาน
                                    </option>
                                    <option value="locked" <?= $userData['status'] === STATUS_LOCKED ? 'selected' : '' ?>>
                                        ถูกล็อค
                                    </option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">ข้อมูลระบบ</label>
                                <div class="small text-muted">
                                    <div>สร้างเมื่อ: <?= formatThaiDate($userData['created_at'], true) ?></div>
                                    <div>เข้าใช้ล่าสุด: <?= $userData['last_login'] ? formatThaiDate($userData['last_login'], true) : 'ยังไม่เคยเข้าใช้' ?></div>
                                    <?php if ($userData['locked_until']): ?>
                                    <div class="text-danger">ล็อคจนถึง: <?= formatThaiDate($userData['locked_until'], true) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>กลับ
                            </a>
                            <a href="view.php?id=<?= $userId ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>ดูรายละเอียด
                            </a>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>บันทึกการเปลี่ยนแปลง
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
        const password = $('#new_password');
        const icon = $(this).find('i');
        
        if (password.attr('type') === 'password') {
            password.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            password.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Real-time username validation (only if changed)
    const originalUsername = '<?= htmlspecialchars($userData['username']) ?>';
    $('#username').on('input', function() {
        const username = $(this).val();
        if (username !== originalUsername && username.length >= 3) {
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
    
    // Real-time email validation (only if changed)
    const originalEmail = '<?= htmlspecialchars($userData['email']) ?>';
    $('#email').on('input', function() {
        const email = $(this).val();
        if (email !== originalEmail && email.length >= 3 && email.includes('@')) {
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
    
    // Password confirmation validation
    $('#confirm_password').on('input', function() {
        const newPassword = $('#new_password').val();
        const confirmPassword = $(this).val();
        
        if (newPassword && confirmPassword && newPassword !== confirmPassword) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>