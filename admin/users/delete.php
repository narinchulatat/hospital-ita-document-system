<?php
require_once '../includes/auth.php';

// Require admin role
requireRole(ROLE_ADMIN);

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
    header('Location: index.php');
    exit;
}

// Prevent self-deletion
if ($userId == $_SESSION['user_id']) {
    $_SESSION['error'] = 'ไม่สามารถลบบัญชีของตนเองได้';
    header('Location: index.php');
    exit;
}

try {
    $user = new User();
    $userData = $user->getById($userId);
    
    if (!$userData) {
        $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
        header('Location: index.php');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        // Delete user
        if ($user->delete($userId)) {
            // Log activity
            $activityLog = new ActivityLog();
            $activityLog->log($_SESSION['user_id'], ACTION_DELETE, 'users', $userId, 
                json_encode(['username' => $userData['username']]));
            
            $_SESSION['success'] = 'ลบผู้ใช้เรียบร้อยแล้ว';
        } else {
            $_SESSION['error'] = 'เกิดข้อผิดพลาดในการลบผู้ใช้';
        }
        
        header('Location: index.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("User delete error: " . $e->getMessage());
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการลบผู้ใช้';
    header('Location: index.php');
    exit;
}

$pageTitle = 'ลบผู้ใช้';
require_once '../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="view.php?id=<?= $userId ?>" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">ลบผู้ใช้</h1>
                <p class="text-gray-600">ยืนยันการลบข้อมูลผู้ใช้</p>
            </div>
        </div>
    </div>

    <!-- Confirmation Form -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-medium text-red-900">ยืนยันการลบผู้ใช้</h3>
                    <p class="text-red-700 text-sm mt-1">การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- User Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-user text-gray-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-lg font-medium text-gray-900">
                            <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?= htmlspecialchars($userData['username']) ?> • <?= htmlspecialchars($userData['email']) ?>
                        </div>
                        <div class="text-sm text-gray-500">
                            บทบาท: <?= htmlspecialchars($userData['role_name']) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Warning Message -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
                    <div class="text-yellow-800">
                        <h4 class="font-medium">คำเตือน</h4>
                        <ul class="mt-2 text-sm list-disc list-inside space-y-1">
                            <li>ข้อมูลผู้ใช้จะถูกลบออกจากระบบโดยสมบูรณ์</li>
                            <li>ประวัติการใช้งานและกิจกรรมจะยังคงอยู่ในระบบ</li>
                            <li>เอกสารที่ผู้ใช้อัปโหลดจะยังคงอยู่ในระบบ</li>
                            <li>การดำเนินการนี้ไม่สามารถยกเลิกได้</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Confirmation -->
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" required 
                               class="text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">
                            ฉันเข้าใจและยืนยันที่จะลบผู้ใช้ <strong><?= htmlspecialchars($userData['username']) ?></strong>
                        </span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <a href="view.php?id=<?= $userId ?>" 
                       class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        ยกเลิก
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-2"></i>ลบผู้ใช้
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Additional confirmation
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'ยืนยันการลบผู้ใช้',
        text: 'คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ <?= htmlspecialchars($userData['username']) ?>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>