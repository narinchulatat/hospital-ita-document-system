<?php
/**
 * Document Summary Report
 */

// Page configuration
$pageTitle = 'สรุปรายงานเอกสาร';
$pageDescription = 'รายงานสรุปข้อมูลเอกสารทั้งหมดในระบบ';
$pageIcon = 'fas fa-chart-bar';
$breadcrumb = generateReportBreadcrumb([
    ['name' => 'รายงานเอกสาร', 'url' => REPORTS_URL . '/documents/'],
    ['name' => 'สรุปเอกสาร']
]);

// Include header
include_once __DIR__ . '/../includes/header.php';

// Check permission
if (!hasReportPermission('documents')) {
    echo '<div class="alert alert-danger">คุณไม่มีสิทธิ์เข้าถึงรายงานเอกสาร</div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Get report data
$report = new Report();
$documentStats = $report->getDocumentStats();

// Handle export
if (isset($_POST['export']) && isset($_POST['csrf_token'])) {
    // TODO: Implement export functionality
}

// Get database instance
$db = Database::getInstance();

// Get document statistics by category
$categoryStats = $db->fetchAll("
    SELECT c.name as category_name, COUNT(d.id) as document_count,
           SUM(CASE WHEN d.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
           SUM(CASE WHEN d.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
           SUM(CASE WHEN d.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM categories c
    LEFT JOIN documents d ON c.id = d.category_id
    GROUP BY c.id, c.name
    ORDER BY document_count DESC
");

// Get document statistics by month
$monthlyStats = $db->fetchAll("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
           COUNT(*) as document_count,
           SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
           SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
           SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM documents
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");

// Get top file types
$fileTypeStats = $db->fetchAll("
    SELECT file_type, COUNT(*) as count
    FROM documents
    GROUP BY file_type
    ORDER BY count DESC
    LIMIT 10
");
?>

<!-- Export Buttons -->
<div class="export-buttons">
    <button onclick="exportReport('pdf')" class="export-btn pdf">
        <i class="fas fa-file-pdf"></i> ส่งออก PDF
    </button>
    <button onclick="exportReport('excel')" class="export-btn excel">
        <i class="fas fa-file-excel"></i> ส่งออก Excel
    </button>
    <button onclick="exportReport('csv')" class="export-btn csv">
        <i class="fas fa-file-csv"></i> ส่งออก CSV
    </button>
    <button onclick="window.print()" class="export-btn">
        <i class="fas fa-print"></i> พิมพ์
    </button>
</div>

<div class="space-y-8">
    <!-- Summary Statistics -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
            สรุปภาพรวม
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="stat-number text-blue-600"><?php echo formatNumber($documentStats['total'] ?? 0); ?></div>
                        <div class="stat-label">เอกสารทั้งหมด</div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 100%"></div>
                </div>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="stat-number text-green-600"><?php echo formatNumber($documentStats['approved'] ?? 0); ?></div>
                        <div class="stat-label">อนุมัติแล้ว</div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill success" style="width: <?php echo ($documentStats['total'] > 0) ? (($documentStats['approved'] / $documentStats['total']) * 100) : 0; ?>%"></div>
                </div>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="stat-number text-yellow-600"><?php echo formatNumber($documentStats['pending'] ?? 0); ?></div>
                        <div class="stat-label">รออนุมัติ</div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill warning" style="width: <?php echo ($documentStats['total'] > 0) ? (($documentStats['pending'] / $documentStats['total']) * 100) : 0; ?>%"></div>
                </div>
            </div>
            
            <div class="stat-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="stat-number text-red-600"><?php echo formatNumber($documentStats['rejected'] ?? 0); ?></div>
                        <div class="stat-label">ไม่อนุมัติ</div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill danger" style="width: <?php echo ($documentStats['total'] > 0) ? (($documentStats['rejected'] / $documentStats['total']) * 100) : 0; ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Documents by Category -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                เอกสารตามหมวดหมู่
            </h3>
            <div class="relative">
                <canvas id="category-chart" height="300"></canvas>
            </div>
        </div>
        
        <!-- Documents by File Type -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                เอกสารตามประเภทไฟล์
            </h3>
            <div class="relative">
                <canvas id="filetype-chart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-chart-line mr-2 text-purple-600"></i>
            สถิติรายเดือน (12 เดือนที่ผ่านมา)
        </h3>
        
        <div class="chart-container">
            <canvas id="monthly-chart" height="400"></canvas>
        </div>
    </div>

    <!-- Category Statistics Table -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-table mr-2 text-indigo-600"></i>
            สถิติเอกสารตามหมวดหมู่
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full data-table">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมวดหมู่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จำนวนทั้งหมด</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อนุมัติแล้ว</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รออนุมัติ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ไม่อนุมัติ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เปอร์เซ็นต์อนุมัติ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($categoryStats as $stat): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($stat['category_name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatNumber($stat['document_count']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-green-600"><?php echo formatNumber($stat['approved_count']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-yellow-600"><?php echo formatNumber($stat['pending_count']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-red-600"><?php echo formatNumber($stat['rejected_count']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?php echo formatPercentage($stat['approved_count'], $stat['document_count']); ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Custom JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category Chart
    const categoryChart = new Chart(document.getElementById('category-chart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($categoryStats, 'category_name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($categoryStats, 'document_count')); ?>,
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                    '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
                ]
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
    
    // File Type Chart
    const fileTypeChart = new Chart(document.getElementById('filetype-chart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($fileTypeStats, 'file_type')); ?>,
            datasets: [{
                label: 'จำนวนไฟล์',
                data: <?php echo json_encode(array_column($fileTypeStats, 'count')); ?>,
                backgroundColor: '#10B981'
            }]
        },
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
    
    // Monthly Chart
    const monthlyChart = new Chart(document.getElementById('monthly-chart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthlyStats, 'month')); ?>,
            datasets: [{
                label: 'เอกสารทั้งหมด',
                data: <?php echo json_encode(array_column($monthlyStats, 'document_count')); ?>,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true
            }, {
                label: 'อนุมัติแล้ว',
                data: <?php echo json_encode(array_column($monthlyStats, 'approved_count')); ?>,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true
            }, {
                label: 'รออนุมัติ',
                data: <?php echo json_encode(array_column($monthlyStats, 'pending_count')); ?>,
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'เดือน'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'จำนวนเอกสาร'
                    }
                }
            }
        }
    });
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>