<?php
/**
 * Staff Document Edit Page
 * Edit document details and upload new file
 */

$pageTitle = 'แก้ไขเอกสาร';
require_once '../../includes/header.php';

// Require staff role
requireRole(ROLE_STAFF);

$error = '';
$success = '';
$document = null;

// Get document ID
$documentId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if (!$documentId) {
    header('Location: ' . BASE_URL . '/staff/documents/');
    exit;
}

try {
    $documentObj = new Document();
    $category = new Category();
    $fileManager = new FileManager();
    $currentUserId = getCurrentUserId();
    
    // Get document details
    $document = $documentObj->getById($documentId);
    
    // Check if document exists and belongs to current user
    if (!$document || $document['uploaded_by'] != $currentUserId) {
        header('Location: ' . BASE_URL . '/staff/documents/?error=not_found');
        exit;
    }
    
    // Check if document can be edited (only draft or rejected documents)
    if (!in_array($document['status'], [DOC_STATUS_DRAFT, DOC_STATUS_REJECTED])) {
        header('Location: ' . BASE_URL . '/staff/documents/view.php?id=' . $documentId . '&error=cannot_edit');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Document edit error: " . $e->getMessage());
    header('Location: ' . BASE_URL . '/staff/documents/?error=not_found');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        // Sanitize input
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $status = sanitizeInput($_POST['status'] ?? $document['status']);
        $tags = sanitizeInput($_POST['tags'] ?? '');
        $action = sanitizeInput($_POST['action'] ?? 'update');
        
        // Validation
        $errors = [];
        
        if (empty($title)) {
            $errors[] = 'กรุณาระบุชื่อเอกสาร';
        }
        
        if (empty($categoryId)) {
            $errors[] = 'กรุณาเลือกหมวดหมู่เอกสาร';
        }
        
        // Validate file if uploaded
        $newFile = false;
        if (!empty($_FILES['document_file']['name'])) {
            $fileValidation = validateFileUpload($_FILES['document_file']);
            if (!empty($fileValidation)) {
                $errors = array_merge($errors, $fileValidation);
            } else {
                $newFile = true;
            }
        }
        
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            // Prepare update data
            $updateData = [
                'id' => $documentId,
                'title' => $title,
                'description' => $description,
                'category_id' => $categoryId,
                'tags' => $tags,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle file replacement
            if ($newFile) {
                // Upload new file
                $uploadResult = $fileManager->upload(
                    $_FILES['document_file'],
                    $categoryId,
                    $currentUserId,
                    [
                        'title' => $title,
                        'description' => $description,
                        'tags' => $tags,
                        'status' => $status,
                        'replace_document_id' => $documentId
                    ]
                );
                
                if (!$uploadResult) {
                    throw new Exception('เกิดข้อผิดพลาดในการอัปโหลดไฟล์ใหม่');
                }
            } else {
                // Update document without file change
                $result = $documentObj->update($updateData);
                
                if (!$result) {
                    throw new Exception('เกิดข้อผิดพลาดในการอัปเดตข้อมูลเอกสาร');
                }
            }
            
            // Log activity
            $activityLog = new ActivityLog();
            $activityLog->log(ACTION_UPDATE, 'document', $documentId, 'Updated document: ' . $title);
            
            // Handle submit for approval action
            if ($action === 'submit_approval') {
                // Update status to pending
                $documentObj->update([
                    'id' => $documentId,
                    'status' => DOC_STATUS_PENDING,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Create notification for approvers
                $notification = new Notification();
                $notification->createForRole(
                    ROLE_APPROVER,
                    'มีเอกสารใหม่รออนุมัติ',
                    'เอกสาร "' . $title . '" ต้องการการอนุมัติ',
                    NOTIF_TYPE_INFO,
                    '/approver/approval/view.php?id=' . $documentId
                );
                
                $success = 'ส่งเอกสารเข้าสู่กระบวนการอนุมัติเรียบร้อยแล้ว';
            } else {
                $success = 'อัปเดตข้อมูลเอกสารเรียบร้อยแล้ว';
            }
            
            // Refresh document data
            $document = $documentObj->getById($documentId);
        }
        
    } catch (Exception $e) {
        error_log("Document update error: " . $e->getMessage());
        $error = 'เกิดข้อผิดพลาดในการอัปเดตเอกสาร: ' . $e->getMessage();
    }
}

// Get categories
try {
    $categories = $category->getAll(['status' => 'active']);
} catch (Exception $e) {
    error_log("Categories fetch error: " . $e->getMessage());
    $categories = [];
}
?>

<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="<?= BASE_URL ?>/staff/documents/view.php?id=<?= $documentId ?>" 
               class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-edit mr-3"></i>แก้ไขเอกสาร
                </h1>
                <p class="text-gray-600 mt-1">แก้ไขข้อมูลและไฟล์เอกสาร</p>
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

    <!-- Edit Form -->
    <div class="bg-white shadow rounded-lg">
        <form method="POST" enctype="multipart/form-data" class="divide-y divide-gray-200">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id" value="<?= $documentId ?>">
            
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>ข้อมูลเอกสาร
                </h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Document Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            ชื่อเอกสาร <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="<?= htmlspecialchars($_POST['title'] ?? $document['title']) ?>"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="ระบุชื่อเอกสาร">
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            รายละเอียดเอกสาร
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                  placeholder="อธิบายรายละเอียดของเอกสาร"><?= htmlspecialchars($_POST['description'] ?? $document['description']) ?></textarea>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                            หมวดหมู่เอกสาร <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" 
                                id="category_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">เลือกหมวดหมู่</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" 
                                    <?= (($_POST['category_id'] ?? $document['category_id']) == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                                <?php if (!empty($cat['description'])): ?>
                                - <?= htmlspecialchars($cat['description']) ?>
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tags -->
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">
                            แท็ก (คั่นด้วยเครื่องหมายจุลภาค)
                        </label>
                        <input type="text" 
                               id="tags" 
                               name="tags" 
                               value="<?= htmlspecialchars($_POST['tags'] ?? $document['tags']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="เช่น: รายงาน, การเงิน, ประจำเดือน">
                        <p class="mt-1 text-xs text-gray-500">
                            ใช้แท็กเพื่อช่วยในการค้นหาและจัดหมวดหมู่เอกสาร
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-file mr-2"></i>ไฟล์เอกสาร
                </h3>
                
                <!-- Current File Info -->
                <div class="mb-6 p-4 bg-gray-50 rounded-md">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">ไฟล์ปัจจุบัน</h4>
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <i class="<?= getFileTypeIcon($document['file_type']) ?> text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($document['original_name'] ?? $document['file_name']) ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?= strtoupper($document['file_type']) ?> • <?= formatFileSize($document['file_size']) ?>
                            </p>
                        </div>
                        <?php if (!empty($document['file_path']) && file_exists($document['file_path'])): ?>
                        <div class="ml-auto">
                            <a href="<?= BASE_URL ?>/api/download.php?id=<?= $documentId ?>" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-500 text-sm">
                                <i class="fas fa-download mr-1"></i>ดาวน์โหลด
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- File Upload (Optional) -->
                <div>
                    <label for="document_file" class="block text-sm font-medium text-gray-700 mb-1">
                        อัปโหลดไฟล์ใหม่ (ถ้าต้องการเปลี่ยนแปลง)
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <div id="file-preview" class="hidden">
                                <i class="fas fa-file text-4xl text-gray-400 mb-2"></i>
                                <div id="file-name" class="text-sm text-gray-600"></div>
                                <div id="file-size" class="text-xs text-gray-500"></div>
                            </div>
                            <div id="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="document_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>เลือกไฟล์ใหม่</span>
                                        <input id="document_file" 
                                               name="document_file" 
                                               type="file" 
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                               class="sr-only">
                                    </label>
                                    <p class="pl-1">หรือลากไฟล์มาวาง</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">
                                รองรับไฟล์: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (สูงสุด <?= formatFileSize(MAX_FILE_SIZE) ?>)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-cog mr-2"></i>ตัวเลือกการเผยแพร่
                </h3>
                
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">สถานะเอกสาร</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input id="status_draft" 
                                   name="status" 
                                   type="radio" 
                                   value="<?= DOC_STATUS_DRAFT ?>"
                                   <?= (($_POST['status'] ?? $document['status']) === DOC_STATUS_DRAFT) ? 'checked' : '' ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="status_draft" class="ml-3">
                                <div class="text-sm font-medium text-gray-700">บันทึกเป็นร่าง</div>
                                <div class="text-xs text-gray-500">เก็บไว้แก้ไขต่อ ยังไม่ส่งอนุมัติ</div>
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input id="status_pending" 
                                   name="status" 
                                   type="radio" 
                                   value="<?= DOC_STATUS_PENDING ?>"
                                   <?= (($_POST['status'] ?? $document['status']) === DOC_STATUS_PENDING) ? 'checked' : '' ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="status_pending" class="ml-3">
                                <div class="text-sm font-medium text-gray-700">ส่งอนุมัติทันที</div>
                                <div class="text-xs text-gray-500">ส่งเอกสารเข้าสู่กระบวนการอนุมัติ</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="px-6 py-4 bg-gray-50 flex justify-between">
                <a href="<?= BASE_URL ?>/staff/documents/view.php?id=<?= $documentId ?>" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-times mr-2"></i>ยกเลิก
                </a>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            name="action" 
                            value="update"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <i class="fas fa-save mr-2"></i>บันทึกการแก้ไข
                    </button>
                    
                    <?php if ($document['status'] === DOC_STATUS_DRAFT): ?>
                    <button type="submit" 
                            name="action" 
                            value="submit_approval"
                            onclick="return confirm('คุณต้องการส่งเอกสารนี้เข้าสู่กระบวนการอนุมัติหรือไม่?')"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-paper-plane mr-2"></i>บันทึกและส่งอนุมัติ
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// File upload preview
document.getElementById('document_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('file-preview');
    const placeholder = document.getElementById('upload-placeholder');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    
    if (file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        
        // Update icon based on file type
        const extension = file.name.split('.').pop().toLowerCase();
        const icon = preview.querySelector('i');
        icon.className = getFileIcon(extension);
        
        placeholder.classList.add('hidden');
        preview.classList.remove('hidden');
    } else {
        placeholder.classList.remove('hidden');
        preview.classList.add('hidden');
    }
});

// Drag and drop functionality
const dropZone = document.querySelector('.border-dashed');
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropZone.classList.add('border-blue-400', 'bg-blue-50');
}

function unhighlight(e) {
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
}

dropZone.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0) {
        document.getElementById('document_file').files = files;
        document.getElementById('document_file').dispatchEvent(new Event('change'));
    }
}

