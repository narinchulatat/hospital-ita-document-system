<?php
$pageTitle = 'ตั้งค่าระบบ';
$pageSubtitle = 'จัดการการตั้งค่าต่างๆ ของระบบ';

require_once '../includes/header.php';
require_once '../../classes/Setting.php';

// Check permission
requirePermission(PERM_SETTING_VIEW);

$activeTab = $_GET['tab'] ?? 'general';
$success = '';
$error = '';

try {
    $setting = new Setting();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasMenuPermission(PERM_SETTING_EDIT)) {
        checkCSRF();
        
        $settingsData = $_POST['settings'] ?? [];
        $tabName = $_POST['tab'] ?? 'general';
        
        if (!empty($settingsData)) {
            $updatedCount = 0;
            
            foreach ($settingsData as $key => $value) {
                if ($setting->set($key, $value)) {
                    $updatedCount++;
                }
            }
            
            if ($updatedCount > 0) {
                // Log activity
                logAdminActivity(ACTION_UPDATE, 'settings', null, null, ['tab' => $tabName, 'count' => $updatedCount]);
                
                setFlashMessage('success', "บันทึกการตั้งค่าเรียบร้อยแล้ว ($updatedCount รายการ)");
                header('Location: index.php?tab=' . $tabName);
                exit;
            } else {
                $error = 'ไม่มีการเปลี่ยนแปลงการตั้งค่า';
            }
        }
    }
    
    // Get all settings
    $allSettings = $setting->getAll();
    
    // Group settings by category
    $settingGroups = [
        'general' => [],
        'upload' => [],
        'security' => [],
        'backup' => [],
        'notification' => [],
        'display' => []
    ];
    
    foreach ($allSettings as $key => $value) {
        if (strpos($key, 'upload_') === 0) {
            $settingGroups['upload'][$key] = $value;
        } elseif (strpos($key, 'security_') === 0) {
            $settingGroups['security'][$key] = $value;
        } elseif (strpos($key, 'backup_') === 0) {
            $settingGroups['backup'][$key] = $value;
        } elseif (strpos($key, 'notification_') === 0) {
            $settingGroups['notification'][$key] = $value;
        } elseif (strpos($key, 'display_') === 0) {
            $settingGroups['display'][$key] = $value;
        } else {
            $settingGroups['general'][$key] = $value;
        }
    }
    
} catch (Exception $e) {
    error_log("Settings error: " . $e->getMessage());
    $error = 'เกิดข้อผิดพลาดในการโหลดการตั้งค่า';
    $settingGroups = array_fill_keys(['general', 'upload', 'security', 'backup', 'notification', 'display'], []);
}

// Set breadcrumb
$breadcrumbItems = [
    ['title' => 'หน้าหลัก', 'url' => BASE_URL . '/admin/'],
    ['title' => 'ตั้งค่าระบบ']
];
?>

