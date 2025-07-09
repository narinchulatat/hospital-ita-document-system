<?php
/**
 * Database Initialization Script
 * Creates tables and sample data for development/testing
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

echo "=== Database Initialization ===\n\n";

try {
    $db = Database::getInstance();
    $dbType = $db->getDatabaseType();
    
    echo "Database type: " . $dbType . "\n";
    
    if ($dbType === 'sqlite') {
        echo "Initializing SQLite database...\n";
        initializeSQLite($db);
    } else {
        echo "Initializing MySQL database...\n";
        initializeMySQL($db);
    }
    
    echo "\n✅ Database initialization completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Database initialization failed: " . $e->getMessage() . "\n";
    exit(1);
}

function initializeSQLite($db) {
    // Create basic tables for SQLite
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                role_id INTEGER NOT NULL DEFAULT 2,
                status VARCHAR(20) DEFAULT 'active',
                failed_attempts INTEGER DEFAULT 0,
                locked_until DATETIME NULL,
                last_login DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ",
        'roles' => "
            CREATE TABLE IF NOT EXISTS roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(50) NOT NULL UNIQUE,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ",
        'categories' => "
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                parent_id INTEGER NULL,
                level INTEGER NOT NULL DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_by INTEGER NULL,
                updated_by INTEGER NULL,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
            )
        ",
        'documents' => "
            CREATE TABLE IF NOT EXISTS documents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(500) NOT NULL,
                description TEXT,
                category_id INTEGER NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size BIGINT,
                file_type VARCHAR(50),
                status VARCHAR(20) DEFAULT 'draft',
                uploaded_by INTEGER NOT NULL,
                approved_by INTEGER NULL,
                approved_at DATETIME NULL,
                rejection_reason TEXT NULL,
                view_count INTEGER DEFAULT 0,
                download_count INTEGER DEFAULT 0,
                is_featured BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id),
                FOREIGN KEY (uploaded_by) REFERENCES users(id),
                FOREIGN KEY (approved_by) REFERENCES users(id)
            )
        ",
        'settings' => "
            CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                `key` VARCHAR(100) NOT NULL UNIQUE,
                `value` TEXT,
                `type` VARCHAR(20) DEFAULT 'string',
                description TEXT,
                updated_by INTEGER NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (updated_by) REFERENCES users(id)
            )
        ",
        'notifications' => "
            CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                type VARCHAR(20) DEFAULT 'info',
                action_url VARCHAR(500) NULL,
                is_read BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        'activity_logs' => "
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                action VARCHAR(100) NOT NULL,
                table_name VARCHAR(100) NULL,
                record_id INTEGER NULL,
                old_values TEXT NULL,
                new_values TEXT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        "
    ];
    
    foreach ($tables as $tableName => $sql) {
        echo "Creating table: $tableName\n";
        $db->execute($sql);
    }
    
    // Insert sample data
    insertSampleData($db);
}

function initializeMySQL($db) {
    // For MySQL, we'll use the existing SQL file
    $sqlFile = __DIR__ . '/ita_hospital_db.sql';
    if (file_exists($sqlFile)) {
        echo "Executing SQL file: $sqlFile\n";
        $sql = file_get_contents($sqlFile);
        
        // Split by semicolon and execute each statement
        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $db->execute($statement);
                } catch (Exception $e) {
                    // Skip errors for existing tables, etc.
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }
}

function insertSampleData($db) {
    echo "Inserting sample data...\n";
    
    // Insert roles
    $roles = [
        [1, 'ผู้ดูแลระบบ', 'สิทธิ์เต็มในการจัดการระบบ'],
        [2, 'เจ้าหน้าที่', 'สิทธิ์ในการจัดการเอกสาร'],
        [3, 'ผู้อนุมัติ', 'สิทธิ์ในการอนุมัติเอกสาร'],
        [4, 'ผู้เยี่ยมชม', 'สิทธิ์ในการดูเอกสารที่เผยแพร่']
    ];
    
    foreach ($roles as $role) {
        try {
            $db->execute("INSERT OR IGNORE INTO roles (id, name, description) VALUES (?, ?, ?)", $role);
        } catch (Exception $e) {
            // Role already exists
        }
    }
    
    // Insert admin user
    $adminExists = $db->fetch("SELECT id FROM users WHERE username = 'admin'");
    if (!$adminExists) {
        $adminData = [
            'username' => 'admin',
            'email' => 'admin@hospital.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'first_name' => 'ผู้ดูแลระบบ',
            'last_name' => 'หลัก',
            'role_id' => 1,
            'status' => 'active'
        ];
        
        $db->insert('users', $adminData);
        echo "Created admin user (username: admin, password: admin123)\n";
    }
    
    // Insert sample categories
    $categories = [
        ['เอกสารบริหาร', 'เอกสารเกี่ยวกับการบริหารจัดการ', null, 1, 1],
        ['เอกสารการเงิน', 'เอกสารเกี่ยวกับการเงินและบัญชี', null, 1, 2],
        ['เอกสารทางการแพทย์', 'เอกสารเกี่ยวกับการแพทย์และการรักษา', null, 1, 3]
    ];
    
    foreach ($categories as $category) {
        try {
            $db->execute("INSERT OR IGNORE INTO categories (name, description, parent_id, level, sort_order) VALUES (?, ?, ?, ?, ?)", $category);
        } catch (Exception $e) {
            // Category already exists
        }
    }
    
    // Insert basic settings
    $settings = [
        ['site_name', 'ระบบจัดเก็บเอกสาร ITA โรงพยาบาล', 'string', 'ชื่อเว็บไซต์'],
        ['site_description', 'ระบบจัดการเอกสารสำหรับโรงพยาบาล', 'string', 'คำอธิบายเว็บไซต์'],
        ['maintenance_mode', '0', 'boolean', 'โหมดปิดปรับปรุง'],
        ['max_file_size', '52428800', 'integer', 'ขนาดไฟล์สูงสุด (bytes)']
    ];
    
    foreach ($settings as $setting) {
        try {
            $db->execute("INSERT OR IGNORE INTO settings (`key`, `value`, `type`, description) VALUES (?, ?, ?, ?)", $setting);
        } catch (Exception $e) {
            // Setting already exists
        }
    }
}