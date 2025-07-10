<?php
$pageTitle = 'แดชบอร์ดผู้ดูแลระบบ';
$pageSubtitle = 'ภาพรวมและการจัดการระบบ';

require_once 'includes/header.php';
require_once '../classes/User.php';
require_once '../classes/Document.php';
require_once '../classes/Category.php';
require_once '../classes/Backup.php';
require_once '../classes/Download.php';

try {
    $db = Database::getInstance();
    $user = new User();
    $document = new Document();
    $category = new Category();
    
    // Get dashboard statistics
    $stats = [
        'total_users' => $user->getTotalCount(),
        'total_documents' => $document->getTotalCount(),
        'pending_documents' => $document->getTotalCount(['status' => DOC_STATUS_PENDING]),
        'total_categories' => count($category->getAll(false)),
        'total_downloads' => 0,
        'storage_used' => 0
    ];
    
    // Get total downloads
    $downloadStats = $db->fetch("SELECT SUM(download_count) as total FROM documents");
    $stats['total_downloads'] = $downloadStats['total'] ?? 0;
    
    // Calculate storage usage
    $storageStats = $db->fetch("SELECT SUM(file_size) as total FROM documents");
    $stats['storage_used'] = $storageStats['total'] ?? 0;
    
    // Get recent activities
    $recentActivities = $db->fetchAll(
        "SELECT al.*, u.first_name, u.last_name 
         FROM activity_logs al 
         LEFT JOIN users u ON al.user_id = u.id 
         ORDER BY al.created_at DESC 
         LIMIT 10"
    );
    
    // Get recent documents
    $recentDocuments = $document->getAll([], 1, 5);
    
    // Get pending approvals
    $pendingApprovals = $document->getPendingDocuments(1, 5);
    
    // Get user statistics by role
    $usersByRole = $db->fetchAll(
        "SELECT r.name as role_name, COUNT(u.id) as count
         FROM roles r
         LEFT JOIN users u ON r.id = u.role_id AND u.status = 'active'
         GROUP BY r.id, r.name
         ORDER BY r.id"
    );
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $stats = array_fill_keys(['total_users', 'total_documents', 'pending_documents', 'total_categories', 'total_downloads', 'storage_used'], 0);
    $recentActivities = [];
    $recentDocuments = [];
    $pendingApprovals = [];
    $usersByRole = [];
}
?>

<!-- Dashboard Statistics -->
<div class="row dashboard-stats mb-4">
    <!-- Total Users -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['total_users']) ?></div>
                    <div class="stats-label">ผู้ใช้ทั้งหมด</div>
                </div>
            </div>
            <div class="stats-footer">
                <a href="<?= BASE_URL ?>/admin/users/">จัดการผู้ใช้ <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Total Documents -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card success">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['total_documents']) ?></div>
                    <div class="stats-label">เอกสารทั้งหมด</div>
                </div>
            </div>
            <div class="stats-footer">
                <a href="<?= BASE_URL ?>/admin/documents/">จัดการเอกสาร <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Pending Documents -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card warning">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= number_format($stats['pending_documents']) ?></div>
                    <div class="stats-label">รออนุมัติ</div>
                </div>
            </div>
            <div class="stats-footer">
                <a href="<?= BASE_URL ?>/admin/documents/?status=pending">ดูรายการ <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Storage Used -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card info">
            <div class="card-body d-flex align-items-center">
                <div class="stats-icon">
                    <i class="fas fa-hdd"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number"><?= formatFileSize($stats['storage_used']) ?></div>
                    <div class="stats-label">พื้นที่ใช้งาน</div>
                </div>
            </div>
            <div class="stats-footer">
                <a href="<?= BASE_URL ?>/admin/reports/">ดูรายงาน <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions mb-4">
    <a href="<?= BASE_URL ?>/admin/users/create.php" class="quick-action-card">
        <div class="quick-action-icon">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="quick-action-title">เพิ่มผู้ใช้ใหม่</div>
        <div class="quick-action-description">สร้างบัญชีผู้ใช้ใหม่ในระบบ</div>
    </a>
    
    <a href="<?= BASE_URL ?>/admin/documents/create.php" class="quick-action-card">
        <div class="quick-action-icon">
            <i class="fas fa-file-plus"></i>
        </div>
        <div class="quick-action-title">เพิ่มเอกสาร</div>
        <div class="quick-action-description">อัปโหลดเอกสารใหม่เข้าสู่ระบบ</div>
    </a>
    
    <a href="<?= BASE_URL ?>/admin/backups/create.php" class="quick-action-card">
        <div class="quick-action-icon">
            <i class="fas fa-database"></i>
        </div>
        <div class="quick-action-title">สำรองข้อมูล</div>
        <div class="quick-action-description">สร้างข้อมูลสำรองของระบบ</div>
    </a>
    
    <a href="<?= BASE_URL ?>/admin/settings/" class="quick-action-card">
        <div class="quick-action-icon">
            <i class="fas fa-cog"></i>
        </div>
        <div class="quick-action-title">ตั้งค่าระบบ</div>
        <div class="quick-action-description">จัดการการตั้งค่าของระบบ</div>
    </a>
</div>

