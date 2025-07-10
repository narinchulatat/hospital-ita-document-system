<?php
$pageTitle = 'แดshboard - สถิติและข้อมูลสำคัญ';
require_once 'includes/header.php';

try {
    $document = new Document();
    $currentUserId = getCurrentUserId();
    
    // Get comprehensive statistics
    $stats = [
        'pending_documents' => $document->getTotalCount(['status' => DOC_STATUS_PENDING]),
        'approved_by_me' => $document->getTotalCount(['approved_by' => $currentUserId, 'status' => DOC_STATUS_APPROVED]),
        'rejected_by_me' => $document->getTotalCount(['approved_by' => $currentUserId, 'status' => DOC_STATUS_REJECTED]),
        'total_approved' => $document->getTotalCount(['status' => DOC_STATUS_APPROVED]),
        'total_documents' => $document->getTotalCount([]),
        'this_month_approved' => $document->getTotalCount([
            'approved_by' => $currentUserId,
            'status' => DOC_STATUS_APPROVED,
            'approved_at_month' => date('Y-m')
        ])
    ];
    
    // Get category statistics
    $categoryStats = $document->getApprovalStatsByCategory($currentUserId);
    
    // Get monthly trend data for charts
    $monthlyData = $document->getMonthlyApprovalData($currentUserId, 6);
    
    // Get recent activities
    $recentApprovals = $document->getRecentApprovals($currentUserId, 10);
    $pendingUrgent = $document->getPendingUrgentDocuments(5);
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = array_fill_keys(['pending_documents', 'approved_by_me', 'rejected_by_me', 'total_approved', 'total_documents', 'this_month_approved'], 0);
    $categoryStats = [];
    $monthlyData = [];
    $recentApprovals = [];
    $pendingUrgent = [];
}

