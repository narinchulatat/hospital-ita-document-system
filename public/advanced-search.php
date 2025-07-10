<?php
$pageTitle = 'ค้นหาขั้นสูง - ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';

try {
    $category = new Category();
    $document = new Document();
    
    // Get all categories for filter
    $categories = $category->getAll(true);
    
    // Get search parameters
    $keyword = trim($_GET['keyword'] ?? '');
    $title = trim($_GET['title'] ?? '');
    $description = trim($_GET['description'] ?? '');
    $categoryId = intval($_GET['category_id'] ?? 0);
    $fileType = $_GET['file_type'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $sortBy = $_GET['sort'] ?? 'created_at';
    $sortOrder = $_GET['order'] ?? 'desc';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 20;
    
    $searchPerformed = !empty($keyword) || !empty($title) || !empty($description) || 
                      $categoryId > 0 || !empty($fileType) || !empty($dateFrom) || !empty($dateTo);
    
    $documents = [];
    $totalDocuments = 0;
    $totalPages = 0;
    
    if ($searchPerformed) {
        // Build search conditions
        $conditions = ['is_public' => 1, 'status' => 'approved'];
        
        if (!empty($keyword)) {
            $conditions['search'] = $keyword;
        }
        if (!empty($title)) {
            $conditions['title_search'] = $title;
        }
        if (!empty($description)) {
            $conditions['description_search'] = $description;
        }
        if ($categoryId > 0) {
            $conditions['category_id'] = $categoryId;
        }
        if (!empty($fileType)) {
            $conditions['file_type'] = $fileType;
        }
        if (!empty($dateFrom)) {
            $conditions['date_from'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $conditions['date_to'] = $dateTo;
        }
        
        $documents = $document->getAll($conditions, $page, $limit, $sortBy, $sortOrder);
        $totalDocuments = $document->getTotalCount($conditions);
        $totalPages = ceil($totalDocuments / $limit);
    }
    
} catch (Exception $e) {
    error_log("Advanced search error: " . $e->getMessage());
    $categories = [];
    $documents = [];
    $totalDocuments = 0;
    $totalPages = 0;
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
                        <span class="text-gray-900 font-medium">ค้นหาขั้นสูง</span>
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
            <i class="fas fa-search-plus text-blue-600 mr-3"></i>ค้นหาขั้นสูง
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            ค้นหาเอกสารด้วยตัวกรองหลายเงื่อนไข เพื่อให้ได้ผลลัพธ์ที่ตรงกับความต้องการมากที่สุด
        </p>
    </div>

    <!-- Advanced Search Form -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <form method="GET" class="space-y-6">
            <!-- Basic Search -->
            <div>
                <label for="keyword" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-2"></i>คำค้นหาทั่วไป
                </label>
                <input type="text" id="keyword" name="keyword" value="<?= htmlspecialchars($keyword) ?>"
                       placeholder="ค้นหาในชื่อ คำอธิบาย และเนื้อหาเอกสาร..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">สามารถใช้เครื่องหมาย " " เพื่อค้นหาวลีที่ตรงกันทุกคำ</p>
            </div>

            <!-- Specific Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heading mr-2"></i>ชื่อเอกสาร
                    </label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>"
                           placeholder="ค้นหาในชื่อเอกสารเท่านั้น..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-2"></i>คำอธิบาย
                    </label>
                    <input type="text" id="description" name="description" value="<?= htmlspecialchars($description) ?>"
                           placeholder="ค้นหาในคำอธิบายเอกสาร..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-folder mr-2"></i>หมวดหมู่
                    </label>
                    <select id="category_id" name="category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">ทุกหมวดหมู่</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?> (<?= $cat['documents_count'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="file_type" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file mr-2"></i>ประเภทไฟล์
                    </label>
                    <select id="file_type" name="file_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">ทุกประเภท</option>
                        <option value="pdf" <?= $fileType === 'pdf' ? 'selected' : '' ?>>PDF</option>
                        <option value="doc" <?= $fileType === 'doc' ? 'selected' : '' ?>>Word (DOC)</option>
                        <option value="docx" <?= $fileType === 'docx' ? 'selected' : '' ?>>Word (DOCX)</option>
                        <option value="xls" <?= $fileType === 'xls' ? 'selected' : '' ?>>Excel (XLS)</option>
                        <option value="xlsx" <?= $fileType === 'xlsx' ? 'selected' : '' ?>>Excel (XLSX)</option>
                        <option value="jpg" <?= $fileType === 'jpg' ? 'selected' : '' ?>>รูปภาพ (JPG)</option>
                        <option value="png" <?= $fileType === 'png' ? 'selected' : '' ?>>รูปภาพ (PNG)</option>
                    </select>
                </div>
                
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-2"></i>เรียงตาม
                    </label>
                    <select id="sort" name="sort"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="created_at-desc" <?= $sortBy === 'created_at' && $sortOrder === 'desc' ? 'selected' : '' ?>>วันที่สร้าง (ใหม่สุด)</option>
                        <option value="created_at-asc" <?= $sortBy === 'created_at' && $sortOrder === 'asc' ? 'selected' : '' ?>>วันที่สร้าง (เก่าสุด)</option>
                        <option value="updated_at-desc" <?= $sortBy === 'updated_at' && $sortOrder === 'desc' ? 'selected' : '' ?>>วันที่อัปเดต (ใหม่สุด)</option>
                        <option value="title-asc" <?= $sortBy === 'title' && $sortOrder === 'asc' ? 'selected' : '' ?>>ชื่อ (ก-ฮ)</option>
                        <option value="title-desc" <?= $sortBy === 'title' && $sortOrder === 'desc' ? 'selected' : '' ?>>ชื่อ (ฮ-ก)</option>
                        <option value="download_count-desc" <?= $sortBy === 'download_count' && $sortOrder === 'desc' ? 'selected' : '' ?>>ยอดนิยม</option>
                        <option value="view_count-desc" <?= $sortBy === 'view_count' && $sortOrder === 'desc' ? 'selected' : '' ?>>ดูมากที่สุด</option>
                    </select>
                </div>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-2"></i>ช่วงวันที่สร้าง
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="date_from" class="block text-xs text-gray-500 mb-1">จากวันที่</label>
                        <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="date_to" class="block text-xs text-gray-500 mb-1">ถึงวันที่</label>
                        <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-center pt-6 border-t border-gray-200">
                <div class="flex gap-2">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-search mr-2"></i>ค้นหา
                    </button>
                    
                    <a href="<?= BASE_URL ?>/public/advanced-search.php" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-eraser mr-2"></i>ล้างค่า
                    </a>
                </div>
                
                <div class="text-sm text-gray-500">
                    <i class="fas fa-lightbulb mr-1"></i>
                    เคล็ดลับ: ใช้เครื่องหมาย * เพื่อค้นหาแบบไวด์การ์ด
                </div>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    <?php if ($searchPerformed): ?>
        <div class="bg-white shadow rounded-lg">
            <!-- Results Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            ผลการค้นหา
                        </h3>
                        <p class="text-sm text-gray-600">
                            พบ <?= number_format($totalDocuments) ?> เอกสาร
                            <?php if ($totalPages > 1): ?>
                                (หน้า <?= $page ?> จาก <?= $totalPages ?>)
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <?php if (!empty($documents)): ?>
                        <div class="flex items-center space-x-2">
                            <button onclick="exportResults()" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-download mr-2"></i>ส่งออก
                            </button>
                            
                            <button onclick="printResults()" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-print mr-2"></i>พิมพ์
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Results List -->
            <div class="divide-y divide-gray-200">
                <?php if (!empty($documents)): ?>
                    <?php foreach ($documents as $doc): ?>
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">
                                        <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                                           class="hover:text-blue-600 transition-colors">
                                            <?= highlightSearchTerms(htmlspecialchars($doc['title']), $keyword) ?>
                                        </a>
                                    </h4>
                                    
                                    <?php if ($doc['description']): ?>
                                        <p class="text-gray-600 mb-3 line-clamp-2">
                                            <?= highlightSearchTerms(htmlspecialchars(substr($doc['description'], 0, 200)), $keyword) ?>
                                            <?= strlen($doc['description']) > 200 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <span>
                                            <i class="fas fa-folder mr-1"></i>
                                            <?= htmlspecialchars($doc['category_name'] ?? 'ไม่ระบุ') ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?= formatThaiDate($doc['created_at']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-download mr-1"></i>
                                            <?= number_format($doc['download_count']) ?> ครั้ง
                                        </span>
                                        <span>
                                            <i class="fas fa-eye mr-1"></i>
                                            <?= number_format($doc['view_count']) ?> ครั้ง
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="ml-6 flex flex-col items-end space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= strtoupper($doc['file_type']) ?>
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            <?= formatFileSize($doc['file_size']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                                           class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                            <i class="fas fa-eye mr-1"></i>ดู
                                        </a>
                                        <a href="<?= BASE_URL ?>/public/documents/download.php?id=<?= $doc['id'] ?>" 
                                           class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700"
                                           target="_blank">
                                            <i class="fas fa-download mr-1"></i>ดาวน์โหลด
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">ไม่พบเอกสารที่ตรงกับเงื่อนไขการค้นหา</h3>
                        <p class="text-gray-500 mb-6">ลองปรับเปลี่ยนเงื่อนไขการค้นหา หรือใช้คำค้นหาอื่น</p>
                        <div class="space-y-2 text-sm text-gray-600">
                            <p>💡 เคล็ดลับการค้นหา:</p>
                            <ul class="space-y-1 text-left max-w-md mx-auto">
                                <li>• ลองใช้คำที่สั้นกว่า หรือกว้างกว่า</li>
                                <li>• ตรวจสอบการสะกดคำ</li>
                                <li>• ลองค้นหาในหมวดหมู่อื่น</li>
                                <li>• ใช้การค้นหาทั่วไปแทนการค้นหาเฉพาะเจาะจง</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            แสดง <?= (($page - 1) * $limit) + 1 ?> ถึง <?= min($page * $limit, $totalDocuments) ?> 
                            จาก <?= number_format($totalDocuments) ?> รายการ
                        </div>
                        
                        <div class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                                   class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                                   class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            for ($i = $startPage; $i <= $endPage; $i++): 
                            ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                   class="px-3 py-2 text-sm <?= $i === $page ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-700' ?> rounded">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                                   class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>" 
                                   class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Search Tips -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>เคล็ดลับการค้นหาขั้นสูง
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-quote-left text-blue-500 mr-2"></i>การค้นหาแบบแม่นยำ
                    </h4>
                    <p class="text-sm text-gray-600">ใช้เครื่องหมาย " " ครอบคำที่ต้องการค้นหาแบบตรงกันทุกคำ เช่น "นโยบายโรงพยาบาล"</p>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-asterisk text-green-500 mr-2"></i>การค้นหาแบบไวด์การ์ด
                    </h4>
                    <p class="text-sm text-gray-600">ใช้เครื่องหมาย * แทนตัวอักษรที่ไม่แน่ใจ เช่น "การ*" จะค้นหา "การพยาบาล", "การดูแล"</p>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-filter text-purple-500 mr-2"></i>การใช้ตัวกรอง
                    </h4>
                    <p class="text-sm text-gray-600">ใช้ตัวกรองหมวดหมู่และประเภทไฟล์เพื่อจำกัดขอบเขตการค้นหา</p>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-calendar-alt text-orange-500 mr-2"></i>การค้นหาตามวันที่
                    </h4>
                    <p class="text-sm text-gray-600">ระบุช่วงวันที่เพื่อค้นหาเอกสารที่สร้างในช่วงเวลาที่ต้องการ</p>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-sort text-red-500 mr-2"></i>การเรียงลำดับ
                    </h4>
                    <p class="text-sm text-gray-600">เลือกการเรียงลำดับที่เหมาะสม เช่น ตามความนิยม หรือวันที่อัปเดต</p>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-search text-blue-500 mr-2"></i>การค้นหาเฉพาะส่วน
                    </h4>
                    <p class="text-sm text-gray-600">ใช้ช่องค้นหาชื่อเอกสารและคำอธิบายแยกกันเพื่อความแม่นยำ</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle sort selection change
    document.getElementById('sort').addEventListener('change', function() {
        const value = this.value;
        const [sortBy, sortOrder] = value.split('-');
        
        // Update hidden form inputs if needed
        const form = this.closest('form');
        let sortInput = form.querySelector('input[name="sort"]');
        let orderInput = form.querySelector('input[name="order"]');
        
        if (!sortInput) {
            sortInput = document.createElement('input');
            sortInput.type = 'hidden';
            sortInput.name = 'sort';
            form.appendChild(sortInput);
        }
        
        if (!orderInput) {
            orderInput = document.createElement('input');
            orderInput.type = 'hidden';
            orderInput.name = 'order';
            form.appendChild(orderInput);
        }
        
        sortInput.value = sortBy;
        orderInput.value = sortOrder;
    });
    
    // Auto-submit on filter changes (optional)
    document.querySelectorAll('select[name="category_id"], select[name="file_type"]').forEach(select => {
        select.addEventListener('change', function() {
            // Optional: auto-submit form when filters change
            // this.closest('form').submit();
        });
    });
});

function exportResults() {
    // Generate CSV export of search results
    const results = <?= json_encode($documents) ?>;
    if (results.length === 0) return;
    
    let csv = 'ชื่อเอกสาร,หมวดหมู่,ประเภทไฟล์,วันที่สร้าง,จำนวนดาวน์โหลด,จำนวนการดู\n';
    results.forEach(doc => {
        csv += `"${doc.title}","${doc.category_name || ''}","${doc.file_type}","${doc.created_at}","${doc.download_count}","${doc.view_count}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'search_results.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printResults() {
    window.print();
}

<?php 
// Function to highlight search terms
function highlightSearchTerms($text, $searchTerm) {
    if (empty($searchTerm)) return $text;
    
    $searchTerm = preg_quote($searchTerm, '/');
    return preg_replace('/(' . $searchTerm . ')/ui', '<mark class="bg-yellow-200">$1</mark>', $text);
}
?>
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>