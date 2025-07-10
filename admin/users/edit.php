<?php
require_once '../includes/header.php';

// Check permission
if (!hasPermission('users.edit')) {
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
$user = new User();

// Get user data
$user_sql = "SELECT u.*, r.name as role_name, r.display_name as role_display_name, d.name as department_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN departments d ON u.department_id = d.id 
            WHERE u.id = :id";
$user_stmt = $db->query($user_sql, [':id' => $user_id]);
$user_data = $user_stmt->fetch();

if (!$user_data) {
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

$pageTitle = 'แก้ไขผู้ใช้';
$pageSubtitle = 'แก้ไขข้อมูล ' . $user_data['first_name'] . ' ' . $user_data['last_name'];

$errors = [];
$success = '';

// Get roles for dropdown
$roles_sql = "SELECT * FROM roles WHERE status = 'active' ORDER BY display_name";
$roles_stmt = $db->query($roles_sql);
$roles = $roles_stmt->fetchAll();

// Get departments for dropdown
$departments_sql = "SELECT * FROM departments WHERE status = 'active' ORDER BY name";
$departments_stmt = $db->query($departments_sql);
$departments = $departments_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role_id = (int)($_POST['role_id'] ?? 0);
    $department_id = (int)($_POST['department_id'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $employee_id = trim($_POST['employee_id'] ?? '');
    $position = trim($_POST['position'] ?? '');
    
    // Validation
    if (empty($username)) {
        $errors['username'] = 'กรุณากรอกชื่อผู้ใช้';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'ชื่อผู้ใช้ใช้ได้เฉพาะภาษาอังกฤษ ตัวเลข และ _';
    } else {
        // Check if username already exists (exclude current user)
        $check_sql = "SELECT id FROM users WHERE username = :username AND id != :current_id";
        $check_stmt = $db->query($check_sql, [':username' => $username, ':current_id' => $user_id]);
        if ($check_stmt->fetch()) {
            $errors['username'] = 'ชื่อผู้ใช้นี้มีอยู่แล้ว';
        }
    }
    
    if (empty($email)) {
        $errors['email'] = 'กรุณากรอกอีเมล';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'รูปแบบอีเมลไม่ถูกต้อง';
    } else {
        // Check if email already exists (exclude current user)
        $check_sql = "SELECT id FROM users WHERE email = :email AND id != :current_id";
        $check_stmt = $db->query($check_sql, [':email' => $email, ':current_id' => $user_id]);
        if ($check_stmt->fetch()) {
            $errors['email'] = 'อีเมลนี้มีอยู่แล้ว';
        }
    }
    
    // Password validation (only if provided)
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors['password'] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        }
        
        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'รหัสผ่านไม่ตรงกัน';
        }
    }
    
    if (empty($first_name)) {
        $errors['first_name'] = 'กรุณากรอกชื่อจริง';
    }
    
    if (empty($last_name)) {
        $errors['last_name'] = 'กรุณากรอกนามสกุล';
    }
    
    if (!empty($phone) && !preg_match('/^[0-9]{9,10}$/', $phone)) {
        $errors['phone'] = 'รูปแบบหมายเลขโทรศัพท์ไม่ถูกต้อง';
    }
    
    if ($role_id <= 0) {
        $errors['role_id'] = 'กรุณาเลือกบทบาท';
    }
    
    // If no errors, update user
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Prepare update SQL
            $update_fields = [
                'username = :username',
                'email = :email',
                'first_name = :first_name',
                'last_name = :last_name',
                'phone = :phone',
                'role_id = :role_id',
                'department_id = :department_id',
                'status = :status',
                'employee_id = :employee_id',
                'position = :position',
                'updated_at = NOW()',
                'updated_by = :updated_by'
            ];
            
            $params = [
                ':username' => $username,
                ':email' => $email,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':phone' => $phone,
                ':role_id' => $role_id,
                ':department_id' => $department_id ?: null,
                ':status' => $status,
                ':employee_id' => $employee_id,
                ':position' => $position,
                ':updated_by' => $_SESSION['user_id'],
                ':id' => $user_id
            ];
            
            // Add password to update if provided
            if (!empty($password)) {
                $update_fields[] = 'password = :password';
                $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $update_sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = :id";
            $stmt = $db->query($update_sql, $params);
            
            // Log activity
            $activity_log = new ActivityLog();
            $changes = [];
            
            // Track changes
            if ($user_data['username'] !== $username) $changes[] = "ชื่อผู้ใช้: {$user_data['username']} → {$username}";
            if ($user_data['email'] !== $email) $changes[] = "อีเมล: {$user_data['email']} → {$email}";
            if ($user_data['first_name'] !== $first_name) $changes[] = "ชื่อจริง: {$user_data['first_name']} → {$first_name}";
            if ($user_data['last_name'] !== $last_name) $changes[] = "นามสกุล: {$user_data['last_name']} → {$last_name}";
            if ($user_data['status'] !== $status) $changes[] = "สถานะ: {$user_data['status']} → {$status}";
            if (!empty($password)) $changes[] = "เปลี่ยนรหัสผ่าน";
            
            $change_description = "แก้ไขผู้ใช้ {$first_name} {$last_name}";
            if (!empty($changes)) {
                $change_description .= " (" . implode(', ', $changes) . ")";
            }
            
            $activity_log->log($_SESSION['user_id'], ACTION_UPDATE, 'users', $user_id, 
                              $change_description, 
                              $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            
            $db->commit();
            
            $success = 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว';
            
            // Update user_data for display
            $user_data = array_merge($user_data, [
                'username' => $username,
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'role_id' => $role_id,
                'department_id' => $department_id,
                'status' => $status,
                'employee_id' => $employee_id,
                'position' => $position
            ]);
            
        } catch (Exception $e) {
            $db->rollback();
            error_log("Update user error: " . $e->getMessage());
            $errors['general'] = 'เกิดข้อผิดพลาดในการแก้ไขข้อมูลผู้ใช้';
        }
    }
}
?>

