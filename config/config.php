<?php
/**
 * Application Configuration
 */

// Site Settings
define('SITE_NAME', 'ระบบจัดเก็บเอกสาร ITA โรงพยาบาล');
define('SITE_DESCRIPTION', 'ระบบจัดการเอกสารสำหรับโรงพยาบาล');
define('SITE_VERSION', '1.0.0');

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/documents/');
define('BACKUP_PATH', ROOT_PATH . '/backups/');
define('TEMP_PATH', ROOT_PATH . '/temp/');

// URL paths
define('BASE_URL', 'http://localhost/hospital-ita-document-system');
define('UPLOAD_URL', BASE_URL . '/uploads/documents/');
define('ASSETS_URL', BASE_URL . '/assets/');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes
define('CSRF_TOKEN_EXPIRE', 3600);

// File upload settings
define('MAX_FILE_SIZE', 52428800); // 50MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);
define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png'
]);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Email settings
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@hospital.com');
define('FROM_NAME', SITE_NAME);

// Error reporting
if (defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Include database connection
require_once __DIR__ . '/database.php';