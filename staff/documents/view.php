<?php
/**
 * Staff Document View Page
 * View document details and download
 */

$pageTitle = 'รายละเอียดเอกสาร';
require_once '../../includes/header.php';

// Require staff role
requireRole(ROLE_STAFF);

$error = '';
$success = '';
$document = null;
$approvalHistory = [];

// Get document ID
$documentId = (int)($_GET['id'] ?? 0);

if (!$documentId) {
    header('Location: ' . BASE_URL . '/staff/documents/');
    exit;
}

try {
    $documentObj = new Document();
    $currentUserId = getCurrentUserId();
    
    // Get document details
    $document = $documentObj->getById($documentId);
    
    // Check if document exists and belongs to current user
    if (!$document || $document['uploaded_by'] != $currentUserId) {
        header('Location: ' . BASE_URL . '/staff/documents/?error=not_found');
        exit;
    }
    
    // Get approval history
    $activityLog = new ActivityLog();
    $approvalHistory = $activityLog->getByTarget('document', $documentId, [ACTION_APPROVE, ACTION_REJECT]);
    
    // Check for success message
    if (isset($_GET['uploaded']) && $_GET['uploaded'] == '1') {
        $success = 'อัปโหลดเอกสารเรียบร้อยแล้ว';
    }
    
} catch (Exception $e) {
    error_log("Document view error: " . $e->getMessage());
    $error = 'ไม่สามารถโหลดข้อมูลเอกสารได้';
}

if (!$document) {
    header('Location: ' . BASE_URL . '/staff/documents/?error=not_found');
    exit;
}
?>

