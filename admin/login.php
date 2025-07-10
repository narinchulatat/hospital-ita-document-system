<?php
session_start();
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/ActivityLog.php';

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && isAdmin()) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        try {
            $user = new User();
            $userData = $user->authenticate($username, $password);
            
            if ($userData) {
                // Check if user has admin role
                if ($userData['role_name'] === 'admin' || $user->hasRole($userData['id'], ROLE_ADMIN)) {
                    $_SESSION['user_id'] = $userData['id'];
                    $_SESSION['username'] = $userData['username'];
                    $_SESSION['first_name'] = $userData['first_name'];
                    $_SESSION['last_name'] = $userData['last_name'];
                    $_SESSION['role'] = $userData['role_name'];
                    $_SESSION['is_admin'] = true;
                    
                    // Log the activity
                    $activityLog = new ActivityLog();
                    $activityLog->log($userData['id'], ACTION_LOGIN, null, null, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                    
                    header('Location: ' . BASE_URL . '/admin/');
                    exit;
                } else {
                    $error = 'คุณไม่มีสิทธิ์เข้าใช้งานระบบผู้ดูแล';
                }
            } else {
                $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
            }
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            $error = 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ';
        }
    }
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแล - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/admin.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sarabun': ['Sarabun', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>
<body>
<body class="font-sarabun min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600">
    <div class="w-full max-w-md mx-4">
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 px-8 py-6 text-center text-white">
                <h2 class="text-2xl font-semibold mb-2">
                    <i class="fas fa-shield-alt mr-2"></i>
                    ระบบผู้ดูแล
                </h2>
                <p class="text-blue-100">เข้าสู่ระบบจัดการ</p>
            </div>
            
            <div class="p-8">
                <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">ชื่อผู้ใช้</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300" 
                                   id="username" 
                                   name="username" 
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                                   placeholder="กรอกชื่อผู้ใช้" 
                                   required 
                                   autofocus>
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">รหัสผ่าน</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300" 
                                   id="password" 
                                   name="password" 
                                   placeholder="กรอกรหัสผ่าน" 
                                   required>
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center" 
                                    id="togglePassword">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:shadow-lg transform hover:-translate-y-0.5 transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        เข้าสู่ระบบ
                    </button>
                </form>
                
                <div class="text-center mt-6">
                    <a href="<?= BASE_URL ?>" class="text-gray-600 hover:text-gray-800 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        กลับสู่หน้าหลัก
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Auto focus on username field
        document.getElementById('username').focus();
    </script>
</body>
</html>