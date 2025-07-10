<?php
require_once '../includes/auth.php';

// Check permission
if (!hasPermission('users.delete')) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ในการลบผู้ใช้']);
        exit;
    } else {
        header('Location: ' . BASE_URL . '/admin/users/');
        exit;
    }
}

// Get user ID
$user_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($user_id <= 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสผู้ใช้']);
        exit;
    } else {
        header('Location: ' . BASE_URL . '/admin/users/');
        exit;
    }
}

// Check if trying to delete own account
if ($user_id === (int)$_SESSION['user_id']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบบัญชีของตนเองได้']);
        exit;
    } else {
        $_SESSION['error'] = 'ไม่สามารถลบบัญชีของตนเองได้';
        header('Location: ' . BASE_URL . '/admin/users/');
        exit;
    }
}

// Initialize database and classes
$db = new Database();

// Get user data
$user_sql = "SELECT u.*, r.name as role_name, r.display_name as role_display_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = :id";
$user_stmt = $db->query($user_sql, [':id' => $user_id]);
$user_data = $user_stmt->fetch();

if (!$user_data) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'ไม่พบผู้ใช้ที่ต้องการลบ']);
        exit;
    } else {
        header('Location: ' . BASE_URL . '/admin/users/');
        exit;
    }
}

// Handle POST request (actual deletion)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    
    try {
        $db->beginTransaction();
        
        // Check for dependencies
        $dependencies = [];
        
        // Check documents created by this user
        $doc_count = $db->query("SELECT COUNT(*) as count FROM documents WHERE created_by = :user_id", 
                               [':user_id' => $user_id])->fetch()['count'];
        if ($doc_count > 0) {
            $dependencies[] = "เอกสาร {$doc_count} รายการ";
        }
        
        // Check documents assigned to this user
        $assigned_count = $db->query("SELECT COUNT(*) as count FROM documents WHERE assigned_to = :user_id", 
                                   [':user_id' => $user_id])->fetch()['count'];
        if ($assigned_count > 0) {
            $dependencies[] = "เอกสารที่ได้รับมอบหมาย {$assigned_count} รายการ";
        }
        
        // Check activity logs
        $activity_count = $db->query("SELECT COUNT(*) as count FROM activity_logs WHERE user_id = :user_id", 
                                   [':user_id' => $user_id])->fetch()['count'];
        if ($activity_count > 0) {
            $dependencies[] = "บันทึกกิจกรรม {$activity_count} รายการ";
        }
        
        // If there are dependencies, ask for confirmation or handle appropriately
        $force_delete = $_POST['force_delete'] ?? false;
        
        if (!empty($dependencies) && !$force_delete) {
            $db->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'ผู้ใช้นี้มีข้อมูลที่เกี่ยวข้อง: ' . implode(', ', $dependencies),
                'dependencies' => $dependencies,
                'requires_confirmation' => true
            ]);
            exit;
        }
        
        // Perform soft delete or hard delete based on configuration
        $soft_delete = true; // You can make this configurable
        
        if ($soft_delete) {
            // Soft delete: mark as deleted but keep data
            $delete_sql = "UPDATE users SET 
                          status = 'deleted',
                          deleted_at = NOW(),
                          deleted_by = :deleted_by,
                          updated_at = NOW(),
                          updated_by = :updated_by
                          WHERE id = :id";
            
            $params = [
                ':deleted_by' => $_SESSION['user_id'],
                ':updated_by' => $_SESSION['user_id'],
                ':id' => $user_id
            ];
        } else {
            // Hard delete: remove all related data first
            
            // Delete user sessions
            $db->query("DELETE FROM user_sessions WHERE user_id = :user_id", [':user_id' => $user_id]);
            
            // Delete login history
            $db->query("DELETE FROM user_login_history WHERE user_id = :user_id", [':user_id' => $user_id]);
            
            // Update documents to remove user references
            $db->query("UPDATE documents SET created_by = NULL WHERE created_by = :user_id", [':user_id' => $user_id]);
            $db->query("UPDATE documents SET assigned_to = NULL WHERE assigned_to = :user_id", [':user_id' => $user_id]);
            $db->query("UPDATE documents SET updated_by = NULL WHERE updated_by = :user_id", [':user_id' => $user_id]);
            
            // Update activity logs to remove user references (or delete them)
            $db->query("UPDATE activity_logs SET user_id = NULL WHERE user_id = :user_id", [':user_id' => $user_id]);
            
            // Finally delete the user
            $delete_sql = "DELETE FROM users WHERE id = :id";
            $params = [':id' => $user_id];
        }
        
        $stmt = $db->query($delete_sql, $params);
        
        // Log the deletion activity
        $activity_log = new ActivityLog();
        $activity_log->log($_SESSION['user_id'], ACTION_DELETE, 'users', $user_id, 
                          "ลบผู้ใช้: {$user_data['first_name']} {$user_data['last_name']} ({$user_data['username']})", 
                          $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        
        $db->commit();
        
        // Send notification email to admin (if configured)
        if (ENABLE_EMAIL_NOTIFICATIONS) {
            $admin_email = ADMIN_EMAIL;
            $subject = 'ผู้ใช้ถูกลบจากระบบ';
            $message = "ผู้ใช้ {$user_data['first_name']} {$user_data['last_name']} ({$user_data['username']}) ถูกลบจากระบบโดย " . 
                      $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] . " เมื่อ " . date('Y-m-d H:i:s');
            
            // Send email (implement your email function)
            // sendNotificationEmail($admin_email, $subject, $message);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'ลบผู้ใช้เรียบร้อยแล้ว',
            'redirect' => BASE_URL . '/admin/users/'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        error_log("Delete user error: " . $e->getMessage());
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการลบผู้ใช้'
        ]);
    }
    exit;
}

