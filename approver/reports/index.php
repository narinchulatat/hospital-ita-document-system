<?php
$pageTitle = 'รายงานการอนุมัติ';
require_once '../includes/header.php';

try {
    $currentUserId = getCurrentUserId();
    $db = Database::getInstance();
    
    // Get date range parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
    $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
    $reportType = $_GET['type'] ?? 'summary';
    
    // Validate dates
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = date('Y-m-d', strtotime($endDate));
    
    // Get overall statistics for the period
    $periodStats = [
        'total_approved' => $db->fetch(
            "SELECT COUNT(*) as count FROM approval_logs 
             WHERE approver_id = ? AND action = 'approve' 
             AND DATE(created_at) BETWEEN ? AND ?",
            [$currentUserId, $startDate, $endDate]
        )['count'] ?? 0,
        
        'total_rejected' => $db->fetch(
            "SELECT COUNT(*) as count FROM approval_logs 
             WHERE approver_id = ? AND action = 'reject' 
             AND DATE(created_at) BETWEEN ? AND ?",
            [$currentUserId, $startDate, $endDate]
        )['count'] ?? 0,
        
        'unique_documents' => $db->fetch(
            "SELECT COUNT(DISTINCT document_id) as count FROM approval_logs 
             WHERE approver_id = ? AND DATE(created_at) BETWEEN ? AND ?",
            [$currentUserId, $startDate, $endDate]
        )['count'] ?? 0
    ];
    
    $periodStats['total_actions'] = $periodStats['total_approved'] + $periodStats['total_rejected'];
    $periodStats['approval_rate'] = $periodStats['total_actions'] > 0 ? 
        round(($periodStats['total_approved'] / $periodStats['total_actions']) * 100, 1) : 0;
    
    // Get daily breakdown
    $dailyData = $db->fetchAll(
        "SELECT 
            DATE(al.created_at) as date,
            SUM(CASE WHEN al.action = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN al.action = 'reject' THEN 1 ELSE 0 END) as rejected,
            COUNT(*) as total
         FROM approval_logs al
         WHERE al.approver_id = ? AND DATE(al.created_at) BETWEEN ? AND ?
         GROUP BY DATE(al.created_at)
         ORDER BY date ASC",
        [$currentUserId, $startDate, $endDate]
    );
    
    // Get category breakdown
    $categoryData = $db->fetchAll(
        "SELECT 
            c.name as category_name,
            SUM(CASE WHEN al.action = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN al.action = 'reject' THEN 1 ELSE 0 END) as rejected,
            COUNT(*) as total
         FROM approval_logs al
         JOIN documents d ON al.document_id = d.id
         JOIN categories c ON d.category_id = c.id
         WHERE al.approver_id = ? AND DATE(al.created_at) BETWEEN ? AND ?
         GROUP BY c.id, c.name
         HAVING total > 0
         ORDER BY total DESC",
        [$currentUserId, $startDate, $endDate]
    );
    
    // Get monthly comparison (last 6 months)
    $monthlyComparison = $db->fetchAll(
        "SELECT 
            DATE_FORMAT(al.created_at, '%Y-%m') as month,
            DATE_FORMAT(al.created_at, '%m/%Y') as month_display,
            SUM(CASE WHEN al.action = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN al.action = 'reject' THEN 1 ELSE 0 END) as rejected,
            COUNT(*) as total
         FROM approval_logs al
         WHERE al.approver_id = ? 
           AND al.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY DATE_FORMAT(al.created_at, '%Y-%m')
         ORDER BY month ASC",
        [$currentUserId]
    );
    
    // Get performance metrics
    $averageTime = $db->fetch(
        "SELECT AVG(TIMESTAMPDIFF(HOUR, d.created_at, al.created_at)) as avg_hours
         FROM approval_logs al
         JOIN documents d ON al.document_id = d.id
         WHERE al.approver_id = ? AND DATE(al.created_at) BETWEEN ? AND ?",
        [$currentUserId, $startDate, $endDate]
    )['avg_hours'] ?? 0;
    
} catch (Exception $e) {
    error_log("Reports error: " . $e->getMessage());
    $periodStats = array_fill_keys(['total_approved', 'total_rejected', 'total_actions', 'unique_documents', 'approval_rate'], 0);
    $dailyData = [];
    $categoryData = [];
    $monthlyComparison = [];
    $averageTime = 0;
}