// Calculate approval rate
$totalProcessed = $stats['approved_by_me'] + $stats['rejected_by_me'];
$approvalRate = $totalProcessed > 0 ? round(($stats['approved_by_me'] / $totalProcessed) * 100, 1) : 0;
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold leading-7 text-gray-900 sm:text-4xl sm:truncate">
                <i class="fas fa-chart-line mr-3 text-blue-600"></i>Dashboard สถิติและข้อมูลสำคัญ
            </h1>
            <p class="mt-2 text-sm text-gray-500">
                ภาพรวมการอนุมัติเอกสารและสถิติประสิทธิภาพ - อัปเดตล่าสุด: <?= formatThaiDate(date('Y-m-d H:i:s'), true) ?>
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
            <button onclick="location.reload()" class="btn-secondary">
                <i class="fas fa-sync-alt mr-2"></i>รีเฟรช
            </button>
            <a href="<?= BASE_URL ?>/approver/reports/" class="btn-primary">
                <i class="fas fa-download mr-2"></i>ดาวน์โหลดรายงาน
            </a>
        </div>
    </div>

    <!-- Main Statistics Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Pending Documents -->
        <div class="card stat-card pending">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hourglass-half text-2xl text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">เอกสารรออนุมัติ</dt>
                            <dd class="text-3xl font-bold text-gray-900" data-stat="pending_documents">
                                <?= number_format($stats['pending_documents']) ?>
                            </dd>
                            <dd class="text-sm text-yellow-600">ต้องดำเนินการ</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-yellow-50 border-t border-yellow-100">
                <a href="<?= BASE_URL ?>/approver/documents/pending.php" class="text-sm text-yellow-700 hover:text-yellow-900 font-medium">
                    ดำเนินการทันที <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Approved by Me -->
        <div class="card stat-card approved">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">ที่ฉันอนุมัติ</dt>
                            <dd class="text-3xl font-bold text-gray-900" data-stat="approved_by_me">
                                <?= number_format($stats['approved_by_me']) ?>
                            </dd>
                            <dd class="text-sm text-green-600">อัตราอนุมัติ <?= $approvalRate ?>%</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-green-50 border-t border-green-100">
                <a href="<?= BASE_URL ?>/approver/documents/history.php?filter=approved" class="text-sm text-green-700 hover:text-green-900 font-medium">
                    ดูรายการ <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- This Month Performance -->
        <div class="card stat-card info">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-check text-2xl text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">เดือนนี้</dt>
                            <dd class="text-3xl font-bold text-gray-900" data-stat="this_month_approved">
                                <?= number_format($stats['this_month_approved']) ?>
                            </dd>
                            <dd class="text-sm text-blue-600">เอกสารที่อนุมัติ</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-blue-50 border-t border-blue-100">
                <a href="<?= BASE_URL ?>/approver/reports/monthly.php" class="text-sm text-blue-700 hover:text-blue-900 font-medium">
                    ดูรายงาน <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Total Efficiency -->
        <div class="card stat-card info">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tachometer-alt text-2xl text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">ประสิทธิภาพ</dt>
                            <dd class="text-3xl font-bold text-gray-900">
                                <?= $totalProcessed > 0 ? number_format($totalProcessed) : '0' ?>
                            </dd>
                            <dd class="text-sm text-purple-600">เอกสารที่ดำเนินการ</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-purple-50 border-t border-purple-100">
                <a href="<?= BASE_URL ?>/approver/reports/" class="text-sm text-purple-700 hover:text-purple-900 font-medium">
                    วิเคราะห์เพิ่มเติม <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Approval Statistics Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-chart-pie mr-2"></i>สถิติการอนุมัติ
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="approvalChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-chart-line mr-2"></i>แนวโน้ม 6 เดือนที่ผ่านมา
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
        <!-- Urgent Pending Documents -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>เอกสารด่วนที่ต้องอนุมัติ
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingUrgent)): ?>
                <div class="space-y-4">
                    <?php foreach ($pendingUrgent as $doc): ?>
                    <div class="border-l-4 border-red-400 bg-red-50 p-4 rounded-r-lg">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">
                                    <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </h4>
                                <div class="space-y-1 text-xs text-gray-600">
                                    <p>หมวดหมู่: <?= htmlspecialchars($doc['category_name']) ?></p>
                                    <p>อัปโหลดเมื่อ: <?= formatThaiDate($doc['created_at'], true) ?></p>
                                </div>
                            </div>
                            <div class="ml-4 flex space-x-2">
                                <button onclick="approverPanel.showApprovalModal(<?= $doc['id'] ?>, 'approve')" 
                                        class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="approverPanel.showApprovalModal(<?= $doc['id'] ?>, 'reject')" 
                                        class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="<?= BASE_URL ?>/approver/documents/pending.php?urgent=1" 
                       class="text-red-600 hover:text-red-500 text-sm font-medium">
                        ดูเอกสารด่วนทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-300 mb-4"></i>
                    <p class="text-gray-500">ไม่มีเอกสารด่วนที่ต้องอนุมัติ</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Approval Activities -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-history mr-2"></i>กิจกรรมล่าสุด
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($recentApprovals)): ?>
                <div class="space-y-4">
                    <?php foreach ($recentApprovals as $approval): ?>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <?php if ($approval['action'] === 'approve'): ?>
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            <?php else: ?>
                            <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-times text-red-600 text-xs"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                <?= $approval['action'] === 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ' ?>: 
                                <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $approval['document_id'] ?>" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($approval['document_title']) ?>
                                </a>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?= formatThaiDate($approval['created_at'], true) ?>
                            </p>
                            <?php if ($approval['comments']): ?>
                            <p class="text-xs text-gray-600 italic mt-1">
                                "<?= htmlspecialchars($approval['comments']) ?>"
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="<?= BASE_URL ?>/approver/documents/history.php" 
                       class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                        ดูประวัติทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">ยังไม่มีการดำเนินการ</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Category Performance -->
    <?php if (!empty($categoryStats)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-folder-open mr-2"></i>ประสิทธิภาพการอนุมัติตามหมวดหมู่
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>หมวดหมู่</th>
                            <th class="text-center">รออนุมัติ</th>
                            <th class="text-center">อนุมัติแล้ว</th>
                            <th class="text-center">ไม่อนุมัติ</th>
                            <th class="text-center">อัตราอนุมัติ</th>
                            <th class="text-center">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categoryStats as $stat): ?>
                        <?php 
                        $categoryTotal = $stat['approved'] + $stat['rejected'];
                        $categoryRate = $categoryTotal > 0 ? round(($stat['approved'] / $categoryTotal) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td class="font-medium"><?= htmlspecialchars($stat['category_name']) ?></td>
                            <td class="text-center">
                                <span class="badge badge-warning"><?= number_format($stat['pending']) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success"><?= number_format($stat['approved']) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-danger"><?= number_format($stat['rejected']) ?></span>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: <?= $categoryRate ?>%"></div>
                                    </div>
                                    <span class="text-sm font-medium"><?= $categoryRate ?>%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/approver/documents/?category=<?= $stat['category_id'] ?>" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Initialize charts
    const approvalData = {
        approved: <?= $stats['approved_by_me'] ?>,
        pending: <?= $stats['pending_documents'] ?>,
        rejected: <?= $stats['rejected_by_me'] ?>
    };
    
    const monthlyData = {
        labels: <?= json_encode(array_column($monthlyData, 'month_name')) ?>,
        approved: <?= json_encode(array_column($monthlyData, 'approved')) ?>,
        rejected: <?= json_encode(array_column($monthlyData, 'rejected')) ?>
    };
    
    chartManager.createApprovalChart('approvalChart', approvalData);
    chartManager.createTrendChart('trendChart', monthlyData);
});
</script>

<?php require_once 'includes/footer.php'; ?>