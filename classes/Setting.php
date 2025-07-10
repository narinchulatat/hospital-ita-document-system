<?php
/**
 * Setting Class
 * Handles system settings management
 */

class Setting {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get setting value by key
     */
    public function get($keyName, $defaultValue = null) {
        $query = "SELECT value, data_type FROM settings WHERE key_name = ?";
        $result = $this->db->fetch($query, [$keyName]);
        
        if (!$result) {
            return $defaultValue;
        }
        
        // Convert value based on data type
        return $this->convertValue($result['value'], $result['data_type']);
    }
    
    /**
     * Set setting value
     */
    public function set($keyName, $value, $dataType = 'string', $category = 'general', $description = null, $isPublic = false, $updatedBy = null) {
        // Check if setting exists
        $existing = $this->db->fetch("SELECT id FROM settings WHERE key_name = ?", [$keyName]);
        
        $data = [
            'value' => $this->prepareValue($value, $dataType),
            'data_type' => $dataType,
            'category' => $category,
            'description' => $description,
            'is_public' => $isPublic ? 1 : 0,
            'updated_by' => $updatedBy
        ];
        
        if ($existing) {
            // Update existing setting
            return $this->db->update('settings', $data, ['key_name' => $keyName]);
        } else {
            // Create new setting
            $data['key_name'] = $keyName;
            return $this->db->insert('settings', $data);
        }
    }
    
