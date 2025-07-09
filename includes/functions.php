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
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate secure random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Redirect to URL
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Show alert message
 */
function showAlert($message, $type = 'info') {
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
}

/**
 * Get and clear alert message
 */
function getAlert() {
    if (isset($_SESSION['alert_message'])) {
        $alert = [
            'message' => $_SESSION['alert_message'],
            'type' => $_SESSION['alert_type'] ?? 'info'
        ];
        unset($_SESSION['alert_message'], $_SESSION['alert_type']);
        return $alert;
    }
    return null;
}

/**
 * Escape output for HTML
 */
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Debug function
 */
function dd($value) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
        exit;
    }
}