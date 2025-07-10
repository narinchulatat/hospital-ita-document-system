<?php
require_once '../includes/header.php';
require_once 'functions.php';

$pageTitle = 'แก้ไขหมวดหมู่';
$pageSubtitle = 'แก้ไขข้อมูลหมวดหมู่เอกสาร';

// Check permission
if (!hasPermission('categories.edit')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$category = new Category();
$errors = [];
$success = false;

// Get category ID
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/categories/');
    exit;
}

// Get category data
$categoryData = $category->getById($id);
if (!$categoryData) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ไม่พบหมวดหมู่ที่ต้องการแก้ไข'
    ];
    header('Location: ' . BASE_URL . '/admin/categories/');
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
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'icon' => trim($_POST['icon'] ?? 'fa-folder'),
            'color' => trim($_POST['color'] ?? '#3B82F6'),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'status' => $_POST['status'] ?? 'active',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'updated_by' => $_SESSION['user_id']
        ];
        
        // Auto-generate slug if empty
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = generateSlug($data['name']);
        }
        
        // Check if trying to set self as parent
        if ($data['parent_id'] == $id) {
            $errors[] = 'ไม่สามารถตั้งหมวดหมู่นี้เป็นหมวดหมู่หลักของตัวเองได้';
        }
        
        // Check if trying to set child as parent (circular reference)
        if ($data['parent_id']) {
            $descendants = $category->getDescendants($id);
            foreach ($descendants as $descendant) {
                if ($descendant['id'] == $data['parent_id']) {
                    $errors[] = 'ไม่สามารถตั้งหมวดหมู่ย่อยเป็นหมวดหมู่หลักได้';
                    break;
                }
            }
        }
        
        // Validate data
        if (empty($errors)) {
            $errors = validateCategoryData($data, true);
        }
        
        if (empty($errors)) {
            try {
                $category->update($id, $data);
                
                // Set success message
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'แก้ไขหมวดหมู่เรียบร้อยแล้ว'
                ];
                
                // Redirect to list
                header('Location: ' . BASE_URL . '/admin/categories/');
                exit;
                
            } catch (Exception $e) {
                $errors[] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    }
} else {
    // Pre-populate form with existing data
    $_POST = $categoryData;
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
                <div class="mt-2">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2">
                            <?php foreach (getCategoryBreadcrumb($id) as $index => $breadcrumb): ?>
                                <?php if ($index > 0): ?>
                                    <li><i class="fas fa-chevron-right text-gray-400 text-xs"></i></li>
                                <?php endif; ?>
                                <li class="text-sm <?= $breadcrumb['id'] == $id ? 'font-medium text-blue-600' : 'text-gray-500' ?>">
                                    <?= htmlspecialchars($breadcrumb['name']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="<?= BASE_URL ?>/admin/categories/view.php?id=<?= $id ?>" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-eye mr-2"></i>
                    ดู
                </a>
                <a href="<?= BASE_URL ?>/admin/categories/" 
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
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    
                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"
                               required
                               pattern="[a-z0-9-]+"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="เช่น general-documents">
                        <p class="mt-1 text-xs text-gray-500">ใช้เฉพาะตัวอักษรภาษาอังกฤษพิมพ์เล็ก ตัวเลข และเครื่องหมาย - เท่านั้น</p>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mt-6">
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
                            <?= getCategoryOptions($_POST['parent_id'] ?? null, $id) ?>
                        </select>
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
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Icon -->
                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">
                            ไอคอน
                        </label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 py-2 border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-l-md">
                                <i class="fas fa-<?= htmlspecialchars($_POST['icon'] ?? 'folder') ?>"></i>
                            </span>
                            <input type="text" 
                                   id="icon" 
                                   name="icon" 
                                   value="<?= htmlspecialchars($_POST['icon'] ?? 'folder') ?>"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="folder">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">ใช้ชื่อไอคอนจาก Font Awesome (ไม่ต้องใส่ fa-)</p>
                    </div>
                    
                    <!-- Color -->
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                            สี
                        </label>
                        <div class="flex">
                            <input type="color" 
                                   id="color" 
                                   name="color" 
                                   value="<?= htmlspecialchars($_POST['color'] ?? '#3B82F6') ?>"
                                   class="h-10 w-16 border border-gray-300 rounded-l-md">
                            <input type="text" 
                                   value="<?= htmlspecialchars($_POST['color'] ?? '#3B82F6') ?>"
                                   readonly
                                   class="flex-1 px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-sm">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status and Options -->
            <div class="px-6 py-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">สถานะและตัวเลือก</h3>
                
                <div class="space-y-4">
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                        <div class="space-y-2">
                            <label class="inline-flex items-center">
                                <input type="radio" 
                                       name="status" 
                                       value="active" 
                                       <?= ($_POST['status'] ?? 'active') === 'active' ? 'checked' : '' ?>
                                       class="form-radio text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">ใช้งาน</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" 
                                       name="status" 
                                       value="inactive" 
                                       <?= ($_POST['status'] ?? '') === 'inactive' ? 'checked' : '' ?>
                                       class="form-radio text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">ไม่ใช้งาน</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Featured -->
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   name="is_featured" 
                                   value="1" 
                                   <?= !empty($_POST['is_featured']) ? 'checked' : '' ?>
                                   class="form-checkbox text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">หมวดหมู่เด่น (แสดงในหน้าแรก)</span>
                        </label>
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
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
    
    <!-- Additional Info -->
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">ข้อมูลเพิ่มเติม</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <span class="font-medium text-gray-700">สร้างโดย:</span>
                <span class="text-gray-600"><?= htmlspecialchars($categoryData['first_name'] . ' ' . $categoryData['last_name']) ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">วันที่สร้าง:</span>
                <span class="text-gray-600"><?= formatThaiDate($categoryData['created_at']) ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">จำนวนหมวดหมู่ย่อย:</span>
                <span class="text-gray-600"><?= number_format($categoryData['children_count']) ?> หมวดหมู่</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">จำนวนเอกสาร:</span>
                <span class="text-gray-600"><?= number_format($categoryData['documents_count']) ?> เอกสาร</span>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-generate slug from name (only if slug is empty)
    $('#name').on('input', function() {
        const name = $(this).val();
        const currentSlug = $('#slug').val();
        if (name && !currentSlug) {
            const slug = generateSlug(name);
            $('#slug').val(slug);
        }
    });
    
    // Update color preview
    $('#color').on('input', function() {
        $(this).next('input').val($(this).val());
    });
    
    // Update icon preview
    $('#icon').on('input', function() {
        const iconName = $(this).val();
        $(this).prev('span').find('i').attr('class', 'fas fa-' + iconName);
    });
});

function generateSlug(text) {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
}
</script>

<?php require_once '../includes/footer.php'; ?>