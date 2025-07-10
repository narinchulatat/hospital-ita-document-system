<?php
$pageTitle = 'หน้าอนุมัติเอกสาร';
require_once '../includes/header.php';

// Get document ID
$documentId = intval($_GET['id'] ?? 0);

if (!$documentId) {
    header('Location: ' . BASE_URL . '/approver/documents/pending.php');
    exit;
}

try {
    $document = new Document();
    $doc = $document->getById($documentId);
    
    if (!$doc) {
        throw new Exception('ไม่พบเอกสารที่ระบุ');
    }
    
    if ($doc['status'] !== DOC_STATUS_PENDING) {
        throw new Exception('เอกสารนี้ได้รับการอนุมัติแล้ว');
    }
    
    // Get approval history for this document
    $db = Database::getInstance();
    $approvalHistory = $db->fetchAll(
        "SELECT al.*, u.first_name, u.last_name 
         FROM approval_logs al 
         JOIN users u ON al.approver_id = u.id 
         WHERE al.document_id = ? 
         ORDER BY al.created_at DESC",
        [$documentId]
    );
    
    $pageTitle = 'อนุมัติเอกสาร: ' . $doc['title'];
    
} catch (Exception $e) {
    error_log("Approval page error: " . $e->getMessage());
    $error = $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('โทเค็นความปลอดภัยไม่ถูกต้อง');
        }
        
        $action = $_POST['action'] ?? '';
        $comment = trim($_POST['comment'] ?? '');
        $currentUserId = getCurrentUserId();
        
        if (!in_array($action, ['approve', 'reject'])) {
            throw new Exception('การดำเนินการไม่ถูกต้อง');
        }
        
        if ($action === 'reject' && empty($comment)) {
            throw new Exception('กรุณาระบุเหตุผลในการไม่อนุมัติ');
        }
        
        $db->beginTransaction();
        
        // Update document status
        if ($action === 'approve') {
            $document->approve($documentId, $currentUserId, $comment);
            $message = 'อนุมัติเอกสารเรียบร้อยแล้ว';
        } else {
            $document->reject($documentId, $currentUserId, $comment);
            $message = 'ไม่อนุมัติเอกสารเรียบร้อยแล้ว';
        }
        
        // Log approval activity
        $logData = [
            'document_id' => $documentId,
            'approver_id' => $currentUserId,
            'action' => $action,
            'comments' => $comment,
            'previous_status' => DOC_STATUS_PENDING,
            'new_status' => $action === 'approve' ? DOC_STATUS_APPROVED : DOC_STATUS_REJECTED
        ];
        $db->insert('approval_logs', $logData);
        
        // Send notification to uploader
        $uploaderNotification = [
            'user_id' => $doc['uploaded_by'],
            'title' => $action === 'approve' ? 'เอกสารได้รับการอนุมัติ' : 'เอกสารไม่ได้รับการอนุมัติ',
            'message' => 'เอกสาร "' . $doc['title'] . '" ' . ($action === 'approve' ? 'ได้รับการอนุมัติ' : 'ไม่ได้รับการอนุมัติ') . ' โดย ' . $currentUser['first_name'] . ' ' . $currentUser['last_name'],
            'type' => $action === 'approve' ? 'document_approved' : 'document_rejected',
            'action_url' => '/public/documents/view.php?id=' . $documentId
        ];
        $db->insert('notifications', $uploaderNotification);
        
        // Log activity
        logActivity($action === 'approve' ? ACTION_APPROVE : ACTION_REJECT, 'documents', $documentId);
        
        $db->commit();
        
        // Redirect with success message
        header('Location: ' . BASE_URL . '/approver/documents/view.php?id=' . $documentId . '&success=' . urlencode($message));
        exit;
        
    } catch (Exception $e) {
        $db->rollback();
        $error = $e->getMessage();
    }
}
?>

