<?php
$pageTitle = 'นโยบายความเป็นส่วนตัว - ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
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
                        <span class="text-gray-900 font-medium">นโยบายความเป็นส่วนตัว</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            <i class="fas fa-shield-alt text-blue-600 mr-3"></i>นโยบายความเป็นส่วนตัว
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            นโยบายการคุ้มครองข้อมูลส่วนบุคคล การใช้คุกกี้ และข้อกำหนดการใช้งานระบบ
        </p>
    </div>

    <!-- Last Updated -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
        <div class="flex items-center">
            <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>
            <div>
                <h3 class="text-sm font-medium text-blue-900">อัปเดตล่าสุด</h3>
                <p class="text-sm text-blue-800"><?= formatThaiDate(date('Y-m-d')) ?></p>
            </div>
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="bg-gray-50 rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-list text-gray-600 mr-2"></i>สารบัญ
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <a href="#data-collection" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-circle text-xs mr-2"></i>การเก็บรวบรวมข้อมูล
            </a>
            <a href="#data-usage" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-circle text-xs mr-2"></i>การใช้ข้อมูล
            </a>
            <a href="#data-sharing" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-circle text-xs mr-2"></i>การเปิดเผยข้อมูล
            </a>
            <a href="#data-security" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-circle text-xs mr-2"></i>ความปลอดภัยของข้อมูล
            </a>
            <a href="#cookies" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-circle text-xs mr-2"></i>การใช้คุกกี้
            </a>
            <a href="#user-rights" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-circle text-xs mr-2"></i>สิทธิของผู้ใช้
            </a>
            <a href="#terms" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-circle text-xs mr-2"></i>ข้อกำหนดการใช้งาน
            </a>
            <a href="#contact" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-circle text-xs mr-2"></i>ติดต่อเรา
            </a>
        </div>
    </div>

    <!-- Privacy Policy Content -->
    <div class="bg-white shadow rounded-lg p-8 space-y-12">
        
        <!-- Data Collection -->
        <section id="data-collection">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-database text-blue-600 mr-3"></i>การเก็บรวบรวมข้อมูล
            </h2>
            
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">ข้อมูลที่เราเก็บรวบรวม</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                                <span><strong>ข้อมูลการใช้งาน:</strong> หน้าที่เยี่ยมชม เวลาการเข้าชม และการโต้ตอบกับระบบ</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                                <span><strong>ข้อมูลเทคนิค:</strong> ที่อยู่ IP ประเภทเบราว์เซอร์ ระบบปฏิบัติการ</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                                <span><strong>ข้อมูลการติดต่อ:</strong> ชื่อ อีเมล เบอร์โทรศัพท์ (เมื่อท่านส่งฟอร์มติดต่อ)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                                <span><strong>ข้อมูลการดาวน์โหลด:</strong> เอกสารที่ดาวน์โหลด และความถี่ในการใช้งาน</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">วิธีการเก็บรวบรวมข้อมูล</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <i class="fas fa-arrow-right text-blue-500 mr-2 mt-1 text-sm"></i>
                            การใช้งานเว็บไซต์และระบบโดยตรง
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-arrow-right text-blue-500 mr-2 mt-1 text-sm"></i>
                            การกรอกฟอร์มติดต่อหรือแบบสอบถาม
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-arrow-right text-blue-500 mr-2 mt-1 text-sm"></i>
                            คุกกี้และเทคโนโลยีติดตามอื่นๆ
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-arrow-right text-blue-500 mr-2 mt-1 text-sm"></i>
                            การบันทึกการใช้งานระบบอัตโนมัติ
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Data Usage -->
        <section id="data-usage">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-cogs text-green-600 mr-3"></i>การใช้ข้อมูล
            </h2>
            
            <div class="space-y-4">
                <p class="text-gray-700">เราใช้ข้อมูลที่เก็บรวบรวมเพื่อวัตถุประสงค์ดังต่อไปนี้:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2">
                            <i class="fas fa-server text-blue-500 mr-2"></i>การให้บริการ
                        </h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• จัดเตรียมและปรับปรุงการให้บริการ</li>
                            <li>• ประมวลผลคำขอและการค้นหา</li>
                            <li>• สร้างสถิติการใช้งาน</li>
                        </ul>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2">
                            <i class="fas fa-shield-alt text-green-500 mr-2"></i>ความปลอดภัย
                        </h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• ป้องกันการใช้งานที่ไม่เหมาะสม</li>
                            <li>• ตรวจจับและป้องกันการโจมตี</li>
                            <li>• รักษาความปลอดภัยของระบบ</li>
                        </ul>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2">
                            <i class="fas fa-chart-line text-purple-500 mr-2"></i>การปรับปรุง
                        </h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• วิเคราะห์พฤติกรรมการใช้งาน</li>
                            <li>• ปรับปรุงประสิทธิภาพระบบ</li>
                            <li>• พัฒนาฟีเจอร์ใหม่</li>
                        </ul>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2">
                            <i class="fas fa-envelope text-orange-500 mr-2"></i>การติดต่อสื่อสาร
                        </h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• ตอบกลับคำถามและข้อสงสัย</li>
                            <li>• แจ้งข่าวสารและการอัปเดต</li>
                            <li>• ให้การสนับสนุนทางเทคนิค</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Data Sharing -->
        <section id="data-sharing">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-share-alt text-yellow-600 mr-3"></i>การเปิดเผยข้อมูล
            </h2>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-sm font-medium text-yellow-900">หลักการสำคัญ</h3>
                        <p class="text-sm text-yellow-800 mt-1">
                            เราจะไม่ขาย แลกเปลี่ยน หรือเปิดเผยข้อมูลส่วนบุคคลของท่านให้แก่บุคคลที่สาม 
                            ยกเว้นในกรณีที่ระบุไว้ในนโยบายนี้
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">กรณีที่อาจเปิดเผยข้อมูล:</h3>
                <ul class="space-y-3 text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-gavel text-red-500 mr-3 mt-1"></i>
                        <div>
                            <strong>ตามข้อกำหนดของกฎหมาย:</strong> 
                            เมื่อมีคำสั่งจากศาล หรือหน่วยงานราชการที่มีอำนาจ
                        </div>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-shield-alt text-blue-500 mr-3 mt-1"></i>
                        <div>
                            <strong>เพื่อความปลอดภัย:</strong> 
                            ป้องกันการใช้งานที่ผิดกฎหมายหรือเป็นอันตราย
                        </div>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-user-check text-green-500 mr-3 mt-1"></i>
                        <div>
                            <strong>ด้วยความยินยอม:</strong> 
                            เมื่อได้รับความยินยอมจากท่านอย่างชัดเจน
                        </div>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-handshake text-purple-500 mr-3 mt-1"></i>
                        <div>
                            <strong>ผู้ให้บริการ:</strong> 
                            บริษัทคู่ค้าที่ช่วยดำเนินการระบบ (ภายใต้ข้อตกลงความลับ)
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Data Security -->
        <section id="data-security">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-lock text-red-600 mr-3"></i>ความปลอดภัยของข้อมูล
            </h2>
            
            <div class="space-y-6">
                <p class="text-gray-700">
                    เรามีมาตรการรักษาความปลอดภัยที่เข้มงวดเพื่อคุ้มครองข้อมูลของท่าน:
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-server text-2xl text-blue-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">ความปลอดภัยทางเทคนิค</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• การเข้ารหัสข้อมูล (SSL/TLS)</li>
                            <li>• ไฟร์วอลล์และระบบป้องกัน</li>
                            <li>• การสำรองข้อมูลสม่ำเสมอ</li>
                        </ul>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-2xl text-green-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">ความปลอดภัยของบุคลากร</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• การฝึกอบรมด้านความปลอดภัย</li>
                            <li>• การควบคุมการเข้าถึงข้อมูล</li>
                            <li>• ข้อตกลงความลับ</li>
                        </ul>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clipboard-check text-2xl text-purple-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">มาตรการองค์กร</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• นโยบายรักษาความปลอดภัย</li>
                            <li>• การตรวจสอบสม่ำเสมอ</li>
                            <li>• แผนรองรับเหตุฉุกเฉิน</li>
                        </ul>
                    </div>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-semibold text-red-900 mb-2">
                        <i class="fas fa-exclamation-circle mr-2"></i>การแจ้งเหตุการณ์ฉุกเฉิน
                    </h4>
                    <p class="text-sm text-red-800">
                        หากเกิดเหตุการณ์ที่อาจส่งผลกระทบต่อความปลอดภัยของข้อมูล 
                        เราจะแจ้งให้ท่านทราบภายใน 72 ชั่วโมง พร้อมมาตรการแก้ไข
                    </p>
                </div>
            </div>
        </section>

        <!-- Cookies -->
        <section id="cookies">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-cookie-bite text-orange-600 mr-3"></i>การใช้คุกกี้
            </h2>
            
            <div class="space-y-6">
                <p class="text-gray-700">
                    เว็บไซต์นี้ใช้คุกกี้เพื่อปรับปรุงประสบการณ์การใช้งานของท่าน
                </p>
                
                <div class="space-y-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs mr-3">จำเป็น</span>
                            คุกกี้ที่จำเป็น
                        </h4>
                        <p class="text-sm text-gray-600 mb-2">
                            คุกกี้เหล่านี้มีความจำเป็นสำหรับการทำงานของเว็บไซต์ และไม่สามารถปิดการใช้งานได้
                        </p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• การจดจำการตั้งค่าภาษา</li>
                            <li>• การรักษาสถานะการเข้าสู่ระบบ</li>
                            <li>• การป้องกัน CSRF</li>
                        </ul>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-3">วิเคราะห์</span>
                            คุกกี้เพื่อการวิเคราะห์
                        </h4>
                        <p class="text-sm text-gray-600 mb-2">
                            ช่วยให้เราเข้าใจพฤติกรรมการใช้งานเพื่อปรับปรุงเว็บไซต์
                        </p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• การนับจำนวนผู้เข้าชม</li>
                            <li>• การวิเคราะห์หน้าที่ได้รับความนิยม</li>
                            <li>• การติดตามการใช้งานฟีเจอร์</li>
                        </ul>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs mr-3">ตั้งค่า</span>
                            คุกกี้การตั้งค่า
                        </h4>
                        <p class="text-sm text-gray-600 mb-2">
                            จดจำการตั้งค่าของท่านเพื่อประสบการณ์ที่ดีขึ้น
                        </p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• ธีมสีของเว็บไซต์</li>
                            <li>• ขนาดตัวอักษร</li>
                            <li>• การตั้งค่าการแสดงผล</li>
                        </ul>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-cog mr-2"></i>การจัดการคุกกี้
                    </h4>
                    <p class="text-sm text-blue-800 mb-3">
                        ท่านสามารถควบคุมการใช้งานคุกกี้ผ่านการตั้งค่าเบราว์เซอร์ 
                        แต่การปิดใช้งานอาจส่งผลต่อการทำงานของเว็บไซต์
                    </p>
                    <div class="flex space-x-2">
                        <button onclick="acceptAllCookies()" 
                                class="px-3 py-2 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                            ยอมรับทั้งหมด
                        </button>
                        <button onclick="manageCookies()" 
                                class="px-3 py-2 bg-white text-blue-600 text-xs rounded border border-blue-600 hover:bg-blue-50">
                            จัดการคุกกี้
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- User Rights -->
        <section id="user-rights">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-user-shield text-purple-600 mr-3"></i>สิทธิของผู้ใช้
            </h2>
            
            <div class="space-y-4">
                <p class="text-gray-700">
                    ภายใต้กฎหมายคุ้มครองข้อมูลส่วนบุคคล ท่านมีสิทธิดังต่อไปนี้:
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-eye text-blue-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">สิทธิในการเข้าถึงข้อมูล</h4>
                                <p class="text-sm text-gray-600">ขอดูข้อมูลส่วนบุคคลที่เราเก็บรักษาไว้</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i class="fas fa-edit text-green-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">สิทธิในการแก้ไขข้อมูล</h4>
                                <p class="text-sm text-gray-600">ขอแก้ไขข้อมูลที่ไม่ถูกต้องหรือไม่ครบถ้วน</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i class="fas fa-trash text-red-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">สิทธิในการลบข้อมูล</h4>
                                <p class="text-sm text-gray-600">ขอให้ลบข้อมูลส่วนบุคคล (ในบางกรณี)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-ban text-orange-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">สิทธิในการจำกัดการประมวลผล</h4>
                                <p class="text-sm text-gray-600">ขอจำกัดการใช้ข้อมูลในบางกรณี</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i class="fas fa-download text-purple-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">สิทธิในการพกพาข้อมูล</h4>
                                <p class="text-sm text-gray-600">ขอรับข้อมูลในรูปแบบที่สามารถอ่านได้</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">สิทธิในการคัดค้าน</h4>
                                <p class="text-sm text-gray-600">คัดค้านการประมวลผลข้อมูลในบางกรณี</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-semibold text-green-900 mb-2">
                        <i class="fas fa-paper-plane mr-2"></i>การใช้สิทธิ
                    </h4>
                    <p class="text-sm text-green-800 mb-3">
                        หากต้องการใช้สิทธิข้างต้น กรุณาติดต่อเราผ่านช่องทางที่ระบุไว้ 
                        เราจะดำเนินการภายใน 30 วัน
                    </p>
                    <a href="<?= BASE_URL ?>/public/contact.php" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                        <i class="fas fa-envelope mr-2"></i>ติดต่อเรา
                    </a>
                </div>
            </div>
        </section>

        <!-- Terms of Use -->
        <section id="terms">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-file-contract text-indigo-600 mr-3"></i>ข้อกำหนดการใช้งาน
            </h2>
            
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">การยอมรับข้อกำหนด</h3>
                    <p class="text-gray-700">
                        การใช้งานเว็บไซต์นี้ถือว่าท่านยอมรับข้อกำหนดและเงื่อนไขที่ระบุไว้ในนโยบายนี้
                    </p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">การใช้งานที่อนุญาต</h3>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                                ค้นหาและดาวน์โหลดเอกสารสำหรับวัตถุประสงค์ที่ถูกต้อง
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                                ใช้ข้อมูลเพื่อการศึกษาและงานวิจัย
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                                แชร์ลิงก์ไปยังเอกสารสาธารณะ
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">การใช้งานที่ห้าม</h3>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start">
                                <i class="fas fa-times text-red-500 mr-2 mt-1 text-sm"></i>
                                ใช้ระบบเพื่อกิจกรรมที่ผิดกฎหมาย
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-times text-red-500 mr-2 mt-1 text-sm"></i>
                                พยายามเข้าถึงข้อมูลที่ไม่ได้รับอนุญาต
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-times text-red-500 mr-2 mt-1 text-sm"></i>
                                ทำลาย หรือรบกวนการทำงานของระบบ
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-times text-red-500 mr-2 mt-1 text-sm"></i>
                                เผยแพร่เอกสารที่มีลิขสิทธิ์โดยไม่ได้รับอนุญาต
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">ข้อจำกัดความรับผิดชอบ</h3>
                    <p class="text-gray-700 text-sm">
                        เราจัดหาข้อมูลและบริการบนเว็บไซต์นี้ตามสภาพที่เป็นอยู่ โดยไม่รับประกันความถูกต้อง 
                        ครบถ้วน หรือความเหมาะสมสำหรับวัตถุประสงค์เฉพาะใดๆ ท่านใช้ข้อมูลโดยความเสี่ยงของตนเอง
                    </p>
                </div>
            </div>
        </section>

        <!-- Contact -->
        <section id="contact">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-address-book text-teal-600 mr-3"></i>ติดต่อเรา
            </h2>
            
            <div class="bg-gradient-to-r from-teal-50 to-blue-50 rounded-lg p-6">
                <p class="text-gray-700 mb-6">
                    หากท่านมีคำถามเกี่ยวกับนโยบายความเป็นส่วนตัวนี้ หรือต้องการใช้สิทธิในการคุ้มครองข้อมูลส่วนบุคคล 
                    กรุณาติดต่อเรา:
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3">ข้อมูลติดต่อหลัก</h4>
                        <div class="space-y-2 text-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-blue-500 w-5 mr-3"></i>
                                <span>privacy@hospital.go.th</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-phone text-blue-500 w-5 mr-3"></i>
                                <span>02-123-4567 ต่อ 101</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-blue-500 w-5 mr-3 mt-1"></i>
                                <span>
                                    123 ถนนโรงพยาบาล แขวงสุขภาพ<br>
                                    เขตสาธารณสุข กรุงเทพมหานคร 10110
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3">เจ้าหน้าที่คุ้มครองข้อมูล</h4>
                        <div class="space-y-2 text-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-user text-green-500 w-5 mr-3"></i>
                                <span>นายแพทย์ สมชาย ใจดี</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-green-500 w-5 mr-3"></i>
                                <span>dpo@hospital.go.th</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock text-green-500 w-5 mr-3"></i>
                                <span>จันทร์-ศุกร์ 08:00-16:30 น.</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                    <a href="<?= BASE_URL ?>/public/contact.php" 
                       class="inline-flex items-center px-6 py-3 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>ส่งข้อความถึงเรา
                    </a>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer Note -->
    <div class="mt-12 text-center text-sm text-gray-500">
        <p>นโยบายนี้มีผลบังคับใช้ตั้งแต่วันที่ <?= formatThaiDate(date('Y-m-d')) ?></p>
        <p class="mt-2">อัปเดตครั้งสุดท้าย: <?= formatThaiDate(date('Y-m-d')) ?></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for table of contents links
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

function acceptAllCookies() {
    // Set cookie preferences
    localStorage.setItem('cookiePreferences', JSON.stringify({
        necessary: true,
        analytics: true,
        preferences: true
    }));
    
    // Hide cookie banner if exists
    const banner = document.getElementById('cookie-banner');
    if (banner) banner.style.display = 'none';
    
    alert('การตั้งค่าคุกกี้ได้รับการบันทึกแล้ว');
}

function manageCookies() {
    // Open cookie management modal/page
    alert('ฟีเจอร์จัดการคุกกี้จะเปิดในหน้าต่างใหม่');
    // In a real implementation, this would open a cookie management interface
}
</script>

<?php require_once '../includes/footer.php'; ?>