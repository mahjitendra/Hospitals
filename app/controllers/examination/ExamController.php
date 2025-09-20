<?php

/**
 * Examination Controller
 * 
 * Handles exam management and operations
 */
class ExamController extends Controller
{
    private $examModel;
    private $examTypeModel;
    private $courseModel;
    private $subjectModel;
    private $classModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->examModel = new Exam();
        $this->examTypeModel = new ExamType();
        $this->courseModel = new Course();
        $this->subjectModel = new Subject();
        $this->classModel = new ClassModel();
    }
    
    /**
     * Display exams list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $course = $this->input('course', '');
        $examType = $this->input('exam_type', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (exam_name LIKE :search OR exam_code LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($examType)) {
            $where .= ' AND exam_type_id = :exam_type';
            $params['exam_type'] = $examType;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $exams = $this->examModel->paginate($page, 25, $where, $params, 'exam_date DESC');
        $courses = $this->courseModel->all('course_name ASC');
        $examTypes = $this->examTypeModel->all('name ASC');
        
        $this->render('examination/exams/index', [
            'title' => 'Examination Management',
            'exams' => $exams,
            'courses' => $courses,
            'examTypes' => $examTypes,
            'filters' => [
                'search' => $search,
                'course' => $course,
                'exam_type' => $examType,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show create exam form
     */
    public function create()
    {
        $this->requirePermission('exam_create');
        
        $courses = $this->courseModel->all('course_name ASC');
        $examTypes = $this->examTypeModel->all('name ASC');
        $classes = $this->classModel->all('name ASC');
        
        $this->render('examination/exams/create', [
            'title' => 'Create New Exam',
            'courses' => $courses,
            'examTypes' => $examTypes,
            'classes' => $classes
        ]);
    }
    
    /**
     * Store new exam
     */
    public function store()
    {
        $this->requirePermission('exam_create');
        
        $data = $this->validate([
            'exam_code' => 'required|min:2|max:20|unique:exams',
            'exam_name' => 'required|min:2|max:100',
            'exam_type_id' => 'required|exists:exam_types,id',
            'course_id' => 'required|exists:courses,id',
            'class_id' => 'required|exists:classes,id',
            'semester' => 'required|numeric|min:1|max:10',
            'exam_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'total_marks' => 'required|numeric|min:1',
            'passing_marks' => 'required|numeric|min:1',
            'instructions' => 'min:10',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled'
        ]);
        
        // Validate time
        if (strtotime($data['start_time']) >= strtotime($data['end_time'])) {
            $this->flash('error', 'End time must be after start time');
            $this->back();
            return;
        }
        
        // Validate passing marks
        if ($data['passing_marks'] >= $data['total_marks']) {
            $this->flash('error', 'Passing marks must be less than total marks');
            $this->back();
            return;
        }
        
        try {
            $this->examModel->beginTransaction();
            
            // Create exam
            $examId = $this->examModel->create($data);
            
            // Create exam subjects
            $subjects = $this->input('subjects', []);
            $this->createExamSubjects($examId, $subjects);
            
            $this->examModel->commit();
            
            $this->logActivity('exam_created', "Created exam: {$data['exam_name']}", $data);
            $this->flash('success', 'Exam created successfully');
            
            $this->redirect('/examinations');
            
        } catch (Exception $e) {
            $this->examModel->rollback();
            Logger::error('Exam creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create exam');
            $this->back();
        }
    }
    
    /**
     * Show exam details
     */
    public function show($id)
    {
        $exam = $this->examModel->find($id);
        if (!$exam) {
            $this->flash('error', 'Exam not found');
            $this->redirect('/examinations');
        }
        
        // Get related data
        $exam['course'] = $this->courseModel->find($exam['course_id']);
        $exam['class'] = $this->classModel->find($exam['class_id']);
        $exam['exam_type'] = $this->examTypeModel->find($exam['exam_type_id']);
        $exam['subjects'] = $this->getExamSubjects($id);
        $exam['students'] = $this->getExamStudents($id);
        $exam['results'] = $this->getExamResults($id);
        
        $this->render('examination/exams/show', [
            'title' => 'Exam Details',
            'exam' => $exam
        ]);
    }
    
    /**
     * Show edit exam form
     */
    public function edit($id)
    {
        $this->requirePermission('exam_edit');
        
        $exam = $this->examModel->find($id);
        if (!$exam) {
            $this->flash('error', 'Exam not found');
            $this->redirect('/examinations');
        }
        
        $courses = $this->courseModel->all('course_name ASC');
        $examTypes = $this->examTypeModel->all('name ASC');
        $classes = $this->classModel->all('name ASC');
        $examSubjects = $this->getExamSubjects($id);
        
        $this->render('examination/exams/edit', [
            'title' => 'Edit Exam',
            'exam' => $exam,
            'courses' => $courses,
            'examTypes' => $examTypes,
            'classes' => $classes,
            'examSubjects' => $examSubjects
        ]);
    }
    
    /**
     * Update exam
     */
    public function update($id)
    {
        $this->requirePermission('exam_edit');
        
        $exam = $this->examModel->find($id);
        if (!$exam) {
            $this->flash('error', 'Exam not found');
            $this->redirect('/examinations');
        }
        
        $data = $this->validate([
            'exam_name' => 'required|min:2|max:100',
            'exam_type_id' => 'required|exists:exam_types,id',
            'course_id' => 'required|exists:courses,id',
            'class_id' => 'required|exists:classes,id',
            'semester' => 'required|numeric|min:1|max:10',
            'exam_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'total_marks' => 'required|numeric|min:1',
            'passing_marks' => 'required|numeric|min:1',
            'instructions' => 'min:10',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled'
        ]);
        
        try {
            $this->examModel->beginTransaction();
            
            // Update exam
            $this->examModel->update($id, $data);
            
            // Update exam subjects
            $subjects = $this->input('subjects', []);
            $this->updateExamSubjects($id, $subjects);
            
            $this->examModel->commit();
            
            $this->logActivity('exam_updated', "Updated exam: {$data['exam_name']}", $data);
            $this->flash('success', 'Exam updated successfully');
            
            $this->redirect('/examinations');
            
        } catch (Exception $e) {
            $this->examModel->rollback();
            Logger::error('Exam update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update exam');
            $this->back();
        }
    }
    
    /**
     * Delete exam
     */
    public function destroy($id)
    {
        $this->requirePermission('exam_delete');
        
        $exam = $this->examModel->find($id);
        if (!$exam) {
            $this->flash('error', 'Exam not found');
            $this->redirect('/examinations');
        }
        
        // Check if exam has results
        $resultCount = $this->getExamResultCount($id);
        if ($resultCount > 0) {
            $this->flash('error', 'Cannot delete exam with existing results');
            $this->redirect('/examinations');
        }
        
        try {
            $this->examModel->beginTransaction();
            
            // Delete exam subjects
            $db = Database::getInstance();
            $db->delete('exam_subjects', 'exam_id = :exam_id', ['exam_id' => $id]);
            
            // Soft delete exam
            $this->examModel->softDelete($id);
            
            $this->examModel->commit();
            
            $this->logActivity('exam_deleted', "Deleted exam: {$exam['exam_name']}", $exam);
            $this->flash('success', 'Exam deleted successfully');
            
        } catch (Exception $e) {
            $this->examModel->rollback();
            Logger::error('Exam deletion failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to delete exam');
        }
        
        $this->redirect('/examinations');
    }
    
    /**
     * Create exam subjects
     */
    private function createExamSubjects($examId, $subjects)
    {
        $db = Database::getInstance();
        
        foreach ($subjects as $subjectData) {
            $db->insert('exam_subjects', [
                'exam_id' => $examId,
                'subject_id' => $subjectData['subject_id'],
                'max_marks' => $subjectData['max_marks'],
                'min_marks' => $subjectData['min_marks'],
                'exam_date' => $subjectData['exam_date'],
                'start_time' => $subjectData['start_time'],
                'end_time' => $subjectData['end_time']
            ]);
        }
    }
    
    /**
     * Update exam subjects
     */
    private function updateExamSubjects($examId, $subjects)
    {
        $db = Database::getInstance();
        
        // Delete existing subjects
        $db->delete('exam_subjects', 'exam_id = :exam_id', ['exam_id' => $examId]);
        
        // Add new subjects
        $this->createExamSubjects($examId, $subjects);
    }
    
    /**
     * Get exam subjects
     */
    private function getExamSubjects($examId)
    {
        $db = Database::getInstance();
        $sql = "SELECT es.*, s.subject_name 
                FROM exam_subjects es 
                JOIN subjects s ON es.subject_id = s.id 
                WHERE es.exam_id = :exam_id 
                ORDER BY es.exam_date, es.start_time";
        
        return $db->fetchAll($sql, ['exam_id' => $examId]);
    }
    
    /**
     * Get exam students
     */
    private function getExamStudents($examId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as total FROM students s 
                JOIN exams e ON s.course_id = e.course_id AND s.class_id = e.class_id 
                WHERE e.id = :exam_id AND s.status = 'active'";
        
        $result = $db->fetch($sql, ['exam_id' => $examId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get exam results summary
     */
    private function getExamResults($examId)
    {
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(*) as total_results,
                    SUM(CASE WHEN total_marks >= passing_marks THEN 1 ELSE 0 END) as passed,
                    SUM(CASE WHEN total_marks < passing_marks THEN 1 ELSE 0 END) as failed,
                    AVG(total_marks) as average_marks
                FROM results 
                WHERE exam_id = :exam_id";
        
        return $db->fetch($sql, ['exam_id' => $examId]);
    }
    
    /**
     * Get exam result count
     */
    private function getExamResultCount($examId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM results WHERE exam_id = :exam_id";
        $result = $db->fetch($sql, ['exam_id' => $examId]);
        return $result['count'] ?? 0;
    }
}