<?php
require_once '../includes/auth.php';
require_once '../../classes/User.php';

// Check permission
requirePermission(PERM_USER_DELETE);

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    header('Location: index.php?error=not_found');
    exit;
}

// Prevent self-deletion
if ($userId == $_SESSION['user_id']) {
    setFlashMessage('error', 'คุณไม่สามารถลบบัญชีของตัวเองได้');
    header('Location: view.php?id=' . $userId);
    exit;
}

try {
    $user = new User();
    
    // Get user data for logging
    $userData = $user->getById($userId);
    if (!$userData) {
        header('Location: index.php?error=not_found');
        exit;
    }
    
    // Check if user has uploaded documents
    $documentCount = $user->getDocumentCount($userId);
    if ($documentCount > 0) {
        setFlashMessage('warning', 'ไม่สามารถลบผู้ใช้ได้ เนื่องจากมีเอกสารที่เกี่ยวข้อง ' . $documentCount . ' รายการ กรุณาจัดการเอกสารก่อน');
        header('Location: view.php?id=' . $userId);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        checkCSRF();
        
        // Delete profile image if exists
        if ($userData['profile_image']) {
            $imagePath = '../../uploads/profiles/' . $userData['profile_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete user
        if ($user->delete($userId)) {
            // Log activity
            logAdminActivity(ACTION_DELETE, 'users', $userId, $userData, null);
            
            setFlashMessage('success', 'ลบผู้ใช้ ' . $userData['first_name'] . ' ' . $userData['last_name'] . ' เรียบร้อยแล้ว');
            header('Location: index.php');
            exit;
        } else {
            setFlashMessage('error', 'เกิดข้อผิดพลาดในการลบผู้ใช้');
            header('Location: view.php?id=' . $userId);
            exit;
        }
    }
    
} catch (Exception $e) {
    error_log("Delete user error: " . $e->getMessage());
    setFlashMessage('error', 'เกิดข้อผิดพลาดในระบบ');
    header('Location: view.php?id=' . $userId);
    exit;
}

// If GET request, show confirmation page
$pageTitle = 'ยืนยันการลบผู้ใช้';
$pageSubtitle = 'การดำเนินการนี้ไม่สามารถยกเลิกได้';

require_once '../includes/header.php';

// Set breadcrumb
$breadcrumbItems = [
    ['title' => 'หน้าหลัก', 'url' => BASE_URL . '/admin/'],
    ['title' => 'จัดการผู้ใช้', 'url' => BASE_URL . '/admin/users/'],
    ['title' => 'ลบผู้ใช้']
];
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>ยืนยันการลบผู้ใช้
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <?php if ($userData['profile_image']): ?>
                    <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($userData['profile_image']) ?>" 
                         class="rounded-circle border" width="80" height="80" alt="Profile">
                    <?php else: ?>
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user fa-2x text-white"></i>
                    </div>
                    <?php endif; ?>
                    
                    <h6 class="mt-3 mb-1"><?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></h6>
                    <p class="text-muted">@<?= htmlspecialchars($userData['username']) ?></p>
                </div>
                
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>คำเตือน</h6>
                    <p class="mb-2">คุณกำลังจะลบผู้ใช้ <strong><?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></strong></p>
                    <p class="mb-0">การดำเนินการนี้จะ:</p>
                    <ul class="mb-0">
                        <li>ลบข้อมูลผู้ใช้ออกจากระบบถาวร</li>
                        <li>ลบรูปโปรไฟล์และไฟล์ที่เกี่ยวข้อง</li>
                        <li>ลบประวัติการเข้าใช้งาน</li>
                        <li><strong>ไม่สามารถยกเลิกการดำเนินการนี้ได้</strong></li>
                    </ul>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <div class="fw-bold text-primary"><?= number_format($documentCount) ?></div>
                                <small class="text-muted">เอกสารที่อัปโหลด</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <div class="fw-bold text-success"><?= formatThaiDate($userData['created_at']) ?></div>
                                <small class="text-muted">สมัครสมาชิกเมื่อ</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($documentCount > 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>ไม่สามารถลบผู้ใช้ได้</strong><br>
                    ผู้ใช้คนนี้มีเอกสารที่อัปโหลดอยู่ <?= number_format($documentCount) ?> รายการ 
                    กรุณาจัดการเอกสารก่อนดำเนินการลบผู้ใช้
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="view.php?id=<?= $userId ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>กลับ
                    </a>
                    <a href="<?= BASE_URL ?>/admin/documents/?uploader=<?= $userId ?>" class="btn btn-primary">
                        <i class="fas fa-file-alt me-2"></i>จัดการเอกสาร
                    </a>
                </div>
                
                <?php else: ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <?= getCSRFInput() ?>
                    
                    <div class="mb-3">
                        <label for="confirm_text" class="form-label">
                            พิมพ์ <code>DELETE</code> เพื่อยืนยันการลบ:
                        </label>
                        <input type="text" class="form-control" id="confirm_text" name="confirm_text" 
                               required autocomplete="off" placeholder="พิมพ์ DELETE">
                        <div class="invalid-feedback">
                            กรุณาพิมพ์ DELETE เพื่อยืนยัน
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="view.php?id=<?= $userId ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-danger" id="delete-btn" disabled>
                            <i class="fas fa-trash me-2"></i>ลบผู้ใช้
                        </button>
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Enable delete button only when correct text is entered
    $('#confirm_text').on('input', function() {
        const deleteBtn = $('#delete-btn');
        if ($(this).val().trim() === 'DELETE') {
            deleteBtn.prop('disabled', false);
        } else {
            deleteBtn.prop('disabled', true);
        }
    });
    
    // Form submission confirmation
    $('form').on('submit', function(e) {
        const confirmText = $('#confirm_text').val().trim();
        if (confirmText !== 'DELETE') {
            e.preventDefault();
            $('#confirm_text').addClass('is-invalid');
            return false;
        }
        
        // Show loading
        const deleteBtn = $('#delete-btn');
        deleteBtn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังลบ...');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>