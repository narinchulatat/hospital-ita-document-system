<?php
$pageTitle = 'แดชบอร์ดเจ้าหน้าที่';
require_once '../includes/header.php';

// Require staff role
requireRole(ROLE_STAFF);

try {
    $document = new Document();
    $currentUserId = getCurrentUserId();
    
    // Get staff statistics
    $stats = [
        'my_documents' => $document->getTotalCount(['uploaded_by' => $currentUserId]),
        'pending_documents' => $document->getTotalCount(['uploaded_by' => $currentUserId, 'status' => DOC_STATUS_PENDING]),
        'approved_documents' => $document->getTotalCount(['uploaded_by' => $currentUserId, 'status' => DOC_STATUS_APPROVED]),
        'rejected_documents' => $document->getTotalCount(['uploaded_by' => $currentUserId, 'status' => DOC_STATUS_REJECTED])
    ];
    
    // Get recent documents
    $recentDocuments = $document->getAll(['uploaded_by' => $currentUserId], 1, 10);
    
    // Get pending documents
    $pendingDocuments = $document->getAll(['uploaded_by' => $currentUserId, 'status' => DOC_STATUS_PENDING], 1, 5);
    
} catch (Exception $e) {
    error_log("Staff dashboard error: " . $e->getMessage());
    $stats = array_fill_keys(['my_documents', 'pending_documents', 'approved_documents', 'rejected_documents'], 0);
    $recentDocuments = [];
    $pendingDocuments = [];
}
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <i class="fas fa-tachometer-alt mr-3"></i>แดชบอร์ดเจ้าหน้าที่
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                จัดการเอกสารและติดตามสถานะการอนุมัติ
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="<?= BASE_URL ?>/staff/documents/upload.php" 
               class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-upload mr-2"></i>อัปโหลดเอกสาร
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Documents -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-alt text-3xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">เอกสารทั้งหมด</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['my_documents']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/staff/documents/" class="font-medium text-blue-600 hover:text-blue-500">
                        จัดการเอกสาร <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

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
                    <a href="<?= BASE_URL ?>/staff/documents/?status=pending" class="font-medium text-yellow-600 hover:text-yellow-500">
                        ดูรายการ <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Approved Documents -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-3xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">อนุมัติแล้ว</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['approved_documents']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/staff/documents/?status=approved" class="font-medium text-green-600 hover:text-green-500">
                        ดูรายการ <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Rejected Documents -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-3xl text-red-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">ไม่อนุมัติ</dt>
                            <dd class="text-3xl font-bold text-gray-900"><?= number_format($stats['rejected_documents']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="<?= BASE_URL ?>/staff/documents/?status=rejected" class="font-medium text-red-600 hover:text-red-500">
                        ดูรายการ <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Documents -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-file-alt mr-2"></i>เอกสารล่าสุด
                </h3>
            </div>
            <div class="p-6">
                <?php if (!empty($recentDocuments)): ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($recentDocuments, 0, 5) as $doc): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">
                                    <a href="<?= BASE_URL ?>/staff/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </h4>
                                <p class="text-xs text-gray-500 mb-2">
                                    <?= formatThaiDate($doc['created_at']) ?>
                                </p>
                                <p class="text-xs text-gray-600">
                                    หมวดหมู่: <?= htmlspecialchars($doc['category_name']) ?>
                                </p>
                            </div>
                            <div class="ml-4 flex flex-col items-end space-y-1">
                                <?php
                                $statusClasses = [
                                    DOC_STATUS_DRAFT => 'bg-gray-100 text-gray-800',
                                    DOC_STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
                                    DOC_STATUS_APPROVED => 'bg-green-100 text-green-800',
                                    DOC_STATUS_REJECTED => 'bg-red-100 text-red-800'
                                ];
                                global $DOC_STATUS_NAMES;
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClasses[$doc['status']] ?>">
                                    <?= $DOC_STATUS_NAMES[$doc['status']] ?>
                                </span>
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="<?= getFileTypeIcon($doc['file_type']) ?> mr-1"></i>
                                    <?= strtoupper($doc['file_type']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="<?= BASE_URL ?>/staff/documents/" 
                       class="text-blue-600 hover:text-blue-500 text-sm">
                        ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 mb-4">ยังไม่มีเอกสาร</p>
                    <a href="<?= BASE_URL ?>/staff/documents/upload.php" class="btn-primary">
                        <i class="fas fa-upload mr-2"></i>อัปโหลดเอกสารแรก
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Documents -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-clock mr-2"></i>เอกสารรออนุมัติ
                </h3>
            </div>
            <div class="p-6">
                <?php if (!empty($pendingDocuments)): ?>
                <div class="space-y-4">
                    <?php foreach ($pendingDocuments as $doc): ?>
                    <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 mb-1">
                                    <a href="<?= BASE_URL ?>/staff/documents/view.php?id=<?= $doc['id'] ?>" 
                                       class="hover:text-blue-600">
                                        <?= htmlspecialchars($doc['title']) ?>
                                    </a>
                                </h4>
                                <p class="text-xs text-gray-500 mb-2">
                                    อัปโหลดเมื่อ: <?= formatThaiDate($doc['created_at']) ?>
                                </p>
                                <p class="text-xs text-gray-600">
                                    หมวดหมู่: <?= htmlspecialchars($doc['category_name']) ?>
                                </p>
                            </div>
                            <div class="ml-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    รออนุมัติ
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="<?= BASE_URL ?>/staff/documents/?status=pending" 
                       class="text-yellow-600 hover:text-yellow-500 text-sm">
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
                <a href="<?= BASE_URL ?>/staff/documents/upload.php" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors group">
                    <div class="flex-shrink-0">
                        <i class="fas fa-upload text-2xl text-gray-400 group-hover:text-blue-500"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-blue-600">อัปโหลดเอกสารใหม่</h4>
                        <p class="text-xs text-gray-500">เพิ่มเอกสารใหม่เข้าสู่ระบบ</p>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/staff/documents/" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors group">
                    <div class="flex-shrink-0">
                        <i class="fas fa-list text-2xl text-gray-400 group-hover:text-green-500"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-green-600">จัดการเอกสาร</h4>
                        <p class="text-xs text-gray-500">ดู แก้ไข และจัดการเอกสาร</p>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/public/documents/" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors group">
                    <div class="flex-shrink-0">
                        <i class="fas fa-eye text-2xl text-gray-400 group-hover:text-purple-500"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-purple-600">ดูเอกสารสาธารณะ</h4>
                        <p class="text-xs text-gray-500">เรียกดูเอกสารที่เผยแพร่แล้ว</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>