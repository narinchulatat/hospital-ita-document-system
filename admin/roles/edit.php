<?php
require_once '../includes/header.php';
require_once 'functions.php';

$pageTitle = 'แก้ไขบทบาท';
$pageSubtitle = 'แก้ไขข้อมูลบทบาทผู้ใช้';

// Check permission
if (!hasPermission('roles.edit')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$role = new Role();
$errors = [];

// Get role ID
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/roles/');
    exit;
}

// Get role data
$roleData = $role->getById($id);
if (!$roleData) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ไม่พบบทบาทที่ต้องการแก้ไข'
    ];
    header('Location: ' . BASE_URL . '/admin/roles/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $data = [
            'id' => $id,
            'name' => trim($_POST['name'] ?? ''),
            'display_name' => trim($_POST['display_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validate data
        $errors = validateRoleData($data, true);
        
        if (empty($errors)) {
            try {
                $role->update($id, $data);
                
                // Set success message
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'แก้ไขบทบาทเรียบร้อยแล้ว'
                ];
                
                // Redirect to list
                header('Location: ' . BASE_URL . '/admin/roles/');
                exit;
                
            } catch (Exception $e) {
                $errors[] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    }
} else {
    // Pre-populate form with existing data
    $_POST = $roleData;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get users count for this role
$userCount = 0;
$users = [];
if ($id) {
    $db = new Database();
    $userCount = $db->query("SELECT COUNT(*) as count FROM users WHERE role_id = ?", [$id])->fetch()['count'];
    if ($userCount > 0) {
        $users = $db->query("SELECT id, first_name, last_name, email FROM users WHERE role_id = ? LIMIT 10", [$id])->fetchAll();
    }
}
?>

<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
                <div class="mt-2 flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getRoleBadgeClass($roleData['name']) ?>">
                        <i class="fas <?= getRoleIcon($roleData['name']) ?> mr-1"></i>
                        <?= htmlspecialchars($roleData['display_name']) ?>
                    </span>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="<?= BASE_URL ?>/admin/roles/view.php?id=<?= $id ?>" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-eye mr-2"></i>
                    ดู
                </a>
                <a href="<?= BASE_URL ?>/admin/roles/" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    กลับ
                </a>
            </div>
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">พบข้อผิดพลาด</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <form method="POST" class="divide-y divide-gray-200">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <!-- Basic Information -->
            <div class="px-6 py-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">ข้อมูลพื้นฐาน</h3>
                
                <div class="space-y-6">
                    <!-- Display Name -->
                    <div>
                        <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">
                            ชื่อที่แสดง <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="display_name" 
                               name="display_name" 
                               value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>"
                               required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="เช่น ผู้จัดการระบบ">
                    </div>
                    
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            ชื่อบทบาท <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               required
                               pattern="[a-z0-9_]+"
                               <?= in_array($roleData['name'], ['admin', 'staff', 'approver', 'visitor']) ? 'readonly' : '' ?>
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= in_array($roleData['name'], ['admin', 'staff', 'approver', 'visitor']) ? 'bg-gray-100' : '' ?>"
                               placeholder="เช่น manager">
                        <?php if (in_array($roleData['name'], ['admin', 'staff', 'approver', 'visitor'])): ?>
                        <p class="mt-1 text-xs text-red-600">ไม่สามารถแก้ไขชื่อบทบาทระบบได้</p>
                        <?php else: ?>
                        <p class="mt-1 text-xs text-gray-500">ใช้เฉพาะตัวอักษรภาษาอังกฤษพิมพ์เล็ก ตัวเลข และเครื่องหมาย _ เท่านั้น</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            คำอธิบาย
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                  placeholder="อธิบายเกี่ยวกับบทบาทนี้"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Status -->
            <div class="px-6 py-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">สถานะ</h3>
                
                <div class="space-y-4">
                    <!-- Active Status -->
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   <?= !empty($_POST['is_active']) ? 'checked' : '' ?>
                                   class="form-checkbox text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">เปิดใช้งาน</span>
                        </label>
                        <p class="mt-1 ml-6 text-xs text-gray-500">บทบาทที่ไม่เปิดใช้งานจะไม่สามารถมอบหมายให้ผู้ใช้ใหม่ได้</p>
                        <?php if ($userCount > 0 && empty($_POST['is_active'])): ?>
                        <p class="mt-1 ml-6 text-xs text-amber-600">
                            <i class="fas fa-warning mr-1"></i>
                            มีผู้ใช้ <?= number_format($userCount) ?> คนที่ใช้บทบาทนี้อยู่
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <a href="<?= BASE_URL ?>/admin/roles/" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    ยกเลิก
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-save mr-2"></i>
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
    
    <!-- Users with this role -->
    <?php if ($userCount > 0): ?>
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">ผู้ใช้ในบทบาทนี้</h3>
            <p class="text-sm text-gray-600 mt-1">มีผู้ใช้ <?= number_format($userCount) ?> คนที่ใช้บทบาทนี้</p>
        </div>
        <div class="px-6 py-4">
            <div class="space-y-3">
                <?php foreach ($users as $user): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-xs font-medium text-gray-700">
                                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                </span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            </p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $user['id'] ?>" 
                       class="text-blue-600 hover:text-blue-900 text-sm">
                        ดูรายละเอียด
                    </a>
                </div>
                <?php endforeach; ?>
                
                <?php if ($userCount > 10): ?>
                <div class="text-center pt-3">
                    <a href="<?= BASE_URL ?>/admin/users/?role=<?= urlencode($roleData['name']) ?>" 
                       class="text-blue-600 hover:text-blue-900 text-sm">
                        ดูทั้งหมด <?= number_format($userCount) ?> คน
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Additional Info -->
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">ข้อมูลเพิ่มเติม</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <span class="font-medium text-gray-700">วันที่สร้าง:</span>
                <span class="text-gray-600"><?= formatThaiDate($roleData['created_at']) ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">วันที่แก้ไขล่าสุด:</span>
                <span class="text-gray-600"><?= formatThaiDate($roleData['updated_at']) ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">จำนวนผู้ใช้:</span>
                <span class="text-gray-600"><?= number_format($userCount) ?> คน</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">ประเภท:</span>
                <span class="text-gray-600">
                    <?= in_array($roleData['name'], ['admin', 'staff', 'approver', 'visitor']) ? 'บทบาทระบบ' : 'บทบาทกำหนดเอง' ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>