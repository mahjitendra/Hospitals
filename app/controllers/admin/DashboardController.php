<?php

/**
 * Admin Dashboard Controller
 * 
 * Handles admin dashboard operations and statistics
 */
class DashboardController extends Controller
{
    private $userModel;
    private $studentModel;
    private $facultyModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(ROLE_ADMIN);
        
        $this->userModel = new User();
        $this->studentModel = new Student();
        $this->facultyModel = new Faculty();
    }
    
    /**
     * Display admin dashboard
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $systemHealth = $this->getSystemHealth();
        
        $this->render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'activities' => $recentActivities,
            'health' => $systemHealth
        ], 'admin');
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        return [
            'total_students' => $this->studentModel->count('status = :status', ['status' => STATUS_ACTIVE]),
            'total_faculty' => $this->facultyModel->count('status = :status', ['status' => STATUS_ACTIVE]),
            'total_users' => $this->userModel->count(),
            'new_admissions' => $this->studentModel->count('created_at >= :date', ['date' => date('Y-m-01')]),
            'pending_fees' => $this->getPendingFeesCount(),
            'attendance_today' => $this->getTodayAttendanceStats(),
            'upcoming_exams' => $this->getUpcomingExamsCount(),
            'library_issues' => $this->getLibraryIssuesCount()
        ];
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM activity_logs 
                ORDER BY created_at DESC 
                LIMIT 10";
        
        return $db->fetchAll($sql);
    }
    
    /**
     * Get system health metrics
     */
    private function getSystemHealth()
    {
        return [
            'database_status' => $this->checkDatabaseHealth(),
            'storage_usage' => $this->getStorageUsage(),
            'cache_status' => $this->checkCacheHealth(),
            'backup_status' => $this->getLastBackupStatus(),
            'error_rate' => $this->getErrorRate()
        ];
    }
    
    /**
     * Get pending fees count
     */
    private function getPendingFeesCount()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM fees 
                WHERE status = :status";
        
        $result = $db->fetch($sql, ['status' => FEE_PENDING]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get today's attendance statistics
     */
    private function getTodayAttendanceStats()
    {
        $db = Database::getInstance();
        $today = date('Y-m-d');
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
                FROM student_attendance 
                WHERE attendance_date = :date";
        
        return $db->fetch($sql, ['date' => $today]);
    }
    
    /**
     * Get upcoming exams count
     */
    private function getUpcomingExamsCount()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM exams 
                WHERE exam_date >= :date AND exam_date <= :end_date";
        
        $result = $db->fetch($sql, [
            'date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days'))
        ]);
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Get library issues count
     */
    private function getLibraryIssuesCount()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM book_issues 
                WHERE return_date IS NULL";
        
        $result = $db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            $db = Database::getInstance();
            $db->fetch("SELECT 1");
            return 'healthy';
        } catch (Exception $e) {
            return 'error';
        }
    }
    
    /**
     * Get storage usage
     */
    private function getStorageUsage()
    {
        $uploadPath = public_path('uploads');
        $size = $this->getDirectorySize($uploadPath);
        
        return [
            'used' => format_bytes($size),
            'percentage' => min(100, ($size / (1024 * 1024 * 1024)) * 100) // Assume 1GB limit
        ];
    }
    
    /**
     * Check cache health
     */
    private function checkCacheHealth()
    {
        try {
            $cache = Cache::getInstance();
            $cache->set('health_check', 'ok', 60);
            $result = $cache->get('health_check');
            return $result === 'ok' ? 'healthy' : 'error';
        } catch (Exception $e) {
            return 'error';
        }
    }
    
    /**
     * Get last backup status
     */
    private function getLastBackupStatus()
    {
        $backupPath = storage_path('backups');
        if (!is_dir($backupPath)) {
            return 'no_backups';
        }
        
        $files = glob($backupPath . '/*.sql');
        if (empty($files)) {
            return 'no_backups';
        }
        
        $lastBackup = max(array_map('filemtime', $files));
        $daysSince = (time() - $lastBackup) / (24 * 60 * 60);
        
        if ($daysSince <= 1) {
            return 'recent';
        } elseif ($daysSince <= 7) {
            return 'warning';
        } else {
            return 'outdated';
        }
    }
    
    /**
     * Get error rate
     */
    private function getErrorRate()
    {
        $logFile = storage_path('logs/error.log');
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $errors = file($logFile);
        $todayErrors = 0;
        $today = date('Y-m-d');
        
        foreach ($errors as $line) {
            if (strpos($line, $today) !== false) {
                $todayErrors++;
            }
        }
        
        return $todayErrors;
    }
    
    /**
     * Get directory size
     */
    private function getDirectorySize($directory)
    {
        $size = 0;
        if (is_dir($directory)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }
}