<?php
require_once '../includes/header.php';
require_once 'functions.php';

// Check permission
if (!hasPermission('categories.delete')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$category = new Category();

// Get category ID
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ไม่พบรหัสหมวดหมู่'
    ];
    header('Location: ' . BASE_URL . '/admin/categories/');
    exit;
}

// Get category data
$categoryData = $category->getById($id);
if (!$categoryData) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ไม่พบหมวดหมู่ที่ต้องการลบ'
    ];
    header('Location: ' . BASE_URL . '/admin/categories/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Invalid CSRF token'
        ];
        header('Location: ' . BASE_URL . '/admin/categories/');
        exit;
    }
    
    // Check if can delete
    if (!canDeleteCategory($id)) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากมีหมวดหมู่ย่อยหรือเอกสารที่เชื่อมโยงอยู่'
        ];
        header('Location: ' . BASE_URL . '/admin/categories/');
        exit;
    }
    
    try {
        // Store old data for logging
        $oldData = $categoryData;
        
        // Delete category
        $category->delete($id);
        
        // Log activity
        logActivity(ACTION_DELETE, 'categories', $id, $oldData, null);
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'ลบหมวดหมู่ "' . $categoryData['name'] . '" เรียบร้อยแล้ว'
        ];
        
    } catch (Exception $e) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ];
    }
    
    header('Location: ' . BASE_URL . '/admin/categories/');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'ลบหมวดหมู่';
$pageSubtitle = 'ยืนยันการลบหมวดหมู่เอกสาร';
?>

<div class="max-w-2xl mx-auto">
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

    <!-- Warning Card -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-red-800">คำเตือน</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>คุณกำลังจะลบหมวดหมู่นี้ การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">ข้อมูลหมวดหมู่ที่จะลบ</h3>
        </div>
        
        <div class="px-6 py-4">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" 
                         style="background-color: <?= htmlspecialchars($categoryData['color']) ?>20;">
                        <i class="fas fa-<?= htmlspecialchars($categoryData['icon']) ?> text-xl" 
                           style="color: <?= htmlspecialchars($categoryData['color']) ?>"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($categoryData['name']) ?></h4>
                    <?php if ($categoryData['description']): ?>
                    <p class="text-gray-600 mt-1"><?= htmlspecialchars($categoryData['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Slug:</span>
                            <span class="text-gray-600"><?= htmlspecialchars($categoryData['slug']) ?></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">สถานะ:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $categoryData['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $categoryData['status'] === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน' ?>
                            </span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">หมวดหมู่ย่อย:</span>
                            <span class="text-gray-600"><?= number_format($categoryData['children_count']) ?> หมวดหมู่</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">เอกสาร:</span>
                            <span class="text-gray-600"><?= number_format($categoryData['documents_count']) ?> เอกสาร</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">สร้างโดย:</span>
                            <span class="text-gray-600"><?= htmlspecialchars($categoryData['first_name'] . ' ' . $categoryData['last_name']) ?></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">วันที่สร้าง:</span>
                            <span class="text-gray-600"><?= formatThaiDate($categoryData['created_at']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check if can delete -->
    <?php if (!canDeleteCategory($id)): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">ไม่สามารถลบได้</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>ไม่สามารถลบหมวดหมู่นี้ได้ด้วยเหตุผลต่อไปนี้:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <?php if ($categoryData['children_count'] > 0): ?>
                        <li>มีหมวดหมู่ย่อย <?= number_format($categoryData['children_count']) ?> หมวดหมู่</li>
                        <?php endif; ?>
                        <?php if ($categoryData['documents_count'] > 0): ?>
                        <li>มีเอกสารที่เชื่อมโยง <?= number_format($categoryData['documents_count']) ?> เอกสาร</li>
                        <?php endif; ?>
                    </ul>
                    <p class="mt-2">กรุณาย้ายหรือลบหมวดหมู่ย่อยและเอกสารทั้งหมดก่อนลบหมวดหมู่นี้</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons - View Only -->
    <div class="flex justify-end space-x-3">
        <a href="<?= BASE_URL ?>/admin/categories/" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            กลับ
        </a>
        <a href="<?= BASE_URL ?>/admin/categories/edit.php?id=<?= $id ?>" 
           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-edit mr-2"></i>
            แก้ไขแทน
        </a>
    </div>
    
    <?php else: ?>
    
    <!-- Confirmation Form -->
    <form method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="px-6 py-4">
            <div class="text-center">
                <p class="text-gray-700 mb-4">
                    พิมพ์ <strong class="text-red-600"><?= htmlspecialchars($categoryData['name']) ?></strong> 
                    เพื่อยืนยันการลบ
                </p>
                
                <input type="text" 
                       id="confirmName" 
                       placeholder="พิมพ์ชื่อหมวดหมู่ที่ต้องการลบ"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm mb-4"
                       required>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
            <a href="<?= BASE_URL ?>/admin/categories/" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                ยกเลิก
            </a>
            <button type="submit" 
                    id="deleteBtn"
                    disabled
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i>
                ลบหมวดหมู่
            </button>
        </div>
    </form>
    
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    const confirmName = '<?= addslashes($categoryData['name']) ?>';
    const $confirmInput = $('#confirmName');
    const $deleteBtn = $('#deleteBtn');
    
    $confirmInput.on('input', function() {
        const inputValue = $(this).val().trim();
        if (inputValue === confirmName) {
            $deleteBtn.prop('disabled', false);
        } else {
            $deleteBtn.prop('disabled', true);
        }
    });
    
    // Additional confirmation on submit
    $('form').on('submit', function(e) {
        const confirmValue = $confirmInput.val().trim();
        if (confirmValue !== confirmName) {
            e.preventDefault();
            alert('กรุณาพิมพ์ชื่อหมวดหมู่ให้ถูกต้อง');
            return false;
        }
        
        return confirm('คุณแน่ใจหรือไม่ที่จะลบหมวดหมู่นี้? การดำเนินการนี้ไม่สามารถยกเลิกได้');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>