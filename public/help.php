<?php
$pageTitle = 'ความช่วยเหลือ - ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';
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
                        <span class="text-gray-900 font-medium">ความช่วยเหลือ</span>
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
            <i class="fas fa-question-circle text-blue-600 mr-3"></i>ความช่วยเหลือ
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            คู่มือการใช้งาน คำถามที่พบบ่อย และวิธีการค้นหาเอกสารอย่างมีประสิทธิภาพ
        </p>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <a href="#faq" class="bg-blue-50 hover:bg-blue-100 p-6 rounded-lg text-center transition-colors">
            <i class="fas fa-question-circle text-3xl text-blue-600 mb-3"></i>
            <h3 class="font-semibold text-gray-900">คำถามที่พบบ่อย</h3>
        </a>
        <a href="#search-guide" class="bg-green-50 hover:bg-green-100 p-6 rounded-lg text-center transition-colors">
            <i class="fas fa-search text-3xl text-green-600 mb-3"></i>
            <h3 class="font-semibold text-gray-900">วิธีการค้นหา</h3>
        </a>
        <a href="#user-guide" class="bg-purple-50 hover:bg-purple-100 p-6 rounded-lg text-center transition-colors">
            <i class="fas fa-book text-3xl text-purple-600 mb-3"></i>
            <h3 class="font-semibold text-gray-900">คู่มือการใช้งาน</h3>
        </a>
        <a href="#troubleshooting" class="bg-orange-50 hover:bg-orange-100 p-6 rounded-lg text-center transition-colors">
            <i class="fas fa-wrench text-3xl text-orange-600 mb-3"></i>
            <h3 class="font-semibold text-gray-900">แก้ไขปัญหา</h3>
        </a>
    </div>

    <!-- FAQ Section -->
    <div id="faq" class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">
            <i class="fas fa-question-circle text-blue-600 mr-3"></i>คำถามที่พบบ่อย
        </h2>
        
        <div class="space-y-4">
            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <button class="w-full text-left flex justify-between items-center faq-toggle" data-target="faq-1">
                        <h3 class="text-lg font-semibold text-gray-900">ระบบนี้คืออะไร และใช้สำหรับอะไร?</h3>
                        <i class="fas fa-chevron-down text-gray-400 transform transition-transform"></i>
                    </button>
                    <div id="faq-1" class="mt-4 text-gray-600 hidden">
                        <p>ระบบจัดเก็บเอกสาร ITA โรงพยาบาล เป็นระบบจัดการเอกสารออนไลน์ที่ช่วยให้บุคลากรและผู้เยี่ยมชมสามารถค้นหา ดู และดาวน์โหลดเอกสารต่างๆ ของโรงพยาบาลได้อย่างสะดวกและรวดเร็ว รวมถึงเอกสารนโยบาย คู่มือปฏิบัติงาน แบบฟอร์ม และเอกสารสำคัญอื่นๆ</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <button class="w-full text-left flex justify-between items-center faq-toggle" data-target="faq-2">
                        <h3 class="text-lg font-semibold text-gray-900">วิธีการค้นหาเอกสารที่ต้องการ?</h3>
                        <i class="fas fa-chevron-down text-gray-400 transform transition-transform"></i>
                    </button>
                    <div id="faq-2" class="mt-4 text-gray-600 hidden">
                        <ul class="list-disc list-inside space-y-2">
                            <li>ใช้ช่องค้นหาด้านบน โดยพิมพ์คำหรือวลีที่เกี่ยวข้อง</li>
                            <li>เลือกหมวดหมู่จากเมนูด้านซ้าย</li>
                            <li>ใช้การค้นหาขั้นสูงสำหรับตัวเลือกเพิ่มเติม</li>
                            <li>เรียงลำดับผลการค้นหาตามวันที่ ชื่อ หรือความนิยม</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <button class="w-full text-left flex justify-between items-center faq-toggle" data-target="faq-3">
                        <h3 class="text-lg font-semibold text-gray-900">เอกสารประเภทไหนที่สามารถดาวน์โหลดได้?</h3>
                        <i class="fas fa-chevron-down text-gray-400 transform transition-transform"></i>
                    </button>
                    <div id="faq-3" class="mt-4 text-gray-600 hidden">
                        <p>ระบบรองรับเอกสารหลายประเภท:</p>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>ไฟล์ PDF (.pdf)</li>
                            <li>ไฟล์ Microsoft Word (.doc, .docx)</li>
                            <li>ไฟล์ Microsoft Excel (.xls, .xlsx)</li>
                            <li>ไฟล์รูปภาพ (.jpg, .jpeg, .png)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <button class="w-full text-left flex justify-between items-center faq-toggle" data-target="faq-4">
                        <h3 class="text-lg font-semibold text-gray-900">ไม่สามารถดาวน์โหลดเอกสารได้ ต้องทำอย่างไร?</h3>
                        <i class="fas fa-chevron-down text-gray-400 transform transition-transform"></i>
                    </button>
                    <div id="faq-4" class="mt-4 text-gray-600 hidden">
                        <p>หากพบปัญหาในการดาวน์โหลด ให้ลองวิธีต่อไปนี้:</p>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>ตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</li>
                            <li>ล้างข้อมูลแคช (cache) ของเบราว์เซอร์</li>
                            <li>ลองใช้เบราว์เซอร์อื่น</li>
                            <li>ตรวจสอบว่าเอกสารยังคงมีอยู่ในระบบ</li>
                            <li>หากยังมีปัญหา กรุณาติดต่อทีมสนับสนุน</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <button class="w-full text-left flex justify-between items-center faq-toggle" data-target="faq-5">
                        <h3 class="text-lg font-semibold text-gray-900">จะขอสิทธิ์เข้าถึงเอกสารเพิ่มเติมได้อย่างไร?</h3>
                        <i class="fas fa-chevron-down text-gray-400 transform transition-transform"></i>
                    </button>
                    <div id="faq-5" class="mt-4 text-gray-600 hidden">
                        <p>สำหรับการขอสิทธิ์เข้าถึงเอกสารเพิ่มเติม:</p>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>ติดต่อผู้ดูแลระบบผ่านฟอร์มติดต่อ</li>
                            <li>ระบุตำแหน่งงานและแผนกที่สังกัด</li>
                            <li>อธิบายเหตุผลในการขอใช้งาน</li>
                            <li>รอการพิจารณาจากผู้มีอำนาจ 1-3 วันทำการ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Guide -->
    <div id="search-guide" class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">
            <i class="fas fa-search text-green-600 mr-3"></i>วิธีการค้นหาเอกสาร
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">การค้นหาแบบทั่วไป</h3>
                <div class="space-y-4">
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-medium text-gray-800">1. ใช้คำหลัก</h4>
                        <p class="text-gray-600 text-sm">พิมพ์คำหรือวลีที่ต้องการค้นหาในช่องค้นหา</p>
                    </div>
                    <div class="border-l-4 border-green-500 pl-4">
                        <h4 class="font-medium text-gray-800">2. เลือกหมวดหมู่</h4>
                        <p class="text-gray-600 text-sm">คลิกที่หมวดหมู่ที่ต้องการจากเมนูด้านซ้าย</p>
                    </div>
                    <div class="border-l-4 border-purple-500 pl-4">
                        <h4 class="font-medium text-gray-800">3. เรียงลำดับผลลัพธ์</h4>
                        <p class="text-gray-600 text-sm">เลือกการเรียงลำดับตามที่ต้องการ</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">เทคนิคการค้นหา</h3>
                <div class="space-y-3">
                    <div class="bg-gray-50 p-3 rounded">
                        <code class="text-sm font-mono">"คำที่ต้องการ"</code>
                        <p class="text-xs text-gray-600 mt-1">ค้นหาวลีที่ตรงกันทุกคำ</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <code class="text-sm font-mono">คำที่1 คำที่2</code>
                        <p class="text-xs text-gray-600 mt-1">ค้นหาเอกสารที่มีทั้งสองคำ</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <code class="text-sm font-mono">*.pdf</code>
                        <p class="text-xs text-gray-600 mt-1">ค้นหาไฟล์ PDF เท่านั้น</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Guide -->
    <div id="user-guide" class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">
            <i class="fas fa-book text-purple-600 mr-3"></i>คู่มือการใช้งาน
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center mb-4">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">สำหรับผู้เยี่ยมชม</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        เข้าถึงเอกสารสาธารณะ
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        ค้นหาและดาวน์โหลดเอกสาร
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        ดูสถิติการใช้งาน
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        ติดต่อขอความช่วยเหลือ
                    </li>
                </ul>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center mb-4">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-tie text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">สำหรับบุคลากร</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        เข้าถึงเอกสารภายใน
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        อัปโหลดเอกสารใหม่
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        แก้ไขข้อมูลเอกสาร
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        ติดตามสถานะอনุมัติ
                    </li>
                </ul>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center mb-4">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-shield text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">สำหรับผู้อนุมัติ</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        อนุมัติ/ปฏิเสธเอกสาร
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        เพิ่มความคิดเห็น
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        ตั้งค่าการมองเห็น
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-xs"></i>
                        ดูรายงานการอนุมัติ
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Troubleshooting -->
    <div id="troubleshooting" class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">
            <i class="fas fa-wrench text-orange-600 mr-3"></i>แก้ไขปัญหา
        </h2>
        
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ปัญหาการเข้าถึง</h3>
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded p-4">
                            <h4 class="font-medium text-red-600 mb-2">ไม่สามารถเข้าสู่ระบบได้</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• ตรวจสอบชื่อผู้ใช้และรหัสผ่าน</li>
                                <li>• ตรวจสอบ Caps Lock</li>
                                <li>• ลองรีเฟรชหน้าเว็บ</li>
                                <li>• ติดต่อผู้ดูแลระบบ</li>
                            </ul>
                        </div>
                        
                        <div class="border border-gray-200 rounded p-4">
                            <h4 class="font-medium text-red-600 mb-2">ไม่พบเอกสารที่ต้องการ</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• ลองใช้คำค้นหาอื่น</li>
                                <li>• ตรวจสอบการสะกดคำ</li>
                                <li>• ใช้การค้นหาขั้นสูง</li>
                                <li>• ตรวจสอบหมวดหมู่</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ปัญหาเทคนิค</h3>
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded p-4">
                            <h4 class="font-medium text-red-600 mb-2">เว็บไซต์โหลดช้า</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• ตรวจสอบการเชื่อมต่ออินเทอร์เน็ต</li>
                                <li>• ปิดแท็บอื่นๆ ที่ไม่จำเป็น</li>
                                <li>• ล้างข้อมูลแคชเบราว์เซอร์</li>
                                <li>• ลองใช้เบราว์เซอร์อื่น</li>
                            </ul>
                        </div>
                        
                        <div class="border border-gray-200 rounded p-4">
                            <h4 class="font-medium text-red-600 mb-2">ไม่สามารถดูไฟล์ได้</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• ตรวจสอบโปรแกรมอ่านไฟล์</li>
                                <li>• อัปเดตเบราว์เซอร์</li>
                                <li>• เปิด PDF Reader/Office</li>
                                <li>• ดาวน์โหลดแล้วเปิดในเครื่อง</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="bg-blue-50 rounded-xl p-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">
            <i class="fas fa-headset text-blue-600 mr-3"></i>ยังมีคำถามอยู่ใช่หรือไม่?
        </h2>
        <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
            หากไม่พบคำตอบที่ต้องการ ทีมสนับสนุนของเรายินดีให้ความช่วยเหลือ
        </p>
        <div class="flex justify-center space-x-4">
            <a href="<?= BASE_URL ?>/public/contact.php" 
               class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-envelope mr-2"></i>ติดต่อเรา
            </a>
            <a href="tel:02-123-4567" 
               class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-phone mr-2"></i>โทร 02-123-4567
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ toggle functionality
    document.querySelectorAll('.faq-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const target = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (target.classList.contains('hidden')) {
                target.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                target.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>