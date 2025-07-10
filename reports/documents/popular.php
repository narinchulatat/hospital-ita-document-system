<?php
/**
 * Popular Documents Report
 */

// Page configuration
$pageTitle = 'รายงานเอกสารยอดนิยม';
$pageDescription = 'รายงานเอกสารที่ได้รับความนิยมสูงสุด';
$pageIcon = 'fas fa-star';
$breadcrumb = generateReportBreadcrumb([
    ['name' => 'รายงานเอกสาร', 'url' => REPORTS_URL . '/documents/'],
    ['name' => 'เอกสารยอดนิยม']
]);

// Include header
include_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

// Check permission
if (!hasReportPermission('documents')) {
    echo '<div class="alert alert-danger">คุณไม่มีสิทธิ์เข้าถึงรายงานเอกสาร</div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Get filters
$orderBy = $_GET['order_by'] ?? 'download_count';
$category = $_GET['category'] ?? '';
$limit = $_GET['limit'] ?? 50;
$dateRange = $_GET['date_range'] ?? 'all';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Validate parameters
$validOrderBy = ['download_count', 'view_count', 'created_at'];
if (!in_array($orderBy, $validOrderBy)) {
    $orderBy = 'download_count';
}

$limit = min(max(intval($limit), 10), 500); // Between 10 and 500

// Get database instance
$db = Database::getInstance();

// Get categories for filter
$categories = $db->fetchAll("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");

// Build query conditions
$whereConditions = ["d.status = 'approved'"];
$params = [];

if ($category) {
    $whereConditions[] = "d.category_id = ?";
    $params[] = $category;
}

if ($dateRange !== 'all') {
    $dateFilter = ($dateRange === 'custom' && $startDate && $endDate) 
        ? ['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59']
        : getDateRange($dateRange);
    
    $whereConditions[] = "d.created_at >= ? AND d.created_at <= ?";
    $params[] = $dateFilter['start'];
    $params[] = $dateFilter['end'];
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

// Get popular documents
$documents = $db->fetchAll("
    SELECT d.*, c.name as category_name,
           CONCAT(u.first_name, ' ', u.last_name) as uploader_name,
           (d.download_count + d.view_count * 0.1) as popularity_score
    FROM documents d
    JOIN categories c ON d.category_id = c.id
    JOIN users u ON d.user_id = u.id
    {$whereClause}
    ORDER BY d.{$orderBy} DESC, d.created_at DESC
    LIMIT ?
", array_merge($params, [$limit]));

// Get statistics
$stats = $db->fetchRow("
    SELECT COUNT(*) as total_documents,
           SUM(download_count) as total_downloads,
           SUM(view_count) as total_views,
           AVG(download_count) as avg_downloads,
           AVG(view_count) as avg_views,
           MAX(download_count) as max_downloads,
           MAX(view_count) as max_views
    FROM documents d
    {$whereClause}
", $params);

// Get top categories by downloads
$topCategories = $db->fetchAll("
    SELECT c.name, SUM(d.download_count) as total_downloads, COUNT(d.id) as document_count
    FROM categories c
    JOIN documents d ON c.id = d.category_id
    WHERE d.status = 'approved'
    GROUP BY c.id, c.name
    ORDER BY total_downloads DESC
    LIMIT 10
");

// Get top file types
$topFileTypes = $db->fetchAll("
    SELECT file_type, COUNT(*) as count, SUM(download_count) as total_downloads
    FROM documents
    WHERE status = 'approved'
    GROUP BY file_type
    ORDER BY total_downloads DESC
    LIMIT 10
");
?>

<!-- Filter Section -->
<div class="filter-section mb-8">
    <form id="filterForm" class="filter-row">
        <div class="filter-group">
            <label for="orderBy">เรียงตาม</label>
            <select id="orderBy" name="order_by" class="filter-select">
                <option value="download_count" <?php echo ($orderBy === 'download_count') ? 'selected' : ''; ?>>จำนวนดาวน์โหลด</option>
                <option value="view_count" <?php echo ($orderBy === 'view_count') ? 'selected' : ''; ?>>จำนวนการเข้าชม</option>
                <option value="created_at" <?php echo ($orderBy === 'created_at') ? 'selected' : ''; ?>>วันที่สร้าง</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="category">หมวดหมู่</label>
            <select id="category" name="category" class="filter-select">
                <option value="">ทั้งหมด</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="limit">จำนวนแสดง</label>
            <select id="limit" name="limit" class="filter-select">
                <option value="10" <?php echo ($limit == 10) ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo ($limit == 25) ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo ($limit == 50) ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo ($limit == 100) ? 'selected' : ''; ?>>100</option>
                <option value="500" <?php echo ($limit == 500) ? 'selected' : ''; ?>>500</option>
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
    <!-- Summary Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-blue-600 text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="stat-number text-blue-600"><?php echo formatNumber($stats['total_documents']); ?></div>
                    <div class="stat-label">เอกสารทั้งหมด</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-download text-green-600 text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="stat-number text-green-600"><?php echo formatNumber($stats['total_downloads']); ?></div>
                    <div class="stat-label">ดาวน์โหลดทั้งหมด</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="w-16 h-16 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-eye text-purple-600 text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="stat-number text-purple-600"><?php echo formatNumber($stats['total_views']); ?></div>
                    <div class="stat-label">การเข้าชมทั้งหมด</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-orange-600 text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="stat-number text-orange-600"><?php echo formatNumber($stats['avg_downloads']); ?></div>
                    <div class="stat-label">ดาวน์โหลดเฉลี่ย</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Categories -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                หมวดหมู่ที่ได้รับความนิยม
            </h3>
            <div class="relative">
                <canvas id="top-categories-chart" height="300"></canvas>
            </div>
        </div>
        
        <!-- Top File Types -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                ประเภทไฟล์ที่ได้รับความนิยม
            </h3>
            <div class="relative">
                <canvas id="top-filetypes-chart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Popular Documents Table -->
    <div class="report-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-star mr-2 text-yellow-600"></i>
            รายการเอกสารยอดนิยม (แสดง <?php echo count($documents); ?> จาก <?php echo $stats['total_documents']; ?> เอกสาร)
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full data-table">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อันดับ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เอกสาร</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมวดหมู่</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้อัปโหลด</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ดาวน์โหลด</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การเข้าชม</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ขนาดไฟล์</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($documents as $index => $doc): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php if ($index < 3): ?>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 <?php echo ['bg-yellow-100 text-yellow-600', 'bg-gray-100 text-gray-600', 'bg-orange-100 text-orange-600'][$index]; ?>">
                                    <i class="fas fa-medal"></i>
                                </div>
                                <?php endif; ?>
                                <span class="text-sm font-medium text-gray-900"><?php echo $index + 1; ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-file-alt text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($doc['description'] ?? '', 0, 50)) . (strlen($doc['description'] ?? '') > 50 ? '...' : ''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($doc['category_name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($doc['uploader_name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-green-600 mr-2"><?php echo formatNumber($doc['download_count']); ?></div>
                                <?php if ($doc['download_count'] == $stats['max_downloads']): ?>
                                <i class="fas fa-crown text-yellow-500" title="ดาวน์โหลดมากที่สุด"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-purple-600 mr-2"><?php echo formatNumber($doc['view_count']); ?></div>
                                <?php if ($doc['view_count'] == $stats['max_views']): ?>
                                <i class="fas fa-eye text-purple-500" title="เข้าชมมากที่สุด"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatFileSize($doc['file_size']); ?></div>
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
</div>

<!-- Custom JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Top Categories Chart
    const topCategoriesChart = new Chart(document.getElementById('top-categories-chart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($topCategories, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($topCategories, 'total_downloads')); ?>,
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            return `${label}: ${value.toLocaleString()} ดาวน์โหลด`;
                        }
                    }
                }
            }
        }
    });
    
    // Top File Types Chart
    const topFileTypesChart = new Chart(document.getElementById('top-filetypes-chart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($topFileTypes, 'file_type')); ?>,
            datasets: [{
                label: 'จำนวนดาวน์โหลด',
                data: <?php echo json_encode(array_column($topFileTypes, 'total_downloads')); ?>,
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
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>