<?php
$pageTitle = 'สำรองข้อมูลและกู้คืน';
$pageSubtitle = 'จัดการการสำรองข้อมูลและกู้คืนระบบ';

require_once '../includes/header.php';

// Require admin role
requireRole(ROLE_ADMIN);

// This is a placeholder page for the backup management module
?>

<div class="bg-white rounded-lg shadow p-6">
    <div class="text-center py-12">
        <i class="fas fa-database text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">การสำรองข้อมูลและกู้คืน</h3>
        <p class="text-gray-500 mb-6">โมดูลนี้จะรวมถึงการสำรองข้อมูล การกู้คืน และการจัดการไฟล์สำรอง</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
            <div class="border border-gray-200 rounded-lg p-4">
                <i class="fas fa-list text-blue-500 text-2xl mb-2"></i>
                <h4 class="font-medium">รายการสำรอง</h4>
                <p class="text-sm text-gray-500">ดูไฟล์สำรองทั้งหมด</p>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <i class="fas fa-plus text-green-500 text-2xl mb-2"></i>
                <h4 class="font-medium">สำรองข้อมูล</h4>
                <p class="text-sm text-gray-500">สร้างไฟล์สำรองใหม่</p>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <i class="fas fa-undo text-yellow-500 text-2xl mb-2"></i>
                <h4 class="font-medium">กู้คืนข้อมูล</h4>
                <p class="text-sm text-gray-500">กู้คืนจากไฟล์สำรอง</p>
            </div>
        </div>
        
        <div class="mt-8">
            <span class="text-sm text-gray-400">โมดูลนี้อยู่ระหว่างการพัฒนา</span>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>