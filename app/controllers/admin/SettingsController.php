<?php

/**
 * Settings Management Controller
 * 
 * Handles system settings and configuration
 */
class SettingsController extends Controller
{
    private $settingModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(ROLE_ADMIN);
        
        $this->settingModel = new Setting();
    }
    
    /**
     * Display settings dashboard
     */
    public function index()
    {
        $settings = $this->settingModel->all();
        $groupedSettings = $this->groupSettingsByCategory($settings);
        
        $this->render('admin/settings/index', [
            'title' => 'System Settings',
            'settings' => $groupedSettings
        ], 'admin');
    }
    
    /**
     * Update settings
     */
    public function update()
    {
        $settings = $this->input('settings', []);
        
        if (empty($settings)) {
            $this->flash('error', 'No settings to update');
            $this->back();
            return;
        }
        
        try {
            $this->settingModel->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $this->settingModel->updateSetting($key, $value);
            }
            
            $this->settingModel->commit();
            
            // Clear settings cache
            $this->clearCache('settings');
            
            $this->logActivity('settings_updated', 'Updated system settings', $settings);
            $this->flash('success', 'Settings updated successfully');
            
        } catch (Exception $e) {
            $this->settingModel->rollback();
            Logger::error('Settings update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update settings');
        }
        
        $this->redirect('/admin/settings');
    }
    
    /**
     * Show general settings
     */
    public function general()
    {
        $settings = $this->settingModel->getByCategory('general');
        
        $this->render('admin/settings/general', [
            'title' => 'General Settings',
            'settings' => $settings
        ], 'admin');
    }
    
    /**
     * Show academic settings
     */
    public function academic()
    {
        $settings = $this->settingModel->getByCategory('academic');
        
        $this->render('admin/settings/academic', [
            'title' => 'Academic Settings',
            'settings' => $settings
        ], 'admin');
    }
    
    /**
     * Show email settings
     */
    public function email()
    {
        $settings = $this->settingModel->getByCategory('email');
        
        $this->render('admin/settings/email', [
            'title' => 'Email Settings',
            'settings' => $settings
        ], 'admin');
    }
    
    /**
     * Show SMS settings
     */
    public function sms()
    {
        $settings = $this->settingModel->getByCategory('sms');
        
        $this->render('admin/settings/sms', [
            'title' => 'SMS Settings',
            'settings' => $settings
        ], 'admin');
    }
    
    /**
     * Show payment settings
     */
    public function payment()
    {
        $settings = $this->settingModel->getByCategory('payment');
        
        $this->render('admin/settings/payment', [
            'title' => 'Payment Settings',
            'settings' => $settings
        ], 'admin');
    }
    
    /**
     * Test email configuration
     */
    public function testEmail()
    {
        $email = $this->input('email');
        
        if (empty($email)) {
            return $this->json(['success' => false, 'message' => 'Email address required']);
        }
        
        try {
            $mailer = new Mailer();
            $result = $mailer->send($email, 'Test Email', 'test_email', [
                'test_time' => now(),
                'sender' => $this->user()['name']
            ]);
            
            if ($result) {
                return $this->json(['success' => true, 'message' => 'Test email sent successfully']);
            } else {
                return $this->json(['success' => false, 'message' => 'Failed to send test email']);
            }
            
        } catch (Exception $e) {
            Logger::error('Test email failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Email test failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Test SMS configuration
     */
    public function testSMS()
    {
        $phone = $this->input('phone');
        
        if (empty($phone)) {
            return $this->json(['success' => false, 'message' => 'Phone number required']);
        }
        
        try {
            $sms = new SMS();
            $result = $sms->send($phone, 'Test SMS from College ERP at ' . now());
            
            if ($result) {
                return $this->json(['success' => true, 'message' => 'Test SMS sent successfully']);
            } else {
                return $this->json(['success' => false, 'message' => 'Failed to send test SMS']);
            }
            
        } catch (Exception $e) {
            Logger::error('Test SMS failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'SMS test failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Upload logo
     */
    public function uploadLogo()
    {
        try {
            $logo = $this->upload('logo', 'settings', ['jpg', 'jpeg', 'png']);
            
            // Update logo setting
            $this->settingModel->updateSetting('app_logo', $logo['filename']);
            
            // Clear cache
            $this->clearCache('settings');
            
            $this->logActivity('logo_updated', 'Updated application logo');
            
            return $this->json(['success' => true, 'logo' => $logo['url']]);
            
        } catch (Exception $e) {
            Logger::error('Logo upload failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to upload logo']);
        }
    }
    
    /**
     * Upload favicon
     */
    public function uploadFavicon()
    {
        try {
            $favicon = $this->upload('favicon', 'settings', ['ico', 'png']);
            
            // Update favicon setting
            $this->settingModel->updateSetting('app_favicon', $favicon['filename']);
            
            // Clear cache
            $this->clearCache('settings');
            
            $this->logActivity('favicon_updated', 'Updated application favicon');
            
            return $this->json(['success' => true, 'favicon' => $favicon['url']]);
            
        } catch (Exception $e) {
            Logger::error('Favicon upload failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to upload favicon']);
        }
    }
    
    /**
     * Group settings by category
     */
    private function groupSettingsByCategory($settings)
    {
        $grouped = [];
        
        foreach ($settings as $setting) {
            $category = $setting['category'] ?? 'general';
            
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            
            $grouped[$category][] = $setting;
        }
        
        return $grouped;
    }
    
    /**
     * Get permission modules
     */
    private function getPermissionModules()
    {
        return [
            'student', 'faculty', 'academic', 'examination', 'attendance',
            'fees', 'library', 'hostel', 'transport', 'communication',
            'hr', 'accounts', 'reports', 'admin', 'system'
        ];
    }
}