<!-- Settings Tabs -->
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'general' ? 'active' : '' ?>" 
                        id="general-tab" data-bs-toggle="tab" data-bs-target="#general" 
                        type="button" role="tab">
                    <i class="fas fa-cog me-2"></i>ทั่วไป
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'upload' ? 'active' : '' ?>" 
                        id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" 
                        type="button" role="tab">
                    <i class="fas fa-upload me-2"></i>การอัปโหลด
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'security' ? 'active' : '' ?>" 
                        id="security-tab" data-bs-toggle="tab" data-bs-target="#security" 
                        type="button" role="tab">
                    <i class="fas fa-shield-alt me-2"></i>ความปลอดภัย
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'backup' ? 'active' : '' ?>" 
                        id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" 
                        type="button" role="tab">
                    <i class="fas fa-database me-2"></i>สำรองข้อมูล
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'notification' ? 'active' : '' ?>" 
                        id="notification-tab" data-bs-toggle="tab" data-bs-target="#notification" 
                        type="button" role="tab">
                    <i class="fas fa-bell me-2"></i>การแจ้งเตือน
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'display' ? 'active' : '' ?>" 
                        id="display-tab" data-bs-toggle="tab" data-bs-target="#display" 
                        type="button" role="tab">
                    <i class="fas fa-desktop me-2"></i>การแสดงผล
                </button>
            </li>
        </ul>
    </div>
    
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <div class="tab-content">
            <!-- General Settings -->
            <div class="tab-pane fade <?= $activeTab === 'general' ? 'show active' : '' ?>" 
                 id="general" role="tabpanel">
                <?php if (hasMenuPermission(PERM_SETTING_EDIT)): ?>
                <form method="POST" class="needs-validation" novalidate>
                    <?= getCSRFInput() ?>
                    <input type="hidden" name="tab" value="general">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">ชื่อเว็บไซต์</label>
                                <input type="text" class="form-control" id="site_name" 
                                       name="settings[site_name]" 
                                       value="<?= htmlspecialchars($settingGroups['general']['site_name'] ?? 'Hospital ITA Document System') ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="site_description" class="form-label">คำอธิบายเว็บไซต์</label>
                                <textarea class="form-control" id="site_description" 
                                          name="settings[site_description]" rows="3"><?= htmlspecialchars($settingGroups['general']['site_description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">อีเมลผู้ดูแลระบบ</label>
                                <input type="email" class="form-control" id="admin_email" 
                                       name="settings[admin_email]" 
                                       value="<?= htmlspecialchars($settingGroups['general']['admin_email'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="timezone" class="form-label">โซนเวลา</label>
                                <select class="form-select" id="timezone" name="settings[timezone]">
                                    <option value="Asia/Bangkok" <?= ($settingGroups['general']['timezone'] ?? 'Asia/Bangkok') === 'Asia/Bangkok' ? 'selected' : '' ?>>
                                        Asia/Bangkok (UTC+7)
                                    </option>
                                    <option value="UTC" <?= ($settingGroups['general']['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>
                                        UTC (UTC+0)
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="default_language" class="form-label">ภาษาเริ่มต้น</label>
                                <select class="form-select" id="default_language" name="settings[default_language]">
                                    <option value="th" <?= ($settingGroups['general']['default_language'] ?? 'th') === 'th' ? 'selected' : '' ?>>
                                        ไทย
                                    </option>
                                    <option value="en" <?= ($settingGroups['general']['default_language'] ?? '') === 'en' ? 'selected' : '' ?>>
                                        English
                                    </option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="items_per_page" class="form-label">จำนวนรายการต่อหน้า</label>
                                <select class="form-select" id="items_per_page" name="settings[items_per_page]">
                                    <option value="10" <?= ($settingGroups['general']['items_per_page'] ?? '25') === '10' ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= ($settingGroups['general']['items_per_page'] ?? '25') === '25' ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= ($settingGroups['general']['items_per_page'] ?? '25') === '50' ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= ($settingGroups['general']['items_per_page'] ?? '25') === '100' ? 'selected' : '' ?>>100</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="maintenance_mode" class="form-label">โหมดปิดปรับปรุง</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" 
                                           name="settings[maintenance_mode]" value="1"
                                           <?= ($settingGroups['general']['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="maintenance_mode">
                                        เปิดใช้งานโหมดปิดปรับปรุง
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    เมื่อเปิดใช้งาน เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าใช้งานได้
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>บันทึกการตั้งค่า
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    คุณไม่มีสิทธิ์แก้ไขการตั้งค่า
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Upload Settings -->
            <div class="tab-pane fade <?= $activeTab === 'upload' ? 'show active' : '' ?>" 
                 id="upload" role="tabpanel">
                <?php if (hasMenuPermission(PERM_SETTING_EDIT)): ?>
                <form method="POST" class="needs-validation" novalidate>
                    <?= getCSRFInput() ?>
                    <input type="hidden" name="tab" value="upload">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="upload_max_file_size" class="form-label">ขนาดไฟล์สูงสุด (MB)</label>
                                <input type="number" class="form-control" id="upload_max_file_size" 
                                       name="settings[upload_max_file_size]" 
                                       value="<?= htmlspecialchars($settingGroups['upload']['upload_max_file_size'] ?? '10') ?>" 
                                       min="1" max="100" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="upload_allowed_types" class="form-label">ประเภทไฟล์ที่อนุญาต</label>
                                <textarea class="form-control" id="upload_allowed_types" 
                                          name="settings[upload_allowed_types]" rows="3"
                                          placeholder="pdf,doc,docx,xls,xlsx,ppt,pptx"><?= htmlspecialchars($settingGroups['upload']['upload_allowed_types'] ?? 'pdf,doc,docx,xls,xlsx,ppt,pptx') ?></textarea>
                                <small class="form-text text-muted">
                                    คั่นด้วยเครื่องหมายจุลภาค (,)
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="upload_path" class="form-label">โฟลเดอร์เก็บไฟล์</label>
                                <input type="text" class="form-control" id="upload_path" 
                                       name="settings[upload_path]" 
                                       value="<?= htmlspecialchars($settingGroups['upload']['upload_path'] ?? 'uploads/documents/') ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="upload_virus_scan" class="form-label">สแกนไวรัส</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="upload_virus_scan" 
                                           name="settings[upload_virus_scan]" value="1"
                                           <?= ($settingGroups['upload']['upload_virus_scan'] ?? '0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="upload_virus_scan">
                                        เปิดใช้งานการสแกนไวรัส
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="upload_auto_approve" class="form-label">อนุมัติอัตโนมัติ</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="upload_auto_approve" 
                                           name="settings[upload_auto_approve]" value="1"
                                           <?= ($settingGroups['upload']['upload_auto_approve'] ?? '0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="upload_auto_approve">
                                        อนุมัติเอกสารอัตโนมัติเมื่ออัปโหลด
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="upload_watermark" class="form-label">ใส่ลายน้ำ</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="upload_watermark" 
                                           name="settings[upload_watermark]" value="1"
                                           <?= ($settingGroups['upload']['upload_watermark'] ?? '0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="upload_watermark">
                                        เพิ่มลายน้ำลงในไฟล์ PDF
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>บันทึกการตั้งค่า
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    คุณไม่มีสิทธิ์แก้ไขการตั้งค่า
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Security Settings -->
            <div class="tab-pane fade <?= $activeTab === 'security' ? 'show active' : '' ?>" 
                 id="security" role="tabpanel">
                <?php if (hasMenuPermission(PERM_SETTING_EDIT)): ?>
                <form method="POST" class="needs-validation" novalidate>
                    <?= getCSRFInput() ?>
                    <input type="hidden" name="tab" value="security">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="security_session_timeout" class="form-label">ระยะเวลา Session Timeout (นาที)</label>
                                <input type="number" class="form-control" id="security_session_timeout" 
                                       name="settings[security_session_timeout]" 
                                       value="<?= htmlspecialchars($settingGroups['security']['security_session_timeout'] ?? '60') ?>" 
                                       min="5" max="480" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="security_max_login_attempts" class="form-label">จำนวนการพยายามเข้าสู่ระบบสูงสุด</label>
                                <input type="number" class="form-control" id="security_max_login_attempts" 
                                       name="settings[security_max_login_attempts]" 
                                       value="<?= htmlspecialchars($settingGroups['security']['security_max_login_attempts'] ?? '5') ?>" 
                                       min="3" max="10" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="security_lockout_duration" class="form-label">ระยะเวลาล็อคบัญชี (นาที)</label>
                                <input type="number" class="form-control" id="security_lockout_duration" 
                                       name="settings[security_lockout_duration]" 
                                       value="<?= htmlspecialchars($settingGroups['security']['security_lockout_duration'] ?? '30') ?>" 
                                       min="5" max="1440" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="security_2fa_enabled" class="form-label">การยืนยันตัวตนสองขั้นตอน</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="security_2fa_enabled" 
                                           name="settings[security_2fa_enabled]" value="1"
                                           <?= ($settingGroups['security']['security_2fa_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="security_2fa_enabled">
                                        เปิดใช้งาน 2FA
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="security_password_complexity" class="form-label">ความซับซ้อนของรหัสผ่าน</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="security_password_complexity" 
                                           name="settings[security_password_complexity]" value="1"
                                           <?= ($settingGroups['security']['security_password_complexity'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="security_password_complexity">
                                        บังคับใช้รหัสผ่านที่ซับซ้อน
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    ต้องมีตัวอักษรพิมพ์ใหญ่ เลขและสัญลักษณ์
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="security_ip_whitelist" class="form-label">IP Address ที่อนุญาต</label>
                                <textarea class="form-control" id="security_ip_whitelist" 
                                          name="settings[security_ip_whitelist]" rows="3"
                                          placeholder="192.168.1.1&#10;10.0.0.0/8"><?= htmlspecialchars($settingGroups['security']['security_ip_whitelist'] ?? '') ?></textarea>
                                <small class="form-text text-muted">
                                    หากไม่ระบุ จะอนุญาตทุก IP Address
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>บันทึกการตั้งค่า
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    คุณไม่มีสิทธิ์แก้ไขการตั้งค่า
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Additional tabs would go here -->
            <!-- For brevity, I'm showing the structure for other tabs -->
            
            <div class="tab-pane fade <?= $activeTab === 'backup' ? 'show active' : '' ?>" 
                 id="backup" role="tabpanel">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    การตั้งค่าสำรองข้อมูลจะแสดงที่นี่
                </div>
            </div>
            
            <div class="tab-pane fade <?= $activeTab === 'notification' ? 'show active' : '' ?>" 
                 id="notification" role="tabpanel">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    การตั้งค่าการแจ้งเตือนจะแสดงที่นี่
                </div>
            </div>
            
            <div class="tab-pane fade <?= $activeTab === 'display' ? 'show active' : '' ?>" 
                 id="display" role="tabpanel">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    การตั้งค่าการแสดงผลจะแสดงที่นี่
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set active tab based on URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'general';
    
    // Activate the correct tab
    $(`#${activeTab}-tab`).tab('show');
    
    // Update URL when tab changes
    $('.nav-link[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const tabId = $(e.target).attr('data-bs-target').replace('#', '');
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    });
    
    // Test settings functionality
    $('.test-setting').on('click', function() {
        const setting = $(this).data('setting');
        
        // Add test functionality here
        AdminJS.showAlert(`การทดสอบ ${setting} กำลังพัฒนา`, 'info');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>