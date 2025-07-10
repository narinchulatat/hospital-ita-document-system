<?php
$pageTitle = 'รายละเอียดผู้ใช้';

require_once '../includes/header.php';
require_once '../../classes/User.php';
require_once '../../classes/ActivityLog.php';
require_once '../../classes/Document.php';

// Check permission
requirePermission(PERM_USER_VIEW);

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    header('Location: index.php?error=not_found');
    exit;
}

try {
    $user = new User();
    $activityLog = new ActivityLog();
    $document = new Document();
    
    // Get user data
    $userData = $user->getById($userId);
    if (!$userData) {
        header('Location: index.php?error=not_found');
        exit;
    }
    
    $pageSubtitle = htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']);
    
    // Get user's roles
    $userRoles = $user->getUserRoles($userId);
    
    // Get user's recent activities
    $recentActivities = $activityLog->getUserActivities($userId, 1, 10);
    
    // Get user's documents
    $userDocuments = $document->getByUploader($userId, 1, 5);
    
    // Get user statistics
    $stats = [
        'total_documents' => $document->getTotalByUploader($userId),
        'pending_documents' => $document->getTotalByUploader($userId, ['status' => DOC_STATUS_PENDING]),
        'approved_documents' => $document->getTotalByUploader($userId, ['status' => DOC_STATUS_APPROVED]),
        'total_logins' => $activityLog->getUserLoginCount($userId)
    ];
    
} catch (Exception $e) {
    error_log("View user error: " . $e->getMessage());
    header('Location: index.php?error=database_error');
    exit;
}

// Set breadcrumb
$breadcrumbItems = [
    ['title' => 'หน้าหลัก', 'url' => BASE_URL . '/admin/'],
    ['title' => 'จัดการผู้ใช้', 'url' => BASE_URL . '/admin/users/'],
    ['title' => 'รายละเอียดผู้ใช้']
];
?>

