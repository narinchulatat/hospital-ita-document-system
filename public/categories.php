<?php
$pageTitle = 'หมวดหมู่เอกสาร - ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';

try {
    $category = new Category();
    $document = new Document();
    
    // Get all categories with document counts
    $categories = $category->getAll(true);
    
    // Sort categories by name (Thai alphabetical order)
    usort($categories, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    // Get category tree for hierarchical display
    $categoryTree = $category->getTree();
    
    // Get search parameter
    $search = trim($_GET['search'] ?? '');
    $sortBy = $_GET['sort'] ?? 'name';
    $sortOrder = $_GET['order'] ?? 'asc';
    
    // Filter categories if search is provided
    if (!empty($search)) {
        $categories = array_filter($categories, function($cat) use ($search) {
            return stripos($cat['name'], $search) !== false || 
                   stripos($cat['description'] ?? '', $search) !== false;
        });
    }
    
    // Sort categories
    if ($sortBy === 'count') {
        usort($categories, function($a, $b) use ($sortOrder) {
            $result = $a['documents_count'] - $b['documents_count'];
            return $sortOrder === 'desc' ? -$result : $result;
        });
    } elseif ($sortBy === 'name') {
        usort($categories, function($a, $b) use ($sortOrder) {
            $result = strcmp($a['name'], $b['name']);
            return $sortOrder === 'desc' ? -$result : $result;
        });
    }
    
} catch (Exception $e) {
    error_log("Categories page error: " . $e->getMessage());
    $categories = [];
    $categoryTree = [];
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
                        <span class="text-gray-900 font-medium">หมวดหมู่เอกสาร</span>
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
            <i class="fas fa-folder-tree text-blue-600 mr-3"></i>หมวดหมู่เอกสาร
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            เรียกดูเอกสารตามหมวดหมู่ต่างๆ เพื่อให้การค้นหาเป็นไปอย่างสะดวกและมีประสิทธิภาพ
        </p>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex-1 max-w-md">
                <form method="GET" class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                           placeholder="ค้นหาหมวดหมู่..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <?php if (!empty($search)): ?>
                        <a href="<?= BASE_URL ?>/public/categories.php" 
                           class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">เรียงตาม:</span>
                <select onchange="updateSort(this.value)" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="name-asc" <?= $sortBy === 'name' && $sortOrder === 'asc' ? 'selected' : '' ?>>ชื่อ (ก-ฮ)</option>
                    <option value="name-desc" <?= $sortBy === 'name' && $sortOrder === 'desc' ? 'selected' : '' ?>>ชื่อ (ฮ-ก)</option>
                    <option value="count-desc" <?= $sortBy === 'count' && $sortOrder === 'desc' ? 'selected' : '' ?>>จำนวนเอกสาร (มาก-น้อย)</option>
                    <option value="count-asc" <?= $sortBy === 'count' && $sortOrder === 'asc' ? 'selected' : '' ?>>จำนวนเอกสาร (น้อย-มาก)</option>
                </select>
            </div>
        </div>
        
        <?php if (!empty($search)): ?>
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    พบ <?= count($categories) ?> หมวดหมู่ที่ตรงกับ "<?= htmlspecialchars($search) ?>"
                </p>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Category Tree Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg p-6 sticky top-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-sitemap text-green-600 mr-2"></i>โครงสร้างหมวดหมู่
                </h3>
                
                <?php if (!empty($categoryTree)): ?>
                    <div class="space-y-2">
                        <?php foreach ($categoryTree as $parentCategory): ?>
                            <div class="category-tree-item">
                                <div class="flex items-center py-2 px-3 rounded hover:bg-gray-50 cursor-pointer category-tree-toggle" 
                                     data-category-id="<?= $parentCategory['id'] ?>">
                                    <?php if (!empty($parentCategory['children'])): ?>
                                        <i class="fas fa-chevron-right text-gray-400 mr-2 transition-transform tree-icon"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                                    <?php endif; ?>
                                    <span class="text-sm font-medium text-gray-700 flex-1">
                                        <?= htmlspecialchars($parentCategory['name']) ?>
                                    </span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                        <?= $parentCategory['documents_count'] ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($parentCategory['children'])): ?>
                                    <div class="ml-6 space-y-1 category-children hidden">
                                        <?php foreach ($parentCategory['children'] as $childCategory): ?>
                                            <div class="category-tree-item">
                                                <div class="flex items-center py-1 px-3 rounded hover:bg-gray-50 cursor-pointer category-tree-toggle"
                                                     data-category-id="<?= $childCategory['id'] ?>">
                                                    <?php if (!empty($childCategory['children'])): ?>
                                                        <i class="fas fa-chevron-right text-gray-400 mr-2 transition-transform tree-icon"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                                                    <?php endif; ?>
                                                    <span class="text-sm text-gray-600 flex-1">
                                                        <?= htmlspecialchars($childCategory['name']) ?>
                                                    </span>
                                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                        <?= $childCategory['documents_count'] ?>
                                                    </span>
                                                </div>
                                                
                                                <?php if (!empty($childCategory['children'])): ?>
                                                    <div class="ml-6 space-y-1 category-children hidden">
                                                        <?php foreach ($childCategory['children'] as $grandChild): ?>
                                                            <div class="flex items-center py-1 px-3 rounded hover:bg-gray-50 cursor-pointer category-tree-toggle"
                                                                 data-category-id="<?= $grandChild['id'] ?>">
                                                                <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                                                                <span class="text-sm text-gray-600 flex-1">
                                                                    <?= htmlspecialchars($grandChild['name']) ?>
                                                                </span>
                                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                                    <?= $grandChild['documents_count'] ?>
                                                                </span>
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
            </div>
        </div>

        <!-- Category Grid -->
        <div class="lg:col-span-3">
            <?php if (!empty($categories)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($categories as $category): ?>
                        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                            <a href="<?= BASE_URL ?>/public/documents/?category=<?= $category['id'] ?>" 
                                               class="hover:text-blue-600 transition-colors">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </a>
                                        </h3>
                                        
                                        <?php if (!empty($category['description'])): ?>
                                            <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                                                <?= htmlspecialchars($category['description']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="ml-4 flex-shrink-0">
                                        <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <?= number_format($category['documents_count']) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-file-alt mr-2"></i>
                                        <?= $category['documents_count'] ?> เอกสาร
                                    </div>
                                    
                                    <a href="<?= BASE_URL ?>/public/documents/?category=<?= $category['id'] ?>" 
                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        ดูเอกสาร
                                    </a>
                                </div>
                                
                                <?php if ($category['parent_id']): ?>
                                    <div class="mt-3 pt-3 border-t border-gray-100">
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-folder mr-1"></i>
                                            หมวดหมู่ย่อย
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Category Statistics -->
                <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">
                        <i class="fas fa-chart-pie text-blue-600 mr-2"></i>สรุปหมวดหมู่เอกสาร
                    </h3>
                    
                    <?php 
                    $totalCategories = count($categories);
                    $totalDocuments = array_sum(array_column($categories, 'documents_count'));
                    $avgDocuments = $totalCategories > 0 ? $totalDocuments / $totalCategories : 0;
                    $maxDocuments = !empty($categories) ? max(array_column($categories, 'documents_count')) : 0;
                    $mostPopularCategory = !empty($categories) ? 
                        array_reduce($categories, function($max, $cat) {
                            return $cat['documents_count'] > ($max['documents_count'] ?? 0) ? $cat : $max;
                        }) : null;
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-2xl font-bold text-blue-600 mb-1">
                                <?= number_format($totalCategories) ?>
                            </div>
                            <div class="text-gray-600 text-sm">หมวดหมู่ทั้งหมด</div>
                        </div>
                        
                        <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-2xl font-bold text-green-600 mb-1">
                                <?= number_format($totalDocuments) ?>
                            </div>
                            <div class="text-gray-600 text-sm">เอกสารทั้งหมด</div>
                        </div>
                        
                        <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-2xl font-bold text-purple-600 mb-1">
                                <?= number_format($avgDocuments, 1) ?>
                            </div>
                            <div class="text-gray-600 text-sm">เฉลี่ยต่อหมวดหมู่</div>
                        </div>
                        
                        <div class="text-center bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-2xl font-bold text-orange-600 mb-1">
                                <?= number_format($maxDocuments) ?>
                            </div>
                            <div class="text-gray-600 text-sm">สูงสุดในหมวดหมู่</div>
                        </div>
                    </div>
                    
                    <?php if ($mostPopularCategory): ?>
                        <div class="mt-6 text-center">
                            <p class="text-gray-700">
                                หมวดหมู่ที่มีเอกสารมากที่สุด: 
                                <a href="<?= BASE_URL ?>/public/documents/?category=<?= $mostPopularCategory['id'] ?>" 
                                   class="font-semibold text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($mostPopularCategory['name']) ?>
                                </a>
                                (<?= number_format($mostPopularCategory['documents_count']) ?> เอกสาร)
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="max-w-md mx-auto">
                        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            <?= !empty($search) ? 'ไม่พบหมวดหมู่ที่ตรงกับการค้นหา' : 'ไม่มีหมวดหมู่' ?>
                        </h3>
                        <p class="text-gray-500 mb-6">
                            <?= !empty($search) ? 'ลองค้นหาด้วยคำอื่น หรือตรวจสอบการสะกดคำ' : 'ยังไม่มีหมวดหมู่เอกสาร' ?>
                        </p>
                        <?php if (!empty($search)): ?>
                            <a href="<?= BASE_URL ?>/public/categories.php" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-arrow-left mr-2"></i>ดูหมวดหมู่ทั้งหมด
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category tree toggle functionality
    document.querySelectorAll('.category-tree-toggle').forEach(function(element) {
        element.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const children = this.parentElement.querySelector('.category-children');
            const icon = this.querySelector('.tree-icon');
            
            if (children) {
                children.classList.toggle('hidden');
                icon.style.transform = children.classList.contains('hidden') ? 
                    'rotate(0deg)' : 'rotate(90deg)';
            } else {
                // Navigate to category documents
                window.location.href = `<?= BASE_URL ?>/public/documents/?category=${categoryId}`;
            }
        });
    });
    
    // Search form auto-submit on enter
    document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.closest('form').submit();
        }
    });
});

function updateSort(value) {
    const [sort, order] = value.split('-');
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    url.searchParams.set('order', order);
    window.location.href = url.toString();
}
</script>

<style>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php require_once '../includes/footer.php'; ?>