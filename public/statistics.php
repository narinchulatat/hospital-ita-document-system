<?php
$pageTitle = 'สถิติการใช้งาน - ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';

try {
    $document = new Document();
    $category = new Category();
    $db = Database::getInstance();
    
    // Get basic statistics
    $totalDocuments = $document->getTotalCount(['is_public' => 1, 'status' => 'approved']);
    $totalCategories = count($category->getAll(true));
    
    // Get download and view statistics
    $downloadStats = $db->fetch("
        SELECT 
            SUM(download_count) as total_downloads,
            SUM(view_count) as total_views,
            AVG(download_count) as avg_downloads,
            AVG(view_count) as avg_views
        FROM documents 
        WHERE is_public = 1 AND status = 'approved'
    ");
    
    // Get top downloaded documents
    $topDownloaded = $db->fetchAll("
        SELECT id, title, download_count, view_count, file_type
        FROM documents 
        WHERE is_public = 1 AND status = 'approved'
        ORDER BY download_count DESC 
        LIMIT 10
    ");
    
    // Get documents by category
    $categoryStats = $db->fetchAll("
        SELECT 
            c.name as category_name,
            COUNT(d.id) as document_count,
            SUM(d.download_count) as total_downloads,
            SUM(d.view_count) as total_views
        FROM categories c
        LEFT JOIN documents d ON c.id = d.category_id AND d.is_public = 1 AND d.status = 'approved'
        WHERE c.is_active = 1
        GROUP BY c.id, c.name
        ORDER BY document_count DESC
    ");
    
    // Get documents by file type
    $fileTypeStats = $db->fetchAll("
        SELECT 
            file_type,
            COUNT(*) as count,
            SUM(download_count) as downloads,
            ROUND(AVG(CAST(file_size AS UNSIGNED))) as avg_size
        FROM documents 
        WHERE is_public = 1 AND status = 'approved'
        GROUP BY file_type
        ORDER BY count DESC
    ");
    
    // Get monthly statistics for the last 6 months
    $monthlyStats = $db->fetchAll("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as documents_added,
            SUM(download_count) as total_downloads
        FROM documents 
        WHERE is_public = 1 AND status = 'approved'
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    
} catch (Exception $e) {
    error_log("Statistics page error: " . $e->getMessage());
    $totalDocuments = 0;
    $totalCategories = 0;
    $downloadStats = ['total_downloads' => 0, 'total_views' => 0, 'avg_downloads' => 0, 'avg_views' => 0];
    $topDownloaded = [];
    $categoryStats = [];
    $fileTypeStats = [];
    $monthlyStats = [];
}
?>

<!-- Breadcrumb -->
<div class="bg-gray-50 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="<?= BASE_URL ?>/public/" class="text-gray-500 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>หน้าหลัก
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-gray-900 font-medium">สถิติการใช้งาน</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            <i class="fas fa-chart-bar text-blue-600 mr-3"></i>สถิติการใช้งานระบบ
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            ภาพรวมการใช้งานระบบจัดเก็บเอกสาร สถิติการดาวน์โหลด และข้อมูลเชิงลึกต่างๆ
        </p>
    </div>

    <!-- Overview Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">เอกสารทั้งหมด</p>
                    <p class="text-3xl font-bold"><?= number_format($totalDocuments) ?></p>
                </div>
                <div class="bg-blue-400 bg-opacity-50 p-3 rounded-full">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium uppercase tracking-wide">หมวดหมู่</p>
                    <p class="text-3xl font-bold"><?= number_format($totalCategories) ?></p>
                </div>
                <div class="bg-green-400 bg-opacity-50 p-3 rounded-full">
                    <i class="fas fa-folder text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium uppercase tracking-wide">ดาวน์โหลดทั้งหมด</p>
                    <p class="text-3xl font-bold"><?= number_format($downloadStats['total_downloads'] ?? 0) ?></p>
                </div>
                <div class="bg-purple-400 bg-opacity-50 p-3 rounded-full">
                    <i class="fas fa-download text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 rounded-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium uppercase tracking-wide">การเข้าชมทั้งหมด</p>
                    <p class="text-3xl font-bold"><?= number_format($downloadStats['total_views'] ?? 0) ?></p>
                </div>
                <div class="bg-orange-400 bg-opacity-50 p-3 rounded-full">
                    <i class="fas fa-eye text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Category Distribution -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-pie-chart text-blue-600 mr-2"></i>การกระจายตามหมวดหมู่
            </h3>
            
            <?php if (!empty($categoryStats)): ?>
                <div class="space-y-4">
                    <?php 
                    $maxCount = max(array_column($categoryStats, 'document_count'));
                    foreach (array_slice($categoryStats, 0, 8) as $category): 
                        $percentage = $maxCount > 0 ? ($category['document_count'] / $maxCount) * 100 : 0;
                    ?>
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">
                                <?= htmlspecialchars($category['category_name']) ?>
                            </span>
                            <span class="text-sm text-gray-500">
                                <?= number_format($category['document_count']) ?> เอกสาร
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-500 ease-out" 
                                 style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-chart-pie text-3xl mb-2"></i>
                    <p>ไม่มีข้อมูลสถิติหมวดหมู่</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- File Type Distribution -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-file-archive text-green-600 mr-2"></i>ประเภทไฟล์
            </h3>
            
            <?php if (!empty($fileTypeStats)): ?>
                <div class="space-y-4">
                    <?php 
                    $colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500'];
                    foreach ($fileTypeStats as $index => $fileType): 
                        $color = $colors[$index % count($colors)];
                    ?>
                    <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center">
                            <div class="w-4 h-4 <?= $color ?> rounded mr-3"></div>
                            <span class="font-medium text-gray-800 uppercase">
                                <?= htmlspecialchars($fileType['file_type']) ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">
                                <?= number_format($fileType['count']) ?> ไฟล์
                            </div>
                            <div class="text-xs text-gray-500">
                                <?= number_format($fileType['downloads']) ?> ดาวน์โหลด
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-file text-3xl mb-2"></i>
                    <p>ไม่มีข้อมูลประเภทไฟล์</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Downloads -->
    <div class="bg-white shadow rounded-lg p-6 mb-12">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-trophy text-yellow-600 mr-2"></i>เอกสารยอดนิยม (Top 10)
        </h3>
        
        <?php if (!empty($topDownloaded)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                อันดับ
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ชื่อเอกสาร
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ประเภท
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ดาวน์โหลด
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                การเข้าชม
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($topDownloaded as $index => $doc): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($index < 3): ?>
                                        <span class="flex items-center justify-center w-8 h-8 rounded-full 
                                               <?= $index === 0 ? 'bg-yellow-100 text-yellow-800' : 
                                                  ($index === 1 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800') ?>">
                                            <?= $index + 1 ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-600 font-medium"><?= $index + 1 ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">
                                    <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= strtoupper(htmlspecialchars($doc['file_type'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <i class="fas fa-download text-green-500 mr-2"></i>
                                    <?= number_format($doc['download_count']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <i class="fas fa-eye text-blue-500 mr-2"></i>
                                    <?= number_format($doc['view_count']) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-file-alt text-3xl mb-2"></i>
                <p>ไม่มีข้อมูลการดาวน์โหลด</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Monthly Trends -->
    <div class="bg-white shadow rounded-lg p-6 mb-12">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-chart-line text-purple-600 mr-2"></i>แนวโน้มรายเดือน (6 เดือนล่าสุด)
        </h3>
        
        <?php if (!empty($monthlyStats)): ?>
            <div class="space-y-4">
                <?php 
                $maxDocuments = max(array_column($monthlyStats, 'documents_added'));
                $maxDownloads = max(array_column($monthlyStats, 'total_downloads'));
                
                // Thai month names
                $thaiMonths = [
                    '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
                    '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
                    '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
                ];
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Documents Added -->
                    <div>
                        <h4 class="text-md font-medium text-gray-800 mb-3">เอกสารที่เพิ่มใหม่</h4>
                        <?php foreach (array_reverse($monthlyStats) as $month): 
                            $monthParts = explode('-', $month['month']);
                            $monthName = $thaiMonths[$monthParts[1]] . ' ' . ($monthParts[0] + 543);
                            $percentage = $maxDocuments > 0 ? ($month['documents_added'] / $maxDocuments) * 100 : 0;
                        ?>
                        <div class="mb-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-600"><?= $monthName ?></span>
                                <span class="text-sm font-semibold text-gray-900">
                                    <?= number_format($month['documents_added']) ?>
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full transition-all duration-500 ease-out" 
                                     style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Downloads -->
                    <div>
                        <h4 class="text-md font-medium text-gray-800 mb-3">การดาวน์โหลด</h4>
                        <?php foreach (array_reverse($monthlyStats) as $month): 
                            $monthParts = explode('-', $month['month']);
                            $monthName = $thaiMonths[$monthParts[1]] . ' ' . ($monthParts[0] + 543);
                            $percentage = $maxDownloads > 0 ? ($month['total_downloads'] / $maxDownloads) * 100 : 0;
                        ?>
                        <div class="mb-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-600"><?= $monthName ?></span>
                                <span class="text-sm font-semibold text-gray-900">
                                    <?= number_format($month['total_downloads']) ?>
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition-all duration-500 ease-out" 
                                     style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-chart-line text-3xl mb-2"></i>
                <p>ไม่มีข้อมูลแนวโน้ม</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Average Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-r from-cyan-50 to-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-calculator text-cyan-600 mr-2"></i>ค่าเฉลี่ย
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">ดาวน์โหลดต่อเอกสาร:</span>
                    <span class="font-bold text-gray-900">
                        <?= number_format($downloadStats['avg_downloads'] ?? 0, 1) ?>
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">การเข้าชมต่อเอกสาร:</span>
                    <span class="font-bold text-gray-900">
                        <?= number_format($downloadStats['avg_views'] ?? 0, 1) ?>
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">เอกสารต่อหมวดหมู่:</span>
                    <span class="font-bold text-gray-900">
                        <?= $totalCategories > 0 ? number_format($totalDocuments / $totalCategories, 1) : '0' ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-emerald-50 to-green-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle text-emerald-600 mr-2"></i>ข้อมูลเพิ่มเติม
            </h3>
            <div class="space-y-3 text-sm text-gray-600">
                <p>
                    <i class="fas fa-clock text-blue-500 mr-2"></i>
                    ข้อมูลอัปเดตล่าสุด: <?= formatThaiDate(date('Y-m-d H:i:s'), true) ?>
                </p>
                <p>
                    <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                    สถิติคำนวณจากเอกสารสาธารณะที่ได้รับการอนุมัติแล้วเท่านั้น
                </p>
                <p>
                    <i class="fas fa-refresh text-purple-500 mr-2"></i>
                    สถิติจะอัปเดตแบบเรียลไทม์เมื่อมีการใช้งาน
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('[style*="width:"]');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
    
    // Auto refresh every 5 minutes
    setTimeout(() => {
        location.reload();
    }, 300000);
});
</script>

<?php require_once '../includes/footer.php'; ?>