// If GET request, show confirmation page
$pageTitle = 'ลบผู้ใช้';
$pageSubtitle = 'ยืนยันการลบ ' . $user_data['first_name'] . ' ' . $user_data['last_name'];

require_once '../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="<?= BASE_URL ?>/admin/" class="text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="<?= BASE_URL ?>/admin/users/" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">จัดการผู้ใช้</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-gray-500 md:ml-2">ลบผู้ใช้</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
            <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
        </div>
    </div>

    <!-- Warning Card -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-red-800">คำเตือน!</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>การลบผู้ใช้นี้จะส่งผลต่อข้อมูลในระบบ กรุณาตรวจสอบข้อมูลต่อไปนี้ก่อนดำเนินการ:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>เอกสารที่สร้างโดยผู้ใช้นี้</li>
                        <li>เอกสารที่ได้รับมอบหมาย</li>
                        <li>ประวัติการทำงานในระบบ</li>
                        <li>การเชื่อมโยงข้อมูลอื่นๆ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- User Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">ข้อมูลผู้ใช้ที่จะลบ</h3>
        
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                <div class="h-16 w-16 rounded-full bg-red-100 flex items-center justify-center">
                    <span class="text-xl font-bold text-red-600">
                        <?= strtoupper(substr($user_data['first_name'], 0, 1) . substr($user_data['last_name'], 0, 1)) ?>
                    </span>
                </div>
            </div>
            <div class="flex-1">
                <h4 class="text-xl font-medium text-gray-900">
                    <?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?>
                </h4>
                <p class="text-gray-500">@<?= htmlspecialchars($user_data['username']) ?></p>
                <p class="text-gray-500"><?= htmlspecialchars($user_data['email']) ?></p>
                
                <div class="mt-2 flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <?= htmlspecialchars($user_data['role_display_name'] ?? $user_data['role_name'] ?? 'ไม่ระบุ') ?>
                    </span>
                    
                    <?php
                    $status_colors = [
                        'active' => 'bg-green-100 text-green-800',
                        'inactive' => 'bg-yellow-100 text-yellow-800',
                        'banned' => 'bg-red-100 text-red-800'
                    ];
                    $status_labels = [
                        'active' => 'ใช้งาน',
                        'inactive' => 'ระงับ',
                        'banned' => 'ห้ามใช้งาน'
                    ];
                    $status_class = $status_colors[$user_data['status']] ?? 'bg-gray-100 text-gray-800';
                    $status_label = $status_labels[$user_data['status']] ?? $user_data['status'];
                    ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $status_class ?>">
                        <?= $status_label ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Dependencies Check -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8" id="dependencies-check">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            กำลังตรวจสอบข้อมูลที่เกี่ยวข้อง...
        </h3>
        <div id="dependencies-list"></div>
    </div>

    <!-- Confirmation Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" id="confirmation-form" style="display: none;">
        <h3 class="text-lg font-medium text-gray-900 mb-4">ยืนยันการลบ</h3>
        
        <form id="deleteForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id" value="<?= $user_id ?>">
            <input type="hidden" name="force_delete" value="false" id="force_delete">
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" id="confirm_delete" class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">
                        ฉันเข้าใจและยืนยันที่จะลบผู้ใช้ <strong><?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?></strong>
                    </span>
                </label>
            </div>
            
            <div id="force-delete-option" class="mb-6" style="display: none;">
                <label class="flex items-center">
                    <input type="checkbox" id="force_delete_checkbox" class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">
                        ลบผู้ใช้แม้ว่าจะมีข้อมูลที่เกี่ยวข้อง (ข้อมูลที่เกี่ยวข้องจะถูกอัปเดตหรือลบด้วย)
                    </span>
                </label>
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $user_id ?>" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    <i class="fas fa-times mr-2"></i>
                    ยกเลิก
                </a>
                <button type="submit" 
                        id="delete_button"
                        disabled
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-trash mr-2"></i>
                    ลบผู้ใช้
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Check dependencies
    checkDependencies();
    
    // Handle confirmation checkbox
    $('#confirm_delete').on('change', function() {
        const deleteButton = $('#delete_button');
        if ($(this).is(':checked')) {
            deleteButton.prop('disabled', false);
        } else {
            deleteButton.prop('disabled', true);
        }
    });
    
    // Handle force delete checkbox
    $('#force_delete_checkbox').on('change', function() {
        $('#force_delete').val($(this).is(':checked') ? 'true' : 'false');
    });
    
    // Handle form submission
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $button = $('#delete_button');
        const originalText = $button.html();
        
        // Show loading state
        $button.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin mr-2"></i>กำลังลบ...');
        
        $.ajax({
            url: '',
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: response.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    if (response.requires_confirmation) {
                        $('#force-delete-option').show();
                        Swal.fire({
                            title: 'พบข้อมูลที่เกี่ยวข้อง',
                            text: response.message,
                            icon: 'warning',
                            confirmButtonText: 'ตกลง'
                        });
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด!', response.message, 'error');
                    }
                }
            },
            error: function() {
                Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).html(originalText);
            }
        });
    });
});

