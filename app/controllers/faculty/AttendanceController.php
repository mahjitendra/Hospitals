<?php

/**
 * Faculty Attendance Controller
 * 
 * Handles faculty attendance management
 */
class AttendanceController extends Controller
{
    private $attendanceModel;
    private $facultyModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->attendanceModel = new FacultyAttendance();
        $this->facultyModel = new Faculty();
    }
    
    /**
     * Display attendance dashboard
     */
    public function index()
    {
        $stats = $this->getAttendanceStats();
        $todayAttendance = $this->getTodayAttendance();
        $recentAttendance = $this->getRecentAttendance();
        
        $this->render('faculty/attendance/index', [
            'title' => 'Faculty Attendance',
            'stats' => $stats,
            'todayAttendance' => $todayAttendance,
            'recentAttendance' => $recentAttendance
        ]);
    }
    
    /**
     * Mark attendance
     */
    public function mark()
    {
        $faculty = $this->getFacultyList();
        $today = today();
        $markedAttendance = $this->getMarkedAttendance($today);
        
        $this->render('faculty/attendance/mark', [
            'title' => 'Mark Faculty Attendance',
            'faculty' => $faculty,
            'today' => $today,
            'markedAttendance' => $markedAttendance
        ]);
    }
    
    /**
     * Store attendance
     */
    public function store()
    {
        $this->requirePermission('attendance_mark');
        
        $data = $this->validate([
            'attendance_date' => 'required|date',
            'attendance' => 'required|array'
        ]);
        
        try {
            $this->attendanceModel->beginTransaction();
            
            $markedCount = 0;
            
            foreach ($data['attendance'] as $facultyId => $attendanceData) {
                $attendanceRecord = [
                    'faculty_id' => $facultyId,
                    'attendance_date' => $data['attendance_date'],
                    'status' => $attendanceData['status'],
                    'check_in_time' => $attendanceData['check_in_time'] ?? null,
                    'check_out_time' => $attendanceData['check_out_time'] ?? null,
                    'working_hours' => $attendanceData['working_hours'] ?? null,
                    'overtime_hours' => $attendanceData['overtime_hours'] ?? null,
                    'remarks' => $attendanceData['remarks'] ?? null,
                    'marked_by' => $this->user()['id']
                ];
                
                $this->attendanceModel->markAttendance($attendanceRecord);
                $markedCount++;
            }
            
            $this->attendanceModel->commit();
            
            $this->logActivity('faculty_attendance_marked', "Marked attendance for {$markedCount} faculty members for {$data['attendance_date']}");
            $this->flash('success', "Attendance marked for {$markedCount} faculty members");
            
            $this->redirect('/faculty/attendance');
            
        } catch (Exception $e) {
            $this->attendanceModel->rollback();
            Logger::error('Faculty attendance marking failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to mark attendance');
            $this->back();
        }
    }
    
    /**
     * Show attendance report
     */
    public function report()
    {
        $month = $this->input('month', date('m'));
        $year = $this->input('year', date('Y'));
        $department = $this->input('department', '');
        $faculty = $this->input('faculty', '');
        
        $attendanceData = $this->getAttendanceReport($year, $month, $department, $faculty);
        $summary = $this->getAttendanceSummary($year, $month, $department);
        
        $this->render('faculty/attendance/report', [
            'title' => 'Faculty Attendance Report',
            'attendanceData' => $attendanceData,
            'summary' => $summary,
            'month' => $month,
            'year' => $year,
            'department' => $department,
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Export attendance report
     */
    public function export()
    {
        $this->requirePermission('attendance_export');
        
        $month = $this->input('month', date('m'));
        $year = $this->input('year', date('Y'));
        $format = $this->input('format', 'excel');
        
        $attendanceData = $this->getAttendanceReport($year, $month);
        
        if ($format === 'excel') {
            return $this->exportAttendanceExcel($attendanceData, $year, $month);
        } elseif ($format === 'pdf') {
            return $this->generatePDF('faculty/attendance/report_pdf', [
                'attendanceData' => $attendanceData,
                'month' => $month,
                'year' => $year
            ], "faculty_attendance_{$year}_{$month}");
        }
        
        $this->flash('error', 'Invalid export format');
        $this->back();
    }
    
    /**
     * Get attendance statistics
     */
    private function getAttendanceStats()
    {
        $db = Database::getInstance();
        $currentMonth = date('Y-m');
        
        $sql = "SELECT 
                    COUNT(DISTINCT faculty_id) as total_faculty,
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                    AVG(working_hours) as avg_working_hours
                FROM faculty_attendance 
                WHERE DATE_FORMAT(attendance_date, '%Y-%m') = :month";
        
        return $db->fetch($sql, ['month' => $currentMonth]);
    }
    
    /**
     * Get today's attendance
     */
    private function getTodayAttendance()
    {
        $today = today();
        
        $db = Database::getInstance();
        $sql = "SELECT fa.*, f.first_name, f.last_name, f.employee_id, d.department_name
                FROM faculty_attendance fa
                JOIN faculty f ON fa.faculty_id = f.id
                LEFT JOIN departments d ON f.department_id = d.id
                WHERE fa.attendance_date = :today
                ORDER BY f.first_name";
        
        return $db->fetchAll($sql, ['today' => $today]);
    }
    
    /**
     * Get recent attendance
     */
    private function getRecentAttendance()
    {
        $db = Database::getInstance();
        $sql = "SELECT fa.*, f.first_name, f.last_name, f.employee_id
                FROM faculty_attendance fa
                JOIN faculty f ON fa.faculty_id = f.id
                ORDER BY fa.attendance_date DESC, fa.created_at DESC
                LIMIT 20";
        
        return $db->fetchAll($sql);
    }
    
    /**
     * Get faculty list
     */
    private function getFacultyList()
    {
        return $this->facultyModel->where('status = :status', ['status' => STATUS_ACTIVE], 'first_name ASC');
    }
    
    /**
     * Get marked attendance for date
     */
    private function getMarkedAttendance($date)
    {
        $db = Database::getInstance();
        $sql = "SELECT faculty_id, status, check_in_time, check_out_time, working_hours, remarks
                FROM faculty_attendance 
                WHERE attendance_date = :date";
        
        $result = $db->fetchAll($sql, ['date' => $date]);
        
        // Convert to associative array with faculty_id as key
        $marked = [];
        foreach ($result as $record) {
            $marked[$record['faculty_id']] = $record;
        }
        
        return $marked;
    }
}