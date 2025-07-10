<?php
/**
 * Reports Dashboard
 */

// Page configuration
$pageTitle = 'Dashboard รายงาน';
$pageDescription = 'ภาพรวมสถิติและรายงานสำคัญ';
$pageIcon = 'fas fa-tachometer-alt';
$breadcrumb = generateReportBreadcrumb([
    ['name' => 'Dashboard', 'url' => REPORTS_URL . '/dashboard.php']
]);

// Include header
include_once __DIR__ . '/includes/header.php';

// Get dashboard data
$report = new Report();
$systemSummary = $report->getSystemSummary();
$topDocuments = $report->getTopDocuments(5);
$topUsers = $report->getTopUsers(5);
$topCategories = $report->getTopCategories(5);

// Get date range from URL parameters
$dateRange = $_GET['date_range'] ?? 'last_30_days';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Apply date filter
$dateFilter = null;
if ($dateRange === 'custom' && $startDate && $endDate) {
    $dateFilter = [
        'start' => $startDate . ' 00:00:00',
        'end' => $endDate . ' 23:59:59'
    ];
} else {
    $dateFilter = getDateRange($dateRange);
}

// Get filtered data
$userActivity = $report->getUserActivity($dateFilter);
$downloadStats = $report->getDownloadStats($dateFilter);
?>

<!-- Filter Section -->
<div class="filter-section mb-8">
    <form id="filterForm" class="filter-row">
        <div class="filter-group">
            <label for="dateRange">ช่วงเวลา</label>
            <select id="dateRange" name="date_range" class="filter-select">
                <?php foreach (DATE_RANGES as $key => $name): ?>
                <option value="<?php echo $key; ?>" <?php echo ($dateRange === $key) ? 'selected' : ''; ?>>
                    <?php echo $name; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group custom-date-range" style="<?php echo ($dateRange === 'custom') ? '' : 'display:none;'; ?>">
            <label for="startDate">วันที่เริ่มต้น</label>
            <input type="date" id="startDate" name="start_date" value="<?php echo $startDate; ?>">
        </div>
        
        <div class="filter-group custom-date-range" style="<?php echo ($dateRange === 'custom') ? '' : 'display:none;'; ?>">
            <label for="endDate">วันที่สิ้นสุด</label>
            <input type="date" id="endDate" name="end_date" value="<?php echo $endDate; ?>">
        </div>
        
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="button" onclick="applyFilters()">
                <i class="fas fa-filter mr-2"></i>กรอง
            </button>
        </div>
        
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="button" onclick="resetFilters()">
                <i class="fas fa-times mr-2"></i>ล้าง
            </button>
        </div>
    </form>
</div>

