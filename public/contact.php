<?php
$pageTitle = 'ติดต่อเรา - ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';

// Handle form submission
$formSubmitted = false;
$formErrors = [];
$formSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formSubmitted = true;
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $formErrors[] = 'Invalid security token';
    } else {
        // Validate form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($name)) $formErrors[] = 'กรุณากรอกชื่อ-นามสกุล';
        if (empty($email)) {
            $formErrors[] = 'กรุณากรอกอีเมล';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formErrors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }
        if (empty($subject)) $formErrors[] = 'กรุณากรอกหัวข้อ';
        if (empty($message)) $formErrors[] = 'กรุณากรอกข้อความ';
        
        if (empty($formErrors)) {
            try {
                // Save contact message to database or send email
                // For now, we'll just mark as success
                $formSuccess = true;
                
                // In a real implementation, you would:
                // 1. Save to database
                // 2. Send email notification
                // 3. Log the contact attempt
                
            } catch (Exception $e) {
                error_log("Contact form error: " . $e->getMessage());
                $formErrors[] = 'เกิดข้อผิดพลาดในการส่งข้อความ กรุณาลองใหม่อีกครั้ง';
            }
        }
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
                        <span class="text-gray-900 font-medium">ติดต่อเรา</span>
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
            <i class="fas fa-envelope text-blue-600 mr-3"></i>ติดต่อเรา
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            หากท่านมีคำถาม ข้อเสนอแนะ หรือต้องการความช่วยเหลือเกี่ยวกับระบบ กรุณาติดต่อเราผ่านช่องทางต่อไปนี้
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-paper-plane text-blue-600 mr-3"></i>ส่งข้อความถึงเรา
            </h2>

            <?php if ($formSuccess): ?>
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
                        <div>
                            <h3 class="text-sm font-medium text-green-800">ส่งข้อความสำเร็จ</h3>
                            <p class="text-sm text-green-700 mt-1">
                                ขอบคุณสำหรับข้อความของท่าน เราจะติดต่อกลับภายใน 1-2 วันทำการ
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($formErrors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-red-400 mr-3 mt-1"></i>
                        <div>
                            <h3 class="text-sm font-medium text-red-800">เกิดข้อผิดพลาด</h3>
                            <ul class="text-sm text-red-700 mt-1 list-disc list-inside">
                                <?php foreach ($formErrors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            ชื่อ-นามสกุล <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" required
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            อีเมล <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        เบอร์โทรศัพท์
                    </label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                        หัวข้อ <span class="text-red-500">*</span>
                    </label>
                    <select id="subject" name="subject" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">เลือกหัวข้อ</option>
                        <option value="general" <?= ($_POST['subject'] ?? '') === 'general' ? 'selected' : '' ?>>คำถามทั่วไป</option>
                        <option value="technical" <?= ($_POST['subject'] ?? '') === 'technical' ? 'selected' : '' ?>>ปัญหาทางเทคนิค</option>
                        <option value="document" <?= ($_POST['subject'] ?? '') === 'document' ? 'selected' : '' ?>>เกี่ยวกับเอกสาร</option>
                        <option value="suggestion" <?= ($_POST['subject'] ?? '') === 'suggestion' ? 'selected' : '' ?>>ข้อเสนอแนะ</option>
                        <option value="other" <?= ($_POST['subject'] ?? '') === 'other' ? 'selected' : '' ?>>อื่นๆ</option>
                    </select>
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                        ข้อความ <span class="text-red-500">*</span>
                    </label>
                    <textarea id="message" name="message" rows="6" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="กรุณาอธิบายรายละเอียดของคำถามหรือปัญหาที่ท่านพบ..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>ส่งข้อความ
                    </button>
                </div>
            </form>
        </div>

        <!-- Contact Information -->
        <div class="space-y-8">
            <!-- Contact Details -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-address-book text-green-600 mr-3"></i>ข้อมูลติดต่อ
                </h2>
                
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-map-marker-alt text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">ที่อยู่</h3>
                            <p class="text-gray-600 mt-1">
                                123 ถนนโรงพยาบาล แขวงสุขภาพ<br>
                                เขตสาธารณสุข กรุงเทพมหานคร 10110
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-phone text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">โทรศัพท์</h3>
                            <p class="text-gray-600 mt-1">
                                02-123-4567<br>
                                02-123-4568 (แฟกซ์)
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-envelope text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">อีเมล</h3>
                            <p class="text-gray-600 mt-1">
                                info@hospital.go.th<br>
                                support@hospital.go.th
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-orange-100 p-3 rounded-full">
                                <i class="fas fa-clock text-orange-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">เวลาทำการ</h3>
                            <div class="text-gray-600 mt-1 space-y-1">
                                <div class="flex justify-between">
                                    <span>จันทร์ - ศุกร์:</span>
                                    <span>08:00 - 16:30 น.</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>เสาร์ - อาทิตย์:</span>
                                    <span>08:00 - 12:00 น.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-map text-red-600 mr-3"></i>แผนที่
                </h2>
                
                <!-- Placeholder for map - in real implementation, use Google Maps or other map service -->
                <div class="bg-gray-200 h-64 rounded-lg flex items-center justify-center">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-map-marked-alt text-4xl mb-4"></i>
                        <p class="text-lg font-medium">แผนที่ตำแหน่งโรงพยาบาล</p>
                        <p class="text-sm mt-2">
                            ตั้งอยู่ใจกลางกรุงเทพมหานคร<br>
                            สะดวกในการเดินทางทุกเส้นทาง
                        </p>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="https://maps.google.com" target="_blank" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        ดูใน Google Maps
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="mt-12 bg-gray-50 rounded-xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">
            <i class="fas fa-question-circle text-blue-600 mr-3"></i>คำถามที่พบบ่อย
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">ระบบเปิดให้บริการตลอด 24 ชั่วโมงหรือไม่?</h3>
                <p class="text-gray-600">
                    ระบบเปิดให้บริการตลอด 24 ชั่วโมง แต่การสนับสนุนเทคนิคจะอยู่ในเวลาทำการเท่านั้น
                </p>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">จะขอเอกสารเพิ่มเติมได้อย่างไร?</h3>
                <p class="text-gray-600">
                    ติดต่อแผนกที่เกี่ยวข้องโดยตรง หรือส่งคำขอผ่านฟอร์มติดต่อนี้ โดยระบุเอกสารที่ต้องการ
                </p>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">มีปัญหาในการดาวน์โหลดเอกสาร?</h3>
                <p class="text-gray-600">
                    ตรวจสอบการเชื่อมต่ออินเทอร์เน็ต หรือลองใช้เบราว์เซอร์อื่น หากยังมีปัญหา กรุณาติดต่อเรา
                </p>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">เอกสารบางตัวไม่สามารถเข้าถึงได้?</h3>
                <p class="text-gray-600">
                    เอกสารบางประเภทอาจมีการจำกัดสิทธิ์การเข้าถึง กรุณาติดต่อแผนกเจ้าของเอกสารโดยตรง
                </p>
            </div>
        </div>
        
        <div class="text-center mt-8">
            <a href="<?= BASE_URL ?>/public/help.php" 
               class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-question-circle mr-2"></i>ดูคำถามเพิ่มเติม
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success/error messages after 5 seconds
    const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>