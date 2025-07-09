<?php
/**
 * System Health Check Script
 * Run this after deployment to verify system is working
 */

// Set content type for better display
header('Content-Type: text/plain; charset=utf-8');

echo "=== Hospital ITA Document System Health Check ===\n\n";

// Check if we can include core files
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/constants.php';
    require_once __DIR__ . '/includes/functions.php';
    echo "✅ Core configuration files loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Failed to load core files: " . $e->getMessage() . "\n";
    exit(1);
}

// Check database connection
try {
    require_once __DIR__ . '/classes/Database.php';
    $db = Database::getInstance();
    echo "✅ Database connection successful\n";
    
    // Test basic query
    $result = $db->fetch("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    if ($result && $result['count'] > 0) {
        echo "✅ Admin user exists in database\n";
    } else {
        echo "⚠️  Admin user not found in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "   Please check your database configuration in config/database.php\n";
}

// Check directory permissions
$directories = [
    'uploads/documents' => 'File uploads',
    'backups' => 'Database backups', 
    'temp' => 'Temporary files'
];

echo "\n--- Directory Permissions ---\n";
foreach ($directories as $dir => $purpose) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        if (is_writable($fullPath)) {
            echo "✅ $dir ($purpose)\n";
        } else {
            echo "⚠️  $dir exists but is not writable ($purpose)\n";
        }
    } else {
        echo "❌ $dir does not exist ($purpose)\n";
    }
}

// Check key configuration values
echo "\n--- Configuration ---\n";
echo "Site Name: " . SITE_NAME . "\n";
echo "Base URL: " . BASE_URL . "\n";
echo "Debug Mode: " . (DEBUG_MODE ? 'ON' : 'OFF') . "\n";
echo "Session Timeout: " . SESSION_TIMEOUT . " seconds\n";

// Basic security check
echo "\n--- Security ---\n";
if (defined('CSRF_TOKEN_EXPIRE')) {
    echo "✅ CSRF protection configured\n";
} else {
    echo "⚠️  CSRF token expiration not configured\n";
}

if (MAX_LOGIN_ATTEMPTS > 0) {
    echo "✅ Login attempt limiting enabled ($MAX_LOGIN_ATTEMPTS attempts)\n";
} else {
    echo "⚠️  Login attempt limiting disabled\n";
}

echo "\n=== Health Check Complete ===\n";
echo "If you see any ❌ or ⚠️  items, please address them before production use.\n";
echo "\nDefault admin credentials:\n";
echo "Username: admin\n";
echo "Password: admin123\n";
echo "⚠️  IMPORTANT: Change the default password after first login!\n";
?>