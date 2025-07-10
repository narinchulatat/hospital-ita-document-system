<?php
// Default breadcrumb items if not set
if (!isset($breadcrumbItems)) {
    $currentPath = getCurrentPage();
    $breadcrumbItems = [
        ['title' => 'หน้าหลัก', 'url' => BASE_URL . '/admin/']
    ];
    
    // Generate breadcrumb based on current path
    $pathParts = explode('/', trim($currentPath, '/'));
    
    if (count($pathParts) >= 2 && $pathParts[0] === 'admin') {
        // Remove 'admin' from path
        array_shift($pathParts);
        
        $currentUrl = BASE_URL . '/admin';
        
        foreach ($pathParts as $index => $part) {
            if (empty($part)) continue;
            
            $currentUrl .= '/' . $part;
            
            // Skip index.php in breadcrumb
            if ($part === 'index.php') continue;
            
            $title = ucfirst($part);
            
            // Convert common paths to Thai
            $titleMap = [
                'users' => 'จัดการผู้ใช้',
                'documents' => 'จัดการเอกสาร',
                'categories' => 'จัดการหมวดหมู่',
                'roles' => 'บทบาทและสิทธิ์',
                'permissions' => 'จัดการสิทธิ์',
                'backups' => 'สำรองข้อมูล',
                'notifications' => 'การแจ้งเตือน',
                'settings' => 'ตั้งค่าระบบ',
                'reports' => 'รายงาน',
                'logs' => 'ประวัติการทำงาน',
                'profile' => 'โปรไฟล์',
                'create.php' => 'เพิ่ม',
                'edit.php' => 'แก้ไข',
                'view.php' => 'ดูรายละเอียด',
                'approve.php' => 'อนุมัติ',
                'tree.php' => 'ดูแบบต้นไม้',
                'general.php' => 'ตั้งค่าทั่วไป',
                'upload.php' => 'ตั้งค่าการอัปโหลด',
                'security.php' => 'ตั้งค่าความปลอดภัย',
                'backup.php' => 'ตั้งค่าสำรองข้อมูล',
                'password.php' => 'เปลี่ยนรหัสผ่าน'
            ];
            
            $title = $titleMap[$part] ?? $title;
            
            // If this is the last item, don't add URL
            if ($index === count($pathParts) - 1) {
                $breadcrumbItems[] = ['title' => $title];
            } else {
                $breadcrumbItems[] = ['title' => $title, 'url' => $currentUrl];
            }
        }
    }
}

echo generateBreadcrumb($breadcrumbItems);
?>