<div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="<?= BASE_URL ?>/staff/documents/" 
               class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-file-alt mr-3"></i>รายละเอียดเอกสาร
                </h1>
                <p class="text-gray-600 mt-1">ดูรายละเอียดและจัดการเอกสาร</p>
            </div>
            <div class="flex items-center space-x-3">
                <?php if ($document['status'] == DOC_STATUS_DRAFT || $document['status'] == DOC_STATUS_REJECTED): ?>
                <a href="<?= BASE_URL ?>/staff/documents/edit.php?id=<?= $documentId ?>" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    <i class="fas fa-edit mr-2"></i>แก้ไข
                </a>
                <?php endif; ?>
                
                <?php if (!empty($document['file_path']) && file_exists($document['file_path'])): ?>
                <a href="<?= BASE_URL ?>/api/download.php?id=<?= $documentId ?>" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-download mr-2"></i>ดาวน์โหลด
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">เกิดข้อผิดพลาด</h3>
                <div class="mt-2 text-sm text-red-700">
                    <?= $error ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">สำเร็จ</h3>
                <div class="mt-2 text-sm text-green-700">
                    <?= $success ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Document Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-info-circle mr-2"></i>ข้อมูลเอกสาร
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ชื่อเอกสาร</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($document['title']) ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">หมวดหมู่</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?= htmlspecialchars($document['category_name']) ?>
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">สถานะ</dt>
                            <dd class="mt-1">
                                <?php
                                $statusClasses = [
                                    DOC_STATUS_DRAFT => 'bg-gray-100 text-gray-800',
                                    DOC_STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
                                    DOC_STATUS_APPROVED => 'bg-green-100 text-green-800',
                                    DOC_STATUS_REJECTED => 'bg-red-100 text-red-800'
                                ];
                                global $DOC_STATUS_NAMES;
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClasses[$document['status']] ?>">
                                    <?= $DOC_STATUS_NAMES[$document['status']] ?>
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">วันที่อัปโหลด</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= formatThaiDate($document['created_at'], true) ?></dd>
                        </div>
                        
                        <?php if (!empty($document['updated_at']) && $document['updated_at'] != $document['created_at']): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">วันที่แก้ไขล่าสุด</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= formatThaiDate($document['updated_at'], true) ?></dd>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">เลขเอกสาร</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($document['document_number'] ?? '-') ?></dd>
                        </div>
                        
                        <?php if (!empty($document['description'])): ?>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">รายละเอียด</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($document['description'])) ?></dd>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($document['tags'])): ?>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">แท็ก</dt>
                            <dd class="mt-1">
                                <?php foreach (explode(',', $document['tags']) as $tag): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-1">
                                    #<?= htmlspecialchars(trim($tag)) ?>
                                </span>
                                <?php endforeach; ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- File Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-file mr-2"></i>ข้อมูลไฟล์
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                <i class="<?= getFileTypeIcon($document['file_type']) ?> text-2xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ชื่อไฟล์</dt>
                                    <dd class="text-sm text-gray-900"><?= htmlspecialchars($document['original_name'] ?? $document['file_name']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ขนาดไฟล์</dt>
                                    <dd class="text-sm text-gray-900"><?= formatFileSize($document['file_size']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ประเภทไฟล์</dt>
                                    <dd class="text-sm text-gray-900"><?= strtoupper($document['file_type']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">เวอร์ชัน</dt>
                                    <dd class="text-sm text-gray-900"><?= htmlspecialchars($document['version'] ?? '1.0') ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval History -->
            <?php if (!empty($approvalHistory)): ?>
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-history mr-2"></i>ประวัติการอนุมัติ
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <?php foreach ($approvalHistory as $index => $history): ?>
                            <li>
                                <div class="relative pb-8">
                                    <?php if ($index !== count($approvalHistory) - 1): ?>
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <?php endif; ?>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <?php if ($history['action'] === ACTION_APPROVE): ?>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-check text-white text-sm"></i>
                                            </span>
                                            <?php else: ?>
                                            <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-times text-white text-sm"></i>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <?= $history['action'] === ACTION_APPROVE ? 'อนุมัติ' : 'ไม่อนุมัติ' ?>
                                                    โดย <span class="font-medium text-gray-900"><?= htmlspecialchars($history['user_name']) ?></span>
                                                </p>
                                                <?php if (!empty($history['description'])): ?>
                                                <p class="text-sm text-gray-700 mt-1"><?= htmlspecialchars($history['description']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <?= formatThaiDate($history['created_at'], true) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-bolt mr-2"></i>การดำเนินการ
                    </h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <?php if (!empty($document['file_path']) && file_exists($document['file_path'])): ?>
                    <a href="<?= BASE_URL ?>/api/download.php?id=<?= $documentId ?>" 
                       target="_blank"
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-download mr-2"></i>ดาวน์โหลดไฟล์
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($document['status'] == DOC_STATUS_DRAFT || $document['status'] == DOC_STATUS_REJECTED): ?>
                    <a href="<?= BASE_URL ?>/staff/documents/edit.php?id=<?= $documentId ?>" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <i class="fas fa-edit mr-2"></i>แก้ไขเอกสาร
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($document['status'] == DOC_STATUS_DRAFT && getCurrentUserId() == $document['uploaded_by']): ?>
                    <button onclick="submitForApproval(<?= $documentId ?>)" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-paper-plane mr-2"></i>ส่งอนุมัติ
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($document['status'] == DOC_STATUS_DRAFT): ?>
                    <button onclick="confirmDelete(<?= $documentId ?>, '<?= htmlspecialchars($document['title']) ?>')" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-trash mr-2"></i>ลบเอกสาร
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Document Stats -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-chart-bar mr-2"></i>สถิติ
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">จำนวนการดาวน์โหลด</dt>
                            <dd class="text-sm text-gray-900"><?= number_format($document['download_count'] ?? 0) ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">จำนวนการเข้าชม</dt>
                            <dd class="text-sm text-gray-900"><?= number_format($document['view_count'] ?? 0) ?></dd>
                        </div>
                        <?php if (!empty($document['approved_at'])): ?>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">วันที่อนุมัติ</dt>
                            <dd class="text-sm text-gray-900"><?= formatThaiDate($document['approved_at']) ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Related Documents -->
            <?php
            try {
                $relatedDocuments = $documentObj->getAll([
                    'category_id' => $document['category_id'],
                    'status' => DOC_STATUS_APPROVED,
                    'exclude_id' => $documentId
                ], 1, 5);
            } catch (Exception $e) {
                $relatedDocuments = [];
            }
            ?>
            
            <?php if (!empty($relatedDocuments)): ?>
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-folder mr-2"></i>เอกสารที่เกี่ยวข้อง
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <?php foreach ($relatedDocuments as $related): ?>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <i class="<?= getFileTypeIcon($related['file_type']) ?> text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $related['id'] ?>" 
                                   class="text-sm font-medium text-blue-600 hover:text-blue-500 truncate block">
                                    <?= htmlspecialchars($related['title']) ?>
                                </a>
                                <p class="text-xs text-gray-500">
                                    <?= formatThaiDate($related['created_at']) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4">
                        <a href="<?= BASE_URL ?>/public/documents/?category=<?= $document['category_id'] ?>" 
                           class="text-sm text-blue-600 hover:text-blue-500">
                            ดูเอกสารทั้งหมดในหมวดหมู่นี้ <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900">ยืนยันการลบเอกสาร</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    คุณแน่ใจหรือไม่ที่จะลบเอกสาร "<span id="deleteDocTitle"></span>"?
                    การกระทำนี้ไม่สามารถย้อนกลับได้
                </p>
            </div>
            <div class="flex justify-center space-x-4 px-4 py-3">
                <button id="confirmDelete" 
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    ลบเอกสาร
                </button>
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteDocumentId = null;

function confirmDelete(id, title) {
    deleteDocumentId = id;
    document.getElementById('deleteDocTitle').textContent = title;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    deleteDocumentId = null;
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteDocumentId) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/staff/documents/delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteDocumentId;
        form.appendChild(idInput);
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = '<?= generateCSRFToken() ?>';
        form.appendChild(tokenInput);
        
        document.body.appendChild(form);
        form.submit();
    }
});

function submitForApproval(id) {
    if (confirm('คุณต้องการส่งเอกสารนี้เข้าสู่กระบวนการอนุมัติหรือไม่?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/staff/documents/edit.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(idInput);
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = '<?= DOC_STATUS_PENDING ?>';
        form.appendChild(statusInput);
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'submit_approval';
        form.appendChild(actionInput);
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = '<?= generateCSRFToken() ?>';
        form.appendChild(tokenInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>