<div class="row">
    <div class="col-lg-4">
        <!-- User Profile Card -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    <?php if ($userData['profile_image']): ?>
                    <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($userData['profile_image']) ?>" 
                         class="rounded-circle border" width="120" height="120" alt="Profile">
                    <?php else: ?>
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                         style="width: 120px; height: 120px;">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <h5 class="mb-1"><?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></h5>
                <p class="text-muted mb-2">@<?= htmlspecialchars($userData['username']) ?></p>
                
                <div class="mb-3">
                    <?= getStatusBadge($userData['status'], 'user') ?>
                    <?php foreach ($userRoles as $role): ?>
                    <span class="badge bg-info ms-1"><?= htmlspecialchars($role['role_name']) ?></span>
                    <?php endforeach; ?>
                </div>
                
                <div class="d-flex justify-content-center gap-2">
                    <?php if (hasMenuPermission(PERM_USER_EDIT)): ?>
                    <a href="edit.php?id=<?= $userId ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>แก้ไข
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasMenuPermission(PERM_USER_DELETE) && $userId != $_SESSION['user_id']): ?>
                    <a href="delete.php?id=<?= $userId ?>" 
                       class="btn btn-danger btn-sm btn-delete"
                       data-title="ยืนยันการลบผู้ใช้"
                       data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?>?">
                        <i class="fas fa-trash me-1"></i>ลบ
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-address-card me-2"></i>ข้อมูลติดต่อ</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small">อีเมล</label>
                        <div>
                            <a href="mailto:<?= htmlspecialchars($userData['email']) ?>">
                                <?= htmlspecialchars($userData['email']) ?>
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($userData['phone']): ?>
                    <div class="col-12">
                        <label class="form-label text-muted small">โทรศัพท์</label>
                        <div>
                            <a href="tel:<?= htmlspecialchars($userData['phone']) ?>">
                                <?= htmlspecialchars($userData['phone']) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($userData['department']): ?>
                    <div class="col-12">
                        <label class="form-label text-muted small">แผนก</label>
                        <div><?= htmlspecialchars($userData['department']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($userData['position']): ?>
                    <div class="col-12">
                        <label class="form-label text-muted small">ตำแหน่ง</label>
                        <div><?= htmlspecialchars($userData['position']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>ข้อมูลระบบ</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small">สมัครสมาชิกเมื่อ</label>
                        <div><?= formatThaiDate($userData['created_at'], true) ?></div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label text-muted small">เข้าใช้ล่าสุด</label>
                        <div>
                            <?= $userData['last_login'] ? formatThaiDate($userData['last_login'], true) : 'ยังไม่เคยเข้าใช้' ?>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label text-muted small">ความล้มเหลวในการเข้าสู่ระบบ</label>
                        <div><?= $userData['failed_login_attempts'] ?? 0 ?> ครั้ง</div>
                    </div>
                    
                    <?php if ($userData['locked_until']): ?>
                    <div class="col-12">
                        <label class="form-label text-muted small">ล็อคจนถึง</label>
                        <div class="text-danger"><?= formatThaiDate($userData['locked_until'], true) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon bg-primary">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                        <div class="stats-content mt-3">
                            <div class="stats-number"><?= number_format($stats['total_documents']) ?></div>
                            <div class="stats-label">เอกสารทั้งหมด</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card warning">
                    <div class="card-body text-center">
                        <div class="stats-icon bg-warning">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div class="stats-content mt-3">
                            <div class="stats-number"><?= number_format($stats['pending_documents']) ?></div>
                            <div class="stats-label">รออนุมัติ</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card success">
                    <div class="card-body text-center">
                        <div class="stats-icon bg-success">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div class="stats-content mt-3">
                            <div class="stats-number"><?= number_format($stats['approved_documents']) ?></div>
                            <div class="stats-label">อนุมัติแล้ว</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card info">
                    <div class="card-body text-center">
                        <div class="stats-icon bg-info">
                            <i class="fas fa-sign-in-alt text-white"></i>
                        </div>
                        <div class="stats-content mt-3">
                            <div class="stats-number"><?= number_format($stats['total_logins']) ?></div>
                            <div class="stats-label">เข้าใช้ทั้งหมด</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" 
                                data-bs-target="#documents" type="button" role="tab">
                            <i class="fas fa-file-alt me-2"></i>เอกสาร
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activities-tab" data-bs-toggle="tab" 
                                data-bs-target="#activities" type="button" role="tab">
                            <i class="fas fa-history me-2"></i>กิจกรรม
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" 
                                data-bs-target="#permissions" type="button" role="tab">
                            <i class="fas fa-shield-alt me-2"></i>สิทธิ์
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content">
                    <!-- Documents Tab -->
                    <div class="tab-pane fade show active" id="documents" role="tabpanel">
                        <?php if (!empty($userDocuments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ชื่อเอกสาร</th>
                                        <th>หมวดหมู่</th>
                                        <th>สถานะ</th>
                                        <th>วันที่อัปโหลด</th>
                                        <th>การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userDocuments as $doc): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas <?= getFileIcon($doc['file_name']) ?> me-2"></i>
                                                <div>
                                                    <div class="fw-medium"><?= htmlspecialchars($doc['title']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($doc['file_name']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($doc['category_name'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td><?= getStatusBadge($doc['status'], 'document') ?></td>
                                        <td><?= formatThaiDate($doc['created_at']) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/documents/view.php?id=<?= $doc['id'] ?>" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($stats['total_documents'] > count($userDocuments)): ?>
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/admin/documents/?uploader=<?= $userId ?>" 
                               class="btn btn-outline-primary">
                                ดูเอกสารทั้งหมด (<?= number_format($stats['total_documents']) ?>)
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">ยังไม่มีเอกสาร</h6>
                            <p class="text-muted">ผู้ใช้คนนี้ยังไม่ได้อัปโหลดเอกสารใดๆ</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Activities Tab -->
                    <div class="tab-pane fade" id="activities" role="tabpanel">
                        <?php if (!empty($recentActivities)): ?>
                        <div class="activity-timeline">
                            <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?= getActivityDescription($activity['action'], $activity['table_name']) ?>
                                    </div>
                                    <div class="activity-description">
                                        IP: <?= htmlspecialchars($activity['ip_address'] ?? '-') ?>
                                        <?php if ($activity['table_name'] && $activity['record_id']): ?>
                                        <br>รายการ: <?= htmlspecialchars($activity['table_name']) ?> ID <?= $activity['record_id'] ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-time">
                                        <?= formatThaiDate($activity['created_at'], true) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/admin/logs/activities.php?user=<?= $userId ?>" 
                               class="btn btn-outline-primary">
                                ดูกิจกรรมทั้งหมด
                            </a>
                        </div>
                        
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">ยังไม่มีกิจกรรม</h6>
                            <p class="text-muted">ผู้ใช้คนนี้ยังไม่มีประวัติการทำงาน</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Permissions Tab -->
                    <div class="tab-pane fade" id="permissions" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">บทบาท</h6>
                                <?php if (!empty($userRoles)): ?>
                                <div class="list-group">
                                    <?php foreach ($userRoles as $role): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($role['role_name']) ?></h6>
                                                <?php if ($role['role_description']): ?>
                                                <small class="text-muted"><?= htmlspecialchars($role['role_description']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge bg-primary">บทบาท</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    ยังไม่ได้กำหนดบทบาท
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">สิทธิ์พิเศษ</h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    ข้อมูลสิทธิ์พิเศษจะแสดงที่นี่
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>กลับสู่รายการ
            </a>
            
            <div class="btn-group">
                <?php if (hasMenuPermission(PERM_USER_EDIT)): ?>
                <a href="edit.php?id=<?= $userId ?>" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>แก้ไขข้อมูล
                </a>
                <?php endif; ?>
                
                <button class="btn btn-info" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>พิมพ์
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .nav-tabs, .card-header .nav {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .tab-content {
        display: block !important;
    }
    
    .tab-pane {
        display: block !important;
        opacity: 1 !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>