<?php
require_once '../includes/header.php';
require_once 'functions.php';

$pageTitle = 'เพิ่มหมวดหมู่ใหม่';
$pageSubtitle = 'สร้างหมวดหมู่เอกสารใหม่';

// Check permission
if (!hasPermission('categories.create')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$category = new Category();
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
            'description' => trim($_POST['description'] ?? ''),
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_by' => $_SESSION['user_id']
        ];
        
        // Validate data
        $errors = validateCategoryData($data);
        
        if (empty($errors)) {
            try {
                $categoryId = $category->create($data);
                
                // Set success message
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'เพิ่มหมวดหมู่ใหม่เรียบร้อยแล้ว'
                ];
                
                // Redirect to list
                header('Location: ' . BASE_URL . '/admin/categories/');
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
            <a href="<?= BASE_URL ?>/admin/categories/" 
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
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            ชื่อหมวดหมู่ <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="เช่น เอกสารทั่วไป">
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
                                  placeholder="อธิบายเกี่ยวกับหมวดหมู่นี้"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Category Settings -->
            <div class="px-6 py-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">การตั้งค่า</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Parent Category -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">
                            หมวดหมู่หลัก
                        </label>
                        <select id="parent_id" 
                                name="parent_id" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">-- ไม่มีหมวดหมู่หลัก --</option>
                            <?= getCategoryOptions($_POST['parent_id'] ?? null) ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">สามารถสร้างหมวดหมู่ย่อยได้สูงสุด 3 ระดับ</p>
                    </div>
                    
                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                            ลำดับการแสดง
                        </label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               value="<?= htmlspecialchars($_POST['sort_order'] ?? '0') ?>"
                               min="0"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">ตัวเลขน้อยจะแสดงก่อน</p>
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
                        <p class="mt-1 ml-6 text-xs text-gray-500">หมวดหมู่ที่ไม่เปิดใช้งานจะไม่แสดงในระบบ</p>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <a href="<?= BASE_URL ?>/admin/categories/" 
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
</div>

<?php require_once '../includes/footer.php'; ?>