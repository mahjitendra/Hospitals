<?php

/**
 * Course Management Controller
 * 
 * Handles course CRUD operations and management
 */
class CourseController extends Controller
{
    private $courseModel;
    private $departmentModel;
    private $subjectModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->courseModel = new Course();
        $this->departmentModel = new Department();
        $this->subjectModel = new Subject();
    }
    
    /**
     * Display courses list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $department = $this->input('department', '');
        $type = $this->input('type', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (course_name LIKE :search OR course_code LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($department)) {
            $where .= ' AND department_id = :department';
            $params['department'] = $department;
        }
        
        if (!empty($type)) {
            $where .= ' AND course_type = :type';
            $params['type'] = $type;
        }
        
        $courses = $this->courseModel->paginate($page, 25, $where, $params, 'course_name ASC');
        $departments = $this->departmentModel->all('name ASC');
        
        $this->render('academic/courses/index', [
            'title' => 'Course Management',
            'courses' => $courses,
            'departments' => $departments,
            'filters' => [
                'search' => $search,
                'department' => $department,
                'type' => $type
            ]
        ]);
    }
    
    /**
     * Show create course form
     */
    public function create()
    {
        $this->requirePermission('course_create');
        
        $departments = $this->departmentModel->all('name ASC');
        
        $this->render('academic/courses/create', [
            'title' => 'Add New Course',
            'departments' => $departments
        ]);
    }
    
    /**
     * Store new course
     */
    public function store()
    {
        $this->requirePermission('course_create');
        
        $data = $this->validate([
            'course_code' => 'required|min:2|max:20|unique:courses',
            'course_name' => 'required|min:2|max:100',
            'department_id' => 'required|exists:departments,id',
            'course_type' => 'required|in:undergraduate,postgraduate,diploma,certificate',
            'duration' => 'required|numeric|min:1|max:10',
            'duration_type' => 'required|in:years,months,weeks',
            'total_semesters' => 'required|numeric|min:1|max:20',
            'total_subjects' => 'required|numeric|min:1|max:100',
            'eligibility' => 'required|min:10',
            'description' => 'min:10',
            'fees' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $courseId = $this->courseModel->create($data);
            
            $this->logActivity('course_created', "Created course: {$data['course_name']}", $data);
            $this->flash('success', 'Course created successfully');
            
            $this->redirect('/academic/courses');
            
        } catch (Exception $e) {
            Logger::error('Course creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create course');
            $this->back();
        }
    }
    
    /**
     * Show course details
     */
    public function show($id)
    {
        $course = $this->courseModel->find($id);
        if (!$course) {
            $this->flash('error', 'Course not found');
            $this->redirect('/academic/courses');
        }
        
        // Get related data
        $course['department'] = $this->departmentModel->find($course['department_id']);
        $course['subjects'] = $this->getCourseSubjects($id);
        $course['students'] = $this->getCourseStudents($id);
        $course['faculty'] = $this->getCourseFaculty($id);
        
        $this->render('academic/courses/show', [
            'title' => 'Course Details',
            'course' => $course
        ]);
    }
    
    /**
     * Show edit course form
     */
    public function edit($id)
    {
        $this->requirePermission('course_edit');
        
        $course = $this->courseModel->find($id);
        if (!$course) {
            $this->flash('error', 'Course not found');
            $this->redirect('/academic/courses');
        }
        
        $departments = $this->departmentModel->all('name ASC');
        
        $this->render('academic/courses/edit', [
            'title' => 'Edit Course',
            'course' => $course,
            'departments' => $departments
        ]);
    }
    
    /**
     * Update course
     */
    public function update($id)
    {
        $this->requirePermission('course_edit');
        
        $course = $this->courseModel->find($id);
        if (!$course) {
            $this->flash('error', 'Course not found');
            $this->redirect('/academic/courses');
        }
        
        $data = $this->validate([
            'course_name' => 'required|min:2|max:100',
            'department_id' => 'required|exists:departments,id',
            'course_type' => 'required|in:undergraduate,postgraduate,diploma,certificate',
            'duration' => 'required|numeric|min:1|max:10',
            'duration_type' => 'required|in:years,months,weeks',
            'total_semesters' => 'required|numeric|min:1|max:20',
            'total_subjects' => 'required|numeric|min:1|max:100',
            'eligibility' => 'required|min:10',
            'description' => 'min:10',
            'fees' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);
        
        // Check course code uniqueness (excluding current course)
        if (!empty($this->input('course_code'))) {
            $existingCourse = $this->courseModel->findBy('course_code', $this->input('course_code'));
            if ($existingCourse && $existingCourse['id'] != $id) {
                $this->flash('error', 'Course code already exists');
                $this->back();
                return;
            }
            $data['course_code'] = $this->input('course_code');
        }
        
        try {
            $this->courseModel->update($id, $data);
            
            $this->logActivity('course_updated', "Updated course: {$data['course_name']}", $data);
            $this->flash('success', 'Course updated successfully');
            
            $this->redirect('/academic/courses');
            
        } catch (Exception $e) {
            Logger::error('Course update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update course');
            $this->back();
        }
    }
    
    /**
     * Delete course
     */
    public function destroy($id)
    {
        $this->requirePermission('course_delete');
        
        $course = $this->courseModel->find($id);
        if (!$course) {
            $this->flash('error', 'Course not found');
            $this->redirect('/academic/courses');
        }
        
        // Check if course has students
        $studentCount = $this->getCourseStudentCount($id);
        if ($studentCount > 0) {
            $this->flash('error', 'Cannot delete course with enrolled students');
            $this->redirect('/academic/courses');
        }
        
        try {
            $this->courseModel->softDelete($id);
            
            $this->logActivity('course_deleted', "Deleted course: {$course['course_name']}", $course);
            $this->flash('success', 'Course deleted successfully');
            
        } catch (Exception $e) {
            Logger::error('Course deletion failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to delete course');
        }
        
        $this->redirect('/academic/courses');
    }
    
    /**
     * Get course subjects
     */
    private function getCourseSubjects($courseId)
    {
        $db = Database::getInstance();
        $sql = "SELECT s.* FROM subjects s 
                WHERE s.course_id = :course_id 
                ORDER BY s.semester, s.subject_name";
        
        return $db->fetchAll($sql, ['course_id' => $courseId]);
    }
    
    /**
     * Get course students
     */
    private function getCourseStudents($courseId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as total,
                       SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                       SUM(CASE WHEN status = 'graduated' THEN 1 ELSE 0 END) as graduated
                FROM students 
                WHERE course_id = :course_id";
        
        return $db->fetch($sql, ['course_id' => $courseId]);
    }
    
    /**
     * Get course faculty
     */
    private function getCourseFaculty($courseId)
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT f.* FROM faculty f 
                JOIN faculty_subjects fs ON f.id = fs.faculty_id 
                JOIN subjects s ON fs.subject_id = s.id 
                WHERE s.course_id = :course_id";
        
        return $db->fetchAll($sql, ['course_id' => $courseId]);
    }
    
    /**
     * Get course student count
     */
    private function getCourseStudentCount($courseId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM students WHERE course_id = :course_id";
        $result = $db->fetch($sql, ['course_id' => $courseId]);
        return $result['count'] ?? 0;
    }
}