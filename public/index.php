<?php
$pageTitle = 'หน้าหลัก - เอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';

try {
    $category = new Category();
    $document = new Document();
    $db = Database::getInstance();
    
    // Get category tree for navigation
    $categoryTree = $category->getTree();
    
    // Get recent documents (last 7 days or latest 10)
    $recentDocuments = $document->getAll(['is_public' => 1, 'status' => 'approved'], 1, 10, 'created_at', 'desc');
    
    // Get featured documents (most downloaded)
    $featuredDocuments = $document->getAll(['is_public' => 1, 'status' => 'approved'], 1, 6, 'download_count', 'desc');
    
    // Get most downloaded documents this month
    $topDownloadsThisMonth = $db->fetchAll("
        SELECT id, title, download_count, view_count, file_type, category_name
        FROM documents 
        WHERE is_public = 1 AND status = 'approved' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        ORDER BY download_count DESC 
        LIMIT 5
    ");
    
    // Get statistics
    $stats = [
        'total_documents' => $document->getTotalCount(['is_public' => 1, 'status' => 'approved']),
        'total_categories' => count($category->getAll(true)),
        'total_downloads' => 0,
        'total_views' => 0,
        'new_this_week' => 0
    ];
    
    // Get comprehensive statistics
    $downloadStats = $db->fetch("
        SELECT 
            SUM(download_count) as total_downloads,
            SUM(view_count) as total_views
        FROM documents 
        WHERE is_public = 1 AND status = 'approved'
    ");
    $stats['total_downloads'] = $downloadStats['total_downloads'] ?? 0;
    $stats['total_views'] = $downloadStats['total_views'] ?? 0;
    
    // Get new documents this week
    $newThisWeek = $db->fetch("
        SELECT COUNT(*) as count 
        FROM documents 
        WHERE is_public = 1 AND status = 'approved' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)
    ");
    $stats['new_this_week'] = $newThisWeek['count'] ?? 0;
    
} catch (Exception $e) {
    error_log("Public index error: " . $e->getMessage());
    $categoryTree = [];
    $recentDocuments = [];
    $featuredDocuments = [];
    $topDownloadsThisMonth = [];
    $stats = ['total_documents' => 0, 'total_categories' => 0, 'total_downloads' => 0, 'total_views' => 0, 'new_this_week' => 0];
}
?>

<!-- Hero Section with Quick Search -->
<div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold sm:text-5xl md:text-6xl">
                <span class="block">ระบบจัดเก็บเอกสาร ITA</span>
                <span class="block text-blue-200">โรงพยาบาล</span>
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-xl text-blue-100">
                ระบบจัดการและเผยแพร่เอกสารสำหรับโรงพยาบาล พร้อมระบบค้นหาและจัดหมวดหมู่ที่ครบถ้วน
            </p>
            
            <!-- Quick Search -->
            <div class="mt-10 max-w-2xl mx-auto">
                <form action="<?= BASE_URL ?>/public/search.php" method="GET" class="relative">
                    <div class="flex rounded-md shadow-lg">
                        <div class="relative flex-1">
                            <input type="text" name="q" 
                                   placeholder="ค้นหาเอกสาร คู่มือ นโยบาย..." 
                                   class="w-full px-4 py-4 text-gray-900 placeholder-gray-500 border-0 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                                   autocomplete="off"
                                   id="quickSearch">
                            <div id="searchSuggestions" class="absolute z-10 w-full bg-white border border-gray-200 rounded-b-md shadow-lg hidden max-h-60 overflow-y-auto"></div>
                        </div>
                        <button type="submit" 
                                class="px-8 py-4 bg-blue-500 hover:bg-blue-400 text-white font-medium rounded-r-md transition-colors">
                            <i class="fas fa-search mr-2"></i>ค้นหา
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 flex justify-center space-x-4 text-sm">
                    <a href="<?= BASE_URL ?>/public/advanced-search.php" class="text-blue-100 hover:text-white transition-colors">
                        <i class="fas fa-search-plus mr-1"></i>ค้นหาขั้นสูง
                    </a>
                    <span class="text-blue-300">|</span>
                    <a href="<?= BASE_URL ?>/public/categories.php" class="text-blue-100 hover:text-white transition-colors">
                        <i class="fas fa-folder mr-1"></i>เรียกดูตามหมวดหมู่
                    </a>
                </div>
            </div>
            
            <div class="mt-8 flex justify-center space-x-4">
                <a href="#documents" class="bg-white text-blue-600 hover:bg-blue-50 px-8 py-3 rounded-md text-lg font-medium transition-colors">
                    <i class="fas fa-file-alt mr-2"></i>ดูเอกสาร
                </a>
                <a href="<?= BASE_URL ?>/public/statistics.php" class="border-2 border-white text-white hover:bg-white hover:text-blue-600 px-8 py-3 rounded-md text-lg font-medium transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i>สถิติการใช้งาน
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Section -->
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-alt text-3xl text-blue-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">เอกสารทั้งหมด</dt>
                                <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_documents']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-green-500">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-folder text-3xl text-green-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">หมวดหมู่</dt>
                                <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_categories']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-purple-500">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-download text-3xl text-purple-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">ดาวน์โหลดทั้งหมด</dt>
                                <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_downloads']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-orange-500">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-star text-3xl text-orange-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">ใหม่สัปดาห์นี้</dt>
                                <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['new_this_week']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Documents Section -->
<div class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-star text-yellow-500 mr-3"></i>เอกสารแนะนำ
            </h2>
            <p class="mt-4 text-xl text-gray-600">เอกสารยอดนิยมและที่มีการดาวน์โหลดมากที่สุด</p>
        </div>
        
        <?php if (!empty($featuredDocuments)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($featuredDocuments as $doc): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                        <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                                           class="hover:text-blue-600 transition-colors">
                                            <?= htmlspecialchars($doc['title']) ?>
                                        </a>
                                    </h3>
                                    
                                    <?php if ($doc['description']): ?>
                                        <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                                            <?= htmlspecialchars(substr($doc['description'], 0, 100)) ?>
                                            <?= strlen($doc['description']) > 100 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="ml-4 flex-shrink-0">
                                    <div class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">
                                        <i class="fas fa-star mr-1"></i>แนะนำ
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>
                                    <i class="fas fa-folder mr-1"></i>
                                    <?= htmlspecialchars($doc['category_name'] ?? 'ทั่วไป') ?>
                                </span>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                    <?= strtoupper($doc['file_type']) ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                <span>
                                    <i class="fas fa-download mr-1"></i>
                                    <?= number_format($doc['download_count']) ?> ครั้ง
                                </span>
                                <span>
                                    <i class="fas fa-eye mr-1"></i>
                                    <?= number_format($doc['view_count']) ?> ครั้ง
                                </span>
                                <span>
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?= formatThaiDate($doc['created_at']) ?>
                                </span>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                                   class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded hover:bg-blue-700 transition-colors text-sm">
                                    <i class="fas fa-eye mr-1"></i>ดูรายละเอียด
                                </a>
                                <a href="<?= BASE_URL ?>/public/documents/download.php?id=<?= $doc['id'] ?>" 
                                   class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition-colors text-sm"
                                   target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="<?= BASE_URL ?>/public/documents/" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    <i class="fas fa-eye mr-2"></i>ดูเอกสารทั้งหมด
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">ยังไม่มีเอกสารแนะนำ</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Main Content -->
<div id="documents" class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Categories Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg p-6 sticky top-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-folder-tree mr-2"></i>หมวดหมู่เอกสาร
                </h3>
                
                <?php if (!empty($categoryTree)): ?>
                <div class="space-y-2">
                    <?php foreach ($categoryTree as $category): ?>
                    <div class="category-item">
                        <div class="flex items-center py-2 px-3 rounded hover:bg-gray-50 cursor-pointer category-toggle" 
                             data-category-id="<?= $category['id'] ?>">
                            <?php if (!empty($category['children'])): ?>
                            <i class="fas fa-chevron-right text-gray-400 mr-2 transition-transform tree-icon"></i>
                            <?php else: ?>
                            <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                            <?php endif; ?>
                            <span class="text-sm font-medium text-gray-700 flex-1"><?= htmlspecialchars($category['name']) ?></span>
                            <span class="text-xs text-gray-500"><?= $category['documents_count'] ?></span>
                        </div>
                        
                        <?php if (!empty($category['children'])): ?>
                        <div class="ml-6 space-y-1 category-children hidden">
                            <?php foreach ($category['children'] as $child): ?>
                            <div class="category-item">
                                <div class="flex items-center py-2 px-3 rounded hover:bg-gray-50 cursor-pointer category-toggle"
                                     data-category-id="<?= $child['id'] ?>">
                                    <?php if (!empty($child['children'])): ?>
                                    <i class="fas fa-chevron-right text-gray-400 mr-2 transition-transform tree-icon"></i>
                                    <?php else: ?>
                                    <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                                    <?php endif; ?>
                                    <span class="text-sm text-gray-600 flex-1"><?= htmlspecialchars($child['name']) ?></span>
                                    <span class="text-xs text-gray-500"><?= $child['documents_count'] ?></span>
                                </div>
                                
                                <?php if (!empty($child['children'])): ?>
                                <div class="ml-6 space-y-1 category-children hidden">
                                    <?php foreach ($child['children'] as $grandChild): ?>
                                    <div class="flex items-center py-2 px-3 rounded hover:bg-gray-50 cursor-pointer category-toggle"
                                         data-category-id="<?= $grandChild['id'] ?>">
                                        <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                                        <span class="text-sm text-gray-600 flex-1"><?= htmlspecialchars($grandChild['name']) ?></span>
                                        <span class="text-xs text-gray-500"><?= $grandChild['documents_count'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-sm">ไม่มีหมวดหมู่</p>
                <?php endif; ?>
                
                <div class="mt-6 pt-4 border-t">
                    <a href="<?= BASE_URL ?>/public/documents/" 
                       class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-eye mr-2"></i>ดูเอกสารทั้งหมด
                    </a>
                </div>
                
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/public/categories.php" 
                       class="block w-full text-center bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition-colors">
                        <i class="fas fa-folder-tree mr-2"></i>ดูหมวดหมู่ทั้งหมด
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-3 space-y-8">
            <!-- Recent Documents -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-clock mr-2"></i>เอกสารล่าสุด
                    </h3>
                    <a href="<?= BASE_URL ?>/public/documents/" class="text-blue-600 hover:text-blue-500 text-sm">
                        ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <?php if (!empty($recentDocuments)): ?>
                <div class="space-y-4">
                    <?php foreach ($recentDocuments as $doc): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900 mb-2">
                                    <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </h4>
                                
                                <?php if ($doc['description']): ?>
                                <p class="text-gray-600 text-sm mb-2 line-clamp-2">
                                    <?= htmlspecialchars(substr($doc['description'], 0, 150)) ?>
                                    <?= strlen($doc['description']) > 150 ? '...' : '' ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                    <span>
                                        <i class="fas fa-folder mr-1"></i>
                                        <?= htmlspecialchars($doc['category_name']) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?= formatThaiDate($doc['created_at']) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-eye mr-1"></i>
                                        <?= number_format($doc['view_count']) ?> ครั้ง
                                    </span>
                                    <span>
                                        <i class="fas fa-download mr-1"></i>
                                        <?= number_format($doc['download_count']) ?> ครั้ง
                                    </span>
                                </div>
                            </div>
                            
                            <div class="ml-4 flex flex-col items-end space-y-2">
                                <div class="flex items-center">
                                    <i class="<?= getFileTypeIcon($doc['file_type']) ?> mr-2"></i>
                                    <span class="text-xs text-gray-500 uppercase"><?= htmlspecialchars($doc['file_type']) ?></span>
                                </div>
                                <span class="text-xs text-gray-500"><?= formatFileSize($doc['file_size']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">ไม่มีเอกสารล่าสุด</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Top Downloads This Month -->
            <?php if (!empty($topDownloadsThisMonth)): ?>
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">
                    <i class="fas fa-fire text-red-500 mr-2"></i>ยอดนิยมในเดือนนี้
                </h3>
                
                <div class="space-y-3">
                    <?php foreach ($topDownloadsThisMonth as $index => $doc): ?>
                        <div class="flex items-center space-x-4 bg-white p-3 rounded-lg shadow-sm">
                            <div class="flex-shrink-0">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full 
                                           <?= $index === 0 ? 'bg-yellow-100 text-yellow-800' : 
                                              ($index === 1 ? 'bg-gray-100 text-gray-800' : 
                                              ($index === 2 ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800')) ?>">
                                    <?= $index + 1 ?>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($doc['category_name'] ?? 'ทั่วไป') ?></p>
                            </div>
                            <div class="flex-shrink-0 text-xs text-gray-500">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                    <i class="fas fa-download mr-1"></i><?= number_format($doc['download_count']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category tree functionality
    document.querySelectorAll('.category-toggle').forEach(function(element) {
        element.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const children = this.parentElement.querySelector('.category-children');
            const icon = this.querySelector('.tree-icon');
            
            if (children) {
                children.classList.toggle('hidden');
                icon.style.transform = children.classList.contains('hidden') ? 
                    'rotate(0deg)' : 'rotate(90deg)';
            } else {
                // Navigate to category page
                window.location.href = `<?= BASE_URL ?>/public/documents/?category=${categoryId}`;
            }
        });
    });
    
    // Smooth scroll to documents section
    document.querySelector('a[href="#documents"]').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('documents').scrollIntoView({
            behavior: 'smooth'
        });
    });
    
    // Quick Search Auto-complete
    const quickSearchInput = document.getElementById('quickSearch');
    const suggestionsDiv = document.getElementById('searchSuggestions');
    let searchTimeout;
    
    if (quickSearchInput && suggestionsDiv) {
        quickSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                suggestionsDiv.classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                fetch(`<?= BASE_URL ?>/public/api/documents.php?search=${encodeURIComponent(query)}&limit=5`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            showSearchSuggestions(data.data);
                        } else {
                            suggestionsDiv.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Search suggestions error:', error);
                        suggestionsDiv.classList.add('hidden');
                    });
            }, 300);
        });
        
        quickSearchInput.addEventListener('blur', function() {
            // Hide suggestions after a delay to allow clicking
            setTimeout(() => {
                suggestionsDiv.classList.add('hidden');
            }, 200);
        });
        
        quickSearchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                // Trigger search again when focused
                this.dispatchEvent(new Event('input'));
            }
        });
    }
    
    function showSearchSuggestions(documents) {
        const html = documents.map(doc => `
            <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                 onclick="window.location.href='<?= BASE_URL ?>/public/documents/view.php?id=${doc.id}'">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900 line-clamp-1">${escapeHtml(doc.title)}</h4>
                        <p class="text-xs text-gray-500 mt-1">${escapeHtml(doc.category_name || 'ทั่วไป')}</p>
                    </div>
                    <div class="ml-2 text-xs text-gray-400">
                        ${doc.file_type.toUpperCase()}
                    </div>
                </div>
            </div>
        `).join('');
        
        suggestionsDiv.innerHTML = html;
        suggestionsDiv.classList.remove('hidden');
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Keyboard navigation for search suggestions
    quickSearchInput.addEventListener('keydown', function(e) {
        const suggestions = suggestionsDiv.querySelectorAll('.cursor-pointer');
        const currentActive = suggestionsDiv.querySelector('.bg-blue-50');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentActive) {
                currentActive.classList.remove('bg-blue-50');
                const next = currentActive.nextElementSibling;
                if (next) {
                    next.classList.add('bg-blue-50');
                } else if (suggestions.length > 0) {
                    suggestions[0].classList.add('bg-blue-50');
                }
            } else if (suggestions.length > 0) {
                suggestions[0].classList.add('bg-blue-50');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentActive) {
                currentActive.classList.remove('bg-blue-50');
                const prev = currentActive.previousElementSibling;
                if (prev) {
                    prev.classList.add('bg-blue-50');
                } else if (suggestions.length > 0) {
                    suggestions[suggestions.length - 1].classList.add('bg-blue-50');
                }
            }
        } else if (e.key === 'Enter') {
            if (currentActive) {
                e.preventDefault();
                currentActive.click();
            }
        } else if (e.key === 'Escape') {
            suggestionsDiv.classList.add('hidden');
        }
    });
    
    // Analytics tracking
    document.querySelectorAll('a[href*="documents/view.php"]').forEach(link => {
        link.addEventListener('click', function() {
            // Track document views
            if (typeof gtag !== 'undefined') {
                gtag('event', 'view_document', {
                    'event_category': 'document',
                    'event_label': this.href
                });
            }
        });
    });
    
    document.querySelectorAll('a[href*="documents/download.php"]').forEach(link => {
        link.addEventListener('click', function() {
            // Track document downloads
            if (typeof gtag !== 'undefined') {
                gtag('event', 'download_document', {
                    'event_category': 'document',
                    'event_label': this.href
                });
            }
        });
    });
});
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php require_once '../includes/footer.php'; ?>