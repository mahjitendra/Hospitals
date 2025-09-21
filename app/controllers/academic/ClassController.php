<?php

/**
 * Class Management Controller
 * 
 * Handles class operations and management
 */
class ClassController extends Controller
{
    private $classModel;
    private $courseModel;
    private $facultyModel;
    private $sectionModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->classModel = new ClassModel();
        $this->courseModel = new Course();
        $this->facultyModel = new Faculty();
        $this->sectionModel = new Section();
    }
    
    /**
     * Display classes list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $course = $this->input('course', '');
        $semester = $this->input('semester', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (class_name LIKE :search OR class_code LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($semester)) {
            $where .= ' AND semester = :semester';
            $params['semester'] = $semester;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $classes = $this->classModel->paginate($page, 25, $where, $params, 'class_name ASC');
        $courses = $this->courseModel->all('course_name ASC');
        
        // Add student count for each class
        foreach ($classes['data'] as &$class) {
            $class['student_count'] = $this->getClassStudentCount($class['id']);
        }
        
        $this->render('academic/classes/index', [
            'title' => 'Class Management',
            'classes' => $classes,
            'courses' => $courses,
            'filters' => [
                'search' => $search,
                'course' => $course,
                'semester' => $semester,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show create class form
     */
    public function create()
    {
        $this->requirePermission('class_create');
        
        $courses = $this->courseModel->all('course_name ASC');
        $faculty = $this->facultyModel->all('first_name ASC');
        
        $this->render('academic/classes/create', [
            'title' => 'Create Class',
            'courses' => $courses,
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Store new class
     */
    public function store()
    {
        $this->requirePermission('class_create');
        
        $data = $this->validate([
            'class_name' => 'required|min:2|max:100',
            'class_code' => 'required|min:2|max:20|unique:classes',
            'course_id' => 'required|exists:courses,id',
            'semester' => 'required|numeric|min:1|max:10',
            'academic_year' => 'required',
            'class_teacher_id' => 'exists:faculty,id',
            'room_number' => 'max:50',
            'capacity' => 'required|numeric|min:1|max:200',
            'description' => 'max:500',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $classId = $this->classModel->create($data);
            
            $this->logActivity('class_created', "Created class: {$data['class_name']}", $data);
            $this->flash('success', 'Class created successfully');
            
            $this->redirect('/academic/classes');
            
        } catch (Exception $e) {
            Logger::error('Class creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create class');
            $this->back();
        }
    }
    
    /**
     * Show class details
     */
    public function show($id)
    {
        $class = $this->classModel->findWithDetails($id);
        if (!$class) {
            $this->flash('error', 'Class not found');
            $this->redirect('/academic/classes');
        }
        
        $class['students'] = $this->classModel->getStudents($id);
        $class['sections'] = $this->classModel->getSections($id);
        $class['timetable'] = $this->classModel->getTimetable($id);
        $class['attendance_stats'] = $this->classModel->getAttendanceStats($id);
        $class['exam_results'] = $this->classModel->getExamResults($id);
        
        $this->render('academic/classes/show', [
            'title' => 'Class Details',
            'class' => $class
        ]);
    }
    
    /**
     * Show edit class form
     */
    public function edit($id)
    {
        $this->requirePermission('class_edit');
        
        $class = $this->classModel->find($id);
        if (!$class) {
            $this->flash('error', 'Class not found');
            $this->redirect('/academic/classes');
        }
        
        $courses = $this->courseModel->all('course_name ASC');
        $faculty = $this->facultyModel->all('first_name ASC');
        
        $this->render('academic/classes/edit', [
            'title' => 'Edit Class',
            'class' => $class,
            'courses' => $courses,
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Update class
     */
    public function update($id)
    {
        $this->requirePermission('class_edit');
        
        $class = $this->classModel->find($id);
        if (!$class) {
            $this->flash('error', 'Class not found');
            $this->redirect('/academic/classes');
        }
        
        $data = $this->validate([
            'class_name' => 'required|min:2|max:100',
            'course_id' => 'required|exists:courses,id',
            'semester' => 'required|numeric|min:1|max:10',
            'academic_year' => 'required',
            'class_teacher_id' => 'exists:faculty,id',
            'room_number' => 'max:50',
            'capacity' => 'required|numeric|min:1|max:200',
            'description' => 'max:500',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $this->classModel->update($id, $data);
            
            $this->logActivity('class_updated', "Updated class: {$data['class_name']}", $data);
            $this->flash('success', 'Class updated successfully');
            
            $this->redirect('/academic/classes');
            
        } catch (Exception $e) {
            Logger::error('Class update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update class');
            $this->back();
        }
    }
    
    /**
     * Delete class
     */
    public function destroy($id)
    {
        $this->requirePermission('class_delete');
        
        $class = $this->classModel->find($id);
        if (!$class) {
            return $this->json(['success' => false, 'message' => 'Class not found']);
        }
        
        // Check if class has students
        $studentCount = $this->getClassStudentCount($id);
        if ($studentCount > 0) {
            return $this->json(['success' => false, 'message' => 'Cannot delete class with enrolled students']);
        }
        
        try {
            $this->classModel->softDelete($id);
            
            $this->logActivity('class_deleted', "Deleted class: {$class['class_name']}", $class);
            
            return $this->json(['success' => true, 'message' => 'Class deleted successfully']);
            
        } catch (Exception $e) {
            Logger::error('Class deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete class']);
        }
    }
    
    /**
     * Promote class to next semester
     */
    public function promote($id)
    {
        $this->requirePermission('class_promote');
        
        $class = $this->classModel->find($id);
        if (!$class) {
            return $this->json(['success' => false, 'message' => 'Class not found']);
        }
        
        try {
            $this->classModel->promoteToNextSemester($id);
            
            $this->logActivity('class_promoted', "Promoted class: {$class['class_name']} to next semester");
            
            return $this->json(['success' => true, 'message' => 'Class promoted to next semester']);
            
        } catch (Exception $e) {
            Logger::error('Class promotion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Get classes by course (AJAX)
     */
    public function getByCourse($courseId)
    {
        $classes = $this->classModel->where('course_id = :course_id AND status = :status', [
            'course_id' => $courseId,
            'status' => 'active'
        ], 'class_name ASC');
        
        return $this->json($classes);
    }
    
    /**
     * Get class student count
     */
    private function getClassStudentCount($classId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM students WHERE class_id = :class_id AND status = 'active'";
        $result = $db->fetch($sql, ['class_id' => $classId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get other units for same subject
     */
    private function getOtherUnits($subjectId, $semester, $excludeId)
    {
        return $this->syllabusModel->where(
            'subject_id = :subject_id AND semester = :semester AND id != :exclude_id',
            ['subject_id' => $subjectId, 'semester' => $semester, 'exclude_id' => $excludeId],
            'unit_number ASC'
        );
    }
}