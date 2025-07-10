<?php
require_once '../includes/header.php';

$pageTitle = 'จัดการสำรองข้อมูล';
$pageSubtitle = 'สำรองและกู้คืนข้อมูลระบบ';

// Check permission
if (!hasPermission('backups.view')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
            <?php if (hasPermission('backups.create')): ?>
            <button type="button"
                    onclick="createBackup()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                สำรองข้อมูลใหม่
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="text-center py-12">
            <i class="fas fa-database text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">จัดการสำรองข้อมูล</h3>
            <p class="text-gray-500 mb-6">ปกป้องข้อมูลของคุณด้วยระบบสำรองข้อมูลอัตโนมัติ</p>
            <div class="space-y-2 text-sm text-gray-600">
                <p>• สำรองข้อมูลแบบอัตโนมัติตามกำหนดเวลา</p>
                <p>• สำรองข้อมูลแบบแมนนวลเมื่อต้องการ</p>
                <p>• กู้คืนข้อมูลจากจุดสำรองที่เลือก</p>
                <p>• ตรวจสอบความสมบูรณ์ของไฟล์สำรอง</p>
            </div>
        </div>
    </div>
</div>

<script>
function createBackup() {
    Swal.fire({
        title: 'สำรองข้อมูล',
        text: 'คุณต้องการสำรองข้อมูลหรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ใช่, สำรอง',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('กำลังสำรอง...', 'กรุณารอสักครู่', 'info');
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>