<?php

/**
 * Setting Model
 * 
 * Handles system settings data operations
 */
class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = ['key', 'value', 'category', 'type', 'description', 'is_public'];
    
    /**
     * Get setting by key
     */
    public function getSetting($key, $default = null)
    {
        $setting = $this->findBy('key', $key);
        
        if (!$setting) {
            return $default;
        }
        
        return $this->castValue($setting['value'], $setting['type']);
    }
    
    /**
     * Update or create setting
     */
    public function updateSetting($key, $value, $category = 'general', $type = 'string', $description = null)
    {
        $setting = $this->findBy('key', $key);
        
        if ($setting) {
            return $this->update($setting['id'], ['value' => $value]);
        } else {
            return $this->create([
                'key' => $key,
                'value' => $value,
                'category' => $category,
                'type' => $type,
                'description' => $description
            ]);
        }
    }
    
    /**
     * Get settings by category
     */
    public function getByCategory($category)
    {
        return $this->where('category = :category', ['category' => $category], 'key ASC');
    }
    
    /**
     * Get public settings
     */
    public function getPublicSettings()
    {
        $settings = $this->where('is_public = :public', ['public' => 1]);
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        
        return $result;
    }
    
    /**
     * Get all settings as key-value pairs
     */
    public function getAllSettings()
    {
        $settings = $this->all();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        
        return $result;
    }
    
    /**
     * Get settings grouped by category
     */
    public function getGroupedSettings()
    {
        $settings = $this->all('category ASC, key ASC');
        $grouped = [];
        
        foreach ($settings as $setting) {
            $category = $setting['category'] ?? 'general';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            
            $setting['value'] = $this->castValue($setting['value'], $setting['type']);
            $grouped[$category][] = $setting;
        }
        
        return $grouped;
    }
    
    /**
     * Cast value to appropriate type
     */
    private function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            case 'string':
            default:
                return $value;
        }
    }
    
    /**
     * Delete setting
     */
    public function deleteSetting($key)
    {
        return $this->db->delete($this->table, 'key = :key', ['key' => $key]);
    }
    
    /**
     * Check if setting exists
     */
    public function settingExists($key)
    {
        return $this->exists('key = :key', ['key' => $key]);
    }
    
    /**
     * Get setting categories
     */
    public function getCategories()
    {
        $sql = "SELECT DISTINCT category FROM {$this->table} WHERE category IS NOT NULL ORDER BY category";
        $result = $this->db->fetchAll($sql);
        return array_column($result, 'category');
    }
    
    /**
     * Bulk update settings
     */
    public function bulkUpdate($settings)
    {
        $this->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $this->updateSetting($key, $value);
            }
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Create default settings
     */
    public function createDefaults()
    {
        $defaults = [
            // General settings
            ['key' => 'app_name', 'value' => 'College ERP', 'category' => 'general', 'type' => 'string', 'description' => 'Application Name', 'is_public' => 1],
            ['key' => 'app_logo', 'value' => 'logo.png', 'category' => 'general', 'type' => 'string', 'description' => 'Application Logo', 'is_public' => 1],
            ['key' => 'app_favicon', 'value' => 'favicon.ico', 'category' => 'general', 'type' => 'string', 'description' => 'Application Favicon', 'is_public' => 1],
            ['key' => 'app_timezone', 'value' => 'UTC', 'category' => 'general', 'type' => 'string', 'description' => 'Application Timezone'],
            ['key' => 'app_language', 'value' => 'en', 'category' => 'general', 'type' => 'string', 'description' => 'Default Language', 'is_public' => 1],
            
            // Academic settings
            ['key' => 'current_session', 'value' => '2024-25', 'category' => 'academic', 'type' => 'string', 'description' => 'Current Academic Session'],
            ['key' => 'session_start_month', 'value' => '4', 'category' => 'academic', 'type' => 'integer', 'description' => 'Session Start Month'],
            ['key' => 'session_end_month', 'value' => '3', 'category' => 'academic', 'type' => 'integer', 'description' => 'Session End Month'],
            ['key' => 'working_days', 'value' => 'monday,tuesday,wednesday,thursday,friday,saturday', 'category' => 'academic', 'type' => 'string', 'description' => 'Working Days'],
            ['key' => 'class_duration', 'value' => '45', 'category' => 'academic', 'type' => 'integer', 'description' => 'Class Duration (minutes)'],
            
            // Attendance settings
            ['key' => 'attendance_required', 'value' => '75', 'category' => 'attendance', 'type' => 'integer', 'description' => 'Minimum Attendance Required (%)'],
            ['key' => 'late_arrival_time', 'value' => '15', 'category' => 'attendance', 'type' => 'integer', 'description' => 'Late Arrival Time (minutes)'],
            ['key' => 'attendance_sms', 'value' => '1', 'category' => 'attendance', 'type' => 'boolean', 'description' => 'Send Attendance SMS'],
            
            // Fee settings
            ['key' => 'late_fee_percentage', 'value' => '2', 'category' => 'fees', 'type' => 'float', 'description' => 'Late Fee Percentage'],
            ['key' => 'grace_period_days', 'value' => '7', 'category' => 'fees', 'type' => 'integer', 'description' => 'Grace Period (days)'],
            ['key' => 'fee_reminder_days', 'value' => '3', 'category' => 'fees', 'type' => 'integer', 'description' => 'Fee Reminder Days'],
            
            // Email settings
            ['key' => 'mail_driver', 'value' => 'smtp', 'category' => 'email', 'type' => 'string', 'description' => 'Mail Driver'],
            ['key' => 'mail_host', 'value' => 'smtp.gmail.com', 'category' => 'email', 'type' => 'string', 'description' => 'SMTP Host'],
            ['key' => 'mail_port', 'value' => '587', 'category' => 'email', 'type' => 'integer', 'description' => 'SMTP Port'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'category' => 'email', 'type' => 'string', 'description' => 'Mail Encryption'],
            
            // SMS settings
            ['key' => 'sms_driver', 'value' => 'twilio', 'category' => 'sms', 'type' => 'string', 'description' => 'SMS Driver'],
            ['key' => 'sms_enabled', 'value' => '1', 'category' => 'sms', 'type' => 'boolean', 'description' => 'SMS Enabled'],
            
            // Security settings
            ['key' => 'password_min_length', 'value' => '8', 'category' => 'security', 'type' => 'integer', 'description' => 'Minimum Password Length'],
            ['key' => 'session_timeout', 'value' => '3600', 'category' => 'security', 'type' => 'integer', 'description' => 'Session Timeout (seconds)'],
            ['key' => 'max_login_attempts', 'value' => '5', 'category' => 'security', 'type' => 'integer', 'description' => 'Maximum Login Attempts'],
            
            // Backup settings
            ['key' => 'backup_enabled', 'value' => '1', 'category' => 'backup', 'type' => 'boolean', 'description' => 'Backup Enabled'],
            ['key' => 'backup_frequency', 'value' => 'daily', 'category' => 'backup', 'type' => 'string', 'description' => 'Backup Frequency'],
            ['key' => 'backup_retention_days', 'value' => '30', 'category' => 'backup', 'type' => 'integer', 'description' => 'Backup Retention (days)']
        ];
        
        foreach ($defaults as $setting) {
            if (!$this->settingExists($setting['key'])) {
                $this->create($setting);
            }
        }
    }
    
    /**
     * Check if setting exists
     */
    private function settingExists($key)
    {
        return $this->exists('key = :key', ['key' => $key]);
    }
}