    /**
     * Get settings by category
     */
    public function getByCategory($category, $publicOnly = false) {
        $query = "SELECT * FROM settings WHERE category = ?";
        $params = [$category];
        
        if ($publicOnly) {
            $query .= " AND is_public = 1";
        }
        
        $query .= " ORDER BY key_name";
        
        $settings = $this->db->fetchAll($query, $params);
        
        // Convert values and create key-value array
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key_name']] = [
                'value' => $this->convertValue($setting['value'], $setting['data_type']),
                'data_type' => $setting['data_type'],
                'description' => $setting['description'],
                'is_public' => $setting['is_public']
            ];
        }
        
        return $result;
    }
    
    /**
     * Get all public settings
     */
    public function getPublic() {
        $query = "SELECT key_name, value, data_type FROM settings WHERE is_public = 1 ORDER BY category, key_name";
        $settings = $this->db->fetchAll($query);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key_name']] = $this->convertValue($setting['value'], $setting['data_type']);
        }
        
        return $result;
    }
    
    /**
     * Get all settings
     */
    public function getAll() {
        $query = "SELECT * FROM settings ORDER BY category, key_name";
        $settings = $this->db->fetchAll($query);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key_name']] = [
                'value' => $this->convertValue($setting['value'], $setting['data_type']),
                'data_type' => $setting['data_type'],
                'category' => $setting['category'],
                'description' => $setting['description'],
                'is_public' => $setting['is_public'],
                'updated_by' => $setting['updated_by'],
                'updated_at' => $setting['updated_at']
            ];
        }
        
        return $result;
    }
    
    /**
     * Delete setting
     */
    public function delete($keyName) {
        return $this->db->delete('settings', ['key_name' => $keyName]);
    }
    
    /**
     * Reset setting to default
     */
    public function reset($keyName, $defaultValue = null) {
        if ($defaultValue !== null) {
            return $this->set($keyName, $defaultValue);
        } else {
            return $this->delete($keyName);
        }
    }
    
    /**
     * Validate setting value
     */
    public function validate($keyName, $value, $dataType) {
        switch ($dataType) {
            case 'integer':
                return is_numeric($value) && is_int($value + 0);
                
            case 'boolean':
                return is_bool($value) || in_array(strtolower($value), ['true', 'false', '1', '0']);
                
            case 'text':
            case 'string':
                return is_string($value);
                
            default:
                return true;
        }
    }
    
    /**
     * Get setting categories
     */
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM settings ORDER BY category";
        $categories = $this->db->fetchAll($query);
        
        return array_column($categories, 'category');
    }
    
    /**
     * Bulk update settings
     */
    public function bulkUpdate($settings, $updatedBy = null) {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $keyName => $data) {
                $value = $data['value'] ?? $data;
                $dataType = $data['data_type'] ?? 'string';
                $category = $data['category'] ?? 'general';
                $description = $data['description'] ?? null;
                $isPublic = $data['is_public'] ?? false;
                
                $this->set($keyName, $value, $dataType, $category, $description, $isPublic, $updatedBy);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Convert database value to proper type
     */
    private function convertValue($value, $dataType) {
        if ($value === null) {
            return null;
        }
        
        switch ($dataType) {
            case 'integer':
                return (int) $value;
                
            case 'boolean':
                return $value === '1' || $value === 'true' || $value === true;
                
            case 'text':
            case 'string':
            default:
                return $value;
        }
    }
    
    /**
     * Prepare value for database storage
     */
    private function prepareValue($value, $dataType) {
        switch ($dataType) {
            case 'boolean':
                return $value ? '1' : '0';
                
            case 'integer':
                return (string) $value;
                
            case 'text':
            case 'string':
            default:
                return (string) $value;
        }
    }
    
    /**
     * Initialize default settings
     */
    public function initializeDefaults() {
        $defaults = [
            'site_name' => [
                'value' => 'Hospital ITA Document System',
                'data_type' => 'string',
                'category' => 'general',
                'description' => 'Site name',
                'is_public' => true
            ],
            'site_description' => [
                'value' => 'Document Management System for Hospital ITA',
                'data_type' => 'text',
                'category' => 'general',
                'description' => 'Site description',
                'is_public' => true
            ],
            'max_file_size' => [
                'value' => '52428800', // 50MB
                'data_type' => 'integer',
                'category' => 'uploads',
                'description' => 'Maximum file size in bytes',
                'is_public' => false
            ],
            'allowed_file_types' => [
                'value' => 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'data_type' => 'string',
                'category' => 'uploads',
                'description' => 'Allowed file types (comma-separated)',
                'is_public' => false
            ],
            'backup_retention_days' => [
                'value' => '30',
                'data_type' => 'integer',
                'category' => 'backup',
                'description' => 'Number of days to retain backups',
                'is_public' => false
            ],
            'enable_virus_scan' => [
                'value' => '1',
                'data_type' => 'boolean',
                'category' => 'security',
                'description' => 'Enable virus scanning for uploads',
                'is_public' => false
            ]
        ];
        
        foreach ($defaults as $key => $setting) {
            // Only create if doesn't exist
            $existing = $this->db->fetch("SELECT id FROM settings WHERE key_name = ?", [$key]);
            if (!$existing) {
                $this->set(
                    $key,
                    $setting['value'],
                    $setting['data_type'],
                    $setting['category'],
                    $setting['description'],
                    $setting['is_public']
                );
            }
        }
    }
    
    /**
     * Get setting by key name
     */
    public function getByKey($keyName) {
        $query = "SELECT * FROM settings WHERE key_name = ?";
        return $this->db->fetch($query, [$keyName]);
    }
    
    /**
     * Get setting by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM settings WHERE id = ?";
        return $this->db->fetch($query, [$id]);
    }
    
    /**
     * Create new setting
     */
    public function create($data) {
        $data['value'] = $this->prepareValue($data['value'], $data['data_type']);
        
        $settingId = $this->db->insert('settings', $data);
        
        // Log activity
        logActivity(ACTION_CREATE, 'settings', $settingId, null, $data);
        
        return $settingId;
    }
    
    /**
     * Update setting
     */
    public function update($id, $data) {
        $oldData = $this->getById($id);
        
        if (isset($data['value'])) {
            $data['value'] = $this->prepareValue($data['value'], $data['data_type'] ?? $oldData['data_type']);
        }
        
        $result = $this->db->update('settings', $data, ['id' => $id]);
        
        if ($result) {
            // Log activity
            logActivity(ACTION_UPDATE, 'settings', $id, $oldData, $data);
        }
        
        return $result;
    }
    
    /**
     * Delete setting by ID
     */
    public function deleteById($id) {
        $setting = $this->getById($id);
        
        if (!$setting) {
            return false;
        }
        
        $result = $this->db->delete('settings', ['id' => $id]);
        
        if ($result) {
            // Log activity
            logActivity(ACTION_DELETE, 'settings', $id, $setting, null);
        }
        
        return $result;
    }
}