<?php if (isset($error)): ?>
<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">เกิดข้อผิดพลาด</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/approver/documents/pending.php" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>กลับไปรายการรออนุมัติ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <?= generateBreadcrumbs([
        ['title' => 'หน้าหลัก', 'url' => '/approver/'],
        ['title' => 'เอกสารรออนุมัติ', 'url' => '/approver/documents/pending.php'],
        ['title' => 'อนุมัติเอกสาร']
    ]) ?>

    <!-- Page Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-clipboard-check mr-3 text-blue-600"></i>อนุมัติเอกสาร
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        ตรวจสอบและดำเนินการอนุมัติเอกสาร
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $documentId ?>" 
                       class="btn-secondary">
                        <i class="fas fa-eye mr-2"></i>ดูรายละเอียด
                    </a>
                    <a href="<?= BASE_URL ?>/approver/documents/pending.php" 
                       class="btn-light">
                        <i class="fas fa-arrow-left mr-2"></i>กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Document Preview -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-file-alt mr-2"></i>ข้อมูลเอกสาร
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Document Info -->
                    <div class="border-b border-gray-200 pb-6 mb-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="file-icon <?= getFileTypeClass($doc['file_type']) ?> text-3xl">
                                    <i class="<?= getFileTypeIcon($doc['file_type']) ?>"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-gray-900 mb-2">
                                    <?= htmlspecialchars($doc['title']) ?>
                                </h2>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-500">หมวดหมู่:</span>
                                        <span class="text-gray-900"><?= htmlspecialchars($doc['category_name']) ?></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-500">ผู้อัปโหลด:</span>
                                        <span class="text-gray-900"><?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-500">วันที่อัปโหลด:</span>
                                        <span class="text-gray-900"><?= formatThaiDate($doc['created_at'], true) ?></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-500">ขนาดไฟล์:</span>
                                        <span class="text-gray-900"><?= formatFileSize($doc['file_size']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-hourglass-half mr-1"></i>รออนุมัติ
                                    </span>
                                    <?php
                                    $daysPending = floor((time() - strtotime($doc['created_at'])) / 86400);
                                    if ($daysPending > 7):
                                    ?>
                                    <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>เกินกำหนด (<?= $daysPending ?> วัน)
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <?php if ($doc['description']): ?>
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-3">คำอธิบาย</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($doc['description']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- File Preview/Download -->
                    <div class="text-center">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8">
                            <div class="file-icon <?= getFileTypeClass($doc['file_type']) ?> text-6xl mx-auto mb-4">
                                <i class="<?= getFileTypeIcon($doc['file_type']) ?>"></i>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">
                                <?= htmlspecialchars($doc['file_name']) ?>
                            </h4>
                            <p class="text-gray-500 mb-4">
                                ไฟล์ <?= strtoupper($doc['file_type']) ?> • <?= formatFileSize($doc['file_size']) ?>
                            </p>
                            <div class="space-x-2">
                                <a href="<?= BASE_URL ?>/public/download.php?id=<?= $documentId ?>" 
                                   class="btn-primary" 
                                   target="_blank">
                                    <i class="fas fa-download mr-2"></i>ดาวน์โหลดเพื่อตรวจสอบ
                                </a>
                                <a href="<?= BASE_URL ?>/approver/documents/view.php?id=<?= $documentId ?>" 
                                   class="btn-secondary"
                                   target="_blank">
                                    <i class="fas fa-external-link-alt mr-2"></i>เปิดในหน้าใหม่
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Previous Comments/History -->
            <?php if (!empty($approvalHistory)): ?>
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-history mr-2"></i>ประวัติการดำเนินการ
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        <?php foreach ($approvalHistory as $history): ?>
                        <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg">
                            <div class="flex-shrink-0 mt-1">
                                <?php if ($history['action'] === 'approve'): ?>
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-green-600"></i>
                                </div>
                                <?php else: ?>
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-times text-red-600"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900">
                                        <?= $history['action'] === 'approve' ? 'อนุมัติโดย' : 'ไม่อนุมัติโดย' ?>: 
                                        <?= htmlspecialchars($history['first_name'] . ' ' . $history['last_name']) ?>
                                    </h4>
                                    <time class="text-sm text-gray-500">
                                        <?= formatThaiDate($history['created_at'], true) ?>
                                    </time>
                                </div>
                                <?php if ($history['comments']): ?>
                                <div class="mt-2 text-sm text-gray-600">
                                    <?= nl2br(htmlspecialchars($history['comments'])) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Approval Form -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-clipboard-check mr-2"></i>ดำเนินการอนุมัติ
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" id="approvalForm">
                        <?= getCSRFTokenInput() ?>
                        
                        <!-- Action Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">เลือกการดำเนินการ</label>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-50 transition-colors">
                                    <input type="radio" name="action" value="approve" class="form-radio text-green-600" required>
                                    <div class="ml-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                            <span class="text-sm font-medium text-gray-900">อนุมัติเอกสาร</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">เอกสารจะเผยแพร่ในระบบและสามารถดาวน์โหลดได้</p>
                                    </div>
                                </label>
                                
                                <label class="flex items-center p-3 border-2 border-red-200 rounded-lg cursor-pointer hover:bg-red-50 transition-colors">
                                    <input type="radio" name="action" value="reject" class="form-radio text-red-600" required>
                                    <div class="ml-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-times-circle text-red-600 mr-2"></i>
                                            <span class="text-sm font-medium text-gray-900">ไม่อนุมัติเอกสาร</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">เอกสารจะไม่เผยแพร่และส่งกลับไปให้ผู้อัปโหลด</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Comments -->
                        <div class="mb-6">
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                ความเห็น/หมายเหตุ
                                <span id="commentRequired" class="text-red-500 hidden">*</span>
                            </label>
                            <textarea id="comment" 
                                      name="comment" 
                                      rows="5" 
                                      class="form-textarea"
                                      placeholder="ระบุความเห็น หมายเหตุ หรือเหตุผล (บังคับสำหรับการไม่อนุมัติ)"></textarea>
                            <p id="commentHelp" class="mt-2 text-sm text-gray-500">
                                ความเห็นนี้จะส่งไปยังผู้อัปโหลดเอกสาร
                            </p>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <button type="submit" 
                                    id="submitBtn"
                                    class="w-full btn-primary"
                                    disabled>
                                <i class="fas fa-paper-plane mr-2"></i>
                                <span id="submitText">เลือกการดำเนินการ</span>
                            </button>
                            
                            <a href="<?= BASE_URL ?>/approver/documents/pending.php" 
                               class="w-full btn-secondary text-center">
                                <i class="fas fa-times mr-2"></i>ยกเลิก
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Info -->
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-info-circle mr-2"></i>ข้อมูลเพิ่มเติม
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">รหัสเอกสาร:</span>
                            <span class="text-gray-900">#<?= str_pad($documentId, 6, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">จำนวนการดู:</span>
                            <span class="text-gray-900"><?= number_format($doc['view_count']) ?> ครั้ง</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">ระยะเวลารอ:</span>
                            <span class="text-gray-900 <?= $daysPending > 7 ? 'text-red-600 font-medium' : '' ?>">
                                <?= $daysPending ?> วัน
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const form = $('#approvalForm');
    const actionRadios = $('input[name="action"]');
    const commentField = $('#comment');
    const commentRequired = $('#commentRequired');
    const commentHelp = $('#commentHelp');
    const submitBtn = $('#submitBtn');
    const submitText = $('#submitText');
    
    // Handle action selection changes
    actionRadios.change(function() {
        const selectedAction = $(this).val();
        
        if (selectedAction === 'approve') {
            commentRequired.addClass('hidden');
            commentField.removeAttr('required');
            commentHelp.text('ความเห็นนี้จะส่งไปยังผู้อัปโหลดเอกสาร (ไม่บังคับ)');
            submitBtn.removeClass('btn-primary').addClass('btn-success');
            submitText.html('<i class="fas fa-check mr-2"></i>อนุมัติเอกสาร');
            submitBtn.prop('disabled', false);
            
        } else if (selectedAction === 'reject') {
            commentRequired.removeClass('hidden');
            commentField.attr('required', 'required');
            commentHelp.text('กรุณาระบุเหตุผลในการไม่อนุมัติ (บังคับ)');
            submitBtn.removeClass('btn-primary').addClass('btn-danger');
            submitText.html('<i class="fas fa-times mr-2"></i>ไม่อนุมัติเอกสาร');
            
            // Check if comment is filled for reject action
            const commentValue = commentField.val().trim();
            submitBtn.prop('disabled', commentValue === '');
        }
    });
    
    // Handle comment field changes for reject action
    commentField.on('input', function() {
        const selectedAction = $('input[name="action"]:checked').val();
        if (selectedAction === 'reject') {
            const commentValue = $(this).val().trim();
            submitBtn.prop('disabled', commentValue === '');
        }
    });
    
    // Form submission confirmation
    form.submit(function(e) {
        e.preventDefault();
        
        const selectedAction = $('input[name="action"]:checked').val();
        const comment = commentField.val().trim();
        
        if (selectedAction === 'reject' && !comment) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณาระบุเหตุผล',
                text: 'คุณต้องระบุเหตุผลในการไม่อนุมัติเอกสาร'
            });
            return;
        }
        
        const actionText = selectedAction === 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ';
        const documentTitle = '<?= addslashes($doc['title']) ?>';
        
        Swal.fire({
            title: 'ยืนยันการ' + actionText,
            html: `คุณต้องการ<strong>${actionText}</strong>เอกสาร<br>"${documentTitle}" ใช่หรือไม่?`,
            icon: selectedAction === 'approve' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: actionText,
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: selectedAction === 'approve' ? '#10b981' : '#ef4444'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                form.off('submit').submit();
            }
        });
    });
});
</script>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>