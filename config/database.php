<?php
/**
 * Database Configuration
 */

// Environment detection and database setup
$useTestDatabase = !empty($_ENV['USE_TEST_DB']) || !empty(getenv('USE_TEST_DB')) || !extension_loaded('mysql');

if ($useTestDatabase) {
    // SQLite for testing/development when MySQL is not available
    define('DB_TYPE', 'sqlite');
    define('DB_PATH', __DIR__ . '/../temp/test_database.sqlite');
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'ita_hospital_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8');
    define('DB_PORT', '3306');
} else {
    // MySQL/MariaDB for production
    define('DB_TYPE', 'mysql');
    define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'ita_hospital_db');
    define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root');
    define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '');
    define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4');
    define('DB_PORT', $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306');
}

// PDO Options
if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
    define('DB_OPTIONS', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30
    ]);
} else {
    define('DB_OPTIONS', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci",
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_PERSISTENT => false
    ]);
}

// Database connection function for testing
function testDatabaseConnection() {
    try {
        if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
            // Ensure directory exists
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $dsn = "sqlite:" . DB_PATH;
            $pdo = new PDO($dsn, null, null, DB_OPTIONS);
        } else {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        }
        
        // Test the connection
        $pdo->query("SELECT 1");
        
        return ['success' => true, 'pdo' => $pdo, 'type' => defined('DB_TYPE') ? DB_TYPE : 'mysql'];
    } catch (PDOException $e) {
        $error = "Database connection failed: " . $e->getMessage();
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
                $error .= "\nSQLite path: " . DB_PATH;
            } else {
                $error .= "\nDSN: mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
                $error .= "\nUser: " . DB_USER;
            }
        }
        return ['success' => false, 'error' => $error, 'type' => defined('DB_TYPE') ? DB_TYPE : 'mysql'];
    }
}