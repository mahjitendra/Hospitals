<?php

/**
 * Home Controller
 * 
 * Handles home page and dashboard operations
 */
class HomeController extends Controller
{
    private $studentModel;
    private $facultyModel;
    private $announcementModel;
    private $eventModel;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->studentModel = new Student();
        $this->facultyModel = new Faculty();
        $this->announcementModel = new Announcement();
        $this->eventModel = new Event();
    }
    
    /**
     * Show home page
     */
    public function index()
    {
        if ($this->auth->check()) {
            return $this->dashboard();
        }
        
        // Public home page
        $stats = $this->getPublicStats();
        $announcements = $this->announcementModel->where('is_public = :public AND status = :status', [
            'public' => 1,
            'status' => 'published'
        ], 'created_at DESC', 5);
        
        $events = $this->eventModel->where('is_public = :public AND event_date >= :today', [
            'public' => 1,
            'today' => today()
        ], 'event_date ASC', 5);
        
        $this->render('home', [
            'title' => 'Welcome to College ERP',
            'stats' => $stats,
            'announcements' => $announcements,
            'events' => $events
        ], 'guest');
    }
    
    /**
     * Show dashboard based on user role
     */
    public function dashboard()
    {
        $this->requireAuth();
        
        $user = $this->user();
        $roles = $user['roles'] ?? [];
        
        if (in_array(ROLE_ADMIN, $roles) || in_array(ROLE_SUPER_ADMIN, $roles)) {
            return $this->adminDashboard();
        } elseif (in_array(ROLE_TEACHER, $roles) || in_array(ROLE_HOD, $roles)) {
            return $this->facultyDashboard();
        } elseif (in_array(ROLE_STUDENT, $roles)) {
            return $this->studentDashboard();
        } elseif (in_array(ROLE_PARENT, $roles)) {
            return $this->parentDashboard();
        } elseif (in_array(ROLE_LIBRARIAN, $roles)) {
            return $this->librarianDashboard();
        } elseif (in_array(ROLE_ACCOUNTANT, $roles)) {
            return $this->accountantDashboard();
        } else {
            return $this->defaultDashboard();
        }
    }
    
    /**
     * Admin dashboard
     */
    private function adminDashboard()
    {
        $stats = [
            'total_students' => $this->studentModel->count('status = :status', ['status' => STATUS_ACTIVE]),
            'total_faculty' => $this->facultyModel->count('status = :status', ['status' => STATUS_ACTIVE]),
            'new_admissions' => $this->getNewAdmissions(),
            'pending_fees' => $this->getPendingFees(),
            'today_attendance' => $this->getTodayAttendance(),
            'upcoming_exams' => $this->getUpcomingExams()
        ];
        
        $recentActivities = $this->getRecentActivities();
        $systemHealth = $this->getSystemHealth();
        
        $this->render('dashboard/admin', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'activities' => $recentActivities,
            'health' => $systemHealth
        ], 'admin');
    }
    
    /**
     * Faculty dashboard
     */
    private function facultyDashboard()
    {
        $user = $this->user();
        $faculty = $this->facultyModel->findByUserId($user['id']);
        
        if (!$faculty) {
            $this->flash('error', 'Faculty profile not found');
            $this->redirect('/profile/create');
        }
        
        $data = [
            'faculty' => $faculty,
            'todayClasses' => $this->getTodayClasses($faculty['id']),
            'upcomingExams' => $this->getFacultyUpcomingExams($faculty['id']),
            'pendingTasks' => $this->getFacultyPendingTasks($faculty['id']),
            'recentAttendance' => $this->getFacultyRecentAttendance($faculty['id']),
            'notifications' => $this->getNotifications($faculty['id'], 'faculty')
        ];
        
        $this->render('dashboard/faculty', [
            'title' => 'Faculty Dashboard',
            'data' => $data
        ], 'faculty');
    }
    
    /**
     * Student dashboard
     */
    private function studentDashboard()
    {
        $user = $this->user();
        $student = $this->studentModel->findByUserId($user['id']);
        
        if (!$student) {
            $this->flash('error', 'Student profile not found');
            $this->redirect('/profile/create');
        }
        
        $data = [
            'student' => $student,
            'todayTimetable' => $this->getStudentTodayTimetable($student['id']),
            'upcomingExams' => $this->getStudentUpcomingExams($student['id']),
            'attendanceStats' => $this->getStudentAttendanceStats($student['id']),
            'feeStatus' => $this->getStudentFeeStatus($student['id']),
            'recentResults' => $this->getStudentRecentResults($student['id']),
            'notifications' => $this->getNotifications($student['id'], 'student')
        ];
        
        $this->render('dashboard/student', [
            'title' => 'Student Dashboard',
            'data' => $data
        ], 'student');
    }
    
    /**
     * Parent dashboard
     */
    private function parentDashboard()
    {
        $user = $this->user();
        $parentModel = new ParentModel();
        $children = $parentModel->getChildren($user['id']);
        
        if (empty($children)) {
            $this->flash('error', 'No children found in your account');
            $this->redirect('/profile/link-children');
        }
        
        $data = [
            'children' => $children,
            'notifications' => $this->getNotifications($user['id'], 'parent')
        ];
        
        // Get data for each child
        foreach ($data['children'] as &$child) {
            $child['attendance'] = $this->getStudentAttendanceStats($child['id']);
            $child['fees'] = $this->getStudentFeeStatus($child['id']);
            $child['recent_results'] = $this->getStudentRecentResults($child['id']);
        }
        
        $this->render('dashboard/parent', [
            'title' => 'Parent Dashboard',
            'data' => $data
        ], 'parent');
    }
    
    /**
     * Default dashboard
     */
    private function defaultDashboard()
    {
        $user = $this->user();
        
        $this->render('dashboard/default', [
            'title' => 'Dashboard',
            'user' => $user
        ]);
    }
    
    /**
     * Get public statistics
     */
    private function getPublicStats()
    {
        return [
            'total_students' => $this->studentModel->count('status = :status', ['status' => STATUS_ACTIVE]),
            'total_faculty' => $this->facultyModel->count('status = :status', ['status' => STATUS_ACTIVE]),
            'total_courses' => $this->getCourseCount(),
            'years_of_excellence' => date('Y') - 2000 // Assuming college started in 2000
        ];
    }
    
    /**
     * Get new admissions count
     */
    private function getNewAdmissions()
    {
        $admissionModel = new Admission();
        return $admissionModel->count('application_date >= :date', ['date' => date('Y-m-01')]);
    }
    
    /**
     * Get pending fees count
     */
    private function getPendingFees()
    {
        $feeModel = new Fee();
        return $feeModel->count('status = :status', ['status' => FEE_PENDING]);
    }
    
    /**
     * Get today's attendance
     */
    private function getTodayAttendance()
    {
        $attendanceModel = new StudentAttendance();
        $db = Database::getInstance();
        
        $sql = "SELECT 
                    COUNT(DISTINCT student_id) as total_students,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_students
                FROM student_attendance 
                WHERE attendance_date = :today";
        
        $result = $db->fetch($sql, ['today' => today()]);
        
        if ($result['total_students'] > 0) {
            $result['percentage'] = round(($result['present_students'] / $result['total_students']) * 100, 2);
        } else {
            $result['percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get upcoming exams count
     */
    private function getUpcomingExams()
    {
        $examModel = new Exam();
        return $examModel->count('exam_date >= :today AND exam_date <= :end_date', [
            'today' => today(),
            'end_date' => date('Y-m-d', strtotime('+30 days'))
        ]);
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities($limit = 10)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM activity_logs 
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $db->fetchAll($sql);
    }
    
    /**
     * Get system health
     */
    private function getSystemHealth()
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->getStorageUsage(),
            'cache' => $this->checkCacheHealth(),
            'backup' => $this->getLastBackupStatus()
        ];
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
        $backupModel = new Backup();
        $lastBackup = $backupModel->first('1=1', [], 'created_at DESC');
        
        if (!$lastBackup) {
            return 'no_backups';
        }
        
        $daysSince = (strtotime(now()) - strtotime($lastBackup['created_at'])) / (24 * 60 * 60);
        
        if ($daysSince <= 1) {
            return 'recent';
        } elseif ($daysSince <= 7) {
            return 'warning';
        } else {
            return 'outdated';
        }
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
    
    /**
     * Get course count
     */
    private function getCourseCount()
    {
        $courseModel = new Course();
        return $courseModel->count('status = :status', ['status' => 'active']);
    }
    
    /**
     * Get notifications
     */
    private function getNotifications($recipientId, $type, $limit = 5)
    {
        $notificationModel = new Notification();
        return $notificationModel->where(
            'recipient_id = :recipient_id AND recipient_type = :type AND is_read = :read',
            ['recipient_id' => $recipientId, 'type' => $type, 'read' => 0],
            'created_at DESC',
            $limit
        );
    }
}