<!-- Main Content Grid -->
<div class="row">
    <!-- Recent Activities -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>กิจกรรมล่าสุด</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivities)): ?>
                <div class="activity-timeline">
                    <?php foreach ($recentActivities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-content">
                            <div class="activity-title">
                                <?= $activity['first_name'] ? htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) : 'ระบบ' ?>
                            </div>
                            <div class="activity-description">
                                <?= getActivityDescription($activity['action'], $activity['table_name']) ?>
                            </div>
                            <div class="activity-time">
                                <?= formatThaiDate($activity['created_at'], true) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-clock fa-3x mb-3"></i>
                    <p>ไม่มีกิจกรรมล่าสุด</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- System Status & Notifications -->
    <div class="col-lg-4 mb-4">
        <!-- System Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-server me-2"></i>สถานะระบบ</h5>
            </div>
            <div class="card-body">
                <div class="system-status">
                    <div class="status-item">
                        <div class="status-indicator"></div>
                        <div class="status-content">
                            <div class="status-title">ฐานข้อมูล</div>
                            <div class="status-value">เชื่อมต่อปกติ</div>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-indicator"></div>
                        <div class="status-content">
                            <div class="status-title">ระบบไฟล์</div>
                            <div class="status-value">ทำงานปกติ</div>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-indicator warning"></div>
                        <div class="status-content">
                            <div class="status-title">พื้นที่เก็บข้อมูล</div>
                            <div class="status-value">ใช้ไป 75%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>เอกสารรออนุมัติ</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingApprovals)): ?>
                <div class="recent-items">
                    <?php foreach ($pendingApprovals as $doc): ?>
                    <div class="recent-item">
                        <div class="recent-item-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="recent-item-content">
                            <div class="recent-item-title">
                                <a href="<?= BASE_URL ?>/admin/documents/view.php?id=<?= $doc['id'] ?>">
                                    <?= htmlspecialchars($doc['title']) ?>
                                </a>
                            </div>
                            <div class="recent-item-meta">
                                โดย: <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?>
                            </div>
                        </div>
                        <div class="recent-item-time">
                            <?= formatThaiDate($doc['created_at']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/admin/documents/?status=pending" class="btn btn-outline-primary btn-sm">
                        ดูทั้งหมด <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <p>ไม่มีเอกสารรออนุมัติ</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Document Statistics Chart -->
    <div class="col-lg-6 mb-4">
        <div class="chart-container">
            <div class="chart-header">
                <h5 class="chart-title">สถิติเอกสาร (7 วันล่าสุด)</h5>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-secondary" data-period="7">7 วัน</button>
                    <button class="btn btn-sm btn-outline-secondary" data-period="30">30 วัน</button>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="documentsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Users by Role Chart -->
    <div class="col-lg-6 mb-4">
        <div class="chart-container">
            <div class="chart-header">
                <h5 class="chart-title">ผู้ใช้ตามบทบาท</h5>
            </div>
            <div class="chart-body">
                <canvas id="usersChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize dashboard charts
document.addEventListener('DOMContentLoaded', function() {
    // Documents Chart
    const documentsCtx = document.getElementById('documentsChart').getContext('2d');
    new Chart(documentsCtx, {
        type: 'line',
        data: {
            labels: ['6 วันที่แล้ว', '5 วันที่แล้ว', '4 วันที่แล้ว', '3 วันที่แล้ว', '2 วันที่แล้ว', 'เมื่อวาน', 'วันนี้'],
            datasets: [{
                label: 'เอกสารใหม่',
                data: [12, 19, 3, 5, 2, 8, 10],
                borderColor: 'rgb(52, 152, 219)',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4
            }, {
                label: 'การดาวน์โหลด',
                data: [25, 32, 18, 28, 15, 35, 42],
                borderColor: 'rgb(39, 174, 96)',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Users Chart
    const usersCtx = document.getElementById('usersChart').getContext('2d');
    new Chart(usersCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php foreach ($usersByRole as $role): ?>'<?= htmlspecialchars($role['role_name']) ?>',<?php endforeach; ?>],
            datasets: [{
                data: [<?php foreach ($usersByRole as $role): ?><?= $role['count'] ?>,<?php endforeach; ?>],
                backgroundColor: [
                    'rgb(52, 152, 219)',
                    'rgb(39, 174, 96)',
                    'rgb(243, 156, 18)',
                    'rgb(155, 89, 182)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>

<?php
function getActivityIcon($action) {
    $icons = [
        ACTION_CREATE => 'plus',
        ACTION_UPDATE => 'edit',
        ACTION_DELETE => 'trash',
        ACTION_LOGIN => 'sign-in-alt',
        ACTION_LOGOUT => 'sign-out-alt',
        ACTION_APPROVE => 'check',
        ACTION_REJECT => 'times',
        ACTION_DOWNLOAD => 'download'
    ];
    return $icons[$action] ?? 'circle';
}

function getActivityDescription($action, $table) {
    $descriptions = [
        ACTION_CREATE => 'สร้าง' . getTableName($table),
        ACTION_UPDATE => 'แก้ไข' . getTableName($table),
        ACTION_DELETE => 'ลบ' . getTableName($table),
        ACTION_LOGIN => 'เข้าสู่ระบบ',
        ACTION_LOGOUT => 'ออกจากระบบ',
        ACTION_APPROVE => 'อนุมัติเอกสาร',
        ACTION_REJECT => 'ไม่อนุมัติเอกสาร',
        ACTION_DOWNLOAD => 'ดาวน์โหลดเอกสาร'
    ];
    return $descriptions[$action] ?? 'ดำเนินการ';
}

function getTableName($table) {
    $names = [
        'users' => 'ผู้ใช้',
        'documents' => 'เอกสาร',
        'categories' => 'หมวดหมู่',
        'settings' => 'การตั้งค่า'
    ];
    return $names[$table] ?? $table;
}
?>

<?php require_once 'includes/footer.php'; ?>