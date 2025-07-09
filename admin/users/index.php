<?php
$pageTitle = 'จัดการผู้ใช้';
require_once '../../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

try {
    $user = new User();
    
    // Handle user actions
    $action = $_GET['action'] ?? '';
    $userId = $_GET['id'] ?? 0;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $userData = [
                'username' => sanitizeInput($_POST['username']),
                'email' => sanitizeInput($_POST['email']),
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'first_name' => sanitizeInput($_POST['first_name']),
                'last_name' => sanitizeInput($_POST['last_name']),
                'role_id' => (int)$_POST['role_id'],
                'status' => 'active'
            ];
            
            if ($user->create($userData)) {
                showAlert('เพิ่มผู้ใช้สำเร็จ', 'success');
            } else {
                showAlert('เกิดข้อผิดพลาดในการเพิ่มผู้ใช้', 'error');
            }
            
            redirectTo(BASE_URL . '/admin/users/');
        }
    }
    
    // Get users with pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $search = sanitizeInput($_GET['search'] ?? '');
    
    $users = $user->getAll($page, ITEMS_PER_PAGE, $search);
    $totalUsers = $user->getTotalCount($search);
    $totalPages = ceil($totalUsers / ITEMS_PER_PAGE);
    
    // Get roles for dropdown
    $db = Database::getInstance();
    $roles = $db->fetchAll("SELECT id, name FROM roles ORDER BY id");
    
} catch (Exception $e) {
    error_log("User management error: " . $e->getMessage());
    $users = [];
    $totalUsers = 0;
    $totalPages = 1;
    $roles = [];
    showAlert('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
}

$alert = getAlert();
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-users mr-3"></i>จัดการผู้ใช้
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                จัดการบัญชีผู้ใช้และสิทธิ์การเข้าถึง
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="showCreateModal()" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i>
                เพิ่มผู้ใช้ใหม่
            </button>
        </div>
    </div>

    <?php if ($alert): ?>
    <div class="mb-6 bg-<?= $alert['type'] === 'success' ? 'green' : 'red' ?>-50 border border-<?= $alert['type'] === 'success' ? 'green' : 'red' ?>-200 text-<?= $alert['type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline"><?= e($alert['message']) ?></span>
    </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <form method="GET" class="flex items-center space-x-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">ค้นหา</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" id="search" value="<?= e($search) ?>" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="ค้นหาผู้ใช้...">
                    </div>
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    ค้นหา
                </button>
                <?php if ($search): ?>
                <a href="<?= BASE_URL ?>/admin/users/" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    ล้างการค้นหา
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            <?php if (empty($users)): ?>
            <li class="px-6 py-4 text-center text-gray-500">
                <?= $search ? 'ไม่พบผู้ใช้ที่ค้นหา' : 'ไม่มีผู้ใช้ในระบบ' ?>
            </li>
            <?php else: ?>
            <?php foreach ($users as $userData): ?>
            <li>
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="text-white font-medium text-sm">
                                    <?= strtoupper(substr($userData['first_name'], 0, 1)) ?>
                                </span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= e($userData['first_name'] . ' ' . $userData['last_name']) ?>
                                </div>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $userData['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $userData['status'] === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน' ?>
                                </span>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?= e($userData['username']) ?> • <?= e($userData['email']) ?>
                            </div>
                            <div class="text-xs text-gray-400">
                                <?= e($userData['role_name']) ?> • สมัครเมื่อ <?= formatThaiDate($userData['created_at']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="editUser(<?= $userData['id'] ?>)" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($userData['id'] != getCurrentUserId()): ?>
                        <button onclick="deleteUser(<?= $userData['id'] ?>)" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-6">
        <?= generatePagination($page, $totalPages, BASE_URL . '/admin/users/' . ($search ? '?search=' . urlencode($search) : '')) ?>
    </div>
    <?php endif; ?>
</div>

<!-- Create User Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">เพิ่มผู้ใช้ใหม่</h3>
            <form method="POST">
                <?= getCSRFTokenInput() ?>
                <input type="hidden" name="action" value="create">
                
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้</label>
                    <input type="text" name="username" id="username" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                    <input type="email" name="email" id="email" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน</label>
                    <input type="password" name="password" id="password" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="first_name" class="block text-sm font-medium text-gray-700">ชื่อ</label>
                    <input type="text" name="first_name" id="first_name" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="last_name" class="block text-sm font-medium text-gray-700">นามสกุล</label>
                    <input type="text" name="last_name" id="last_name" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="role_id" class="block text-sm font-medium text-gray-700">บทบาท</label>
                    <select name="role_id" id="role_id" required 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>"><?= e($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideCreateModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        ยกเลิก
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        เพิ่มผู้ใช้
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function hideCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function editUser(userId) {
    // TODO: Implement edit functionality
    alert('ฟีเจอร์แก้ไขผู้ใช้จะพัฒนาในอนาคต');
}

function deleteUser(userId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?')) {
        // TODO: Implement delete functionality
        alert('ฟีเจอร์ลบผู้ใช้จะพัฒนาในอนาคต');
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>