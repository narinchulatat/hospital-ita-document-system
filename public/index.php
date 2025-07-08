<?php
$pageTitle = 'หน้าหลัก - เอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';

try {
    $category = new Category();
    $document = new Document();
    
    // Get category tree for navigation
    $categoryTree = $category->getTree();
    
    // Get recent documents
    $recentDocuments = $document->getAll(['is_public' => 1, 'status' => DOC_STATUS_APPROVED], 1, 10);
    
    // Get statistics
    $stats = [
        'total_documents' => $document->getTotalCount(['is_public' => 1, 'status' => DOC_STATUS_APPROVED]),
        'total_categories' => count($category->getAll(true)),
        'total_downloads' => 0 // Will be calculated from database
    ];
    
    // Get total downloads
    $db = Database::getInstance();
    $downloadStats = $db->fetch("SELECT SUM(download_count) as total_downloads FROM documents WHERE is_public = 1 AND status = 'approved'");
    $stats['total_downloads'] = $downloadStats['total_downloads'] ?? 0;
    
} catch (Exception $e) {
    error_log("Public index error: " . $e->getMessage());
    $categoryTree = [];
    $recentDocuments = [];
    $stats = ['total_documents' => 0, 'total_categories' => 0, 'total_downloads' => 0];
}
?>

<!-- Hero Section -->
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
            <div class="mt-10 flex justify-center space-x-4">
                <a href="#documents" class="bg-white text-blue-600 hover:bg-blue-50 px-8 py-3 rounded-md text-lg font-medium">
                    <i class="fas fa-file-alt mr-2"></i>ดูเอกสาร
                </a>
                <a href="<?= BASE_URL ?>/public/search.php" class="border-2 border-white text-white hover:bg-white hover:text-blue-600 px-8 py-3 rounded-md text-lg font-medium">
                    <i class="fas fa-search mr-2"></i>ค้นหาเอกสาร
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="bg-white overflow-hidden shadow rounded-lg">
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

            <div class="bg-white overflow-hidden shadow rounded-lg">
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

            <div class="bg-white overflow-hidden shadow rounded-lg">
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
        </div>
    </div>
</div>

<!-- Main Content -->
<div id="documents" class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Categories Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg p-6">
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
                       class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                        <i class="fas fa-eye mr-2"></i>ดูเอกสารทั้งหมด
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Documents -->
        <div class="lg:col-span-3">
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
                    <p class="text-gray-500">ไม่มีเอกสาร</p>
                </div>
                <?php endif; ?>
            </div>
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