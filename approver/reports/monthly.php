<?php
$pageTitle = 'รายงานรายเดือน';
require_once '../includes/header.php';

try {
    $currentUserId = getCurrentUserId();
    $db = Database::getInstance();
    
    // Get month and year parameters
    $selectedMonth = $_GET['month'] ?? date('m');
    $selectedYear = $_GET['year'] ?? date('Y');
    
    // Validate month and year
    $selectedMonth = str_pad(intval($selectedMonth), 2, '0', STR_PAD_LEFT);
    $selectedYear = intval($selectedYear);
    
    if ($selectedMonth < 1 || $selectedMonth > 12 || $selectedYear < 2020 || $selectedYear > date('Y')) {
        $selectedMonth = date('m');
        $selectedYear = date('Y');
    }
    
    $monthStart = "$selectedYear-$selectedMonth-01";
    $monthEnd = date('Y-m-t', strtotime($monthStart));
    
    // Get monthly statistics
    $monthlyStats = [
        'total_approved' => $db->fetch(
            "SELECT COUNT(*) as count FROM approval_logs 
             WHERE approver_id = ? AND action = 'approve' 
             AND DATE(created_at) BETWEEN ? AND ?",
            [$currentUserId, $monthStart, $monthEnd]
        )['count'] ?? 0,
        
        'total_rejected' => $db->fetch(
            "SELECT COUNT(*) as count FROM approval_logs 
             WHERE approver_id = ? AND action = 'reject' 
             AND DATE(created_at) BETWEEN ? AND ?",
            [$currentUserId, $monthStart, $monthEnd]
        )['count'] ?? 0,
        
        'unique_documents' => $db->fetch(
            "SELECT COUNT(DISTINCT document_id) as count FROM approval_logs 
             WHERE approver_id = ? AND DATE(created_at) BETWEEN ? AND ?",
            [$currentUserId, $monthStart, $monthEnd]
        )['count'] ?? 0,
        
        'working_days' => $db->fetch(
            "SELECT COUNT(DISTINCT DATE(created_at)) as count FROM approval_logs 
             WHERE approver_id = ? AND DATE(created_at) BETWEEN ? AND ?",
            [$currentUserId, $monthStart, $monthEnd]
        )['count'] ?? 0
    ];
    
    $monthlyStats['total_actions'] = $monthlyStats['total_approved'] + $monthlyStats['total_rejected'];
    $monthlyStats['approval_rate'] = $monthlyStats['total_actions'] > 0 ? 
        round(($monthlyStats['total_approved'] / $monthlyStats['total_actions']) * 100, 1) : 0;
    $monthlyStats['avg_per_day'] = $monthlyStats['working_days'] > 0 ? 
        round($monthlyStats['total_actions'] / $monthlyStats['working_days'], 1) : 0;
    
    // Get daily breakdown for the month
    $dailyBreakdown = $db->fetchAll(
        "SELECT 
            DATE(al.created_at) as date,
            DAY(al.created_at) as day,
            SUM(CASE WHEN al.action = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN al.action = 'reject' THEN 1 ELSE 0 END) as rejected,
            COUNT(*) as total
         FROM approval_logs al
         WHERE al.approver_id = ? AND DATE(al.created_at) BETWEEN ? AND ?
         GROUP BY DATE(al.created_at)
         ORDER BY date ASC",
        [$currentUserId, $monthStart, $monthEnd]
    );
    
    // Get category breakdown for the month
    $categoryBreakdown = $db->fetchAll(
        "SELECT 
            c.name as category_name,
            SUM(CASE WHEN al.action = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN al.action = 'reject' THEN 1 ELSE 0 END) as rejected,
            COUNT(*) as total,
            AVG(TIMESTAMPDIFF(HOUR, d.created_at, al.created_at)) as avg_processing_hours
         FROM approval_logs al
         JOIN documents d ON al.document_id = d.id
         JOIN categories c ON d.category_id = c.id
         WHERE al.approver_id = ? AND DATE(al.created_at) BETWEEN ? AND ?
         GROUP BY c.id, c.name
         HAVING total > 0
         ORDER BY total DESC",
        [$currentUserId, $monthStart, $monthEnd]
    );
    
    // Get weekly summary
    $weeklyData = $db->fetchAll(
        "SELECT 
            WEEK(al.created_at, 1) as week_number,
            CONCAT('สัปดาห์ที่ ', WEEK(al.created_at, 1) - WEEK(DATE_SUB(al.created_at, INTERVAL DAY(al.created_at)-1 DAY), 1) + 1) as week_display,
            SUM(CASE WHEN al.action = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN al.action = 'reject' THEN 1 ELSE 0 END) as rejected,
            COUNT(*) as total
         FROM approval_logs al
         WHERE al.approver_id = ? AND DATE(al.created_at) BETWEEN ? AND ?
         GROUP BY WEEK(al.created_at, 1)
         ORDER BY week_number ASC",
        [$currentUserId, $monthStart, $monthEnd]
    );
    
    // Get top performing days
    $topDays = $db->fetchAll(
        "SELECT 
            DATE(al.created_at) as date,
            COUNT(*) as total_actions,
            SUM(CASE WHEN al.action = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN al.action = 'reject' THEN 1 ELSE 0 END) as rejected
         FROM approval_logs al
         WHERE al.approver_id = ? AND DATE(al.created_at) BETWEEN ? AND ?
         GROUP BY DATE(al.created_at)
         ORDER BY total_actions DESC
         LIMIT 5",
        [$currentUserId, $monthStart, $monthEnd]
    );
    
    // Generate available months and years for dropdown
    $availableMonths = $db->fetchAll(
        "SELECT DISTINCT 
            YEAR(created_at) as year,
            MONTH(created_at) as month
         FROM approval_logs 
         WHERE approver_id = ?
         ORDER BY year DESC, month DESC",
        [$currentUserId]
    );
    
    // Thai month names
    $thaiMonths = [
        '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
        '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
        '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
    ];
    
} catch (Exception $e) {
    error_log("Monthly reports error: " . $e->getMessage());
    $monthlyStats = array_fill_keys(['total_approved', 'total_rejected', 'total_actions', 'unique_documents', 'working_days', 'approval_rate', 'avg_per_day'], 0);
    $dailyBreakdown = [];
    $categoryBreakdown = [];
    $weeklyData = [];
    $topDays = [];
    $availableMonths = [];
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-calendar-alt mr-3"></i>รายงานรายเดือน
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                รายงานการอนุมัติประจำเดือน <?= $thaiMonths[$selectedMonth] ?> <?= $selectedYear + 543 ?>
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/approver/reports/" class="btn-secondary">
                <i class="fas fa-chart-bar mr-2"></i>รายงานทั่วไป
            </a>
            <button onclick="window.print()" class="btn-info">
                <i class="fas fa-print mr-2"></i>พิมพ์
            </button>
        </div>
    </div>

    <!-- Month/Year Filter -->
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">เดือน</label>
                    <select id="month" name="month" class="form-select">
                        <?php foreach ($thaiMonths as $num => $name): ?>
                        <option value="<?= $num ?>" <?= $selectedMonth === $num ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">ปี พ.ศ.</label>
                    <select id="year" name="year" class="form-select">
                        <?php 
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year >= 2020; $year--): 
                        ?>
                        <option value="<?= $year ?>" <?= $selectedYear === $year ? 'selected' : '' ?>>
                            <?= $year + 543 ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="btn-primary w-full">
                        <i class="fas fa-search mr-2"></i>ดูรายงาน
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="card stat-card approved">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-green-600"><?= number_format($monthlyStats['total_approved']) ?></div>
                <div class="text-sm text-gray-500">เอกสารที่อนุมัติ</div>
            </div>
        </div>
        
        <div class="card stat-card rejected">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-red-600"><?= number_format($monthlyStats['total_rejected']) ?></div>
                <div class="text-sm text-gray-500">เอกสารที่ไม่อนุมัติ</div>
            </div>
        </div>
        
        <div class="card stat-card info">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-blue-600"><?= $monthlyStats['approval_rate'] ?>%</div>
                <div class="text-sm text-gray-500">อัตราการอนุมัติ</div>
            </div>
        </div>
        
        <div class="card stat-card pending">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-purple-600"><?= $monthlyStats['avg_per_day'] ?></div>
                <div class="text-sm text-gray-500">เฉลี่ย/วัน</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Daily Activity Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-chart-line mr-2"></i>กิจกรรมรายวัน
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dailyActivityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Weekly Summary Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-chart-bar mr-2"></i>สรุปรายสัปดาห์
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <!-- Category Performance -->
        <?php if (!empty($categoryBreakdown)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-folder mr-2"></i>ประสิทธิภาพตามหมวดหมู่
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
                                <th class="text-center">เวลาเฉลี่ย</th>
                                <th class="text-center">อัตราอนุมัติ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoryBreakdown as $cat): ?>
                            <?php 
                            $rate = $cat['total'] > 0 ? round(($cat['approved'] / $cat['total']) * 100, 1) : 0;
                            $avgHours = round($cat['avg_processing_hours'], 1);
                            ?>
                            <tr>
                                <td class="font-medium"><?= htmlspecialchars($cat['category_name']) ?></td>
                                <td class="text-center text-green-600"><?= number_format($cat['approved']) ?></td>
                                <td class="text-center text-red-600"><?= number_format($cat['rejected']) ?></td>
                                <td class="text-center"><?= $avgHours ?> ชม.</td>
                                <td class="text-center">
                                    <span class="font-medium"><?= $rate ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Top Performance Days -->
        <?php if (!empty($topDays)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-star mr-2"></i>วันที่มีประสิทธิภาพสูงสุด
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th class="text-center">อนุมัติ</th>
                                <th class="text-center">ไม่อนุมัติ</th>
                                <th class="text-center">รวม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topDays as $index => $day): ?>
                            <tr class="<?= $index === 0 ? 'bg-yellow-50' : '' ?>">
                                <td class="font-medium">
                                    <?= formatThaiDate($day['date']) ?>
                                    <?php if ($index === 0): ?>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-crown mr-1"></i>สูงสุด
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-green-600"><?= number_format($day['approved']) ?></td>
                                <td class="text-center text-red-600"><?= number_format($day['rejected']) ?></td>
                                <td class="text-center font-medium"><?= number_format($day['total_actions']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Summary Box -->
    <div class="card mt-8">
        <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-clipboard-list mr-2"></i>สรุปผลการดำเนินงาน
            </h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="text-sm text-gray-500 mb-1">เอกสารทั้งหมด</div>
                    <div class="text-2xl font-bold text-gray-900"><?= number_format($monthlyStats['unique_documents']) ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">การดำเนินการทั้งหมด</div>
                    <div class="text-2xl font-bold text-gray-900"><?= number_format($monthlyStats['total_actions']) ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">วันที่ทำงาน</div>
                    <div class="text-2xl font-bold text-gray-900"><?= number_format($monthlyStats['working_days']) ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">ประสิทธิภาพ</div>
                    <div class="text-2xl font-bold <?= $monthlyStats['approval_rate'] >= 80 ? 'text-green-600' : ($monthlyStats['approval_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') ?>">
                        <?php if ($monthlyStats['approval_rate'] >= 80): ?>
                        ดีเยี่ยม
                        <?php elseif ($monthlyStats['approval_rate'] >= 60): ?>
                        ดี
                        <?php else: ?>
                        ปรับปรุง
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    createDailyActivityChart();
    createWeeklyChart();
});