<div class="max-w-4xl mx-auto">
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
                        <span class="ml-1 text-gray-500 md:ml-2">แก้ไขผู้ใช้</span>
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
                <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $user_id ?>" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-eye mr-2"></i>
                    ดูรายละเอียด
                </a>
                <a href="<?= BASE_URL ?>/admin/users/" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    กลับ
                </a>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <?php if ($success): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
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

    <!-- Error Messages -->
    <?php if (!empty($errors['general'])): ?>
    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($errors['general']) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="" class="admin-form needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">ข้อมูลผู้ใช้</h3>
                <p class="text-sm text-gray-500 mt-1">แก้ไขข้อมูลผู้ใช้ในระบบ</p>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                            ชื่อผู้ใช้ <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($_POST['username'] ?? $user_data['username']) ?>"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['username']) ? 'border-red-500' : '' ?>"
                               placeholder="กรอกชื่อผู้ใช้"
                               required>
                        <?php if (isset($errors['username'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['username']) ?></p>
                        <?php endif; ?>
                        <p class="mt-1 text-xs text-gray-500">ใช้ได้เฉพาะภาษาอังกฤษ ตัวเลข และ _ อย่างน้อย 3 ตัวอักษร</p>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            อีเมล <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($_POST['email'] ?? $user_data['email']) ?>"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['email']) ? 'border-red-500' : '' ?>"
                               placeholder="กรอกอีเมล"
                               required>
                        <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Password Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">เปลี่ยนรหัสผ่าน</h4>
                    <p class="text-sm text-gray-500 mb-4">ปล่อยว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านใหม่</label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['password']) ? 'border-red-500' : '' ?>"
                                       placeholder="กรอกรหัสผ่านใหม่">
                                <button type="button" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center" 
                                        onclick="togglePassword('password')">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-toggle"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['password']) ?></p>
                            <?php endif; ?>
                            <p class="mt-1 text-xs text-gray-500">อย่างน้อย 6 ตัวอักษร</p>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่านใหม่</label>
                            <div class="relative">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['confirm_password']) ? 'border-red-500' : '' ?>"
                                       placeholder="ยืนยันรหัสผ่านใหม่">
                                <button type="button" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center" 
                                        onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="confirm_password-toggle"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['confirm_password'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['confirm_password']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Personal Information -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">ข้อมูลส่วนตัว</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                ชื่อจริง <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? $user_data['first_name']) ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['first_name']) ? 'border-red-500' : '' ?>"
                                   placeholder="กรอกชื่อจริง"
                                   required>
                            <?php if (isset($errors['first_name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['first_name']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                นามสกุล <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? $user_data['last_name']) ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['last_name']) ? 'border-red-500' : '' ?>"
                                   placeholder="กรอกนามสกุล"
                                   required>
                            <?php if (isset($errors['last_name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['last_name']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">หมายเลขโทรศัพท์</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? $user_data['phone']) ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['phone']) ? 'border-red-500' : '' ?>"
                                   placeholder="กรอกหมายเลขโทรศัพท์">
                            <?php if (isset($errors['phone'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['phone']) ?></p>
                            <?php endif; ?>
                            <p class="mt-1 text-xs text-gray-500">ตัวเลข 9-10 หลัก</p>
                        </div>
                        
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">รหัสพนักงาน</label>
                            <input type="text" 
                                   id="employee_id" 
                                   name="employee_id" 
                                   value="<?= htmlspecialchars($_POST['employee_id'] ?? $user_data['employee_id']) ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="กรอกรหัสพนักงาน">
                        </div>
                    </div>
                </div>
                
                <!-- Role and Department -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">บทบาทและหน่วยงาน</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">
                                บทบาท <span class="text-red-500">*</span>
                            </label>
                            <select id="role_id" 
                                    name="role_id" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['role_id']) ? 'border-red-500' : '' ?>"
                                    required>
                                <option value="">เลือกบทบาท</option>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= (int)($_POST['role_id'] ?? $user_data['role_id']) === (int)$role['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['display_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['role_id'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['role_id']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">หน่วยงาน</label>
                            <select id="department_id" 
                                    name="department_id" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">เลือกหน่วยงาน</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= (int)($_POST['department_id'] ?? $user_data['department_id']) === (int)$dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-700 mb-1">ตำแหน่ง</label>
                            <input type="text" 
                                   id="position" 
                                   name="position" 
                                   value="<?= htmlspecialchars($_POST['position'] ?? $user_data['position']) ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="กรอกตำแหน่ง">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                            <select id="status" 
                                    name="status" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="active" <?= ($_POST['status'] ?? $user_data['status']) === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                                <option value="inactive" <?= ($_POST['status'] ?? $user_data['status']) === 'inactive' ? 'selected' : '' ?>>ระงับ</option>
                                <option value="banned" <?= ($_POST['status'] ?? $user_data['status']) === 'banned' ? 'selected' : '' ?>>ห้ามใช้งาน</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $user_id ?>" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    <i class="fas fa-times mr-2"></i>
                    ยกเลิก
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    <i class="fas fa-save mr-2"></i>
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + '-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    // Real-time validation
    $('#username').on('blur', function() {
        const username = $(this).val();
        const currentId = <?= $user_id ?>;
        
        if (username.length >= 3) {
            // Check username availability via AJAX
            $.ajax({
                url: '<?= BASE_URL ?>/admin/api/check-username.php',
                method: 'POST',
                data: { username: username, exclude_id: currentId },
                success: function(response) {
                    if (!response.available) {
                        $('#username').addClass('border-red-500');
                        $('#username').siblings('.error-message').remove();
                        $('#username').after('<p class="error-message mt-1 text-sm text-red-600">ชื่อผู้ใช้นี้มีอยู่แล้ว</p>');
                    } else {
                        $('#username').removeClass('border-red-500').addClass('border-green-500');
                        $('#username').siblings('.error-message').remove();
                    }
                }
            });
        }
    });
    
    $('#email').on('blur', function() {
        const email = $(this).val();
        const currentId = <?= $user_id ?>;
        
        if (email) {
            // Check email availability via AJAX
            $.ajax({
                url: '<?= BASE_URL ?>/admin/api/check-email.php',
                method: 'POST',
                data: { email: email, exclude_id: currentId },
                success: function(response) {
                    if (!response.available) {
                        $('#email').addClass('border-red-500');
                        $('#email').siblings('.error-message').remove();
                        $('#email').after('<p class="error-message mt-1 text-sm text-red-600">อีเมลนี้มีอยู่แล้ว</p>');
                    } else {
                        $('#email').removeClass('border-red-500').addClass('border-green-500');
                        $('#email').siblings('.error-message').remove();
                    }
                }
            });
        }
    });
    
    // Password confirmation validation
    $('#confirm_password').on('blur', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('border-red-500');
            $(this).siblings('.error-message').remove();
            $(this).after('<p class="error-message mt-1 text-sm text-red-600">รหัสผ่านไม่ตรงกัน</p>');
        } else {
            $(this).removeClass('border-red-500');
            $(this).siblings('.error-message').remove();
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>