<?php

/**
 * Semester Management Controller
 * 
 * Handles semester operations and management
 */
class SemesterController extends Controller
{
    private $semesterModel;
    private $courseModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->semesterModel = new Semester();
        $this->courseModel = new Course();
    }
    
    /**
     * Display semesters list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $course = $this->input('course', '');
        $session = $this->input('session', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($session)) {
            $where .= ' AND academic_session = :session';
            $params['session'] = $session;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $semesters = $this->semesterModel->paginate($page, 25, $where, $params, 'course_id ASC, semester_number ASC');
        $courses = $this->courseModel->all('course_name ASC');
        $sessions = $this->getAcademicSessions();
        
        $this->render('academic/semesters/index', [
            'title' => 'Semester Management',
            'semesters' => $semesters,
            'courses' => $courses,
            'sessions' => $sessions,
            'filters' => [
                'course' => $course,
                'session' => $session,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show create semester form
     */
    public function create()
    {
        $this->requirePermission('semester_create');
        
        $courses = $this->courseModel->all('course_name ASC');
        $sessions = $this->getAcademicSessions();
        
        $this->render('academic/semesters/create', [
            'title' => 'Create Semester',
            'courses' => $courses,
            'sessions' => $sessions
        ]);
    }
    
    /**
     * Store new semester
     */
    public function store()
    {
        $this->requirePermission('semester_create');
        
        $data = $this->validate([
            'course_id' => 'required|exists:courses,id',
            'semester_number' => 'required|numeric|min:1|max:10',
            'semester_name' => 'required|min:2|max:100',
            'academic_session' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'exam_start_date' => 'required|date',
            'exam_end_date' => 'required|date',
            'result_declaration_date' => 'date',
            'description' => 'max:500',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled'
        ]);
        
        // Validate dates
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            $this->flash('error', 'End date must be after start date');
            $this->back();
            return;
        }
        
        try {
            // Check if semester already exists
            $existing = $this->semesterModel->first(
                'course_id = :course_id AND semester_number = :semester AND academic_session = :session',
                [
                    'course_id' => $data['course_id'],
                    'semester' => $data['semester_number'],
                    'session' => $data['academic_session']
                ]
            );
            
            if ($existing) {
                throw new Exception('Semester already exists for this course and session');
            }
            
            $semesterId = $this->semesterModel->create($data);
            
            $course = $this->courseModel->find($data['course_id']);
            $this->logActivity('semester_created', "Created semester: {$data['semester_name']} for course: {$course['course_name']}", $data);
            $this->flash('success', 'Semester created successfully');
            
            $this->redirect('/academic/semesters');
            
        } catch (Exception $e) {
            Logger::error('Semester creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create semester: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show semester details
     */
    public function show($id)
    {
        $semester = $this->semesterModel->find($id);
        if (!$semester) {
            $this->flash('error', 'Semester not found');
            $this->redirect('/academic/semesters');
        }
        
        $semester['course'] = $this->courseModel->find($semester['course_id']);
        $semester['subjects'] = $this->getSemesterSubjects($id);
        $semester['students'] = $this->getSemesterStudents($id);
        $semester['exams'] = $this->getSemesterExams($id);
        $semester['statistics'] = $this->getSemesterStatistics($id);
        
        $this->render('academic/semesters/show', [
            'title' => 'Semester Details',
            'semester' => $semester
        ]);
    }
    
    /**
     * Start semester
     */
    public function start($id)
    {
        $this->requirePermission('semester_manage');
        
        $semester = $this->semesterModel->find($id);
        if (!$semester) {
            return $this->json(['success' => false, 'message' => 'Semester not found']);
        }
        
        if ($semester['status'] !== 'upcoming') {
            return $this->json(['success' => false, 'message' => 'Only upcoming semesters can be started']);
        }
        
        try {
            $this->semesterModel->update($id, [
                'status' => 'ongoing',
                'actual_start_date' => now()
            ]);
            
            $this->logActivity('semester_started', "Started semester: {$semester['semester_name']}");
            
            return $this->json(['success' => true, 'message' => 'Semester started successfully']);
            
        } catch (Exception $e) {
            Logger::error('Semester start failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to start semester']);
        }
    }
    
    /**
     * Complete semester
     */
    public function complete($id)
    {
        $this->requirePermission('semester_manage');
        
        $semester = $this->semesterModel->find($id);
        if (!$semester) {
            return $this->json(['success' => false, 'message' => 'Semester not found']);
        }
        
        if ($semester['status'] !== 'ongoing') {
            return $this->json(['success' => false, 'message' => 'Only ongoing semesters can be completed']);
        }
        
        try {
            $this->semesterModel->beginTransaction();
            
            // Update semester status
            $this->semesterModel->update($id, [
                'status' => 'completed',
                'actual_end_date' => now()
            ]);
            
            // Process semester completion
            $this->processSemesterCompletion($id);
            
            $this->semesterModel->commit();
            
            $this->logActivity('semester_completed', "Completed semester: {$semester['semester_name']}");
            
            return $this->json(['success' => true, 'message' => 'Semester completed successfully']);
            
        } catch (Exception $e) {
            $this->semesterModel->rollback();
            Logger::error('Semester completion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to complete semester']);
        }
    }
    
    /**
     * Get semester subjects
     */
    private function getSemesterSubjects($semesterId)
    {
        $semester = $this->semesterModel->find($semesterId);
        
        $subjectModel = new Subject();
        return $subjectModel->where(
            'course_id = :course_id AND semester = :semester',
            ['course_id' => $semester['course_id'], 'semester' => $semester['semester_number']],
            'subject_name ASC'
        );
    }
    
    /**
     * Get semester students
     */
    private function getSemesterStudents($semesterId)
    {
        $semester = $this->semesterModel->find($semesterId);
        
        $studentModel = new Student();
        return $studentModel->where(
            'course_id = :course_id AND current_semester = :semester AND academic_session = :session',
            [
                'course_id' => $semester['course_id'],
                'semester' => $semester['semester_number'],
                'session' => $semester['academic_session']
            ],
            'first_name ASC'
        );
    }
    
    /**
     * Get semester exams
     */
    private function getSemesterExams($semesterId)
    {
        $semester = $this->semesterModel->find($semesterId);
        
        $examModel = new Exam();
        return $examModel->where(
            'course_id = :course_id AND semester = :semester AND academic_session = :session',
            [
                'course_id' => $semester['course_id'],
                'semester' => $semester['semester_number'],
                'session' => $semester['academic_session']
            ],
            'exam_date ASC'
        );
    }
    
    /**
     * Get semester statistics
     */
    private function getSemesterStatistics($semesterId)
    {
        $semester = $this->semesterModel->find($semesterId);
        
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(DISTINCT sub.id) as total_subjects,
                    COUNT(DISTINCT e.id) as total_exams,
                    AVG(r.marks_obtained) as average_marks
                FROM students s
                LEFT JOIN subjects sub ON s.course_id = sub.course_id AND sub.semester = :semester
                LEFT JOIN exams e ON s.course_id = e.course_id AND e.semester = :semester
                LEFT JOIN results r ON s.id = r.student_id AND e.id = r.exam_id
                WHERE s.course_id = :course_id AND s.academic_session = :session";
        
        return $db->fetch($sql, [
            'course_id' => $semester['course_id'],
            'semester' => $semester['semester_number'],
            'session' => $semester['academic_session']
        ]);
    }
    
    /**
     * Process semester completion
     */
    private function processSemesterCompletion($semesterId)
    {
        // Generate semester reports
        $this->generateSemesterReports($semesterId);
        
        // Update student progression
        $this->updateStudentProgression($semesterId);
        
        // Archive semester data
        $this->archiveSemesterData($semesterId);
    }
    
    /**
     * Get academic sessions
     */
    private function getAcademicSessions()
    {
        $sessionModel = new AcademicSession();
        return $sessionModel->all('start_date DESC');
    }
}