function createDailyActivityChart() {
    const ctx = document.getElementById('dailyActivityChart');
    if (!ctx) return;
    
    const dailyData = <?= json_encode($dailyBreakdown) ?>;
    
    // Create labels for all days in month
    const daysInMonth = new Date(<?= $selectedYear ?>, <?= $selectedMonth ?>, 0).getDate();
    const labels = [];
    const approvedData = [];
    const rejectedData = [];
    
    for (let day = 1; day <= daysInMonth; day++) {
        labels.push(day);
        
        const dayData = dailyData.find(d => parseInt(d.day) === day);
        approvedData.push(dayData ? dayData.approved : 0);
        rejectedData.push(dayData ? dayData.rejected : 0);
    }
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'อนุมัติ',
                data: approvedData,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: '#10b981',
                borderWidth: 1
            }, {
                label: 'ไม่อนุมัติ',
                data: rejectedData,
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: '#ef4444',
                borderWidth: 1
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
                x: {
                    title: {
                        display: true,
                        text: 'วันที่'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนเอกสาร'
                    }
                }
            }
        }
    });
}

function createWeeklyChart() {
    const ctx = document.getElementById('weeklyChart');
    if (!ctx) return;
    
    const weeklyData = <?= json_encode($weeklyData) ?>;
    
    if (weeklyData.length === 0) {
        ctx.parentElement.innerHTML = '<div class="text-center py-8 text-gray-500">ไม่มีข้อมูลสำหรับเดือนนี้</div>';
        return;
    }
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: weeklyData.map(d => d.week_display),
            datasets: [{
                data: weeklyData.map(d => d.total),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
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
                    position: 'bottom'
                }
            }
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>