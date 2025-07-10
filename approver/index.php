<?php
$pageTitle = 'แดชบอร์ดผู้อนุมัติ';
require_once 'includes/header.php';

try {
    $document = new Document();
    $currentUserId = getCurrentUserId();
    
    // Get approver statistics
    $stats = [
        'pending_documents' => $document->getTotalCount(['status' => DOC_STATUS_PENDING]),
        'approved_by_me' => $document->getTotalCount(['approved_by' => $currentUserId, 'status' => DOC_STATUS_APPROVED]),
        'rejected_by_me' => $document->getTotalCount(['approved_by' => $currentUserId, 'status' => DOC_STATUS_REJECTED]),
        'total_approved' => $document->getTotalCount(['status' => DOC_STATUS_APPROVED])
    ];
    
    // Get pending documents for approval
    $pendingDocuments = $document->getPendingDocuments(1, 10);
    
    // Get recently approved/rejected documents by this user
    $recentActions = $document->getAll(['approved_by' => $currentUserId], 1, 5);
    
} catch (Exception $e) {
    error_log("Approver dashboard error: " . $e->getMessage());
    $stats = array_fill_keys(['pending_documents', 'approved_by_me', 'rejected_by_me', 'total_approved'], 0);
    $pendingDocuments = [];
    $recentActions = [];
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-check-circle mr-3"></i>แดชบอร์ดผู้อนุมัติ
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                อนุมัติและจัดการเอกสารในระบบ
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/approver/approval/" 
               class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-list mr-2"></i>รายการอนุมัติ
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Pending Documents -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-3xl text-yellow-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">รออนุมัติ</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['pending_documents']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/approver/approval/" class="font-medium text-yellow-600 hover:text-yellow-500">
                        ดำเนินการ <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Approved by Me -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-3xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ที่ฉันอนุมัติ</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['approved_by_me']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/approver/approval/?filter=approved_by_me" class="font-medium text-green-600 hover:text-green-500">
                        ดูรายการ <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Rejected by Me -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-3xl text-red-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ที่ฉันไม่อนุมัติ</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['rejected_by_me']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/approver/approval/?filter=rejected_by_me" class="font-medium text-red-600 hover:text-red-500">
                        ดูรายการ <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Approved -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-check text-3xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">อนุมัติทั้งหมด</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_approved']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/public/documents/" class="font-medium text-blue-600 hover:text-blue-500">
                        ดูเอกสาร <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Pending Approvals -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-hourglass-half mr-2"></i>เอกสารรออนุมัติ
                </h3>
            </div>
            <div class="p-6">
                <?php if (!empty($pendingDocuments)): ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($pendingDocuments, 0, 5) as $doc): ?>
                    <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">
                                    <a href="<?= BASE_URL ?>/approver/approval/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </h4>
                                
                                <div class="space-y-1 text-xs text-gray-600">
                                    <p>อัปโหลดโดย: <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?></p>
                                    <p>หมวดหมู่: <?= htmlspecialchars($doc['category_name']) ?></p>
                                    <p>วันที่: <?= formatThaiDate($doc['created_at']) ?></p>
                                </div>
                            </div>
                            
                            <div class="ml-4 flex flex-col space-y-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    รออนุมัติ
                                </span>
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="<?= getFileTypeIcon($doc['file_type']) ?> mr-1"></i>
                                    <?= strtoupper($doc['file_type']) ?>
                                </div>
                                <div class="flex space-x-1">
                                    <button onclick="quickApprove(<?= $doc['id'] ?>)" 
                                            class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700"
                                            title="อนุมัติ">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="quickReject(<?= $doc['id'] ?>)" 
                                            class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                                            title="ไม่อนุมัติ">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>/approver/approval/view.php?id=<?= $doc['id'] ?>" 
                                       class="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                                       title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="<?= BASE_URL ?>/approver/approval/" 
                       class="text-blue-600 hover:text-blue-500 text-sm">
                        ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-300 mb-4"></i>
                    <p class="text-gray-500">ไม่มีเอกสารรออนุมัติ</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Actions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-history mr-2"></i>การดำเนินการล่าสุด
                </h3>
            </div>
            <div class="p-6">
                <?php if (!empty($recentActions)): ?>
                <div class="space-y-4">
                    <?php foreach ($recentActions as $doc): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">
                                    <a href="<?= BASE_URL ?>/public/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </h4>
                                <p class="text-xs text-gray-500 mb-2">
                                    อัปโหลดโดย: <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?>
                                </p>
                                <p class="text-xs text-gray-600">
                                    <?= $doc['status'] === DOC_STATUS_APPROVED ? 'อนุมัติเมื่อ' : 'ไม่อนุมัติเมื่อ' ?>: 
                                    <?= formatThaiDate($doc['approved_at']) ?>
                                </p>
                                <?php if ($doc['approval_comment']): ?>
                                <p class="text-xs text-gray-600 mt-1 italic">
                                    "<?= htmlspecialchars($doc['approval_comment']) ?>"
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <?php if ($doc['status'] === DOC_STATUS_APPROVED): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    อนุมัติแล้ว
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ไม่อนุมัติ
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="<?= BASE_URL ?>/approver/approval/?filter=my_actions" 
                       class="text-blue-600 hover:text-blue-500 text-sm">
                        ดูประวัติทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">ยังไม่มีการดำเนินการ</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-bolt mr-2"></i>การดำเนินการด่วน
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="<?= BASE_URL ?>/approver/approval/" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-yellow-300 hover:bg-yellow-50 transition-colors group">
                    <div class="flex-shrink-0">
                        <i class="fas fa-hourglass-half text-2xl text-gray-400 group-hover:text-yellow-500"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-yellow-600">อนุมัติเอกสาร</h4>
                        <p class="text-xs text-gray-500">ดูและอนุมัติเอกสารรออนุมัติ</p>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/public/documents/" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors group">
                    <div class="flex-shrink-0">
                        <i class="fas fa-eye text-2xl text-gray-400 group-hover:text-blue-500"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-blue-600">ดูเอกสารสาธารณะ</h4>
                        <p class="text-xs text-gray-500">เรียกดูเอกสารที่เผยแพร่แล้ว</p>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/approver/approval/?filter=my_actions" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors group">
                    <div class="flex-shrink-0">
                        <i class="fas fa-history text-2xl text-gray-400 group-hover:text-green-500"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-green-600">ประวัติการอนุมัติ</h4>
                        <p class="text-xs text-gray-500">ดูประวัติการอนุมัติของฉัน</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Modals -->
