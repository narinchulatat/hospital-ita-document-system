<?php
require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

$userId = intval($_GET['id'] ?? 0);

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($userId <= 0) {
    $_SESSION['error_message'] = 'ไม่พบผู้ใช้ที่ต้องการลบ';
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

// Prevent self-deletion
if ($userId == $_SESSION['user_id']) {
    $_SESSION['error_message'] = 'คุณไม่สามารถลบบัญชีของตัวเองได้';
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

try {
    $database = Database::getInstance();
    
    // Get user details
    $user = $database->fetch(
        "SELECT * FROM users WHERE id = ?",
        [$userId]
    );
    
    if (!$user) {
        $_SESSION['error_message'] = 'ไม่พบผู้ใช้ที่ต้องการลบ';
        header('Location: ' . BASE_URL . '/admin/users/');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error_message'] = 'Invalid CSRF token';
            header('Location: ' . BASE_URL . '/admin/users/');
            exit;
        }
        
        // Begin transaction
        $database->beginTransaction();
        
        try {
            // Log activity before deletion
            $activityLog = new ActivityLog();
            $activityLog->log(
                $_SESSION['user_id'], 
                ACTION_DELETE, 
                'users', 
                $userId, 
                $user['username'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );
            
            // Update documents uploaded by this user
            $database->execute(
                "UPDATE documents SET uploaded_by = NULL WHERE uploaded_by = ?",
                [$userId]
            );
            
            // Delete user
            $database->execute("DELETE FROM users WHERE id = ?", [$userId]);
            
            $database->commit();
            
            $_SESSION['success_message'] = 'ลบผู้ใช้ ' . $user['first_name'] . ' ' . $user['last_name'] . ' เรียบร้อยแล้ว';
            header('Location: ' . BASE_URL . '/admin/users/');
            exit;
            
        } catch (Exception $e) {
            $database->rollback();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    error_log("Delete user error: " . $e->getMessage());
    $_SESSION['error_message'] = 'เกิดข้อผิดพลาดในการลบผู้ใช้';
    header('Location: ' . BASE_URL . '/admin/users/');
    exit;
}

// If we reach here, show confirmation form (though normally handled by JavaScript)
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลบผู้ใช้ - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    'sans': ['Sarabun', 'sans-serif'],
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">ยืนยันการลบผู้ใช้</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>?
                        </p>
                        <p class="text-sm text-red-600 mt-2">
                            การดำเนินการนี้ไม่สามารถยกเลิกได้
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center justify-center space-x-4">
                    <a href="<?= BASE_URL ?>/admin/users/" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        ยกเลิก
                    </a>
                    
                    <form method="POST" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                            ลบผู้ใช้
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>