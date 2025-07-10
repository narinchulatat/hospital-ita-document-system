<?php
/**
 * Common Functions
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log(1024));
    
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

/**
 * Format date in Thai format
 */
function formatThaiDate($date, $includeTime = false) {
    if (!$date) return '-';
    
    $thaiMonths = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    $day = date('j', $timestamp);
    $month = $thaiMonths[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;
    
    $result = $day . ' ' . $month . ' ' . $year;
    
    if ($includeTime) {
        $result .= ' เวลา ' . date('H:i', $timestamp) . ' น.';
    }
    
    return $result;
}

/**
 * Get file type icon class
 */
function getFileTypeIcon($fileType) {
    global $FILE_TYPE_ICONS;
    return $FILE_TYPE_ICONS[$fileType] ?? $FILE_TYPE_ICONS['default'];
}

/**
 * Validate file upload
 */
function validateFileUpload($file) {
    $errors = [];
    
    // Check if file was uploaded
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
                $errors[] = 'ไม่ได้เลือกไฟล์';
                break;
            default:
                $errors[] = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
        }
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'ไฟล์มีขนาดใหญ่เกิน ' . formatFileSize(MAX_FILE_SIZE);
    }
    
    // Check file type
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
        $errors[] = 'ประเภทไฟล์ไม่ได้รับอนุญาต อนุญาตเฉพาะ: ' . implode(', ', ALLOWED_FILE_TYPES);
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        $errors[] = 'ประเภทไฟล์ไม่ถูกต้อง';
    }
    
    return $errors;
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid('doc_') . '_' . time() . '.' . $ext;
}

/**
 * Log activity
 */
function logActivity($action, $tableName, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        $db = Database::getInstance();
        
        $data = [
            'user_id' => getCurrentUserId(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $db->insert('activity_logs', $data);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Send notification
 */
function sendNotification($userId, $title, $message, $type = NOTIF_TYPE_INFO, $actionUrl = null) {
    try {
        $db = Database::getInstance();
        
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl
        ];
        
        return $db->insert('notifications', $data);
    } catch (Exception $e) {
        error_log("Failed to send notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get setting value
 */
function getSetting($key, $default = null) {
    static $settings = null;
    
    if ($settings === null) {
        try {
            $db = Database::getInstance();
            $allSettings = $db->fetchAll("SELECT `key`, `value`, `type` FROM settings");
            
            foreach ($allSettings as $setting) {
                $value = $setting['value'];
                
                // Convert value based on type
                switch ($setting['type']) {
                    case SETTING_INTEGER:
                        $value = (int)$value;
                        break;
                    case SETTING_BOOLEAN:
                        $value = (bool)$value;
                        break;
                    case SETTING_JSON:
                        $value = json_decode($value, true);
                        break;
                }
                
                $settings[$setting['key']] = $value;
            }
        } catch (Exception $e) {
            error_log("Failed to load settings: " . $e->getMessage());
            $settings = [];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Update setting value
 */
function updateSetting($key, $value) {
    try {
        $db = Database::getInstance();
        
        // Get setting type
        $setting = $db->fetch("SELECT `type` FROM settings WHERE `key` = ?", [$key]);
        if (!$setting) {
            return false;
        }
        
        // Convert value based on type
        switch ($setting['type']) {
            case SETTING_JSON:
                $value = json_encode($value);
                break;
            case SETTING_BOOLEAN:
                $value = $value ? 1 : 0;
                break;
        }
        
        $data = [
            'value' => $value,
            'updated_by' => getCurrentUserId()
        ];
        
        return $db->update('settings', $data, ['key' => $key]);
    } catch (Exception $e) {
        error_log("Failed to update setting: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Pagination" class="flex justify-center mt-6">';
    $html .= '<div class="flex space-x-1">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">ก่อนหน้า</a>';
    } else {
        $html .= '<span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 rounded-l-md">ก่อนหน้า</span>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<a href="' . $baseUrl . '?page=1" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">1</a>';
        if ($start > 2) {
            $html .= '<span class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">' . $i . '</a>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<span class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">...</span>';
        }
        $html .= '<a href="' . $baseUrl . '?page=' . $totalPages . '" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">' . $totalPages . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">ถัดไป</a>';
    } else {
        $html .= '<span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 rounded-r-md">ถัดไป</span>';
    }
    
    $html .= '</div></nav>';
    
    return $html;
}

/**
 * Check if string is JSON
 */
function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Get file type CSS class for styling
 */
function getFileTypeClass($fileType) {
    $classes = [
        'pdf' => 'pdf',
        'doc' => 'doc',
        'docx' => 'doc',
        'xls' => 'excel',
        'xlsx' => 'excel',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image'
    ];
    
    return $classes[strtolower($fileType)] ?? 'default';
}

/**
 * Check if user can approve documents (approver role)
 */
function canApproveDocuments() {
    return isApprover() || isAdmin();
}

/**
 * Get document status badge HTML
 */
function getDocumentStatusBadge($status) {
    global $DOC_STATUS_NAMES;
    
    $classes = [
        DOC_STATUS_DRAFT => 'badge-secondary',
        DOC_STATUS_PENDING => 'badge-warning',
        DOC_STATUS_APPROVED => 'badge-success',
        DOC_STATUS_REJECTED => 'badge-danger',
        DOC_STATUS_ARCHIVED => 'badge-info'
    ];
    
    $class = $classes[$status] ?? 'badge-secondary';
    $text = $DOC_STATUS_NAMES[$status] ?? $status;
    
    return '<span class="badge ' . $class . '">' . htmlspecialchars($text) . '</span>';
}

/**
 * Check if document needs urgent attention
 */
function isDocumentUrgent($document) {
    // Check if marked as urgent
    if (isset($document['is_urgent']) && $document['is_urgent']) {
        return true;
    }
    
    // Check if pending for more than 7 days
    if ($document['status'] === DOC_STATUS_PENDING) {
        $daysPending = floor((time() - strtotime($document['created_at'])) / 86400);
        return $daysPending > 7;
    }
    
    return false;
}

/**
 * Get time difference in human readable format
 */
function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'เมื่อสักครู่';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' นาทีที่แล้ว';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' ชั่วโมงที่แล้ว';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' วันที่แล้ว';
    } else {
        return formatThaiDate($datetime);
    }
}

/**
 * Truncate text with ellipsis
 */
function truncateText($text, $length = 100, $ellipsis = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $ellipsis;
}

/**
 * Generate breadcrumb navigation
 */
function generateBreadcrumbs($items) {
    if (empty($items)) {
        return '';
    }
    
    $html = '<nav class="flex mb-6" aria-label="Breadcrumb">';
    $html .= '<ol class="inline-flex items-center space-x-1 md:space-x-3">';
    
    foreach ($items as $index => $item) {
        $isLast = $index === count($items) - 1;
        
        $html .= '<li class="inline-flex items-center">';
        
        if ($index > 0) {
            $html .= '<svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>';
        }
        
        if ($isLast) {
            $html .= '<span class="text-gray-500 ml-1 md:ml-2 text-sm font-medium">' . htmlspecialchars($item['title']) . '</span>';
        } else {
            $html .= '<a href="' . htmlspecialchars($item['url']) . '" class="text-gray-700 hover:text-blue-600 ml-1 md:ml-2 text-sm font-medium">' . htmlspecialchars($item['title']) . '</a>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ol></nav>';
    
    return $html;
}