// Helper functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getFileIcon(extension) {
    const icons = {
        'pdf': 'fas fa-file-pdf text-red-500 text-4xl',
        'doc': 'fas fa-file-word text-blue-500 text-4xl',
        'docx': 'fas fa-file-word text-blue-500 text-4xl',
        'xls': 'fas fa-file-excel text-green-500 text-4xl',
        'xlsx': 'fas fa-file-excel text-green-500 text-4xl',
        'jpg': 'fas fa-file-image text-purple-500 text-4xl',
        'jpeg': 'fas fa-file-image text-purple-500 text-4xl',
        'png': 'fas fa-file-image text-purple-500 text-4xl'
    };
    return icons[extension] || 'fas fa-file text-gray-500 text-4xl';
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const categoryId = document.getElementById('category_id').value;
    const file = document.getElementById('document_file').files[0];
    
    if (!title) {
        e.preventDefault();
        alert('กรุณาระบุชื่อเอกสาร');
        document.getElementById('title').focus();
        return;
    }
    
    if (!categoryId) {
        e.preventDefault();
        alert('กรุณาเลือกหมวดหมู่เอกสาร');
        document.getElementById('category_id').focus();
        return;
    }
    
    // Check file size if new file is selected
    if (file && file.size > <?= MAX_FILE_SIZE ?>) {
        e.preventDefault();
        alert('ไฟล์มีขนาดใหญ่เกิน <?= formatFileSize(MAX_FILE_SIZE) ?>');
        return;
    }
    
    // Show loading state
    const submitBtns = this.querySelectorAll('button[type="submit"]');
    submitBtns.forEach(btn => {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังบันทึก...';
        btn.disabled = true;
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>