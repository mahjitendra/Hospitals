<?php

/**
 * Backup Management Controller
 * 
 * Handles database and file backups
 */
class BackupController extends Controller
{
    private $backupModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(ROLE_ADMIN);
        
        $this->backupModel = new Backup();
    }
    
    /**
     * Display backups list
     */
    public function index()
    {
        $backups = $this->backupModel->all('created_at DESC');
        $backupStats = $this->getBackupStats();
        
        $this->render('admin/backups/index', [
            'title' => 'Backup Management',
            'backups' => $backups,
            'stats' => $backupStats
        ], 'admin');
    }
    
    /**
     * Create new backup
     */
    public function create()
    {
        $type = $this->input('type', 'full'); // full, database, files
        
        try {
            $backup = new Backup();
            $result = $backup->create($type);
            
            if ($result) {
                $this->logActivity('backup_created', "Created {$type} backup");
                return $this->json(['success' => true, 'message' => 'Backup created successfully']);
            } else {
                return $this->json(['success' => false, 'message' => 'Failed to create backup']);
            }
            
        } catch (Exception $e) {
            Logger::error('Backup creation failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Backup failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Download backup
     */
    public function download($id)
    {
        $backup = $this->backupModel->find($id);
        if (!$backup) {
            $this->flash('error', 'Backup not found');
            $this->redirect('/admin/backups');
        }
        
        $filePath = storage_path('backups/' . $backup['filename']);
        
        if (!file_exists($filePath)) {
            $this->flash('error', 'Backup file not found');
            $this->redirect('/admin/backups');
        }
        
        $this->logActivity('backup_downloaded', "Downloaded backup: {$backup['filename']}");
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    
    /**
     * Restore backup
     */
    public function restore($id)
    {
        $backup = $this->backupModel->find($id);
        if (!$backup) {
            return $this->json(['success' => false, 'message' => 'Backup not found']);
        }
        
        if ($backup['type'] !== 'database') {
            return $this->json(['success' => false, 'message' => 'Only database backups can be restored']);
        }
        
        try {
            $backupClass = new Backup();
            $result = $backupClass->restore($backup['filename']);
            
            if ($result) {
                $this->logActivity('backup_restored', "Restored backup: {$backup['filename']}");
                return $this->json(['success' => true, 'message' => 'Backup restored successfully']);
            } else {
                return $this->json(['success' => false, 'message' => 'Failed to restore backup']);
            }
            
        } catch (Exception $e) {
            Logger::error('Backup restore failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Restore failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete backup
     */
    public function delete($id)
    {
        $backup = $this->backupModel->find($id);
        if (!$backup) {
            return $this->json(['success' => false, 'message' => 'Backup not found']);
        }
        
        try {
            // Delete file
            $filePath = storage_path('backups/' . $backup['filename']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete record
            $this->backupModel->delete($id);
            
            $this->logActivity('backup_deleted', "Deleted backup: {$backup['filename']}");
            
            return $this->json(['success' => true, 'message' => 'Backup deleted successfully']);
            
        } catch (Exception $e) {
            Logger::error('Backup deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete backup']);
        }
    }
    
    /**
     * Schedule backup
     */
    public function schedule()
    {
        $data = $this->validate([
            'type' => 'required|in:daily,weekly,monthly',
            'time' => 'required',
            'backup_type' => 'required|in:full,database,files',
            'retention_days' => 'required|numeric|min:1|max:365'
        ]);
        
        try {
            $this->settingModel->updateSetting('backup_schedule_type', $data['type']);
            $this->settingModel->updateSetting('backup_schedule_time', $data['time']);
            $this->settingModel->updateSetting('backup_type', $data['backup_type']);
            $this->settingModel->updateSetting('backup_retention_days', $data['retention_days']);
            
            $this->logActivity('backup_scheduled', 'Updated backup schedule', $data);
            $this->flash('success', 'Backup schedule updated successfully');
            
        } catch (Exception $e) {
            Logger::error('Backup schedule update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update backup schedule');
        }
        
        $this->redirect('/admin/backups');
    }
    
    /**
     * Get backup statistics
     */
    private function getBackupStats()
    {
        $stats = $this->backupModel->getStats();
        
        // Calculate storage usage
        $backupPath = storage_path('backups');
        $totalSize = 0;
        
        if (is_dir($backupPath)) {
            $files = glob($backupPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                }
            }
        }
        
        $stats['storage_used'] = format_bytes($totalSize);
        $stats['last_backup'] = $this->backupModel->first('1=1', [], 'created_at DESC');
        
        return $stats;
    }
}