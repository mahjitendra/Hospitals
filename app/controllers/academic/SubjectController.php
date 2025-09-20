<?php

/**
 * Subject Management Controller
 * 
 * Handles subject CRUD operations and management
 */
class SubjectController extends Controller
{
    private $subjectModel;
    private $courseModel;
    private $departmentModel;
    private $facultyModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->subjectModel = new Subject();
        $this->courseModel = new Course();
        $this->departmentModel = new Department();
        $this->facultyModel = new Faculty();
    }
    
    /**
     * Display subjects list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $course = $this->input('course', '');
        $department = $this->input('department', '');
        $semester = $this->input('semester', '');
        $type = $this->input('type', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (subject_name LIKE :search OR subject_code LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($department)) {
            $where .= ' AND department_id = :department';
            $params['department'] = $department;
        }
        
        if (!empty($semester)) {
            $where .= ' AND semester = :semester';
            $params['semester'] = $semester;
        }
        
        if (!empty($type)) {
            $where .= ' AND subject_type = :type';
            $params['type'] = $type;
        }
        
        $subjects = $this->subjectModel->paginate($page, 25, $where, $params, 'subject_name ASC');
        $courses = $this->courseModel->all('course_name ASC');
        $departments = $this->departmentModel->all('department_name ASC');
        
        $this->render('academic/subjects/index', [
            'title' => 'Subject Management',
            'subjects' => $subjects,
            'courses' => $courses,
            'departments' => $departments,
            'filters' => [
                'search' => $search,
                'course' => $course,
                'department' => $department,
                'semester' => $semester,
                'type' => $type
            ]
        ]);
    }
    
    /**
     * Show create subject form
     */
    public function create()
    {
        $this->requirePermission('subject_create');
        
        $courses = $this->courseModel->all('course_name ASC');
        $departments = $this->departmentModel->all('department_name ASC');
        
        $this->render('academic/subjects/create', [
            'title' => 'Add New Subject',
            'courses' => $courses,
            'departments' => $departments
        ]);
    }
    
    /**
     * Store new subject
     */
    public function store()
    {
        $this->requirePermission('subject_create');
        
        $data = $this->validate([
            'subject_code' => 'required|min:2|max:20|unique:subjects',
            'subject_name' => 'required|min:2|max:100',
            'course_id' => 'required|exists:courses,id',
            'department_id' => 'required|exists:departments,id',
            'semester' => 'required|numeric|min:1|max:10',
            'subject_type' => 'required|in:core,elective,practical,project',
            'credits' => 'required|numeric|min:1|max:10',
            'theory_hours' => 'required|numeric|min:0',
            'practical_hours' => 'required|numeric|min:0',
            'total_hours' => 'required|numeric|min:1',
            'description' => 'min:10',
            'prerequisites' => 'max:255',
            'learning_outcomes' => 'min:10',
            'assessment_pattern' => 'min:10',
            'reference_books' => 'min:10',
            'status' => 'required|in:active,inactive'
        ]);
        
        // Validate total hours
        if ($data['total_hours'] != ($data['theory_hours'] + $data['practical_hours'])) {
            $this->flash('error', 'Total hours must equal theory hours plus practical hours');
            $this->back();
            return;
        }
        
        try {
            $subjectId = $this->subjectModel->create($data);
            
            $this->logActivity('subject_created', "Created subject: {$data['subject_name']}", $data);
            $this->flash('success', 'Subject created successfully');
            
            $this->redirect('/academic/subjects');
            
        } catch (Exception $e) {
            Logger::error('Subject creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create subject');
            $this->back();
        }
    }
    
    /**
     * Show subject details
     */
    public function show($id)
    {
        $subject = $this->subjectModel->find($id);
        if (!$subject) {
            $this->flash('error', 'Subject not found');
            $this->redirect('/academic/subjects');
        }
        
        // Get related data
        $subject['course'] = $this->courseModel->find($subject['course_id']);
        $subject['department'] = $this->departmentModel->find($subject['department_id']);
        $subject['faculty'] = $this->getSubjectFaculty($id);
        $subject['students'] = $this->getSubjectStudents($id);
        $subject['syllabus'] = $this->getSubjectSyllabus($id);
        
        $this->render('academic/subjects/show', [
            'title' => 'Subject Details',
            'subject' => $subject
        ]);
    }
    
    /**
     * Show edit subject form
     */
    public function edit($id)
    {
        $this->requirePermission('subject_edit');
        
        $subject = $this->subjectModel->find($id);
        if (!$subject) {
            $this->flash('error', 'Subject not found');
            $this->redirect('/academic/subjects');
        }
        
        $courses = $this->courseModel->all('course_name ASC');
        $departments = $this->departmentModel->all('department_name ASC');
        
        $this->render('academic/subjects/edit', [
            'title' => 'Edit Subject',
            'subject' => $subject,
            'courses' => $courses,
            'departments' => $departments
        ]);
    }
    
    /**
     * Update subject
     */
    public function update($id)
    {
        $this->requirePermission('subject_edit');
        
        $subject = $this->subjectModel->find($id);
        if (!$subject) {
            $this->flash('error', 'Subject not found');
            $this->redirect('/academic/subjects');
        }
        
        $data = $this->validate([
            'subject_name' => 'required|min:2|max:100',
            'course_id' => 'required|exists:courses,id',
            'department_id' => 'required|exists:departments,id',
            'semester' => 'required|numeric|min:1|max:10',
            'subject_type' => 'required|in:core,elective,practical,project',
            'credits' => 'required|numeric|min:1|max:10',
            'theory_hours' => 'required|numeric|min:0',
            'practical_hours' => 'required|numeric|min:0',
            'total_hours' => 'required|numeric|min:1',
            'description' => 'min:10',
            'prerequisites' => 'max:255',
            'learning_outcomes' => 'min:10',
            'assessment_pattern' => 'min:10',
            'reference_books' => 'min:10',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $this->subjectModel->update($id, $data);
            
            $this->logActivity('subject_updated', "Updated subject: {$data['subject_name']}", $data);
            $this->flash('success', 'Subject updated successfully');
            
            $this->redirect('/academic/subjects');
            
        } catch (Exception $e) {
            Logger::error('Subject update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update subject');
            $this->back();
        }
    }
    
    /**
     * Delete subject
     */
    public function destroy($id)
    {
        $this->requirePermission('subject_delete');
        
        $subject = $this->subjectModel->find($id);
        if (!$subject) {
            $this->flash('error', 'Subject not found');
            $this->redirect('/academic/subjects');
        }
        
        // Check if subject has dependencies
        if ($this->hasSubjectDependencies($id)) {
            $this->flash('error', 'Cannot delete subject with existing data');
            $this->redirect('/academic/subjects');
        }
        
        try {
            $this->subjectModel->softDelete($id);
            
            $this->logActivity('subject_deleted', "Deleted subject: {$subject['subject_name']}", $subject);
            $this->flash('success', 'Subject deleted successfully');
            
        } catch (Exception $e) {
            Logger::error('Subject deletion failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to delete subject');
        }
        
        $this->redirect('/academic/subjects');
    }
    
    /**
     * Get subjects by course (AJAX)
     */
    public function getByCourse($courseId)
    {
        $subjects = $this->subjectModel->where('course_id = :course_id AND status = :status', [
            'course_id' => $courseId,
            'status' => 'active'
        ], 'semester ASC, subject_name ASC');
        
        return $this->json($subjects);
    }
    
    /**
     * Get subjects by semester (AJAX)
     */
    public function getBySemester($courseId, $semester)
    {
        $subjects = $this->subjectModel->where(
            'course_id = :course_id AND semester = :semester AND status = :status',
            ['course_id' => $courseId, 'semester' => $semester, 'status' => 'active'],
            'subject_name ASC'
        );
        
        return $this->json($subjects);
    }
    
    /**
     * Get subject faculty
     */
    private function getSubjectFaculty($subjectId)
    {
        $db = Database::getInstance();
        $sql = "SELECT f.*, fs.academic_year, fs.semester 
                FROM faculty f 
                JOIN faculty_subjects fs ON f.id = fs.faculty_id 
                WHERE fs.subject_id = :subject_id 
                ORDER BY fs.academic_year DESC";
        
        return $db->fetchAll($sql, ['subject_id' => $subjectId]);
    }
    
    /**
     * Get subject students
     */
    private function getSubjectStudents($subjectId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(DISTINCT s.id) as total_students
                FROM students s
                JOIN subjects sub ON s.course_id = sub.course_id
                WHERE sub.id = :subject_id AND s.status = 'active'";
        
        $result = $db->fetch($sql, ['subject_id' => $subjectId]);
        return $result['total_students'] ?? 0;
    }
    
    /**
     * Get subject syllabus
     */
    private function getSubjectSyllabus($subjectId)
    {
        $syllabusModel = new Syllabus();
        return $syllabusModel->where('subject_id = :subject_id', ['subject_id' => $subjectId], 'unit_number ASC');
    }
    
    /**
     * Check if subject has dependencies
     */
    private function hasSubjectDependencies($subjectId)
    {
        $db = Database::getInstance();
        
        // Check faculty assignments
        $facultyCount = $db->count('faculty_subjects', 'subject_id = :subject_id', ['subject_id' => $subjectId]);
        if ($facultyCount > 0) return true;
        
        // Check exam subjects
        $examCount = $db->count('exam_subjects', 'subject_id = :subject_id', ['subject_id' => $subjectId]);
        if ($examCount > 0) return true;
        
        // Check results
        $resultCount = $db->count('results', 'subject_id = :subject_id', ['subject_id' => $subjectId]);
        if ($resultCount > 0) return true;
        
        // Check timetable
        $timetableCount = $db->count('timetable', 'subject_id = :subject_id', ['subject_id' => $subjectId]);
        if ($timetableCount > 0) return true;
        
        return false;
    }
}