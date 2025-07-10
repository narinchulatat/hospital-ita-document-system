<?php
require_once '../includes/header.php';

$pageTitle = 'ตั้งค่าระบบ';
$pageSubtitle = 'จัดการการตั้งค่าและกำหนดค่าต่างๆ ของระบบ';

// Check permission
if (!hasPermission('settings.view')) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

// Initialize database
$db = new Database();

// Get current settings
$settings_sql = "SELECT * FROM system_settings ORDER BY category, setting_key";
$settings_stmt = $db->query($settings_sql);
$all_settings = $settings_stmt->fetchAll();

// Group settings by category
$settings_by_category = [];
foreach ($all_settings as $setting) {
    $category = $setting['category'] ?? 'general';
    if (!isset($settings_by_category[$category])) {
        $settings_by_category[$category] = [];
    }
    $settings_by_category[$category][] = $setting;
}

$active_tab = $_GET['tab'] ?? 'general';
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= $pageTitle ?></h1>
                <p class="text-gray-600 mt-2"><?= $pageSubtitle ?></p>
            </div>
        </div>
    </div>

    <!-- Settings Navigation Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <a href="?tab=general" 
                   class="<?= $active_tab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-cog mr-2"></i>
                    ทั่วไป
                </a>
                <a href="?tab=upload" 
                   class="<?= $active_tab === 'upload' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-upload mr-2"></i>
                    การอัปโหลด
                </a>
                <a href="?tab=security" 
                   class="<?= $active_tab === 'security' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-shield-alt mr-2"></i>
                    ความปลอดภัย
                </a>
                <a href="?tab=email" 
                   class="<?= $active_tab === 'email' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-envelope mr-2"></i>
                    อีเมล
                </a>
                <a href="?tab=backup" 
                   class="<?= $active_tab === 'backup' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-database mr-2"></i>
                    สำรองข้อมูล
                </a>
                <a href="?tab=maintenance" 
                   class="<?= $active_tab === 'maintenance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-tools mr-2"></i>
                    การบำรุงรักษา
                </a>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- General Settings Tab -->
        <?php if ($active_tab === 'general'): ?>
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">การตั้งค่าทั่วไป</h3>
                <p class="text-sm text-gray-500">กำหนดค่าพื้นฐานของระบบ</p>
            </div>
            
            <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อเว็บไซต์</label>
                        <input type="text" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="ระบบจัดการเอกสาร ITA">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">คำอธิบายเว็บไซต์</label>
                        <input type="text" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="ระบบจัดการเอกสารสำหรับโรงพยาบาล">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">โลโก้เว็บไซต์</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-image text-3xl text-gray-400"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>อัปโหลดไฟล์</span>
                                        <input type="file" class="sr-only">
                                    </label>
                                    <p class="pl-1">หรือลากวาง</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF ขนาดไม่เกิน 2MB</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">เขตเวลา</label>
                        <select class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="Asia/Bangkok" selected>Asia/Bangkok (UTC+07:00)</option>
                            <option value="UTC">UTC (UTC+00:00)</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ภาษาเริ่มต้น</label>
                        <select class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="th" selected>ไทย</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนรายการต่อหน้า</label>
                        <select class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="maintenance_mode" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="maintenance_mode" class="ml-2 text-sm text-gray-700">เปิดโหมดบำรุงรักษา</label>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Upload Settings Tab -->
        <?php elseif ($active_tab === 'upload'): ?>
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">การตั้งค่าการอัปโหลด</h3>
                <p class="text-sm text-gray-500">กำหนดข้อจำกัดและประเภทไฟล์ที่อนุญาต</p>
            </div>
            
            <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ขนาดไฟล์สูงสุด (MB)</label>
                        <input type="number" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="50" min="1" max="500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนไฟล์สูงสุดต่อครั้ง</label>
                        <input type="number" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="10" min="1" max="100">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทไฟล์ที่อนุญาต</label>
                    <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                            <span class="ml-2 text-sm text-gray-700">PDF (.pdf)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                            <span class="ml-2 text-sm text-gray-700">Word (.doc, .docx)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                            <span class="ml-2 text-sm text-gray-700">Excel (.xls, .xlsx)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                            <span class="ml-2 text-sm text-gray-700">PowerPoint (.ppt, .pptx)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                            <span class="ml-2 text-sm text-gray-700">รูปภาพ (.jpg, .png, .gif)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">วิดีโอ (.mp4, .avi)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">เสียง (.mp3, .wav)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">ไฟล์บีบอัด (.zip, .rar)</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">โฟลเดอร์เก็บไฟล์</label>
                    <input type="text" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           value="/uploads/" readonly>
                    <p class="mt-1 text-xs text-gray-500">โฟลเดอร์จัดเก็บไฟล์ที่อัปโหลด</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Security Settings Tab -->
        <?php elseif ($active_tab === 'security'): ?>
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">การตั้งค่าความปลอดภัย</h3>
                <p class="text-sm text-gray-500">กำหนดนโยบายความปลอดภัยของระบบ</p>
            </div>
            
            <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ความยาวรหัสผ่านขั้นต่ำ</label>
                        <input type="number" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="8" min="6" max="32">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">จำนวนครั้งที่เข้าสู่ระบบผิดได้</label>
                        <input type="number" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="5" min="3" max="10">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">เวลา Session หมดอายุ (นาที)</label>
                        <input type="number" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="120" min="15" max="1440">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">เวลาล็อคบัญชี (นาที)</label>
                        <input type="number" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               value="30" min="5" max="1440">
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="require_strong_password" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                        <label for="require_strong_password" class="ml-2 text-sm text-gray-700">บังคับใช้รหัสผ่านที่แข็งแกร่ง</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="two_factor_auth" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="two_factor_auth" class="ml-2 text-sm text-gray-700">เปิดใช้งานการยืนยันตัวตนสองขั้นตอน</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="ip_restriction" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="ip_restriction" class="ml-2 text-sm text-gray-700">จำกัดการเข้าใช้งานตาม IP Address</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="login_notification" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                        <label for="login_notification" class="ml-2 text-sm text-gray-700">แจ้งเตือนเมื่อมีการเข้าสู่ระบบ</label>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </div>
        
        <?php else: ?>
        <!-- Other tabs would go here -->
        <div class="p-6">
            <div class="text-center py-12">
                <i class="fas fa-cog text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900">หน้านี้อยู่ระหว่างการพัฒนา</h3>
                <p class="text-gray-500">กำลังพัฒนาหน้า <?= htmlspecialchars($active_tab) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle form submissions
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        const originalText = $button.html();
        
        // Show loading state
        $button.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin mr-2"></i>กำลังบันทึก...');
        
        // Simulate API call
        setTimeout(() => {
            $button.prop('disabled', false).html(originalText);
            
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'บันทึกการตั้งค่าเรียบร้อยแล้ว',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1000);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>