// Handle export requests
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];
    $filename = "approval_report_{$startDate}_to_{$endDate}";
    
    if ($exportType === 'pdf') {
        // PDF export would be implemented here
        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=\"{$filename}.pdf\"");
        // For now, redirect back with message
        header('Location: ' . $_SERVER['REQUEST_URI'] . '&message=' . urlencode('PDF export feature coming soon'));
        exit;
    } elseif ($exportType === 'excel') {
        // Excel export
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=\"{$filename}.xls\"");
        
        echo "รายงานการอนุมัติเอกสาร\n";
        echo "ระยะเวลา: " . formatThaiDate($startDate) . " ถึง " . formatThaiDate($endDate) . "\n";
        echo "ผู้รายงาน: " . $currentUser['first_name'] . " " . $currentUser['last_name'] . "\n\n";
        
        echo "สรุปภาพรวม\n";
        echo "อนุมัติทั้งหมด\t" . $periodStats['total_approved'] . "\n";
        echo "ไม่อนุมัติทั้งหมด\t" . $periodStats['total_rejected'] . "\n";
        echo "อัตราการอนุมัติ\t" . $periodStats['approval_rate'] . "%\n\n";
        
        if (!empty($categoryData)) {
            echo "รายละเอียดตามหมวดหมู่\n";
            echo "หมวดหมู่\tอนุมัติ\tไม่อนุมัติ\tรวม\n";
            foreach ($categoryData as $cat) {
                echo $cat['category_name'] . "\t" . $cat['approved'] . "\t" . $cat['rejected'] . "\t" . $cat['total'] . "\n";
            }
        }
        exit;
    }
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-chart-bar mr-3"></i>รายงานการอนุมัติ
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                รายงานและสถิติการอนุมัติเอกสารของคุณ
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/approver/reports/monthly.php" class="btn-secondary">
                <i class="fas fa-calendar-alt mr-2"></i>รายงานรายเดือน
            </a>
            <button onclick="window.print()" class="btn-info">
                <i class="fas fa-print mr-2"></i>พิมพ์
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่เริ่มต้น</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="<?= $startDate ?>"
                           class="form-input"
                           max="<?= date('Y-m-d') ?>">
                </div>
                
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่สิ้นสุด</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="<?= $endDate ?>"
                           class="form-input"
                           max="<?= date('Y-m-d') ?>">
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">ประเภทรายงาน</label>
                    <select id="type" name="type" class="form-select">
                        <option value="summary" <?= $reportType === 'summary' ? 'selected' : '' ?>>สรุปภาพรวม</option>
                        <option value="detailed" <?= $reportType === 'detailed' ? 'selected' : '' ?>>รายละเอียด</option>
                    </select>
                </div>
                
                <div class="flex space-x-2">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search mr-2"></i>สร้างรายงาน
                    </button>
                    
                    <div class="relative">
                        <button type="button" 
                                onclick="toggleExportMenu()" 
                                class="btn-success">
                            <i class="fas fa-download mr-2"></i>ส่งออก
                        </button>
                        
                        <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                            <div class="py-1">
                                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" 
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-file-excel mr-2 text-green-600"></i>Excel
                                </a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'pdf'])) ?>" 
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-file-pdf mr-2 text-red-600"></i>PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="card stat-card approved">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-green-600"><?= number_format($periodStats['total_approved']) ?></div>
                <div class="text-sm text-gray-500">เอกสารที่อนุมัติ</div>
            </div>
        </div>
        
        <div class="card stat-card rejected">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-red-600"><?= number_format($periodStats['total_rejected']) ?></div>
                <div class="text-sm text-gray-500">เอกสารที่ไม่อนุมัติ</div>
            </div>
        </div>
        
        <div class="card stat-card info">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-blue-600"><?= $periodStats['approval_rate'] ?>%</div>
                <div class="text-sm text-gray-500">อัตราการอนุมัติ</div>
            </div>
        </div>
        
        <div class="card stat-card pending">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-purple-600"><?= round($averageTime, 1) ?></div>
                <div class="text-sm text-gray-500">ชั่วโมงเฉลี่ย/เอกสาร</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Daily Trend Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-chart-line mr-2"></i>แนวโน้มรายวัน
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-chart-pie mr-2"></i>แยกตามหมวดหมู่
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <!-- Category Details -->
        <?php if (!empty($categoryData)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-folder mr-2"></i>รายละเอียดตามหมวดหมู่
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>หมวดหมู่</th>
                                <th class="text-center">อนุมัติ</th>
                                <th class="text-center">ไม่อนุมัติ</th>
                                <th class="text-center">รวม</th>
                                <th class="text-center">อัตราอนุมัติ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoryData as $cat): ?>
                            <?php $rate = $cat['total'] > 0 ? round(($cat['approved'] / $cat['total']) * 100, 1) : 0; ?>
                            <tr>
                                <td class="font-medium"><?= htmlspecialchars($cat['category_name']) ?></td>
                                <td class="text-center text-green-600 font-medium"><?= number_format($cat['approved']) ?></td>
                                <td class="text-center text-red-600 font-medium"><?= number_format($cat['rejected']) ?></td>
                                <td class="text-center font-medium"><?= number_format($cat['total']) ?></td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: <?= $rate ?>%"></div>
                                        </div>
                                        <span class="text-sm font-medium"><?= $rate ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Monthly Comparison -->
        <?php if (!empty($monthlyComparison)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-calendar mr-2"></i>เปรียบเทียบรายเดือน (6 เดือนล่าสุด)
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>เดือน/ปี</th>
                                <th class="text-center">อนุมัติ</th>
                                <th class="text-center">ไม่อนุมัติ</th>
                                <th class="text-center">รวม</th>
                                <th class="text-center">อัตราอนุมัติ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthlyComparison as $month): ?>
                            <?php $rate = $month['total'] > 0 ? round(($month['approved'] / $month['total']) * 100, 1) : 0; ?>
                            <tr>
                                <td class="font-medium"><?= $month['month_display'] ?></td>
                                <td class="text-center text-green-600"><?= number_format($month['approved']) ?></td>
                                <td class="text-center text-red-600"><?= number_format($month['rejected']) ?></td>
                                <td class="text-center"><?= number_format($month['total']) ?></td>
                                <td class="text-center font-medium"><?= $rate ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize charts
    createDailyChart();
    createCategoryChart();
});

function createDailyChart() {
    const ctx = document.getElementById('dailyChart');
    if (!ctx) return;
    
    const dailyData = <?= json_encode($dailyData) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('th-TH', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'อนุมัติ',
                data: dailyData.map(d => d.approved),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true
            }, {
                label: 'ไม่อนุมัติ',
                data: dailyData.map(d => d.rejected),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;
    
    const categoryData = <?= json_encode($categoryData) ?>;
    
    if (categoryData.length === 0) {
        ctx.parentElement.innerHTML = '<div class="text-center py-8 text-gray-500">ไม่มีข้อมูลสำหรับช่วงเวลานี้</div>';
        return;
    }
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(d => d.category_name),
            datasets: [{
                data: categoryData.map(d => d.total),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                    '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
}

function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('hidden');
}

// Close export menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('exportMenu');
    const button = e.target.closest('[onclick="toggleExportMenu()"]');
    
    if (!button && !menu.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

// Set max date for date inputs
document.getElementById('start_date').addEventListener('change', function() {
    document.getElementById('end_date').min = this.value;
});

document.getElementById('end_date').addEventListener('change', function() {
    document.getElementById('start_date').max = this.value;
});
</script>

<?php require_once '../includes/footer.php'; ?>