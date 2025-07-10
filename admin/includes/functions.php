<?php
/**
 * Admin Helper Functions
 */

/**
 * Format file size
 */
function formatFileSize($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Format Thai date
 */
function formatThaiDate($date, $showTime = false) {
    if (!$date) return '-';
    
    $thaiMonths = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    $day = date('j', $timestamp);
    $month = $thaiMonths[date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;
    
    $formatted = $day . ' ' . $month . ' ' . $year;
    
    if ($showTime) {
        $formatted .= ' ' . date('H:i', $timestamp) . ' น.';
    }
    
    return $formatted;
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status, $type = 'document') {
    $badges = [
        'document' => [
            DOC_STATUS_DRAFT => ['class' => 'bg-secondary', 'text' => 'แบบร่าง'],
            DOC_STATUS_PENDING => ['class' => 'bg-warning', 'text' => 'รออนุมัติ'],
            DOC_STATUS_APPROVED => ['class' => 'bg-success', 'text' => 'อนุมัติแล้ว'],
            DOC_STATUS_REJECTED => ['class' => 'bg-danger', 'text' => 'ไม่อนุมัติ'],
            DOC_STATUS_ARCHIVED => ['class' => 'bg-dark', 'text' => 'เก็บถาวร']
        ],
        'user' => [
            STATUS_ACTIVE => ['class' => 'bg-success', 'text' => 'ใช้งาน'],
            STATUS_INACTIVE => ['class' => 'bg-secondary', 'text' => 'ไม่ใช้งาน'],
            STATUS_LOCKED => ['class' => 'bg-danger', 'text' => 'ถูกล็อค']
        ],
        'backup' => [
            'pending' => ['class' => 'bg-secondary', 'text' => 'รอดำเนินการ'],
            'in_progress' => ['class' => 'bg-info', 'text' => 'กำลังดำเนินการ'],
            'completed' => ['class' => 'bg-success', 'text' => 'เสร็จสิ้น'],
            'failed' => ['class' => 'bg-danger', 'text' => 'ล้มเหลว']
        ]
    ];
    
    $badge = $badges[$type][$status] ?? ['class' => 'bg-secondary', 'text' => $status];
    
    return '<span class="badge ' . $badge['class'] . '">' . $badge['text'] . '</span>';
}

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl, $queryParams = []) {
    if ($totalPages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $currentPage - 1]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">ก่อนหน้า</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">ก่อนหน้า</span></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $url = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => 1]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $url = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $i]));
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $url = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $totalPages]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $currentPage + 1]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">ถัดไป</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">ถัดไป</span></li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash messages
 */
function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Generate breadcrumb items
 */
function generateBreadcrumb($items) {
    $html = '<nav aria-label="breadcrumb">';
    $html .= '<ol class="breadcrumb mb-0">';
    
    foreach ($items as $index => $item) {
        if ($index === count($items) - 1) {
            $html .= '<li class="breadcrumb-item active">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            if (isset($item['url'])) {
                $html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
            } else {
                $html .= '<li class="breadcrumb-item">' . htmlspecialchars($item['title']) . '</li>';
            }
        }
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = [], $maxSize = null) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'ไฟล์มีขนาดใหญ่เกินไป';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'การอัปโหลดไฟล์ไม่สมบูรณ์';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'ไม่มีไฟล์ที่อัปโหลด';
                break;
            default:
                $errors[] = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
        }
        return $errors;
    }
    
    // Check file size
    if ($maxSize && $file['size'] > $maxSize) {
        $errors[] = 'ไฟล์มีขนาดใหญ่เกิน ' . formatFileSize($maxSize);
    }
    
    // Check file type
    if (!empty($allowedTypes)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'ประเภทไฟล์ไม่ได้รับอนุญาต';
        }
    }
    
    return $errors;
}

/**
 * Get file icon based on extension
 */
function getFileIcon($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $icons = [
        'pdf' => 'fa-file-pdf text-danger',
        'doc' => 'fa-file-word text-primary',
        'docx' => 'fa-file-word text-primary',
        'xls' => 'fa-file-excel text-success',
        'xlsx' => 'fa-file-excel text-success',
        'ppt' => 'fa-file-powerpoint text-warning',
        'pptx' => 'fa-file-powerpoint text-warning',
        'jpg' => 'fa-file-image text-info',
        'jpeg' => 'fa-file-image text-info',
        'png' => 'fa-file-image text-info',
        'gif' => 'fa-file-image text-info',
        'zip' => 'fa-file-archive text-secondary',
        'rar' => 'fa-file-archive text-secondary',
        'txt' => 'fa-file-alt text-muted',
    ];
    
    return $icons[$extension] ?? 'fa-file text-muted';
}

/**
 * Calculate reading time
 */
function calculateReadingTime($text, $wordsPerMinute = 200) {
    $wordCount = str_word_count(strip_tags($text));
    $minutes = ceil($wordCount / $wordsPerMinute);
    return $minutes . ' นาที';
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
    
    return $basename . '_' . time() . '_' . uniqid() . '.' . $extension;
}

/**
 * Log admin activity
 */
function logAdminActivity($action, $table = null, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        $activityLog = new ActivityLog();
        return $activityLog->log(
            $_SESSION['user_id'],
            $action,
            $table,
            $recordId,
            $oldValues,
            $newValues,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );
    } catch (Exception $e) {
        error_log("Admin activity log error: " . $e->getMessage());
        return false;
    }
}
?>