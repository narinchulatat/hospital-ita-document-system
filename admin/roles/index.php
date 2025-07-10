<?php
$pageTitle = 'จัดการบทบาทและสิทธิ์';
require_once '../includes/header.php';
requireRole(ROLE_ADMIN);
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">จัดการบทบาทและสิทธิ์</h1>
            <p class="text-gray-600">กำหนดบทบาทและสิทธิ์การเข้าถึงของผู้ใช้</p>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex">
            <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
            <div class="text-blue-800">
                <h3 class="font-medium">โมดูลจัดการบทบาทและสิทธิ์</h3>
                <p class="text-sm mt-1">โมดูลนี้จะรองรับการจัดการสิทธิ์การเข้าถึงแบบละเอียด</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>