<?php
$pageTitle = 'แผนผังเว็บไซต์ - ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
require_once '../includes/header.php';

try {
    $category = new Category();
    $document = new Document();
    
    // Get category tree
    $categoryTree = $category->getTree();
    
    // Get document statistics
    $totalDocuments = $document->getTotalCount(['is_public' => 1, 'status' => 'approved']);
    $totalCategories = count($category->getAll(true));
    
} catch (Exception $e) {
    error_log("Sitemap page error: " . $e->getMessage());
    $categoryTree = [];
    $totalDocuments = 0;
    $totalCategories = 0;
}

// Define main site structure
$siteStructure = [
    'main' => [
        'title' => 'หน้าหลัก',
        'url' => '/public/',
        'icon' => 'fas fa-home',
        'description' => 'หน้าแรกของระบบ แสดงสถิติและเอกสารล่าสุด'
    ],
    'documents' => [
        'title' => 'เอกสาร',
        'url' => '/public/documents/',
        'icon' => 'fas fa-file-alt',
        'description' => 'เรียกดูและค้นหาเอกสารทั้งหมด'
    ],
    'categories' => [
        'title' => 'หมวดหมู่',
        'url' => '/public/categories.php',
        'icon' => 'fas fa-folder-tree',
        'description' => 'เรียกดูเอกสารตามหมวดหมู่'
    ],
    'search' => [
        'title' => 'ค้นหา',
        'url' => '/public/search.php',
        'icon' => 'fas fa-search',
        'description' => 'ค้นหาเอกสารแบบทั่วไป'
    ],
    'advanced_search' => [
        'title' => 'ค้นหาขั้นสูง',
        'url' => '/public/advanced-search.php',
        'icon' => 'fas fa-search-plus',
        'description' => 'ค้นหาเอกสารด้วยตัวกรองหลายเงื่อนไข'
    ],
    'statistics' => [
        'title' => 'สถิติ',
        'url' => '/public/statistics.php',
        'icon' => 'fas fa-chart-bar',
        'description' => 'สถิติการใช้งานระบบและเอกสาร'
    ],
    'about' => [
        'title' => 'เกี่ยวกับเรา',
        'url' => '/public/about.php',
        'icon' => 'fas fa-info-circle',
        'description' => 'ข้อมูลเกี่ยวกับระบบและวิธีการใช้งาน'
    ],
    'contact' => [
        'title' => 'ติดต่อเรา',
        'url' => '/public/contact.php',
        'icon' => 'fas fa-envelope',
        'description' => 'ข้อมูลติดต่อและฟอร์มส่งข้อความ'
    ],
    'help' => [
        'title' => 'ความช่วยเหลือ',
        'url' => '/public/help.php',
        'icon' => 'fas fa-question-circle',
        'description' => 'คำถามที่พบบ่อยและคู่มือการใช้งาน'
    ],
    'privacy' => [
        'title' => 'นโยบายความเป็นส่วนตัว',
        'url' => '/public/privacy.php',
        'icon' => 'fas fa-shield-alt',
        'description' => 'นโยบายการใช้ข้อมูลและความเป็นส่วนตัว'
    ]
];

