<?php
$pageTitle = 'ดูรายละเอียดเอกสาร';
require_once '../includes/header.php';

// Get document ID
$documentId = intval($_GET['id'] ?? 0);

if (!$documentId) {
    header('Location: ' . BASE_URL . '/approver/documents/');
    exit;
}

try {
    $document = new Document();
    $doc = $document->getById($documentId);
    
    if (!$doc) {
        throw new Exception('ไม่พบเอกสารที่ระบุ');
    }
    
    // Increment view count
    $document->incrementViewCount($documentId);
    
    // Get approval history
    $approvalHistory = [];
    if (in_array($doc['status'], [DOC_STATUS_APPROVED, DOC_STATUS_REJECTED])) {
        $db = Database::getInstance();
        $approvalHistory = $db->fetchAll(
            "SELECT al.*, u.first_name, u.last_name 
             FROM approval_logs al 
             JOIN users u ON al.approver_id = u.id 
             WHERE al.document_id = ? 
             ORDER BY al.created_at DESC",
            [$documentId]
        );
    }
    
    // Check if current user can approve this document
    $canApprove = $doc['status'] === DOC_STATUS_PENDING && canApproveDocuments();
    
    // Calculate file info
    $fileInfo = [
        'size' => formatFileSize($doc['file_size']),
        'icon' => getFileTypeIcon($doc['file_type']),
        'class' => getFileTypeClass($doc['file_type']),
        'download_url' => BASE_URL . '/public/download.php?id=' . $documentId
    ];
    
    $pageTitle = 'รายละเอียดเอกสาร: ' . $doc['title'];
    
} catch (Exception $e) {
    error_log("Document view error: " . $e->getMessage());
    $error = $e->getMessage();
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
                    <a href="<?= BASE_URL ?>/approver/documents/" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>กลับไปรายการเอกสาร
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
        ['title' => 'เอกสาร', 'url' => '/approver/documents/'],
        ['title' => $doc['title']]
    ]) ?>

    <!-- Document Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="file-icon <?= $fileInfo['class'] ?> text-2xl">
                            <i class="<?= $fileInfo['icon'] ?>"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                <?= htmlspecialchars($doc['title']) ?>
                            </h1>
                            <div class="flex items-center space-x-4 text-sm text-gray-500 mt-1">
                                <span><i class="fas fa-folder mr-1"></i><?= htmlspecialchars($doc['category_name']) ?></span>
                                <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?></span>
                                <span><i class="fas fa-calendar mr-1"></i><?= formatThaiDate($doc['created_at']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <!-- Status Badge -->
                    <?php
                    $statusClass = match($doc['status']) {
                        DOC_STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
                        DOC_STATUS_APPROVED => 'bg-green-100 text-green-800',
                        DOC_STATUS_REJECTED => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $statusClass ?>">
                        <?= $DOC_STATUS_NAMES[$doc['status']] ?? $doc['status'] ?>
                    </span>
                    
                    <!-- Action Buttons -->
                    <?php if ($canApprove): ?>
                    <button onclick="approverPanel.showApprovalModal(<?= $documentId ?>, 'approve')" 
                            class="btn-success">
                        <i class="fas fa-check mr-2"></i>อนุมัติ
                    </button>
                    <button onclick="approverPanel.showApprovalModal(<?= $documentId ?>, 'reject')" 
                            class="btn-danger">
                        <i class="fas fa-times mr-2"></i>ไม่อนุมัติ
                    </button>
                    <?php endif; ?>
                    
                    <a href="<?= $fileInfo['download_url'] ?>" 
                       class="btn-primary" 
                       target="_blank">
                        <i class="fas fa-download mr-2"></i>ดาวน์โหลด
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Document Stats -->
        <div class="px-6 py-4 bg-gray-50">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-gray-900"><?= $fileInfo['size'] ?></div>
                    <div class="text-sm text-gray-500">ขนาดไฟล์</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900"><?= strtoupper($doc['file_type']) ?></div>
                    <div class="text-sm text-gray-500">ประเภทไฟล์</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900"><?= number_format($doc['view_count']) ?></div>
                    <div class="text-sm text-gray-500">จำนวนการดู</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900"><?= number_format($doc['download_count']) ?></div>
                    <div class="text-sm text-gray-500">จำนวนดาวน์โหลด</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <?php if ($doc['description']): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-align-left mr-2"></i>คำอธิบาย
                    </h3>
                </div>
                <div class="card-body">
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($doc['description'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- File Preview -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-eye mr-2"></i>ตัวอย่างเอกสาร
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (in_array(strtolower($doc['file_type']), ['pdf'])): ?>
                    <div class="w-full h-96 border border-gray-300 rounded-lg overflow-hidden">
                        <iframe src="<?= $fileInfo['download_url'] ?>#toolbar=0" 
                                class="w-full h-full" 
                                frameborder="0">
                        </iframe>
                    </div>
                    <?php elseif (in_array(strtolower($doc['file_type']), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <div class="text-center">
                        <img src="<?= $fileInfo['download_url'] ?>" 
                             alt="<?= htmlspecialchars($doc['title']) ?>"
                             class="max-w-full h-auto rounded-lg shadow-lg mx-auto">
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <div class="file-icon <?= $fileInfo['class'] ?> text-6xl mx-auto mb-4">
                            <i class="<?= $fileInfo['icon'] ?>"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">
                            <?= htmlspecialchars($doc['title']) ?>
                        </h4>
                        <p class="text-gray-500 mb-4">
                            ไฟล์ประเภท <?= strtoupper($doc['file_type']) ?> ขนาด <?= $fileInfo['size'] ?>
                        </p>
                        <a href="<?= $fileInfo['download_url'] ?>" 
                           class="btn-primary" 
                           target="_blank">
                            <i class="fas fa-download mr-2"></i>ดาวน์โหลดเพื่อดู
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Approval History -->
            <?php if (!empty($approvalHistory)): ?>
            <div class="card" id="approval-history">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-history mr-2"></i>ประวัติการอนุมัติ
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
                                    <div class="bg-gray-50 rounded-md p-3">
                                        <div class="font-medium text-gray-700 mb-1">ความเห็น:</div>
                                        <?= nl2br(htmlspecialchars($history['comments'])) ?>
                                    </div>
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
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Document Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-info-circle mr-2"></i>ข้อมูลเอกสาร
                    </h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">หมวดหมู่</dt>
                            <dd class="text-sm text-gray-900"><?= htmlspecialchars($doc['category_name']) ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ผู้อัปโหลด</dt>
                            <dd class="text-sm text-gray-900">
                                <?= htmlspecialchars($doc['uploader_first_name'] . ' ' . $doc['uploader_last_name']) ?>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">วันที่อัปโหลด</dt>
                            <dd class="text-sm text-gray-900"><?= formatThaiDate($doc['created_at'], true) ?></dd>
                        </div>
                        
                        <?php if ($doc['updated_at'] && $doc['updated_at'] !== $doc['created_at']): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">แก้ไขล่าสุด</dt>
                            <dd class="text-sm text-gray-900"><?= formatThaiDate($doc['updated_at'], true) ?></dd>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (in_array($doc['status'], [DOC_STATUS_APPROVED, DOC_STATUS_REJECTED])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ผู้อนุมัติ</dt>
                            <dd class="text-sm text-gray-900">
                                <?= htmlspecialchars($doc['approver_first_name'] . ' ' . $doc['approver_last_name']) ?>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">วันที่อนุมัติ</dt>
                            <dd class="text-sm text-gray-900"><?= formatThaiDate($doc['approved_at'], true) ?></dd>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ประเภทไฟล์</dt>
                            <dd class="text-sm text-gray-900"><?= strtoupper($doc['file_type']) ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ขนาดไฟล์</dt>
                            <dd class="text-sm text-gray-900"><?= $fileInfo['size'] ?></dd>
                        </div>
                        
                        <?php if ($doc['version']): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">เวอร์ชัน</dt>
                            <dd class="text-sm text-gray-900"><?= htmlspecialchars($doc['version']) ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-bolt mr-2"></i>การดำเนินการ
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <a href="<?= $fileInfo['download_url'] ?>" 
                           class="w-full btn-primary text-center" 
                           target="_blank">
                            <i class="fas fa-download mr-2"></i>ดาวน์โหลดเอกสาร
                        </a>
                        
                        <?php if ($canApprove): ?>
                        <button onclick="approverPanel.showApprovalModal(<?= $documentId ?>, 'approve')" 
                                class="w-full btn-success">
                            <i class="fas fa-check mr-2"></i>อนุมัติเอกสาร
                        </button>
                        <button onclick="approverPanel.showApprovalModal(<?= $documentId ?>, 'reject')" 
                                class="w-full btn-danger">
                            <i class="fas fa-times mr-2"></i>ไม่อนุมัติเอกสาร
                        </button>
                        <?php endif; ?>
                        
                        <button onclick="window.print()" class="w-full btn-secondary">
                            <i class="fas fa-print mr-2"></i>พิมพ์หน้านี้
                        </button>
                        
                        <a href="<?= BASE_URL ?>/approver/documents/" class="w-full btn-light text-center">
                            <i class="fas fa-arrow-left mr-2"></i>กลับไปรายการ
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Document Stats -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-chart-bar mr-2"></i>สถิติ
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">จำนวนการดู</span>
                            <span class="text-sm font-medium text-gray-900"><?= number_format($doc['view_count']) ?> ครั้ง</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">จำนวนดาวน์โหลด</span>
                            <span class="text-sm font-medium text-gray-900"><?= number_format($doc['download_count']) ?> ครั้ง</span>
                        </div>
                        <?php if ($doc['status'] === DOC_STATUS_PENDING): ?>
                        <?php 
                        $daysPending = floor((time() - strtotime($doc['created_at'])) / 86400);
                        ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">รออนุมัติ</span>
                            <span class="text-sm font-medium <?= $daysPending > 7 ? 'text-red-600' : 'text-gray-900' ?>">
                                <?= $daysPending ?> วัน
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize approver panel if not already done
    if (typeof window.approverPanel === 'undefined') {
        window.approverPanel = new ApproverPanel();
    }
    
    // Smooth scroll to approval history if hash is present
    if (window.location.hash === '#approval-history') {
        setTimeout(function() {
            document.getElementById('approval-history').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }, 100);
    }
});
</script>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>