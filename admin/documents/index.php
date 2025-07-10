<?php
$pageTitle = 'จัดการเอกสาร';
$pageSubtitle = 'จัดการเอกสารในระบบ';
require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

try {
    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $category_filter = $_GET['category'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = 25;
    
    // Placeholder for documents data
    $documents = [];
    $totalDocuments = 0;
    $totalPages = 1;
    
    // Placeholder for categories
    $categories = [];
    
} catch (Exception $e) {
    error_log("Documents index error: " . $e->getMessage());
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการโหลดข้อมูลเอกสาร';
    $documents = [];
    $categories = [];
    $totalDocuments = 0;
    $totalPages = 1;
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">จัดการเอกสาร</h1>
            <p class="text-gray-600">จัดการเอกสารและการอนุมัติ</p>
        </div>
        <div class="flex space-x-3">
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>เพิ่มเอกสาร
            </a>
            <button onclick="exportDocuments()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-download mr-2"></i>ส่งออก
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">เอกสารทั้งหมด</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">รออนุมัติ</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">อนุมัติแล้ว</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">ปฏิเสธ</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Notice -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex">
            <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
            <div class="text-blue-800">
                <h3 class="font-medium">โมดูลจัดการเอกสาร</h3>
                <p class="text-sm mt-1">
                    โมดูลนี้อยู่ในขั้นตอนการพัฒนา จะประกอบด้วยฟีเจอร์:
                </p>
                <ul class="text-sm mt-2 space-y-1 list-disc list-inside">
                    <li>อัปโหลดและจัดการไฟล์เอกสาร</li>
                    <li>ระบบอนุมัติเอกสาร</li>
                    <li>การจัดหมวดหมู่เอกสาร</li>
                    <li>ระบบค้นหาและกรองข้อมูล</li>
                    <li>การติดตาม version ของเอกสาร</li>
                    <li>ระบบสิทธิ์การเข้าถึง</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Placeholder Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">รายการเอกสาร</h3>
        </div>
        <div class="p-6">
            <div class="text-center py-12">
                <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">ยังไม่มีเอกสารในระบบ</p>
                <p class="text-gray-400 text-sm mt-2">โมดูลจัดการเอกสารอยู่ในขั้นตอนการพัฒนา</p>
            </div>
        </div>
    </div>
</div>

<script>
function exportDocuments() {
    showAlert('ฟีเจอร์ส่งออกข้อมูลอยู่ในขั้นตอนการพัฒนา', 'info');
}
</script>

<?php require_once '../includes/footer.php'; ?>