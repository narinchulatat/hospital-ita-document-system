<?php
$pageTitle = 'เอกสาร';
require_once '../../includes/header.php';

try {
    $document = new Document();
    $category = new Category();
    $db = Database::getInstance();
    
    // Get filter parameters
    $categoryId = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';
    $fiscalYear = $_GET['fiscal_year'] ?? '';
    $quarter = $_GET['quarter'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 20;
    
    // Build filters
    $filters = [
        'is_public' => 1,
        'status' => DOC_STATUS_APPROVED
    ];
    
    if ($categoryId) {
        $filters['category_id'] = $categoryId;
    }
    
    if ($search) {
        $filters['search'] = $search;
    }
    
    if ($fiscalYear) {
        $filters['fiscal_year_id'] = $fiscalYear;
    }
    
    if ($quarter) {
        $filters['quarter_id'] = $quarter;
    }
    
    // Get documents
    $documents = $document->getAll($filters, $page, $limit);
    $totalDocuments = $document->getTotalCount($filters);
    $totalPages = ceil($totalDocuments / $limit);
    
    // Get categories for filter
    $categories = $category->getAll(true);
    
    // Get fiscal years and quarters for filter
    $fiscalYears = $db->fetchAll("SELECT * FROM fiscal_years ORDER BY year DESC");
    $quarters = $db->fetchAll("SELECT q.*, fy.name as fiscal_year_name FROM quarters q JOIN fiscal_years fy ON q.fiscal_year_id = fy.id ORDER BY fy.year DESC, q.quarter");
    
    // Get current category for breadcrumb
    $currentCategory = $categoryId ? $category->getById($categoryId) : null;
    $breadcrumb = $currentCategory ? $category->getBreadcrumb($categoryId) : [];
    
} catch (Exception $e) {
    error_log("Public documents error: " . $e->getMessage());
    $documents = [];
    $totalDocuments = 0;
    $totalPages = 0;
    $categories = [];
    $fiscalYears = [];
    $quarters = [];
    $breadcrumb = [];
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-file-alt mr-3"></i>เอกสาร
        </h1>
        <p class="mt-2 text-gray-600">ค้นหาและดาวน์โหลดเอกสารต่างๆ ของโรงพยาบาล</p>
        
        <!-- Breadcrumb -->
        <?php if (!empty($breadcrumb)): ?>
        <nav class="mt-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="<?= BASE_URL ?>/public/documents/" class="hover:text-blue-600">เอกสาร</a></li>
                <?php foreach ($breadcrumb as $crumb): ?>
                <li>
                    <span class="mx-2">/</span>
                    <span class="text-gray-700"><?= htmlspecialchars($crumb['name']) ?></span>
                </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">ค้นหา</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="ชื่อเอกสาร, คำอธิบาย..."
                           class="form-input">
                </div>
                
                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">ทุกหมวดหมู่</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                            <?= str_repeat('— ', $cat['level'] - 1) ?><?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Fiscal Year -->
                <div>
                    <label for="fiscal_year" class="block text-sm font-medium text-gray-700 mb-1">ปีงบประมาณ</label>
                    <select id="fiscal_year" name="fiscal_year" class="form-select">
                        <option value="">ทุกปี</option>
                        <?php foreach ($fiscalYears as $fy): ?>
                        <option value="<?= $fy['id'] ?>" <?= $fiscalYear == $fy['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fy['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Quarter -->
                <div>
                    <label for="quarter" class="block text-sm font-medium text-gray-700 mb-1">ไตรมาส</label>
                    <select id="quarter" name="quarter" class="form-select">
                        <option value="">ทุกไตรมาส</option>
                        <?php foreach ($quarters as $q): ?>
                        <option value="<?= $q['id'] ?>" <?= $quarter == $q['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($q['fiscal_year_name']) ?> - <?= htmlspecialchars($q['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-between items-center pt-4 border-t">
                <div class="text-sm text-gray-500">
                    พบ <?= number_format($totalDocuments) ?> เอกสาร
                </div>
                <div class="space-x-3">
                    <a href="<?= BASE_URL ?>/public/documents/" class="btn-secondary">
                        <i class="fas fa-times mr-2"></i>ล้างตัวกรอง
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search mr-2"></i>ค้นหา
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Documents List -->
    <div class="space-y-6">
        <?php if (!empty($documents)): ?>
        <?php foreach ($documents as $doc): ?>
        <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                           class="hover:text-blue-600">
                            <?= htmlspecialchars($doc['title']) ?>
                        </a>
                    </h3>
                    
                    <?php if ($doc['description']): ?>
                    <p class="text-gray-600 mb-4 line-clamp-3">
                        <?= htmlspecialchars($doc['description']) ?>
                    </p>
                    <?php endif; ?>
                    
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <i class="fas fa-folder mr-2"></i>
                            <?= htmlspecialchars($doc['category_name']) ?>
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-user mr-2"></i>
                            <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?>
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-calendar mr-2"></i>
                            <?= formatThaiDate($doc['created_at']) ?>
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-eye mr-2"></i>
                            <?= number_format($doc['view_count']) ?> ครั้ง
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-download mr-2"></i>
                            <?= number_format($doc['download_count']) ?> ครั้ง
                        </span>
                    </div>
                </div>
                
                <div class="ml-6 flex flex-col items-end space-y-3">
                    <div class="flex items-center text-sm">
                        <i class="<?= getFileTypeIcon($doc['file_type']) ?> mr-2"></i>
                        <span class="uppercase font-medium"><?= htmlspecialchars($doc['file_type']) ?></span>
                    </div>
                    <span class="text-xs text-gray-500"><?= formatFileSize($doc['file_size']) ?></span>
                    <span class="badge badge-success">อนุมัติแล้ว</span>
                    
                    <div class="space-y-2">
                        <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                           class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            <i class="fas fa-eye mr-2"></i>ดูรายละเอียด
                        </a>
                        <a href="<?= BASE_URL ?>/public/documents/download.php?id=<?= $doc['id'] ?>" 
                           class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
                           target="_blank">
                            <i class="fas fa-download mr-2"></i>ดาวน์โหลด
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-8">
            <?= generatePagination($page, $totalPages, $_SERVER['REQUEST_URI']) ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-12 bg-white rounded-lg shadow">
            <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่พบเอกสาร</h3>
            <p class="text-gray-500 mb-6">ลองปรับเปลี่ยนตัวกรองหรือคำค้นหา</p>
            <a href="<?= BASE_URL ?>/public/documents/" class="btn-primary">
                <i class="fas fa-refresh mr-2"></i>เริ่มใหม่
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php require_once '../../includes/footer.php'; ?>