<div id="quickApprovalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">อนุมัติเอกสาร</h3>
            <form id="quickApprovalForm">
                <input type="hidden" id="approvalDocumentId" name="document_id">
                <?= getCSRFTokenInput() ?>
                
                <div class="mb-4">
                    <label for="approvalComment" class="block text-sm font-medium text-gray-700 mb-2">ความเห็น (ไม่บังคับ)</label>
                    <textarea id="approvalComment" 
                              name="comment" 
                              rows="3" 
                              class="form-textarea"
                              placeholder="ระบุความเห็นเพิ่มเติม..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeQuickModal()" class="btn-secondary">
                        ยกเลิก
                    </button>
                    <button type="submit" class="btn-success">
                        <i class="fas fa-check mr-2"></i>อนุมัติ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="quickRejectionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">ไม่อนุมัติเอกสาร</h3>
            <form id="quickRejectionForm">
                <input type="hidden" id="rejectionDocumentId" name="document_id">
                <?= getCSRFTokenInput() ?>
                
                <div class="mb-4">
                    <label for="rejectionComment" class="block text-sm font-medium text-gray-700 mb-2">เหตุผล <span class="text-red-500">*</span></label>
                    <textarea id="rejectionComment" 
                              name="comment" 
                              rows="3" 
                              class="form-textarea"
                              placeholder="ระบุเหตุผลที่ไม่อนุมัติ..."
                              required></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeQuickModal()" class="btn-secondary">
                        ยกเลิก
                    </button>
                    <button type="submit" class="btn-danger">
                        <i class="fas fa-times mr-2"></i>ไม่อนุมัติ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function quickApprove(documentId) {
    document.getElementById('approvalDocumentId').value = documentId;
    document.getElementById('quickApprovalModal').classList.remove('hidden');
}

function quickReject(documentId) {
    document.getElementById('rejectionDocumentId').value = documentId;
    document.getElementById('quickRejectionModal').classList.remove('hidden');
}

function closeQuickModal() {
    document.getElementById('quickApprovalModal').classList.add('hidden');
    document.getElementById('quickRejectionModal').classList.add('hidden');
    
    // Reset forms
    document.getElementById('quickApprovalForm').reset();
    document.getElementById('quickRejectionForm').reset();
}

// Handle approval form submission
document.getElementById('quickApprovalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'approve');
    
    showLoading();
    
    fetch('<?= BASE_URL ?>/api/approval.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccess('อนุมัติเอกสารสำเร็จ');
            closeQuickModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showError(data.message || 'เกิดข้อผิดพลาด');
        }
    })
    .catch(error => {
        hideLoading();
        showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
});

// Handle rejection form submission
document.getElementById('quickRejectionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'reject');
    
    showLoading();
    
    fetch('<?= BASE_URL ?>/api/approval.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccess('ไม่อนุมัติเอกสารสำเร็จ');
            closeQuickModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showError(data.message || 'เกิดข้อผิดพลาด');
        }
    })
    .catch(error => {
        hideLoading();
        showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
});

// Close modals on outside click
document.getElementById('quickApprovalModal').addEventListener('click', function(e) {
    if (e.target === this) closeQuickModal();
});

document.getElementById('quickRejectionModal').addEventListener('click', function(e) {
    if (e.target === this) closeQuickModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>