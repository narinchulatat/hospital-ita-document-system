<?php
$pageTitle = 'เข้าสู่ระบบ';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(getRoleRedirectUrl(getCurrentUserRole()));
}

$error = '';
$timeout = isset($_GET['timeout']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrfToken)) {
        $error = 'Token ความปลอดภัยไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
    } elseif (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        try {
            $user = new User();
            $authenticatedUser = $user->authenticate($username, $password);
            
            if ($authenticatedUser) {
                loginUser($authenticatedUser);
                
                // Redirect to appropriate dashboard
                redirectTo(getRoleRedirectUrl($authenticatedUser['role_id']));
            } else {
                $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง';
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center">
                <i class="fas fa-hospital text-4xl text-blue-600"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                เข้าสู่ระบบ
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                <?= htmlspecialchars(SITE_NAME) ?>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST">
            <?= getCSRFTokenInput() ?>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($timeout): ?>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่</span>
            </div>
            <?php endif; ?>
            
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">ชื่อผู้ใช้</label>
                    <input id="username" 
                           name="username" 
                           type="text" 
                           required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                           placeholder="ชื่อผู้ใช้"
                           value="<?= htmlspecialchars($username ?? '') ?>">
                </div>
                <div>
                    <label for="password" class="sr-only">รหัสผ่าน</label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                           placeholder="รหัสผ่าน">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" 
                           name="remember-me" 
                           type="checkbox" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        จดจำการเข้าสู่ระบบ
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    เข้าสู่ระบบ
                </button>
            </div>
            
            <div class="text-center">
                <a href="<?= BASE_URL ?>/public/" class="text-blue-600 hover:text-blue-500 text-sm">
                    <i class="fas fa-home mr-2"></i>กลับสู่หน้าหลัก
                </a>
            </div>
        </form>
        
        <!-- Demo credentials info -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-800 mb-2">ข้อมูลการเข้าสู่ระบบสำหรับทดสอบ:</h4>
            <div class="text-xs text-blue-700 space-y-1">
                <div><strong>ผู้ดูแลระบบ:</strong> admin / admin123</div>
                <div class="text-blue-600 italic">* สามารถใช้ข้อมูลนี้สำหรับทดสอบระบบ</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Focus on username field
    document.getElementById('username').focus();
    
    // Auto-fill demo credentials when clicking on the demo info
    const demoInfo = document.querySelector('.bg-blue-50');
    if (demoInfo) {
        demoInfo.addEventListener('click', function() {
            document.getElementById('username').value = 'admin';
            document.getElementById('password').value = 'admin123';
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>