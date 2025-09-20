<?php

/**
 * Teacher Management Controller
 * 
 * Handles teacher-specific operations
 */
class TeacherController extends Controller
{
    private $teacherModel;
    private $subjectModel;
    private $classModel;
    private $timetableModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->teacherModel = new Teacher();
        $this->subjectModel = new Subject();
        $this->classModel = new ClassModel();
        $this->timetableModel = new Timetable();
    }
    
    /**
     * Display teachers list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $department = $this->input('department', '');
        $subject = $this->input('subject', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (first_name LIKE :search OR last_name LIKE :search OR employee_id LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($department)) {
            $where .= ' AND department_id = :department';
            $params['department'] = $department;
        }
        
        if (!empty($subject)) {
            $where .= ' AND id IN (SELECT faculty_id FROM faculty_subjects WHERE subject_id = :subject)';
            $params['subject'] = $subject;
        }
        
        $teachers = $this->teacherModel->paginate($page, 25, $where, $params, 'first_name ASC');
        $departments = $this->getDepartments();
        $subjects = $this->subjectModel->all('subject_name ASC');
        
        $this->render('faculty/teachers/index', [
            'title' => 'Teacher Management',
            'teachers' => $teachers,
            'departments' => $departments,
            'subjects' => $subjects,
            'filters' => [
                'search' => $search,
                'department' => $department,
                'subject' => $subject
            ]
        ]);
    }
    
    /**
     * Show teacher dashboard
     */
    public function dashboard()
    {
        $user = $this->user();
        $teacher = $this->teacherModel->findByUserId($user['id']);
        
        if (!$teacher) {
            $this->flash('error', 'Teacher profile not found');
            $this->redirect('/dashboard');
        }
        
        $data = [
            'teacher' => $teacher,
            'todayClasses' => $this->getTodayClasses($teacher['id']),
            'upcomingExams' => $this->getUpcomingExams($teacher['id']),
            'pendingTasks' => $this->getPendingTasks($teacher['id']),
            'recentAttendance' => $this->getRecentAttendance($teacher['id']),
            'notifications' => $this->getNotifications($teacher['id'])
        ];
        
        $this->render('faculty/teachers/dashboard', [
            'title' => 'Teacher Dashboard',
            'data' => $data
        ]);
    }
    
    /**
     * Show teacher profile
     */
    public function profile($id = null)
    {
        if (!$id) {
            $user = $this->user();
            $teacher = $this->teacherModel->findByUserId($user['id']);
            $id = $teacher['id'] ?? null;
        }
        
        if (!$id) {
            $this->flash('error', 'Teacher not found');
            $this->redirect('/dashboard');
        }
        
        $teacher = $this->teacherModel->findWithDetails($id);
        if (!$teacher) {
            $this->flash('error', 'Teacher not found');
            $this->redirect('/faculty/teachers');
        }
        
        // Get additional data
        $teacher['subjects'] = $this->teacherModel->getSubjects($id);
        $teacher['classes'] = $this->teacherModel->getClasses($id);
        $teacher['timetable'] = $this->teacherModel->getTimetable($id);
        $teacher['performance'] = $this->getPerformanceMetrics($id);
        
        $this->render('faculty/teachers/profile', [
            'title' => 'Teacher Profile',
            'teacher' => $teacher
        ]);
    }
    
    /**
     * Assign subjects to teacher
     */
    public function assignSubjects($id)
    {
        $this->requirePermission('teacher_manage');
        
        $teacher = $this->teacherModel->find($id);
        if (!$teacher) {
            $this->flash('error', 'Teacher not found');
            $this->redirect('/faculty/teachers');
        }
        
        $subjects = $this->input('subjects', []);
        $academicYear = $this->input('academic_year');
        $semester = $this->input('semester');
        
        if (empty($subjects)) {
            $this->flash('error', 'Please select at least one subject');
            $this->back();
            return;
        }
        
        try {
            $this->teacherModel->beginTransaction();
            
            // Remove existing assignments for this academic year and semester
            $db = Database::getInstance();
            $db->delete('faculty_subjects', 
                'faculty_id = :faculty_id AND academic_year = :year AND semester = :semester', 
                ['faculty_id' => $id, 'year' => $academicYear, 'semester' => $semester]
            );
            
            // Add new assignments
            foreach ($subjects as $subjectId) {
                $db->insert('faculty_subjects', [
                    'faculty_id' => $id,
                    'subject_id' => $subjectId,
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                    'assigned_by' => $this->user()['id'],
                    'assigned_date' => now()
                ]);
            }
            
            $this->teacherModel->commit();
            
            $this->logActivity('subjects_assigned', "Assigned subjects to teacher: {$teacher['first_name']} {$teacher['last_name']}");
            $this->flash('success', 'Subjects assigned successfully');
            
        } catch (Exception $e) {
            $this->teacherModel->rollback();
            Logger::error('Subject assignment failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to assign subjects');
        }
        
        $this->redirect('/faculty/teachers/' . $id);
    }
    
    /**
     * Assign classes to teacher
     */
    public function assignClasses($id)
    {
        $this->requirePermission('teacher_manage');
        
        $teacher = $this->teacherModel->find($id);
        if (!$teacher) {
            $this->flash('error', 'Teacher not found');
            $this->redirect('/faculty/teachers');
        }
        
        $classes = $this->input('classes', []);
        $academicYear = $this->input('academic_year');
        $isClassTeacher = $this->input('is_class_teacher', false);
        
        if (empty($classes)) {
            $this->flash('error', 'Please select at least one class');
            $this->back();
            return;
        }
        
        try {
            $this->teacherModel->beginTransaction();
            
            $db = Database::getInstance();
            
            // Remove existing class assignments for this academic year
            $db->delete('class_teachers', 
                'faculty_id = :faculty_id AND academic_year = :year', 
                ['faculty_id' => $id, 'year' => $academicYear]
            );
            
            // Add new assignments
            foreach ($classes as $classId) {
                $db->insert('class_teachers', [
                    'faculty_id' => $id,
                    'class_id' => $classId,
                    'academic_year' => $academicYear,
                    'is_class_teacher' => $isClassTeacher && $classId == $classes[0] ? 1 : 0,
                    'assigned_by' => $this->user()['id'],
                    'assigned_date' => now()
                ]);
            }
            
            $this->teacherModel->commit();
            
            $this->logActivity('classes_assigned', "Assigned classes to teacher: {$teacher['first_name']} {$teacher['last_name']}");
            $this->flash('success', 'Classes assigned successfully');
            
        } catch (Exception $e) {
            $this->teacherModel->rollback();
            Logger::error('Class assignment failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to assign classes');
        }
        
        $this->redirect('/faculty/teachers/' . $id);
    }
    
    /**
     * Get today's classes for teacher
     */
    private function getTodayClasses($teacherId)
    {
        $today = strtolower(date('l'));
        
        $db = Database::getInstance();
        $sql = "SELECT t.*, s.subject_name, c.class_name, sec.section_name 
                FROM timetable t
                JOIN subjects s ON t.subject_id = s.id
                JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE t.faculty_id = :faculty_id AND t.day = :day
                ORDER BY t.start_time";
        
        return $db->fetchAll($sql, ['faculty_id' => $teacherId, 'day' => $today]);
    }
    
    /**
     * Get upcoming exams for teacher
     */
    private function getUpcomingExams($teacherId)
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT e.* 
                FROM exams e
                JOIN exam_subjects es ON e.id = es.exam_id
                JOIN faculty_subjects fs ON es.subject_id = fs.subject_id
                WHERE fs.faculty_id = :faculty_id 
                AND e.exam_date >= :today
                ORDER BY e.exam_date ASC
                LIMIT 5";
        
        return $db->fetchAll($sql, ['faculty_id' => $teacherId, 'today' => today()]);
    }
    
    /**
     * Get pending tasks for teacher
     */
    private function getPendingTasks($teacherId)
    {
        // This would include things like:
        // - Pending result entries
        // - Pending attendance marking
        // - Pending assignments to grade
        
        $tasks = [];
        
        // Add more task types as needed
        return $tasks;
    }
    
    /**
     * Get recent attendance marked by teacher
     */
    private function getRecentAttendance($teacherId)
    {
        $db = Database::getInstance();
        $sql = "SELECT sa.*, s.first_name, s.last_name, c.class_name 
                FROM student_attendance sa
                JOIN students s ON sa.student_id = s.id
                JOIN classes c ON s.class_id = c.id
                WHERE sa.marked_by = :teacher_id
                ORDER BY sa.attendance_date DESC, sa.created_at DESC
                LIMIT 10";
        
        return $db->fetchAll($sql, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Get notifications for teacher
     */
    private function getNotifications($teacherId)
    {
        $notificationModel = new Notification();
        return $notificationModel->where(
            'recipient_id = :recipient_id AND recipient_type = :type AND is_read = :read',
            ['recipient_id' => $teacherId, 'type' => 'faculty', 'read' => 0],
            'created_at DESC',
            10
        );
    }
    
    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics($teacherId)
    {
        $db = Database::getInstance();
        
        // Student pass rate in teacher's subjects
        $sql = "SELECT 
                    COUNT(DISTINCT r.student_id) as total_students,
                    SUM(CASE WHEN r.grade IN ('A+', 'A', 'B+', 'B') THEN 1 ELSE 0 END) as good_performers,
                    AVG(r.marks_obtained) as average_marks
                FROM results r
                JOIN exam_subjects es ON r.exam_id = es.exam_id AND r.subject_id = es.subject_id
                JOIN faculty_subjects fs ON es.subject_id = fs.subject_id
                WHERE fs.faculty_id = :faculty_id
                AND r.created_at >= :start_date";
        
        $performance = $db->fetch($sql, [
            'faculty_id' => $teacherId,
            'start_date' => date('Y-01-01') // Current year
        ]);
        
        if ($performance['total_students'] > 0) {
            $performance['success_rate'] = round(($performance['good_performers'] / $performance['total_students']) * 100, 2);
        } else {
            $performance['success_rate'] = 0;
        }
        
        return $performance;
    }
    
    /**
     * Get departments
     */
    private function getDepartments()
    {
        $departmentModel = new Department();
        return $departmentModel->all('department_name ASC');
    }
}