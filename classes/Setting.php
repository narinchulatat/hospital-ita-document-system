<?php
/**
 * Setting Class
 * Handles system configuration and settings management
 */

class Setting {
    private $db;
    private static $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get setting value
     */
    public function get($key, $default = null) {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $query = "SELECT value, data_type FROM settings WHERE setting_key = ?";
        $result = $this->db->fetch($query, [$key]);
        
        if (!$result) {
            self::$cache[$key] = $default;
            return $default;
        }
        
        $value = $this->castValue($result['value'], $result['data_type']);
        self::$cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Set setting value
     */
    public function set($key, $value, $description = null) {
        $dataType = $this->getDataType($value);
        $stringValue = $this->valueToString($value, $dataType);
        
        // Check if setting exists
        $existing = $this->db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        
        if ($existing) {
            // Update existing setting
            $result = $this->db->update('settings', [
                'value' => $stringValue,
                'data_type' => $dataType,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['setting_key' => $key]);
        } else {
            // Create new setting
            $result = $this->db->insert('settings', [
                'setting_key' => $key,
                'value' => $stringValue,
                'data_type' => $dataType,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        if ($result) {
            // Update cache
            self::$cache[$key] = $value;
            
            // Log activity
            logActivity(ACTION_UPDATE, 'settings', null, null, [$key => $value]);
        }
        
        return $result;
    }
    
    /**
     * Delete setting
     */
    public function delete($key) {
        $result = $this->db->delete('settings', ['setting_key' => $key]);
        
        if ($result) {
            // Remove from cache
            unset(self::$cache[$key]);
            
            // Log activity
            logActivity(ACTION_DELETE, 'settings', null, [$key => $this->get($key)]);
        }
        
        return $result;
    }
    
    /**
     * Get all settings
     */
    public function getAll($category = null) {
        $query = "SELECT * FROM settings";
        $params = [];
        
        if ($category) {
            $query .= " WHERE category = ?";
            $params[] = $category;
        }
        
        $query .= " ORDER BY category, setting_key";
        
        $settings = $this->db->fetchAll($query, $params);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = [
                'value' => $this->castValue($setting['value'], $setting['data_type']),
                'description' => $setting['description'],
                'category' => $setting['category'],
                'data_type' => $setting['data_type'],
                'updated_at' => $setting['updated_at']
            ];
        }
        
        return $result;
    }
    
    /**
     * Get settings by category
     */
    public function getByCategory($category) {
        return $this->getAll($category);
    }
    
    /**
     * Bulk update settings
     */
    public function bulkUpdate($settings) {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get data type of value
     */
    private function getDataType($value) {
        if (is_bool($value)) {
            return SETTING_BOOLEAN;
        } elseif (is_int($value)) {
            return SETTING_INTEGER;
        } elseif (is_array($value) || is_object($value)) {
            return SETTING_JSON;
        } else {
            return SETTING_STRING;
        }
    }
    
    /**
     * Convert value to string for storage
     */
    private function valueToString($value, $dataType) {
        switch ($dataType) {
            case SETTING_BOOLEAN:
                return $value ? '1' : '0';
            case SETTING_INTEGER:
                return (string)$value;
            case SETTING_JSON:
                return json_encode($value);
            default:
                return (string)$value;
        }
    }
    
    /**
     * Cast string value to appropriate type
     */
    private function castValue($stringValue, $dataType) {
        switch ($dataType) {
            case SETTING_BOOLEAN:
                return $stringValue === '1' || $stringValue === 'true';
            case SETTING_INTEGER:
                return (int)$stringValue;
            case SETTING_JSON:
                return json_decode($stringValue, true);
            default:
                return $stringValue;
        }
    }
    
    /**
     * Reset settings to default
     */
    public function resetToDefaults() {
        $defaults = $this->getDefaultSettings();
        
        $this->db->beginTransaction();
        
        try {
            // Clear existing settings
            $this->db->execute("DELETE FROM settings");
            
            // Insert defaults
            foreach ($defaults as $key => $setting) {
                $this->db->insert('settings', [
                    'setting_key' => $key,
                    'value' => $this->valueToString($setting['value'], $setting['data_type']),
                    'data_type' => $setting['data_type'],
                    'description' => $setting['description'],
                    'category' => $setting['category'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            
            // Clear cache
            self::$cache = [];
            
            // Log activity
            logActivity('reset_settings', 'settings', null, null, ['action' => 'reset_to_defaults']);
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get default settings
     */
    private function getDefaultSettings() {
        return [
            // General settings
            'site_name' => [
                'value' => 'ระบบจัดเก็บเอกสาร ITA โรงพยาบาล',
                'description' => 'ชื่อเว็บไซต์',
                'category' => 'general',
                'data_type' => SETTING_STRING
            ],
            'site_description' => [
                'value' => 'ระบบจัดการเอกสารสำหรับโรงพยาบาล',
                'description' => 'คำอธิบายเว็บไซต์',
                'category' => 'general',
                'data_type' => SETTING_STRING
            ],
            'admin_email' => [
                'value' => 'admin@hospital.com',
                'description' => 'อีเมลผู้ดูแลระบบ',
                'category' => 'general',
                'data_type' => SETTING_STRING
            ],
            'timezone' => [
                'value' => 'Asia/Bangkok',
                'description' => 'เขตเวลา',
                'category' => 'general',
                'data_type' => SETTING_STRING
            ],
            'date_format' => [
                'value' => 'd/m/Y',
                'description' => 'รูปแบบวันที่',
                'category' => 'general',
                'data_type' => SETTING_STRING
            ],
            'datetime_format' => [
                'value' => 'd/m/Y H:i:s',
                'description' => 'รูปแบบวันที่และเวลา',
                'category' => 'general',
                'data_type' => SETTING_STRING
            ],
            
            // Security settings
            'session_timeout' => [
                'value' => 3600,
                'description' => 'ระยะเวลา Session หมดอายุ (วินาที)',
                'category' => 'security',
                'data_type' => SETTING_INTEGER
            ],
            'max_login_attempts' => [
                'value' => 5,
                'description' => 'จำนวนครั้งสูงสุดในการ Login ที่ผิด',
                'category' => 'security',
                'data_type' => SETTING_INTEGER
            ],
            'lockout_duration' => [
                'value' => 900,
                'description' => 'ระยะเวลาล็อคบัญชี (วินาที)',
                'category' => 'security',
                'data_type' => SETTING_INTEGER
            ],
            'password_min_length' => [
                'value' => 8,
                'description' => 'ความยาวรหัสผ่านขั้นต่ำ',
                'category' => 'security',
                'data_type' => SETTING_INTEGER
            ],
            'require_strong_password' => [
                'value' => true,
                'description' => 'บังคับใช้รหัสผ่านที่แข็งแกร่ง',
                'category' => 'security',
                'data_type' => SETTING_BOOLEAN
            ],
            
            // File upload settings
            'max_file_size' => [
                'value' => 52428800,
                'description' => 'ขนาดไฟล์สูงสุด (ไบต์)',
                'category' => 'upload',
                'data_type' => SETTING_INTEGER
            ],
            'allowed_file_types' => [
                'value' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
                'description' => 'ประเภทไฟล์ที่อนุญาต',
                'category' => 'upload',
                'data_type' => SETTING_JSON
            ],
            'auto_generate_thumbnails' => [
                'value' => true,
                'description' => 'สร้าง Thumbnail อัตโนมัติสำหรับรูปภาพ',
                'category' => 'upload',
                'data_type' => SETTING_BOOLEAN
            ],
            
            // Email settings
            'smtp_host' => [
                'value' => 'localhost',
                'description' => 'SMTP Server',
                'category' => 'email',
                'data_type' => SETTING_STRING
            ],
            'smtp_port' => [
                'value' => 587,
                'description' => 'SMTP Port',
                'category' => 'email',
                'data_type' => SETTING_INTEGER
            ],
            'smtp_username' => [
                'value' => '',
                'description' => 'SMTP Username',
                'category' => 'email',
                'data_type' => SETTING_STRING
            ],
            'smtp_password' => [
                'value' => '',
                'description' => 'SMTP Password',
                'category' => 'email',
                'data_type' => SETTING_STRING
            ],
            'from_email' => [
                'value' => 'noreply@hospital.com',
                'description' => 'อีเมลผู้ส่ง',
                'category' => 'email',
                'data_type' => SETTING_STRING
            ],
            'from_name' => [
                'value' => 'ระบบจัดเก็บเอกสาร ITA',
                'description' => 'ชื่อผู้ส่ง',
                'category' => 'email',
                'data_type' => SETTING_STRING
            ],
            
            // Backup settings
            'backup_retention_days' => [
                'value' => 30,
                'description' => 'จำนวนวันเก็บไฟล์สำรอง',
                'category' => 'backup',
                'data_type' => SETTING_INTEGER
            ],
            'auto_backup_enabled' => [
                'value' => true,
                'description' => 'เปิดใช้งานการสำรองข้อมูลอัตโนมัติ',
                'category' => 'backup',
                'data_type' => SETTING_BOOLEAN
            ],
            'auto_backup_time' => [
                'value' => '02:00',
                'description' => 'เวลาสำรองข้อมูลอัตโนมัติ',
                'category' => 'backup',
                'data_type' => SETTING_STRING
            ],
            
            // Notification settings
            'notification_retention_days' => [
                'value' => 30,
                'description' => 'จำนวนวันเก็บการแจ้งเตือน',
                'category' => 'notification',
                'data_type' => SETTING_INTEGER
            ],
            'email_notifications_enabled' => [
                'value' => true,
                'description' => 'เปิดใช้งานการแจ้งเตือนทางอีเมล',
                'category' => 'notification',
                'data_type' => SETTING_BOOLEAN
            ],
            
            // Audit settings
            'audit_log_retention_days' => [
                'value' => 365,
                'description' => 'จำนวนวันเก็บ Audit Log',
                'category' => 'audit',
                'data_type' => SETTING_INTEGER
            ],
            'detailed_audit_logging' => [
                'value' => true,
                'description' => 'เปิดใช้งาน Audit Log แบบละเอียด',
                'category' => 'audit',
                'data_type' => SETTING_BOOLEAN
            ],
            
            // Display settings
            'items_per_page' => [
                'value' => 20,
                'description' => 'จำนวนรายการต่อหน้า',
                'category' => 'display',
                'data_type' => SETTING_INTEGER
            ],
            'show_file_sizes' => [
                'value' => true,
                'description' => 'แสดงขนาดไฟล์',
                'category' => 'display',
                'data_type' => SETTING_BOOLEAN
            ],
            'show_download_counts' => [
                'value' => true,
                'description' => 'แสดงจำนวนการดาวน์โหลด',
                'category' => 'display',
                'data_type' => SETTING_BOOLEAN
            ]
        ];
    }
    
    /**
     * Initialize default settings
     */
    public function initializeDefaults() {
        $defaults = $this->getDefaultSettings();
        $existingSettings = $this->getAll();
        
        $insertCount = 0;
        
        foreach ($defaults as $key => $setting) {
            if (!isset($existingSettings[$key])) {
                $this->db->insert('settings', [
                    'setting_key' => $key,
                    'value' => $this->valueToString($setting['value'], $setting['data_type']),
                    'data_type' => $setting['data_type'],
                    'description' => $setting['description'],
                    'category' => $setting['category'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $insertCount++;
            }
        }
        
        return $insertCount;
    }
    
    /**
     * Export settings
     */
    public function exportSettings($category = null) {
        $settings = $this->getAll($category);
        
        $filename = 'settings_export_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = TEMP_PATH . $filename;
        
        // Create temp directory if it doesn't exist
        if (!is_dir(TEMP_PATH)) {
            mkdir(TEMP_PATH, 0755, true);
        }
        
        $exportData = [
            'export_date' => date('Y-m-d H:i:s'),
            'category' => $category,
            'settings' => $settings
        ];
        
        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($filepath, $json) === false) {
            throw new Exception("ไม่สามารถสร้างไฟล์ส่งออกได้");
        }
        
        return $filepath;
    }
    
    /**
     * Import settings
     */
    public function importSettings($jsonFile) {
        if (!file_exists($jsonFile)) {
            throw new Exception("ไม่พบไฟล์การตั้งค่า");
        }
        
        $jsonContent = file_get_contents($jsonFile);
        $data = json_decode($jsonContent, true);
        
        if (!$data || !isset($data['settings'])) {
            throw new Exception("รูปแบบไฟล์การตั้งค่าไม่ถูกต้อง");
        }
        
        $this->db->beginTransaction();
        
        try {
            $importCount = 0;
            
            foreach ($data['settings'] as $key => $setting) {
                $this->set($key, $setting['value'], $setting['description']);
                $importCount++;
            }
            
            $this->db->commit();
            
            // Log activity
            logActivity('import_settings', 'settings', null, null, ['imported_count' => $importCount]);
            
            return $importCount;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get setting categories
     */
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM settings ORDER BY category";
        $results = $this->db->fetchAll($query);
        
        return array_column($results, 'category');
    }
    
    /**
     * Validate setting value
     */
    public function validateSetting($key, $value) {
        $validations = [
            'max_file_size' => function($val) {
                return is_numeric($val) && $val > 0 && $val <= 104857600; // Max 100MB
            },
            'session_timeout' => function($val) {
                return is_numeric($val) && $val >= 300 && $val <= 86400; // 5 mins to 24 hours
            },
            'max_login_attempts' => function($val) {
                return is_numeric($val) && $val >= 3 && $val <= 10;
            },
            'password_min_length' => function($val) {
                return is_numeric($val) && $val >= 6 && $val <= 32;
            },
            'admin_email' => function($val) {
                return filter_var($val, FILTER_VALIDATE_EMAIL);
            },
            'items_per_page' => function($val) {
                return is_numeric($val) && $val >= 5 && $val <= 100;
            }
        ];
        
        if (isset($validations[$key])) {
            return $validations[$key]($value);
        }
        
        return true; // No specific validation
    }
    
    /**
     * Clear cache
     */
    public function clearCache() {
        self::$cache = [];
    }
    
    /**
     * Get cached settings count
     */
    public function getCacheStats() {
        return [
            'cached_count' => count(self::$cache),
            'cached_keys' => array_keys(self::$cache)
        ];
    }
}