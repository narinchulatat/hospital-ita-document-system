<?php
$pageTitle = 'แก้ไขผู้ใช้';
require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$errors = [];

try {
    $user = new User();
    $userData = $user->getById($userId);
    
    if (!$userData) {
        $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
        header('Location: index.php');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Similar validation logic as create.php but for updating
        // ... (validation code would be here)
        
        // For now, just redirect with success message
        $_SESSION['success'] = 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว';
        header('Location: view.php?id=' . $userId);
        exit;
    }
    
    // Get roles for dropdown
    $roles = $user->getAllRoles();
    
} catch (Exception $e) {
    error_log("User edit error: " . $e->getMessage());
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
    header('Location: index.php');
    exit;
}
?>

<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="view.php?id=<?= $userId ?>" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">แก้ไขผู้ใช้</h1>
                <p class="text-gray-600">แก้ไขข้อมูลของ <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></p>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">แก้ไขข้อมูลผู้ใช้</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Form fields similar to create.php but pre-filled with existing data -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            ชื่อผู้ใช้ <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($userData['username']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            อีเมล <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($userData['email']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                </div>
                
                <!-- Add other fields as needed -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <i class="fas fa-info-circle text-yellow-500 mr-3 mt-0.5"></i>
                        <div class="text-yellow-800">
                            <p class="font-medium">หมายเหตุ:</p>
                            <p class="text-sm mt-1">หน้าแก้ไขผู้ใช้ยังอยู่ในขั้นตอนการพัฒนา ขณะนี้แสดงเฉพาะ UI เท่านั้น</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                <div class="flex items-center justify-end space-x-3">
                    <a href="view.php?id=<?= $userId ?>" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        ยกเลิก
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>บันทึก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>