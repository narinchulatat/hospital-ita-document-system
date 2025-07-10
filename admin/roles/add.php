<?php
require_once '../includes/header.php';
require_once 'functions.php';

$pageTitle = 'เพิ่มบทบาทใหม่';
$pageSubtitle = 'สร้างบทบาทผู้ใช้ใหม่';

// Check permission
if (!hasPermission('roles.create')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$role = new Role();
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'display_name' => trim($_POST['display_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Auto-generate name if empty
        if (empty($data['name']) && !empty($data['display_name'])) {
            $data['name'] = generateRoleName($data['display_name']);
        }
        
        // Validate data
        $errors = validateRoleData($data);
        
        if (empty($errors)) {
            try {
                $roleId = $role->create($data);
                
                // Set success message
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'เพิ่มบทบาทใหม่เรียบร้อยแล้ว'
                ];
                
                // Redirect to list
                header('Location: ' . BASE_URL . '/admin/roles/');
                exit;
                
            } catch (Exception $e) {
                $errors[] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
            <a href="<?= BASE_URL ?>/admin/roles/" 
               class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                กลับ
            </a>
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
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="เช่น manager">
                        <p class="mt-1 text-xs text-gray-500">ใช้เฉพาะตัวอักษรภาษาอังกฤษพิมพ์เล็ก ตัวเลข และเครื่องหมาย _ เท่านั้น</p>
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
                                   <?= !isset($_POST['is_active']) || !empty($_POST['is_active']) ? 'checked' : '' ?>
                                   class="form-checkbox text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">เปิดใช้งาน</span>
                        </label>
                        <p class="mt-1 ml-6 text-xs text-gray-500">บทบาทที่ไม่เปิดใช้งานจะไม่สามารถมอบหมายให้ผู้ใช้ใหม่ได้</p>
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
                    บันทึก
                </button>
            </div>
        </form>
    </div>
    
    <!-- Usage Guide -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-blue-900 mb-2">คำแนะนำการใช้งาน</h3>
        <div class="text-sm text-blue-800 space-y-2">
            <p><strong>ชื่อบทบาท:</strong> ใช้สำหรับระบบจัดการสิทธิ์ ควรใช้ภาษาอังกฤษและไม่ควรเปลี่ยนแปลงหลังสร้างแล้ว</p>
            <p><strong>ชื่อที่แสดง:</strong> ชื่อที่จะแสดงให้ผู้ใช้เห็น สามารถใช้ภาษาไทยได้</p>
            <p><strong>การกำหนดสิทธิ์:</strong> หลังจากสร้างบทบาทแล้ว คุณสามารถกำหนดสิทธิ์การเข้าถึงในหน้า "จัดการสิทธิ์"</p>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-generate name from display_name
    $('#display_name').on('input', function() {
        const displayName = $(this).val();
        if (displayName && !$('#name').val()) {
            const name = generateRoleName(displayName);
            $('#name').val(name);
        }
    });
});

function generateRoleName(text) {
    // Basic Thai to English transliteration
    return text
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_]/g, '')
        .replace(/_+/g, '_')
        .replace(/^_+|_+$/g, '');
}
</script>

<?php require_once '../includes/footer.php'; ?>