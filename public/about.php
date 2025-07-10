<?php
$pageTitle = 'เกี่ยวกับเรา - ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';

try {
    $setting = new Setting();
    $siteSettings = $setting->getAll();
} catch (Exception $e) {
    error_log("About page error: " . $e->getMessage());
    $siteSettings = [];
}
?>

<!-- Breadcrumb -->
<div class="bg-gray-50 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="<?= BASE_URL ?>/public/" class="text-gray-500 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>หน้าหลัก
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-gray-900 font-medium">เกี่ยวกับเรา</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            <i class="fas fa-hospital text-blue-600 mr-3"></i>เกี่ยวกับระบบจัดเก็บเอกสาร ITA
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            ระบบจัดการเอกสารอัจฉริยะสำหรับโรงพยาบาล ที่ช่วยให้การจัดเก็บ ค้นหา และเข้าถึงเอกสารเป็นไปอย่างมีประสิทธิภาพ
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- About System -->
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i>เกี่ยวกับระบบ
            </h2>
            
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">วัตถุประสงค์</h3>
                    <p class="text-gray-600 leading-relaxed">
                        ระบบจัดเก็บเอกสาร ITA โรงพยาบาล ได้รับการพัฒนาขึ้นเพื่อให้บุคลากรและผู้เยี่ยมชม
                        สามารถเข้าถึงเอกสารสำคัญต่างๆ ของโรงพยาบาลได้อย่างสะดวก รวดเร็ว และปลอดภัย
                    </p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">คุณสมบัติหลัก</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            ระบบจัดหมวดหมู่เอกสารที่ครบถ้วน
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            การค้นหาที่รวดเร็วและแม่นยำ
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            ระบบการอนุมัติเอกสารที่ปลอดภัย
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            การติดตามการใช้งานและสถิติ
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                            รองรับการใช้งานบนอุปกรณ์ทุกประเภท
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">เวอร์ชันระบบ</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">เวอร์ชัน:</span>
                                <span class="text-gray-600"><?= SITE_VERSION ?></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">อัปเดตล่าสุด:</span>
                                <span class="text-gray-600"><?= formatThaiDate(date('Y-m-d')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- How to Use -->
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-question-circle text-green-600 mr-3"></i>วิธีการใช้งาน
            </h2>
            
            <div class="space-y-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm mr-2">1</span>
                        การค้นหาเอกสาร
                    </h3>
                    <p class="text-gray-600">
                        ใช้ช่องค้นหาด้านบนเพื่อพิมพ์คำที่ต้องการ หรือเลือกหมวดหมู่จากเมนูด้านซ้าย
                        สามารถใช้ตัวกรองขั้นสูงเพื่อค้นหาแบบละเอียดได้
                    </p>
                </div>
                
                <div class="border-l-4 border-green-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm mr-2">2</span>
                        การดูเอกสาร
                    </h3>
                    <p class="text-gray-600">
                        คลิกที่ชื่อเอกสารเพื่อดูรายละเอียด สามารถดาวน์โหลดเอกสารได้โดยคลิกปุ่มดาวน์โหลด
                        เอกสารบางประเภทสามารถดูตัวอย่างได้ก่อนดาวน์โหลด
                    </p>
                </div>
                
                <div class="border-l-4 border-purple-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm mr-2">3</span>
                        การใช้งานขั้นสูง
                    </h3>
                    <p class="text-gray-600">
                        สำหรับบุคลากรโรงพยาบาล สามารถเข้าสู่ระบบเพื่อเข้าถึงเอกสารภายในและฟีเจอร์เพิ่มเติม
                        เช่น การอัปโหลดเอกสาร การจัดการหมวดหมู่ และการสร้างรายงาน
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">
            <i class="fas fa-chart-bar text-blue-600 mr-3"></i>สถิติการใช้งานระบบ
        </h2>
        
        <?php
        try {
            $document = new Document();
            $category = new Category();
            $db = Database::getInstance();
            
            $stats = [
                'total_documents' => $document->getTotalCount(['is_public' => 1, 'status' => 'approved']),
                'total_categories' => count($category->getAll(true)),
                'total_downloads' => 0,
                'total_views' => 0
            ];
            
            $downloadStats = $db->fetch("SELECT SUM(download_count) as total_downloads, SUM(view_count) as total_views FROM documents WHERE is_public = 1 AND status = 'approved'");
            $stats['total_downloads'] = $downloadStats['total_downloads'] ?? 0;
            $stats['total_views'] = $downloadStats['total_views'] ?? 0;
        } catch (Exception $e) {
            $stats = ['total_documents' => 0, 'total_categories' => 0, 'total_downloads' => 0, 'total_views' => 0];
        }
        ?>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center bg-white rounded-lg p-6 shadow-md">
                <div class="text-3xl font-bold text-blue-600 mb-2">
                    <?= number_format($stats['total_documents']) ?>
                </div>
                <div class="text-gray-600 font-medium">เอกสารทั้งหมด</div>
            </div>
            <div class="text-center bg-white rounded-lg p-6 shadow-md">
                <div class="text-3xl font-bold text-green-600 mb-2">
                    <?= number_format($stats['total_categories']) ?>
                </div>
                <div class="text-gray-600 font-medium">หมวดหมู่</div>
            </div>
            <div class="text-center bg-white rounded-lg p-6 shadow-md">
                <div class="text-3xl font-bold text-purple-600 mb-2">
                    <?= number_format($stats['total_downloads']) ?>
                </div>
                <div class="text-gray-600 font-medium">ดาวน์โหลด</div>
            </div>
            <div class="text-center bg-white rounded-lg p-6 shadow-md">
                <div class="text-3xl font-bold text-orange-600 mb-2">
                    <?= number_format($stats['total_views']) ?>
                </div>
                <div class="text-gray-600 font-medium">การเข้าชม</div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="mt-12 bg-white shadow-lg rounded-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
            <i class="fas fa-address-book text-blue-600 mr-3"></i>ข้อมูลติดต่อ
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ติดต่อเราได้ที่</h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <i class="fas fa-phone text-blue-600 w-6 mr-3"></i>
                        <span class="text-gray-700">02-123-4567</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope text-blue-600 w-6 mr-3"></i>
                        <span class="text-gray-700">info@hospital.go.th</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-map-marker-alt text-blue-600 w-6 mr-3 mt-1"></i>
                        <span class="text-gray-700">
                            123 ถนนโรงพยาบาล แขวงสุขภาพ<br>
                            เขตสาธารณสุข กรุงเทพมหานคร 10110
                        </span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">เวลาทำการ</h3>
                <div class="space-y-2 text-gray-700">
                    <div class="flex justify-between">
                        <span>จันทร์ - ศุกร์:</span>
                        <span>08:00 - 16:30 น.</span>
                    </div>
                    <div class="flex justify-between">
                        <span>เสาร์ - อาทิตย์:</span>
                        <span>08:00 - 12:00 น.</span>
                    </div>
                    <div class="flex justify-between">
                        <span>วันหยุดนักขัตฤกษ์:</span>
                        <span>ปิดทำการ</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t text-center">
            <div class="flex justify-center space-x-6">
                <a href="<?= BASE_URL ?>/public/contact.php" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-envelope mr-2"></i>ติดต่อเรา
                </a>
                <a href="<?= BASE_URL ?>/public/help.php" 
                   class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-question-circle mr-2"></i>ความช่วยเหลือ
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>