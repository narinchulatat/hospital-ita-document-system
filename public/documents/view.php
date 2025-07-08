<?php
$pageTitle = 'ดูเอกสาร';
require_once '../../includes/header.php';

$documentId = (int)($_GET['id'] ?? 0);

if (!$documentId) {
    redirectTo('/public/documents/');
}

try {
    $document = new Document();
    $category = new Category();
    
    // Get document details
    $doc = $document->getById($documentId);
    
    if (!$doc || !$doc['is_public'] || $doc['status'] !== DOC_STATUS_APPROVED) {
        header('HTTP/1.0 404 Not Found');
        die('ไม่พบเอกสารที่ต้องการ');
    }
    
    // Increment view count
    $document->incrementViewCount($documentId);
    
    // Get category breadcrumb
    $breadcrumb = $category->getBreadcrumb($doc['category_id']);
    
    // Get document versions if available
    $versions = $document->getVersions($documentId);
    
    // Get related documents from same category
    $relatedDocs = $document->getAll([
        'category_id' => $doc['category_id'],
        'is_public' => 1,
        'status' => DOC_STATUS_APPROVED
    ], 1, 5);
    
    // Remove current document from related
    $relatedDocs = array_filter($relatedDocs, function($d) use ($documentId) {
        return $d['id'] != $documentId;
    });
    
    $pageTitle = htmlspecialchars($doc['title']);
    
} catch (Exception $e) {
    error_log("Document view error: " . $e->getMessage());
    header('HTTP/1.0 500 Internal Server Error');
    die('เกิดข้อผิดพลาดในระบบ');
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="<?= BASE_URL ?>/public/" class="hover:text-blue-600">หน้าหลัก</a></li>
            <li><span class="mx-2">/</span><a href="<?= BASE_URL ?>/public/documents/" class="hover:text-blue-600">เอกสาร</a></li>
            <?php foreach ($breadcrumb as $crumb): ?>
            <li>
                <span class="mx-2">/</span>
                <a href="<?= BASE_URL ?>/public/documents/?category=<?= $crumb['id'] ?>" class="hover:text-blue-600">
                    <?= htmlspecialchars($crumb['name']) ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li><span class="mx-2">/</span><span class="text-gray-700">ดูเอกสาร</span></li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Document Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                                <?= htmlspecialchars($doc['title']) ?>
                            </h1>
                            
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
                                    <i class="fas fa-clock mr-2"></i>
                                    <?= formatThaiDate($doc['updated_at']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="ml-6 flex flex-col items-end space-y-2">
                            <span class="badge badge-success">อนุมัติแล้ว</span>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="<?= getFileTypeIcon($doc['file_type']) ?> mr-2"></i>
                                <span class="uppercase font-medium"><?= htmlspecialchars($doc['file_type']) ?></span>
                            </div>
                            <span class="text-xs text-gray-500"><?= formatFileSize($doc['file_size']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Document Info -->
                <div class="p-6">
                    <?php if ($doc['description']): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">คำอธิบาย</h3>
                        <div class="prose max-w-none text-gray-700">
                            <?= nl2br(htmlspecialchars($doc['description'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Document Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">รายละเอียดเอกสาร</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">เวอร์ชัน:</dt>
                                    <dd class="text-gray-900 font-medium"><?= htmlspecialchars($doc['version']) ?></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">ขนาดไฟล์:</dt>
                                    <dd class="text-gray-900"><?= formatFileSize($doc['file_size']) ?></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">ประเภทไฟล์:</dt>
                                    <dd class="text-gray-900 uppercase"><?= htmlspecialchars($doc['file_type']) ?></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">จำนวนการดู:</dt>
                                    <dd class="text-gray-900"><?= number_format($doc['view_count']) ?> ครั้ง</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">จำนวนดาวน์โหลด:</dt>
                                    <dd class="text-gray-900"><?= number_format($doc['download_count']) ?> ครั้ง</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">ข้อมูลการอนุมัติ</h4>
                            <dl class="space-y-2 text-sm">
                                <?php if ($doc['approved_by']): ?>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">ผู้อนุมัติ:</dt>
                                    <dd class="text-gray-900"><?= htmlspecialchars($doc['approver_first_name'] . ' ' . $doc['approver_last_name']) ?></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">วันที่อนุมัติ:</dt>
                                    <dd class="text-gray-900"><?= formatThaiDate($doc['approved_at']) ?></dd>
                                </div>
                                <?php if ($doc['approval_comment']): ?>
                                <div>
                                    <dt class="text-gray-500 mb-1">ความเห็น:</dt>
                                    <dd class="text-gray-900"><?= htmlspecialchars($doc['approval_comment']) ?></dd>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <!-- Fiscal Years and Quarters -->
                    <?php if (!empty($doc['fiscal_years']) || !empty($doc['quarters'])): ?>
                    <div class="border-t pt-6 mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">ปีงบประมาณและไตรมาสที่เกี่ยวข้อง</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($doc['fiscal_years'] as $fy): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <?= htmlspecialchars($fy['name']) ?>
                            </span>
                            <?php endforeach; ?>
                            
                            <?php foreach ($doc['quarters'] as $q): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-calendar mr-1"></i>
                                <?= htmlspecialchars($q['name']) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="border-t pt-6">
                        <div class="flex flex-wrap gap-3">
                            <a href="<?= BASE_URL ?>/public/documents/download.php?id=<?= $doc['id'] ?>" 
                               class="btn-primary" target="_blank">
                                <i class="fas fa-download mr-2"></i>ดาวน์โหลด
                            </a>
                            
                            <?php if (in_array(strtolower($doc['file_type']), ['pdf'])): ?>
                            <button onclick="previewDocument()" class="btn-secondary">
                                <i class="fas fa-eye mr-2"></i>ดูตัวอย่าง
                            </button>
                            <?php endif; ?>
                            
                            <button onclick="shareDocument()" class="btn-secondary">
                                <i class="fas fa-share mr-2"></i>แชร์
                            </button>
                            
                            <a href="<?= BASE_URL ?>/public/documents/" class="btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>กลับไปยังรายการ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Versions -->
            <?php if (!empty($versions) && count($versions) > 1): ?>
            <div class="bg-white shadow rounded-lg mt-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-history mr-2"></i>ประวัติเวอร์ชัน
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach ($versions as $version): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">เวอร์ชัน <?= htmlspecialchars($version['version']) ?></div>
                                <div class="text-sm text-gray-500">
                                    <?= formatThaiDate($version['created_at']) ?> โดย 
                                    <?= htmlspecialchars($version['first_name'] . ' ' . $version['last_name']) ?>
                                </div>
                                <?php if ($version['change_notes']): ?>
                                <div class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($version['change_notes']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?= formatFileSize($version['file_size']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Related Documents -->
            <?php if (!empty($relatedDocs)): ?>
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-file-alt mr-2"></i>เอกสารที่เกี่ยวข้อง
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach (array_slice($relatedDocs, 0, 4) as $relatedDoc): ?>
                        <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                            <h4 class="text-sm font-medium text-gray-900 mb-1">
                                <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $relatedDoc['id'] ?>" 
                                   class="hover:text-blue-600">
                                    <?= htmlspecialchars($relatedDoc['title']) ?>
                                </a>
                            </h4>
                            <div class="text-xs text-gray-500 space-x-2">
                                <span><?= formatThaiDate($relatedDoc['created_at']) ?></span>
                                <span>•</span>
                                <span><?= number_format($relatedDoc['view_count']) ?> ครั้ง</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t">
                        <a href="<?= BASE_URL ?>/public/documents/?category=<?= $doc['category_id'] ?>" 
                           class="block w-full text-center text-blue-600 hover:text-blue-500 text-sm">
                            ดูทั้งหมดในหมวดหมู่นี้ <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Links -->
            <div class="bg-white shadow rounded-lg mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-link mr-2"></i>ลิงก์ด่วน
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="<?= BASE_URL ?>/public/documents/" 
                           class="block text-blue-600 hover:text-blue-500 text-sm">
                            <i class="fas fa-list mr-2"></i>เอกสารทั้งหมด
                        </a>
                        <a href="<?= BASE_URL ?>/public/search.php" 
                           class="block text-blue-600 hover:text-blue-500 text-sm">
                            <i class="fas fa-search mr-2"></i>ค้นหาเอกสาร
                        </a>
                        <a href="<?= BASE_URL ?>/public/documents/?category=<?= $doc['category_id'] ?>" 
                           class="block text-blue-600 hover:text-blue-500 text-sm">
                            <i class="fas fa-folder mr-2"></i>เอกสารในหมวดหมู่เดียวกัน
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div id="previewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">ดูตัวอย่างเอกสาร</h3>
            <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="previewContent" class="bg-gray-50 rounded" style="height: 600px;">
            <!-- PDF preview will be loaded here -->
        </div>
    </div>
</div>

<script>
function previewDocument() {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    
    content.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">กำลังโหลดตัวอย่าง...</p>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Load PDF preview
    const pdfUrl = '<?= BASE_URL ?>/public/documents/download.php?id=<?= $doc['id'] ?>&preview=1';
    content.innerHTML = `<iframe src="${pdfUrl}" class="w-full h-full rounded" frameborder="0"></iframe>`;
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

function shareDocument() {
    const url = window.location.href;
    const title = '<?= htmlspecialchars($doc['title']) ?>';
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            window.HospitalApp.showSuccess('คัดลอกลิงก์แล้ว');
        });
    }
}

// Close modal on outside click
document.getElementById('previewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>