<?php
$pageTitle = 'ค้นหาเอกสาร';
require_once '../includes/header.php';

$search = $_GET['search'] ?? '';
$results = [];
$totalResults = 0;

if ($search) {
    try {
        $category = new Category();
        $results = $category->search($search);
        $totalResults = count($results);
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        $results = [];
        $totalResults = 0;
    }
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
            <i class="fas fa-search mr-3"></i>ค้นหาเอกสาร
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            ค้นหาเอกสารและหมวดหมู่ต่างๆ ของโรงพยาบาล
        </p>
    </div>

    <!-- Search Form -->
    <div class="bg-white shadow rounded-lg p-8 mb-8">
        <form method="GET" class="max-w-4xl mx-auto">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">ค้นหา</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="<?= htmlspecialchars($search) ?>"
                               placeholder="ป้อนคำค้นหา เช่น ชื่อเอกสาร, คำอธิบาย, หมวดหมู่..."
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-lg">
                    </div>
                </div>
                <button type="submit" 
                        class="inline-flex items-center px-8 py-3 border border-transparent text-lg font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-search mr-2"></i>ค้นหา
                </button>
            </div>
        </form>

        <!-- Search Tips -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-900 mb-3">เทคนิคการค้นหา:</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                <div class="flex items-start">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                    <div>
                        <strong>คำค้นหาทั่วไป:</strong><br>
                        ใช้คำสำคัญในชื่อเอกสารหรือคำอธิบาย
                    </div>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-folder text-blue-500 mr-2 mt-0.5"></i>
                    <div>
                        <strong>ค้นหาหมวดหมู่:</strong><br>
                        ค้นหาชื่อหมวดหมู่เพื่อดูเอกสารในหมวดนั้น
                    </div>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-quote-left text-green-500 mr-2 mt-0.5"></i>
                    <div>
                        <strong>คำค้นหาที่แม่นยำ:</strong><br>
                        ใส่เครื่องหมายอัญประกาศ "คำค้นหา"
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <?php if ($search): ?>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            ผลการค้นหา
            <?php if ($totalResults > 0): ?>
            <span class="text-blue-600">(<?= number_format($totalResults) ?> รายการ)</span>
            <?php endif; ?>
        </h2>
        <p class="text-gray-600 mt-1">
            คำค้นหา: <strong>"<?= htmlspecialchars($search) ?>"</strong>
        </p>
    </div>

    <?php if ($totalResults > 0): ?>
    <div class="space-y-6">
        <?php foreach ($results as $result): ?>
        <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <?php if ($result['type'] === 'category'): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">
                            <i class="fas fa-folder mr-1"></i>หมวดหมู่
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                            <i class="fas fa-file-alt mr-1"></i>เอกสาร
                        </span>
                        <?php endif; ?>
                        
                        <?php if ($result['type'] === 'document' && $result['file_type']): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="<?= getFileTypeIcon($result['file_type']) ?> mr-1"></i>
                            <?= strtoupper($result['file_type']) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        <?php if ($result['type'] === 'category'): ?>
                        <a href="<?= BASE_URL ?>/public/documents/?category=<?= $result['id'] ?>" 
                           class="hover:text-blue-600">
                            <?= htmlspecialchars($result['title']) ?>
                        </a>
                        <?php else: ?>
                        <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $result['id'] ?>" 
                           class="hover:text-blue-600">
                            <?= htmlspecialchars($result['title']) ?>
                        </a>
                        <?php endif; ?>
                    </h3>

                    <?php if ($result['description']): ?>
                    <p class="text-gray-600 mb-3 line-clamp-2">
                        <?= htmlspecialchars(substr($result['description'], 0, 200)) ?>
                        <?= strlen($result['description']) > 200 ? '...' : '' ?>
                    </p>
                    <?php endif; ?>

                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                        <span class="flex items-center">
                            <i class="fas fa-calendar mr-2"></i>
                            <?= formatThaiDate($result['created_at']) ?>
                        </span>
                        
                        <?php if ($result['type'] === 'category'): ?>
                        <span class="flex items-center">
                            <i class="fas fa-folder mr-2"></i>
                            หมวดหมู่
                        </span>
                        <?php else: ?>
                        <span class="flex items-center">
                            <i class="fas fa-file-alt mr-2"></i>
                            เอกสาร
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ml-6 flex flex-col space-y-2">
                    <?php if ($result['type'] === 'category'): ?>
                    <a href="<?= BASE_URL ?>/public/documents/?category=<?= $result['id'] ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-eye mr-2"></i>ดูเอกสาร
                    </a>
                    <?php else: ?>
                    <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $result['id'] ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-eye mr-2"></i>ดูรายละเอียด
                    </a>
                    <a href="<?= BASE_URL ?>/public/documents/download.php?id=<?= $result['id'] ?>" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                       target="_blank">
                        <i class="fas fa-download mr-2"></i>ดาวน์โหลด
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <!-- No Results -->
    <div class="text-center py-12 bg-white rounded-lg shadow">
        <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-medium text-gray-900 mb-2">ไม่พบผลการค้นหา</h3>
        <p class="text-gray-500 mb-6 max-w-md mx-auto">
            ไม่พบเอกสารหรือหมวดหมู่ที่ตรงกับ "<strong><?= htmlspecialchars($search) ?></strong>"
        </p>
        
        <div class="space-y-3 max-w-md mx-auto">
            <p class="text-sm text-gray-600 text-left">คำแนะนำ:</p>
            <ul class="text-sm text-gray-600 text-left space-y-1">
                <li>• ตรวจสอบการสะกดคำ</li>
                <li>• ลองใช้คำค้นหาที่สั้นกว่า</li>
                <li>• ลองค้นหาด้วยคำเฉพาะ</li>
                <li>• ค้นหาด้วยหมวดหมู่แทน</li>
            </ul>
        </div>

        <div class="mt-8">
            <a href="<?= BASE_URL ?>/public/documents/" class="btn-primary">
                <i class="fas fa-list mr-2"></i>ดูเอกสารทั้งหมด
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Popular Categories -->
    <div class="bg-white shadow rounded-lg p-8">
        <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">
            <i class="fas fa-star mr-2"></i>หมวดหมู่ยอดนิยม
        </h3>
        
        <?php
        try {
            $category = new Category();
            $popularCategories = $category->getAll(true);
            // Sort by document count (mock - in real app, you'd track popularity)
            usort($popularCategories, function($a, $b) {
                return $b['documents_count'] <=> $a['documents_count'];
            });
            $popularCategories = array_slice($popularCategories, 0, 6);
        } catch (Exception $e) {
            $popularCategories = [];
        }
        ?>
        
        <?php if (!empty($popularCategories)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($popularCategories as $cat): ?>
            <a href="<?= BASE_URL ?>/public/documents/?category=<?= $cat['id'] ?>" 
               class="group block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900 group-hover:text-blue-600">
                        <?= htmlspecialchars($cat['name']) ?>
                    </h4>
                    <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
                        <?= number_format($cat['documents_count']) ?>
                    </span>
                </div>
                <?php if ($cat['description']): ?>
                <p class="text-sm text-gray-600 line-clamp-2">
                    <?= htmlspecialchars($cat['description']) ?>
                </p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-center text-gray-500">ไม่มีหมวดหมู่</p>
        <?php endif; ?>
        
        <div class="mt-8 text-center">
            <a href="<?= BASE_URL ?>/public/documents/" class="btn-primary">
                <i class="fas fa-folder-open mr-2"></i>ดูหมวดหมู่ทั้งหมด
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Focus on search input
    document.getElementById('search').focus();
    
    // Highlight search terms in results (simple implementation)
    const searchTerm = '<?= htmlspecialchars($search) ?>';
    if (searchTerm) {
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        document.querySelectorAll('h3 a, p').forEach(element => {
            if (element.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                element.innerHTML = element.innerHTML.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>