function checkDependencies() {
    $.ajax({
        url: '<?= BASE_URL ?>/admin/api/check-user-dependencies.php',
        method: 'POST',
        data: {
            user_id: <?= $user_id ?>,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#dependencies-check h3').html('<i class="fas fa-info-circle mr-2"></i>ข้อมูลที่เกี่ยวข้อง');
            
            if (response.dependencies && response.dependencies.length > 0) {
                let html = '<div class="space-y-2">';
                response.dependencies.forEach(function(dep) {
                    html += `<div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-link mr-2 text-gray-400"></i>
                        ${dep}
                    </div>`;
                });
                html += '</div>';
                
                html += '<div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">';
                html += '<p class="text-sm text-yellow-700">หากคุณดำเนินการลบ ข้อมูลเหล่านี้จะได้รับผลกระทบ</p>';
                html += '</div>';
                
                $('#dependencies-list').html(html);
            } else {
                $('#dependencies-list').html('<p class="text-sm text-gray-500">ไม่พบข้อมูลที่เกี่ยวข้อง ปลอดภัยในการลบ</p>');
            }
            
            $('#confirmation-form').show();
        },
        error: function() {
            $('#dependencies-check h3').html('<i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>ไม่สามารถตรวจสอบข้อมูลได้');
            $('#dependencies-list').html('<p class="text-sm text-red-600">เกิดข้อผิดพลาดในการตรวจสอบข้อมูล</p>');
            $('#confirmation-form').show();
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>