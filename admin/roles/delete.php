<?php
require_once '../includes/header.php';
require_once 'functions.php';

// Check permission
if (!hasPermission('roles.delete')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$role = new Role();

// Get role ID
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ไม่พบรหัสบทบาท'
    ];
    header('Location: ' . BASE_URL . '/admin/roles/');
    exit;
}

// Get role data
$roleData = $role->getById($id);
if (!$roleData) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ไม่พบบทบาทที่ต้องการลบ'
    ];
    header('Location: ' . BASE_URL . '/admin/roles/');
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
        header('Location: ' . BASE_URL . '/admin/roles/');
        exit;
    }
    
    // Check if can delete
    if (!canDeleteRole($id)) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'ไม่สามารถลบบทบาทนี้ได้ เนื่องจากมีผู้ใช้ที่เชื่อมโยงอยู่หรือเป็นบทบาทระบบ'
        ];
        header('Location: ' . BASE_URL . '/admin/roles/');
        exit;
    }
    
    try {
        // Delete role
        $role->delete($id);
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'ลบบทบาท "' . $roleData['display_name'] . '" เรียบร้อยแล้ว'
        ];
        
    } catch (Exception $e) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ];
    }
    
    header('Location: ' . BASE_URL . '/admin/roles/');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get users count
$db = new Database();
$userCount = $db->query("SELECT COUNT(*) as count FROM users WHERE role_id = ?", [$id])->fetch()['count'];
$users = [];
if ($userCount > 0) {
    $users = $db->query("SELECT id, first_name, last_name, email FROM users WHERE role_id = ? LIMIT 5", [$id])->fetchAll();
}

$pageTitle = 'ลบบทบาท';
$pageSubtitle = 'ยืนยันการลบบทบาทผู้ใช้';
?>

<div class="max-w-2xl mx-auto">
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

    <!-- Warning Card -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-red-800">คำเตือน</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>คุณกำลังจะลบบทบาทนี้ การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">ข้อมูลบทบาทที่จะลบ</h3>
        </div>
        
        <div class="px-6 py-4">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center <?= getRoleBadgeClass($roleData['name']) ?>">
                        <i class="fas <?= getRoleIcon($roleData['name']) ?> text-xl"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($roleData['display_name']) ?></h4>
                    <?php if ($roleData['description']): ?>
                    <p class="text-gray-600 mt-1"><?= htmlspecialchars($roleData['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">ชื่อระบบ:</span>
                            <span class="text-gray-600 font-mono"><?= htmlspecialchars($roleData['name']) ?></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">สถานะ:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusBadgeClass($roleData['is_active']) ?>">
                                <?= getStatusLabel($roleData['is_active']) ?>
                            </span>
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
                        <div>
                            <span class="font-medium text-gray-700">วันที่สร้าง:</span>
                            <span class="text-gray-600"><?= formatThaiDate($roleData['created_at']) ?></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">วันที่แก้ไขล่าสุด:</span>
                            <span class="text-gray-600"><?= formatThaiDate($roleData['updated_at']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check if can delete -->
    <?php if (!canDeleteRole($id)): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">ไม่สามารถลบได้</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>ไม่สามารถลบบทบาทนี้ได้ด้วยเหตุผลต่อไปนี้:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <?php if ($userCount > 0): ?>
                        <li>มีผู้ใช้ <?= number_format($userCount) ?> คนที่ใช้บทบาทนี้</li>
                        <?php endif; ?>
                        <?php if (in_array($roleData['name'], ['admin', 'staff', 'approver', 'visitor'])): ?>
                        <li>เป็นบทบาทระบบที่จำเป็นต่อการทำงาน</li>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if ($userCount > 0): ?>
                    <div class="mt-4">
                        <p class="font-medium mb-2">ผู้ใช้ที่มีบทบาทนี้:</p>
                        <div class="space-y-2">
                            <?php foreach ($users as $user): ?>
                            <div class="flex items-center justify-between bg-white rounded p-2">
                                <span class="text-sm"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                                <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $user['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-900 text-xs">
                                    เปลี่ยนบทบาท
                                </a>
                            </div>
                            <?php endforeach; ?>
                            <?php if ($userCount > 5): ?>
                            <p class="text-xs text-gray-600">และอีก <?= number_format($userCount - 5) ?> คน</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons - View Only -->
    <div class="flex justify-end space-x-3">
        <a href="<?= BASE_URL ?>/admin/roles/" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            กลับ
        </a>
        <a href="<?= BASE_URL ?>/admin/roles/edit.php?id=<?= $id ?>" 
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
                    พิมพ์ <strong class="text-red-600"><?= htmlspecialchars($roleData['display_name']) ?></strong> 
                    เพื่อยืนยันการลบ
                </p>
                
                <input type="text" 
                       id="confirmName" 
                       placeholder="พิมพ์ชื่อบทบาทที่ต้องการลบ"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm mb-4"
                       required>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
            <a href="<?= BASE_URL ?>/admin/roles/" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                ยกเลิก
            </a>
            <button type="submit" 
                    id="deleteBtn"
                    disabled
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i>
                ลบบทบาท
            </button>
        </div>
    </form>
    
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    const confirmName = '<?= addslashes($roleData['display_name']) ?>';
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
            alert('กรุณาพิมพ์ชื่อบทบาทให้ถูกต้อง');
            return false;
        }
        
        return confirm('คุณแน่ใจหรือไม่ที่จะลบบทบาทนี้? การดำเนินการนี้ไม่สามารถยกเลิกได้');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>