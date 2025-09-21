<?php

/**
 * Admin Report Controller
 * 
 * Handles admin-level reporting and analytics
 */
class ReportController extends Controller
{
    private $studentModel;
    private $facultyModel;
    private $feeModel;
    private $attendanceModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(ROLE_ADMIN);
        
        $this->studentModel = new Student();
        $this->facultyModel = new Faculty();
        $this->feeModel = new Fee();
        $this->attendanceModel = new StudentAttendance();
    }
    
    /**
     * Display reports dashboard
     */
    public function index()
    {
        $reportCategories = [
            'student' => 'Student Reports',
            'faculty' => 'Faculty Reports',
            'academic' => 'Academic Reports',
            'financial' => 'Financial Reports',
            'attendance' => 'Attendance Reports',
            'examination' => 'Examination Reports',
            'library' => 'Library Reports',
            'hostel' => 'Hostel Reports',
            'transport' => 'Transport Reports'
        ];
        
        $quickStats = $this->getQuickStats();
        
        $this->render('admin/reports/index', [
            'title' => 'Reports & Analytics',
            'categories' => $reportCategories,
            'stats' => $quickStats
        ], 'admin');
    }
    
    /**
     * Generate comprehensive system report
     */
    public function systemReport()
    {
        $data = [
            'student_stats' => $this->studentModel->getStats(),
            'faculty_stats' => $this->facultyModel->getStats(),
            'fee_stats' => $this->feeModel->getStats(),
            'attendance_stats' => $this->getAttendanceStats(),
            'academic_stats' => $this->getAcademicStats(),
            'system_health' => $this->getSystemHealth()
        ];
        
        $format = $this->input('format', 'html');
        
        if ($format === 'pdf') {
            return $this->generatePDF('admin/reports/system_pdf', $data, 'system_report_' . date('Y-m-d'));
        } elseif ($format === 'excel') {
            return $this->exportSystemReportExcel($data);
        }
        
        $this->render('admin/reports/system', [
            'title' => 'System Report',
            'data' => $data
        ], 'admin');
    }
    
    /**
     * Generate enrollment report
     */
    public function enrollmentReport()
    {
        $year = $this->input('year', date('Y'));
        $course = $this->input('course', '');
        
        $enrollmentData = $this->getEnrollmentData($year, $course);
        $monthlyTrends = $this->getMonthlyEnrollmentTrends($year);
        $courseWiseData = $this->getCourseWiseEnrollment($year);
        
        $this->render('admin/reports/enrollment', [
            'title' => 'Enrollment Report',
            'data' => $enrollmentData,
            'trends' => $monthlyTrends,
            'courseData' => $courseWiseData,
            'year' => $year,
            'course' => $course
        ], 'admin');
    }
    
    /**
     * Generate financial summary report
     */
    public function financialSummary()
    {
        $startDate = $this->input('start_date', date('Y-m-01'));
        $endDate = $this->input('end_date', date('Y-m-t'));
        
        $data = [
            'fee_collection' => $this->getFeeCollectionSummary($startDate, $endDate),
            'payment_methods' => $this->getPaymentMethodBreakdown($startDate, $endDate),
            'course_wise_collection' => $this->getCourseWiseCollection($startDate, $endDate),
            'outstanding_dues' => $this->getOutstandingDues(),
            'scholarship_data' => $this->getScholarshipData($startDate, $endDate)
        ];
        
        $this->render('admin/reports/financial', [
            'title' => 'Financial Summary',
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate
        ], 'admin');
    }
    
    /**
     * Generate attendance analytics
     */
    public function attendanceAnalytics()
    {
        $month = $this->input('month', date('m'));
        $year = $this->input('year', date('Y'));
        $class = $this->input('class', '');
        
        $data = [
            'overall_stats' => $this->getOverallAttendanceStats($year, $month),
            'class_wise' => $this->getClassWiseAttendance($year, $month, $class),
            'daily_trends' => $this->getDailyAttendanceTrends($year, $month),
            'low_attendance' => $this->getLowAttendanceStudents($year, $month),
            'perfect_attendance' => $this->getPerfectAttendanceStudents($year, $month)
        ];
        
        $this->render('admin/reports/attendance', [
            'title' => 'Attendance Analytics',
            'data' => $data,
            'month' => $month,
            'year' => $year,
            'class' => $class
        ], 'admin');
    }
    
    /**
     * Generate performance report
     */
    public function performanceReport()
    {
        $exam = $this->input('exam', '');
        $course = $this->input('course', '');
        $class = $this->input('class', '');
        
        $data = [
            'exam_performance' => $this->getExamPerformance($exam, $course, $class),
            'subject_wise' => $this->getSubjectWisePerformance($exam, $course, $class),
            'grade_distribution' => $this->getGradeDistribution($exam, $course, $class),
            'top_performers' => $this->getTopPerformers($exam, $course, $class),
            'improvement_needed' => $this->getStudentsNeedingImprovement($exam, $course, $class)
        ];
        
        $this->render('admin/reports/performance', [
            'title' => 'Performance Report',
            'data' => $data,
            'filters' => [
                'exam' => $exam,
                'course' => $course,
                'class' => $class
            ]
        ], 'admin');
    }
    
    /**
     * Export report data
     */
    public function export()
    {
        $reportType = $this->input('type');
        $format = $this->input('format', 'excel');
        
        switch ($reportType) {
            case 'students':
                return $this->exportStudentReport($format);
            case 'faculty':
                return $this->exportFacultyReport($format);
            case 'fees':
                return $this->exportFeeReport($format);
            case 'attendance':
                return $this->exportAttendanceReport($format);
            default:
                $this->flash('error', 'Invalid report type');
                $this->back();
        }
    }
    
    /**
     * Get quick statistics
     */
    private function getQuickStats()
    {
        return [
            'total_students' => $this->studentModel->count('status = :status', ['status' => STATUS_ACTIVE]),
            'total_faculty' => $this->facultyModel->count('status = :status', ['status' => STATUS_ACTIVE]),
            'pending_fees' => $this->feeModel->count('status = :status', ['status' => FEE_PENDING]),
            'today_attendance' => $this->getTodayAttendancePercentage()
        ];
    }
    
    /**
     * Get enrollment data
     */
    private function getEnrollmentData($year, $course = null)
    {
        $where = 'YEAR(admission_date) = :year';
        $params = ['year' => $year];
        
        if ($course) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        return $this->studentModel->where($where, $params, 'admission_date ASC');
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
            'backup_status' => $this->getLastBackupStatus()
        ];
    }
    
    /**
     * Export system report to Excel
     */
    private function exportSystemReportExcel($data)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add headers and data
        $sheet->setCellValue('A1', 'System Report - ' . date('Y-m-d'));
        $sheet->setCellValue('A3', 'Student Statistics');
        $sheet->setCellValue('A4', 'Total Students: ' . $data['student_stats']['total']);
        
        // Set filename and download
        $filename = 'system_report_' . date('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}