<!-- Dashboard Content -->
<div class="space-y-8">
    <!-- Key Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-blue-600 text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="stat-number text-blue-600"><?php echo formatNumber($systemSummary['documents']['total']); ?></div>
                    <div class="stat-label">เอกสารทั้งหมด</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                <div class="text-center">
                    <div class="text-sm font-medium text-green-600"><?php echo formatNumber($systemSummary['documents']['approved']); ?></div>
                    <div class="text-xs text-gray-500">อนุมัติแล้ว</div>
                </div>
                <div class="text-center">
                    <div class="text-sm font-medium text-yellow-600"><?php echo formatNumber($systemSummary['documents']['pending']); ?></div>
                    <div class="text-xs text-gray-500">รออนุมัติ</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="stat-number text-green-600"><?php echo formatNumber($systemSummary['users']['total']); ?></div>
                    <div class="stat-label">ผู้ใช้งาน</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                <div class="text-center">
                    <div class="text-sm font-medium text-green-600"><?php echo formatNumber($systemSummary['users']['active']); ?></div>
                    <div class="text-xs text-gray-500">ใช้งาน</div>
                </div>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-600"><?php echo formatNumber($systemSummary['users']['total'] - $systemSummary['users']['active']); ?></div>
                    <div class="text-xs text-gray-500">ไม่ใช้งาน</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="w-16 h-16 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-folder text-purple-600 text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="stat-number text-purple-600"><?php echo formatNumber($systemSummary['categories']['total']); ?></div>
                    <div class="stat-label">หมวดหมู่</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                <div class="text-center">
                    <div class="text-sm font-medium text-purple-600"><?php echo formatNumber($systemSummary['categories']['active']); ?></div>
                    <div class="text-xs text-gray-500">ใช้งาน</div>
                </div>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-600"><?php echo formatNumber($systemSummary['categories']['total'] - $systemSummary['categories']['active']); ?></div>
                    <div class="text-xs text-gray-500">ไม่ใช้งาน</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-download text-orange-600 text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="stat-number text-orange-600"><?php echo formatNumber(count($downloadStats)); ?></div>
                    <div class="stat-label">การดาวน์โหลด</div>
                </div>
            </div>
            <div class="pt-4 border-t">
                <div class="text-center">
                    <div class="text-sm font-medium text-orange-600">ใน <?php echo DATE_RANGES[$dateRange]; ?></div>
                    <div class="text-xs text-gray-500">ที่ผ่านมา</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Document Categories Chart -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                การกระจายเอกสารตามหมวดหมู่
            </h3>
            <div class="relative">
                <canvas id="category-chart" height="300"></canvas>
            </div>
        </div>
        
        <!-- User Activity Chart -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-line mr-2 text-green-600"></i>
                กิจกรรมผู้ใช้งานรายวัน
            </h3>
            <div class="relative">
                <canvas id="activity-chart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Documents -->
        <div class="report-card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-star mr-2 text-yellow-600"></i>
                เอกสารยอดนิยม
            </h3>
            <div class="space-y-4">
                <?php foreach ($topDocuments as $doc): ?>
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></h4>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($doc['category_name']); ?></p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-blue-600"><?php echo formatNumber($doc['download_count']); ?></div>
                        <div class="text-xs text-gray-500">ดาวน์โหลด</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 text-center">
                <a href="<?php echo REPORTS_URL; ?>/documents/popular.php" 
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    ดูทั้งหมด
                </a>
            </div>
        </div>
        
        <!-- Top Users -->
        <div class="report-card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-users mr-2 text-green-600"></i>
                ผู้ใช้งานที่ใช้งานมากที่สุด
            </h3>
            <div class="space-y-4">
                <?php foreach ($topUsers as $user): ?>
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-user text-green-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        </h4>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-green-600"><?php echo formatNumber($user['activity_count']); ?></div>
                        <div class="text-xs text-gray-500">กิจกรรม</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 text-center">
                <a href="<?php echo REPORTS_URL; ?>/users/activity.php" 
                   class="text-green-600 hover:text-green-800 text-sm font-medium">
                    ดูทั้งหมด
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Custom JavaScript for Dashboard -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category Distribution Chart
    const categoryData = {
        labels: <?php echo json_encode(array_column($topCategories, 'name')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($topCategories, 'document_count')); ?>,
            backgroundColor: [
                '#3B82F6',
                '#10B981',
                '#F59E0B',
                '#EF4444',
                '#8B5CF6'
            ]
        }]
    };
    
    const categoryChart = new Chart(document.getElementById('category-chart'), {
        type: 'doughnut',
        data: categoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Activity Chart (Mock data for now)
    const activityData = {
        labels: <?php 
            $labels = [];
            for ($i = 6; $i >= 0; $i--) {
                $labels[] = date('j M', strtotime("-$i days"));
            }
            echo json_encode($labels);
        ?>,
        datasets: [{
            label: 'กิจกรรมรายวัน',
            data: <?php
                // Mock data for daily activity
                $activityCounts = [];
                for ($i = 6; $i >= 0; $i--) {
                    $activityCounts[] = rand(10, 50);
                }
                echo json_encode($activityCounts);
            ?>,
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4
        }]
    };
    
    const activityChart = new Chart(document.getElementById('activity-chart'), {
        type: 'line',
        data: activityData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<?php
// Include footer
include_once __DIR__ . '/includes/footer.php';
?>