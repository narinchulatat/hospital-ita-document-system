<?php
// Get flash messages and display them
$flashMessages = getFlashMessages();

foreach ($flashMessages as $message): ?>
    <div class="alert alert-<?= $message['type'] ?> alert-dismissible alert-auto-dismiss fade show" role="alert">
        <i class="fas fa-<?= $message['type'] === 'success' ? 'check-circle' : ($message['type'] === 'error' || $message['type'] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
        <?= htmlspecialchars($message['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endforeach;

// Check for URL parameters for one-time messages
if (isset($_GET['success'])) {
    $successMessages = [
        'created' => 'เพิ่มข้อมูลเรียบร้อยแล้ว',
        'updated' => 'อัปเดตข้อมูลเรียบร้อยแล้ว',
        'deleted' => 'ลบข้อมูลเรียบร้อยแล้ว',
        'approved' => 'อนุมัติเรียบร้อยแล้ว',
        'rejected' => 'ไม่อนุมัติเรียบร้อยแล้ว',
        'uploaded' => 'อัปโหลดไฟล์เรียบร้อยแล้ว',
        'saved' => 'บันทึกการตั้งค่าเรียบร้อยแล้ว'
    ];
    
    $message = $successMessages[$_GET['success']] ?? 'ดำเนินการเรียบร้อยแล้ว';
    ?>
    <div class="alert alert-success alert-dismissible alert-auto-dismiss fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php
}

if (isset($_GET['error'])) {
    $errorMessages = [
        'not_found' => 'ไม่พบข้อมูลที่ต้องการ',
        'permission_denied' => 'คุณไม่มีสิทธิ์ในการดำเนินการนี้',
        'invalid_data' => 'ข้อมูลไม่ถูกต้อง',
        'upload_failed' => 'การอัปโหลดไฟล์ล้มเหลว',
        'database_error' => 'เกิดข้อผิดพลาดฐานข้อมูล'
    ];
    
    $message = $errorMessages[$_GET['error']] ?? 'เกิดข้อผิดพลาด';
    ?>
    <div class="alert alert-danger alert-dismissible alert-auto-dismiss fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php
}

if (isset($_GET['warning'])) {
    $warningMessages = [
        'changes_saved' => 'การเปลี่ยนแปลงถูกบันทึกแล้ว แต่อาจต้องใช้เวลาในการมีผล',
        'backup_recommended' => 'แนะนำให้สำรองข้อมูลก่อนดำเนินการ'
    ];
    
    $message = $warningMessages[$_GET['warning']] ?? $_GET['warning'];
    ?>
    <div class="alert alert-warning alert-dismissible alert-auto-dismiss fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php
}
?>