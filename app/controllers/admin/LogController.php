<?php

/**
 * Log Management Controller
 * 
 * Handles system logs viewing and management
 */
class LogController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(ROLE_ADMIN);
    }
    
    /**
     * Display logs dashboard
     */
    public function index()
    {
        $logger = Logger::getInstance();
        $logFiles = $logger->getLogFiles();
        
        $this->render('admin/logs/index', [
            'title' => 'Log Management',
            'logFiles' => $logFiles
        ], 'admin');
    }
    
    /**
     * Show specific log file
     */
    public function show($file)
    {
        $logger = Logger::getInstance();
        $lines = $this->input('lines', 100);
        $level = $this->input('level', '');
        
        try {
            $entries = $logger->getLogEntries($file, $lines);
            
            // Filter by level if specified
            if (!empty($level)) {
                $entries = array_filter($entries, function($entry) use ($level) {
                    return $entry['level'] === $level;
                });
            }
            
            $this->render('admin/logs/show', [
                'title' => 'Log Viewer - ' . $file,
                'file' => $file,
                'entries' => $entries,
                'level' => $level
            ], 'admin');
            
        } catch (Exception $e) {
            $this->flash('error', 'Failed to read log file');
            $this->redirect('/admin/logs');
        }
    }
    
    /**
     * Clear log file
     */
    public function clear($file)
    {
        try {
            $logger = Logger::getInstance();
            $result = $logger->clearLog($file);
            
            if ($result) {
                $this->logActivity('log_cleared', "Cleared log file: {$file}");
                return $this->json(['success' => true, 'message' => 'Log file cleared successfully']);
            } else {
                return $this->json(['success' => false, 'message' => 'Failed to clear log file']);
            }
            
        } catch (Exception $e) {
            Logger::error('Log clear failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to clear log']);
        }
    }
    
    /**
     * Download log file
     */
    public function download($file)
    {
        $filePath = storage_path('logs/' . $file);
        
        if (!file_exists($filePath)) {
            $this->flash('error', 'Log file not found');
            $this->redirect('/admin/logs');
        }
        
        $this->logActivity('log_downloaded', "Downloaded log file: {$file}");
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    
    /**
     * Archive old logs
     */
    public function archive()
    {
        $days = $this->input('days', 30);
        
        try {
            $logger = Logger::getInstance();
            $logger->archiveLogs($days);
            
            $this->logActivity('logs_archived', "Archived logs older than {$days} days");
            return $this->json(['success' => true, 'message' => 'Logs archived successfully']);
            
        } catch (Exception $e) {
            Logger::error('Log archive failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to archive logs']);
        }
    }
    
    /**
     * Get log statistics
     */
    public function stats()
    {
        $logger = Logger::getInstance();
        $logFiles = $logger->getLogFiles();
        
        $stats = [
            'total_files' => count($logFiles),
            'total_size' => 0,
            'error_count' => 0,
            'warning_count' => 0,
            'info_count' => 0
        ];
        
        foreach ($logFiles as $file) {
            $stats['total_size'] += $file['size'];
            
            // Count log levels (simplified)
            $entries = $logger->getLogEntries($file['name'], 1000);
            foreach ($entries as $entry) {
                switch ($entry['level']) {
                    case 'error':
                    case 'critical':
                    case 'emergency':
                        $stats['error_count']++;
                        break;
                    case 'warning':
                        $stats['warning_count']++;
                        break;
                    case 'info':
                    case 'debug':
                        $stats['info_count']++;
                        break;
                }
            }
        }
        
        $stats['total_size'] = format_bytes($stats['total_size']);
        
        return $this->json($stats);
    }
}