$apiEndpoints = [
    'documents' => [
        'title' => 'Documents API',
        'url' => '/public/api/documents.php',
        'method' => 'GET',
        'description' => 'API สำหรับดึงข้อมูลเอกสาร (JSON)'
    ],
    'categories' => [
        'title' => 'Categories API',
        'url' => '/public/api/categories.php',
        'method' => 'GET',
        'description' => 'API สำหรับดึงข้อมูลหมวดหมู่ (JSON)'
    ],
    'rss' => [
        'title' => 'RSS Feed',
        'url' => '/public/rss.php',
        'method' => 'GET',
        'description' => 'RSS Feed สำหรับเอกสารใหม่ (XML)'
    ]
];
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
                        <span class="text-gray-900 font-medium">แผนผังเว็บไซต์</span>
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
            <i class="fas fa-sitemap text-blue-600 mr-3"></i>แผนผังเว็บไซต์
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            ภาพรวมโครงสร้างเว็บไซต์ และลิงก์ไปยังหน้าต่างๆ ในระบบจัดเก็บเอกสาร
        </p>
    </div>

    <!-- Site Statistics -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div>
                <div class="text-3xl font-bold text-blue-600 mb-2">
                    <?= count($siteStructure) ?>
                </div>
                <div class="text-gray-700 font-medium">หน้าหลัก</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-green-600 mb-2">
                    <?= number_format($totalCategories) ?>
                </div>
                <div class="text-gray-700 font-medium">หมวดหมู่เอกสาร</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-purple-600 mb-2">
                    <?= number_format($totalDocuments) ?>
                </div>
                <div class="text-gray-700 font-medium">เอกสารทั้งหมด</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Pages -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-globe text-blue-600 mr-3"></i>หน้าหลักของเว็บไซต์
                </h2>
                
                <div class="space-y-4">
                    <?php foreach ($siteStructure as $key => $page): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        <a href="<?= BASE_URL . $page['url'] ?>" 
                                           class="hover:text-blue-600 transition-colors flex items-center">
                                            <i class="<?= $page['icon'] ?> mr-3 text-blue-500"></i>
                                            <?= htmlspecialchars($page['title']) ?>
                                        </a>
                                    </h3>
                                    <p class="text-gray-600 text-sm">
                                        <?= htmlspecialchars($page['description']) ?>
                                    </p>
                                    <div class="mt-2">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">
                                            <?= BASE_URL . $page['url'] ?>
                                        </code>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <a href="<?= BASE_URL . $page['url'] ?>" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-external-link-alt mr-2"></i>เยี่ยมชม
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- API Endpoints -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-code text-green-600 mr-3"></i>API Endpoints
                </h2>
                
                <div class="space-y-4">
                    <?php foreach ($apiEndpoints as $key => $api): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        <span class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                                                <?= $api['method'] ?>
                                            </span>
                                            <?= htmlspecialchars($api['title']) ?>
                                        </span>
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-2">
                                        <?= htmlspecialchars($api['description']) ?>
                                    </p>
                                    <div>
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">
                                            <?= BASE_URL . $api['url'] ?>
                                        </code>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <a href="<?= BASE_URL . $api['url'] ?>" 
                                       target="_blank"
                                       class="inline-flex items-center px-3 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition-colors">
                                        <i class="fas fa-play mr-2"></i>ทดสอบ
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-medium text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>การใช้งาน API
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• API ส่งคืนข้อมูลในรูปแบบ JSON</li>
                        <li>• รองรับ CORS สำหรับการเรียกใช้จาก JavaScript</li>
                        <li>• สามารถใช้ query parameters เพื่อกรองข้อมูล</li>
                        <li>• มีการแบ่งหน้า (pagination) สำหรับข้อมูลจำนวนมาก</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Category Tree Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg p-6 sticky top-4">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-folder-tree text-yellow-600 mr-3"></i>หมวดหมู่เอกสาร
                </h2>
                
                <?php if (!empty($categoryTree)): ?>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <?php foreach ($categoryTree as $category): ?>
                            <div class="category-item">
                                <div class="flex items-center py-2 px-3 rounded hover:bg-gray-50 cursor-pointer category-toggle" 
                                     data-category-id="<?= $category['id'] ?>">
                                    <?php if (!empty($category['children'])): ?>
                                        <i class="fas fa-chevron-right text-gray-400 mr-2 transition-transform tree-icon"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                                    <?php endif; ?>
                                    <span class="text-sm font-medium text-gray-700 flex-1">
                                        <a href="<?= BASE_URL ?>/public/documents/?category=<?= $category['id'] ?>" 
                                           class="hover:text-blue-600">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </a>
                                    </span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                        <?= $category['documents_count'] ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($category['children'])): ?>
                                    <div class="ml-6 space-y-1 category-children hidden">
                                        <?php foreach ($category['children'] as $child): ?>
                                            <div class="category-item">
                                                <div class="flex items-center py-1 px-3 rounded hover:bg-gray-50 cursor-pointer category-toggle"
                                                     data-category-id="<?= $child['id'] ?>">
                                                    <?php if (!empty($child['children'])): ?>
                                                        <i class="fas fa-chevron-right text-gray-400 mr-2 transition-transform tree-icon"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                                                    <?php endif; ?>
                                                    <span class="text-sm text-gray-600 flex-1">
                                                        <a href="<?= BASE_URL ?>/public/documents/?category=<?= $child['id'] ?>" 
                                                           class="hover:text-blue-600">
                                                            <?= htmlspecialchars($child['name']) ?>
                                                        </a>
                                                    </span>
                                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                        <?= $child['documents_count'] ?>
                                                    </span>
                                                </div>
                                                
                                                <?php if (!empty($child['children'])): ?>
                                                    <div class="ml-6 space-y-1 category-children hidden">
                                                        <?php foreach ($child['children'] as $grandChild): ?>
                                                            <div class="flex items-center py-1 px-3 rounded hover:bg-gray-50">
                                                                <i class="fas fa-circle text-gray-300 mr-2 text-xs"></i>
                                                                <span class="text-sm text-gray-600 flex-1">
                                                                    <a href="<?= BASE_URL ?>/public/documents/?category=<?= $grandChild['id'] ?>" 
                                                                       class="hover:text-blue-600">
                                                                        <?= htmlspecialchars($grandChild['name']) ?>
                                                                    </a>
                                                                </span>
                                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                                    <?= $grandChild['documents_count'] ?>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t">
                        <a href="<?= BASE_URL ?>/public/categories.php" 
                           class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors">
                            <i class="fas fa-eye mr-2"></i>ดูหมวดหมู่ทั้งหมด
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-folder-open text-3xl mb-2"></i>
                        <p>ไม่มีหมวดหมู่</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Navigation -->
    <div class="mt-12 bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            <i class="fas fa-compass text-purple-600 mr-3"></i>เมนูลัด
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php 
            $quickLinks = [
                ['title' => 'หน้าหลัก', 'url' => '/public/', 'icon' => 'fas fa-home', 'color' => 'blue'],
                ['title' => 'ค้นหา', 'url' => '/public/search.php', 'icon' => 'fas fa-search', 'color' => 'green'],
                ['title' => 'หมวดหมู่', 'url' => '/public/categories.php', 'icon' => 'fas fa-folder', 'color' => 'yellow'],
                ['title' => 'สถิติ', 'url' => '/public/statistics.php', 'icon' => 'fas fa-chart-bar', 'color' => 'purple'],
                ['title' => 'ความช่วยเหลือ', 'url' => '/public/help.php', 'icon' => 'fas fa-question-circle', 'color' => 'orange'],
                ['title' => 'ติดต่อ', 'url' => '/public/contact.php', 'icon' => 'fas fa-envelope', 'color' => 'red']
            ];
            ?>
            
            <?php foreach ($quickLinks as $link): ?>
                <a href="<?= BASE_URL . $link['url'] ?>" 
                   class="group bg-<?= $link['color'] ?>-50 hover:bg-<?= $link['color'] ?>-100 p-4 rounded-lg text-center transition-all duration-200 transform hover:scale-105">
                    <div class="text-<?= $link['color'] ?>-600 mb-2">
                        <i class="<?= $link['icon'] ?> text-2xl"></i>
                    </div>
                    <div class="text-sm font-medium text-gray-700 group-hover:text-<?= $link['color'] ?>-800">
                        <?= htmlspecialchars($link['title']) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="mt-12 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
            <i class="fas fa-info-circle text-blue-600 mr-3"></i>ข้อมูลเพิ่มเติม
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-cog text-blue-500 mr-2"></i>ฟีเจอร์ของระบบ
                </h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                        ระบบจัดการเอกสารที่ครบถ้วน
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                        การค้นหาที่รวดเร็วและแม่นยำ
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                        รองรับไฟล์หลายประเภท
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                        ระบบสถิติและรายงาน
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-1 text-sm"></i>
                        API สำหรับการพัฒนาต่อยอด
                    </li>
                </ul>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-users text-purple-500 mr-2"></i>กลุ่มผู้ใช้งาน
                </h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-user text-blue-500 mr-2 mt-1 text-sm"></i>
                        ผู้เยี่ยมชมทั่วไป
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-user-tie text-green-500 mr-2 mt-1 text-sm"></i>
                        บุคลากรโรงพยาบาล
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-user-shield text-purple-500 mr-2 mt-1 text-sm"></i>
                        ผู้อนุมัติเอกสาร
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-user-cog text-orange-500 mr-2 mt-1 text-sm"></i>
                        ผู้ดูแลระบบ
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-300 text-center">
            <p class="text-gray-600 mb-4">
                อัปเดตล่าสุด: <?= formatThaiDate(date('Y-m-d'), true) ?>
            </p>
            <div class="flex justify-center space-x-4">
                <a href="<?= BASE_URL ?>/public/about.php" 
                   class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                    <i class="fas fa-info-circle mr-2"></i>เกี่ยวกับเรา
                </a>
                <a href="<?= BASE_URL ?>/public/help.php" 
                   class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition-colors">
                    <i class="fas fa-question-circle mr-2"></i>ความช่วยเหลือ
                </a>
                <a href="<?= BASE_URL ?>/public/contact.php" 
                   class="inline-flex items-center px-4 py-2 border border-purple-300 text-sm font-medium rounded-md text-purple-700 bg-purple-50 hover:bg-purple-100 transition-colors">
                    <i class="fas fa-envelope mr-2"></i>ติดต่อเรา
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category tree toggle functionality
    document.querySelectorAll('.category-toggle').forEach(function(element) {
        element.addEventListener('click', function(e) {
            // Only toggle if not clicking on the link
            if (e.target.tagName !== 'A') {
                const children = this.parentElement.querySelector('.category-children');
                const icon = this.querySelector('.tree-icon');
                
                if (children) {
                    e.preventDefault();
                    children.classList.toggle('hidden');
                    icon.style.transform = children.classList.contains('hidden') ? 
                        'rotate(0deg)' : 'rotate(90deg)';
                }
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>