<?php
/**
 * Documents by Category Report
 */

// Page configuration
$pageTitle = 'รายงานเอกสารตามหมวดหมู่';
$pageDescription = 'รายงานเอกสารจำแนกตามหมวดหมู่';
$pageIcon = 'fas fa-folder';

require_once __DIR__ . '/../includes/functions.php';
$breadcrumb = generateReportBreadcrumb([
    ['name' => 'รายงานเอกสาร', 'url' => REPORTS_URL . '/documents/'],
    ['name' => 'รายงานตามหมวดหมู่']
]);

// Include header
include_once __DIR__ . '/../includes/header.php';

// Check permission
if (!hasReportPermission('documents')) {
    echo '<div class="alert alert-danger">คุณไม่มีสิทธิ์เข้าถึงรายงานเอกสาร</div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Get filters
$selectedCategory = $_GET['category'] ?? '';
$dateRange = $_GET['date_range'] ?? 'all';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Get database instance
$db = Database::getInstance();

// Get categories for filter
$categories = $db->fetchAll("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");

// Build query conditions
$whereConditions = [];
$params = [];

if ($selectedCategory) {
    $whereConditions[] = "d.category_id = ?";
    $params[] = $selectedCategory;
}

if ($dateRange !== 'all') {
    $dateFilter = ($dateRange === 'custom' && $startDate && $endDate) 
        ? ['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']
        : getDateRange($dateRange);
    
    $whereConditions[] = "d.created_at >= ? AND d.created_at <= ?";
    $params[] = $dateFilter['start'];
    $params[] = $dateFilter['end'];
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get category statistics
$categoryStats = $db->fetchAll("
    SELECT c.id, c.name as category_name, 
           COUNT(d.id) as total_documents,
           SUM(CASE WHEN d.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
           SUM(CASE WHEN d.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
           SUM(CASE WHEN d.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
           SUM(CASE WHEN d.status = 'draft' THEN 1 ELSE 0 END) as draft_count,
           AVG(d.file_size) as avg_file_size,
           SUM(d.download_count) as total_downloads,
           SUM(d.view_count) as total_views
    FROM categories c
    LEFT JOIN documents d ON c.id = d.category_id {$whereClause}
    GROUP BY c.id, c.name
    ORDER BY total_documents DESC
", $params);

// Get documents for selected category
$documents = [];
if ($selectedCategory) {
    $docWhereConditions = ["d.category_id = ?"];
    $docParams = [$selectedCategory];
    
    if ($dateRange !== 'all') {
        $docWhereConditions[] = "d.created_at >= ? AND d.created_at <= ?";
        $docParams[] = $dateFilter['start'];
        $docParams[] = $dateFilter['end'];
    }
    
    $docWhereClause = "WHERE " . implode(" AND ", $docWhereConditions);
    
    $documents = $db->fetchAll("
        SELECT d.*, c.name as category_name,
               CONCAT(u.first_name, ' ', u.last_name) as uploader_name
        FROM documents d
        JOIN categories c ON d.category_id = c.id
        JOIN users u ON d.user_id = u.id
        {$docWhereClause}
        ORDER BY d.created_at DESC
    ", $docParams);
}
?>

<!-- Filter Section -->
<div class="filter-section mb-8">
    <form id="filterForm" class="filter-row">
        <div class="filter-group">
            <label for="category">หมวดหมู่</label>
            <select id="category" name="category" class="filter-select">
                <option value="">ทั้งหมด</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo ($selectedCategory == $category['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="dateRange">ช่วงเวลา</label>
            <select id="dateRange" name="date_range" class="filter-select">
                <option value="all" <?php echo ($dateRange === 'all') ? 'selected' : ''; ?>>ทั้งหมด</option>
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
    <!-- Category Distribution Chart -->
    <div class="chart-container">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
            การกระจายเอกสารตามหมวดหมู่
        </h3>
        <div class="relative">
            <canvas id="category-distribution-chart" height="400"></canvas>
        </div>
    </div>

    <!-- Category Statistics Table -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-table mr-2 text-green-600"></i>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ร่าง</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ขนาดเฉลี่ย</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ดาวน์โหลด</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การเข้าชม</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($categoryStats as $stat): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($stat['category_name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatNumber($stat['total_documents']); ?></div>
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
                            <div class="text-sm text-gray-600"><?php echo formatNumber($stat['draft_count']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatFileSize($stat['avg_file_size'] ?? 0); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-blue-600"><?php echo formatNumber($stat['total_downloads']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-purple-600"><?php echo formatNumber($stat['total_views']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="?category=<?php echo $stat['id']; ?>&date_range=<?php echo $dateRange; ?>&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" 
                               class="text-blue-600 hover:text-blue-900">
                                ดูรายละเอียด
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Document List (if category selected) -->
    <?php if ($selectedCategory && !empty($documents)): ?>
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-list mr-2 text-purple-600"></i>
            รายการเอกสารในหมวดหมู่
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full data-table">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เอกสาร</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้อัปโหลด</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ขนาดไฟล์</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ดาวน์โหลด</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การเข้าชม</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($documents as $doc): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <i class="fas fa-file-alt text-blue-600 mr-3"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($doc['description'] ?? ''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'approved' => 'bg-green-100 text-green-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'draft' => 'bg-gray-100 text-gray-800'
                            ];
                            $statusNames = [
                                'approved' => 'อนุมัติแล้ว',
                                'pending' => 'รออนุมัติ',
                                'rejected' => 'ไม่อนุมัติ',
                                'draft' => 'ร่าง'
                            ];
                            ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColors[$doc['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo $statusNames[$doc['status']] ?? $doc['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($doc['uploader_name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatFileSize($doc['file_size']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-blue-600"><?php echo formatNumber($doc['download_count']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-purple-600"><?php echo formatNumber($doc['view_count']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatThaiDate($doc['created_at']); ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Custom JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category Distribution Chart
    const categoryChart = new Chart(document.getElementById('category-distribution-chart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($categoryStats, 'category_name')); ?>,
            datasets: [{
                label: 'จำนวนเอกสาร',
                data: <?php echo json_encode(array_column($categoryStats, 'total_documents')); ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }, {
                label: 'อนุมัติแล้ว',
                data: <?php echo json_encode(array_column($categoryStats, 'approved_count')); ?>,
                backgroundColor: 'rgba(16, 185, 129, 0.5)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1
            }, {
                label: 'รออนุมัติ',
                data: <?php echo json_encode(array_column($categoryStats, 'pending_count')); ?>,
                backgroundColor: 'rgba(245, 158, 11, 0.5)',
                borderColor: 'rgba(245, 158, 11, 1)',
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
                    beginAtZero: true
                },
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
include_once __DIR__ . '/../includes/footer.php';
?>