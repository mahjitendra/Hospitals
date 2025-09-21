<?php

/**
 * Syllabus Management Controller
 * 
 * Handles syllabus creation and management
 */
class SyllabusController extends Controller
{
    private $syllabusModel;
    private $subjectModel;
    private $courseModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->syllabusModel = new Syllabus();
        $this->subjectModel = new Subject();
        $this->courseModel = new Course();
    }
    
    /**
     * Display syllabus list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $course = $this->input('course', '');
        $subject = $this->input('subject', '');
        $semester = $this->input('semester', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($course)) {
            $where .= ' AND course_id = :course';
            $params['course'] = $course;
        }
        
        if (!empty($subject)) {
            $where .= ' AND subject_id = :subject';
            $params['subject'] = $subject;
        }
        
        if (!empty($semester)) {
            $where .= ' AND semester = :semester';
            $params['semester'] = $semester;
        }
        
        $syllabi = $this->syllabusModel->paginate($page, 25, $where, $params, 'course_id ASC, semester ASC, unit_number ASC');
        $courses = $this->courseModel->all('course_name ASC');
        $subjects = $this->subjectModel->all('subject_name ASC');
        
        $this->render('academic/syllabus/index', [
            'title' => 'Syllabus Management',
            'syllabi' => $syllabi,
            'courses' => $courses,
            'subjects' => $subjects,
            'filters' => [
                'course' => $course,
                'subject' => $subject,
                'semester' => $semester
            ]
        ]);
    }
    
    /**
     * Show create syllabus form
     */
    public function create()
    {
        $this->requirePermission('syllabus_create');
        
        $courses = $this->courseModel->all('course_name ASC');
        $subjects = $this->subjectModel->all('subject_name ASC');
        
        $this->render('academic/syllabus/create', [
            'title' => 'Create Syllabus',
            'courses' => $courses,
            'subjects' => $subjects
        ]);
    }
    
    /**
     * Store new syllabus
     */
    public function store()
    {
        $this->requirePermission('syllabus_create');
        
        $data = $this->validate([
            'course_id' => 'required|exists:courses,id',
            'subject_id' => 'required|exists:subjects,id',
            'semester' => 'required|numeric|min:1|max:10',
            'unit_number' => 'required|numeric|min:1|max:20',
            'unit_title' => 'required|min:2|max:200',
            'topics' => 'required|min:10',
            'learning_objectives' => 'required|min:10',
            'duration_hours' => 'required|numeric|min:1',
            'teaching_methods' => 'min:10',
            'assessment_methods' => 'min:10',
            'reference_materials' => 'min:10',
            'practical_exercises' => 'min:5',
            'assignments' => 'min:5',
            'status' => 'required|in:draft,published,archived'
        ]);
        
        try {
            // Check if unit already exists for this subject and semester
            $existing = $this->syllabusModel->first(
                'subject_id = :subject_id AND semester = :semester AND unit_number = :unit',
                [
                    'subject_id' => $data['subject_id'],
                    'semester' => $data['semester'],
                    'unit' => $data['unit_number']
                ]
            );
            
            if ($existing) {
                throw new Exception('Unit already exists for this subject and semester');
            }
            
            $data['created_by'] = $this->user()['id'];
            $syllabusId = $this->syllabusModel->create($data);
            
            $subject = $this->subjectModel->find($data['subject_id']);
            $this->logActivity('syllabus_created', "Created syllabus unit: {$data['unit_title']} for subject: {$subject['subject_name']}", $data);
            $this->flash('success', 'Syllabus unit created successfully');
            
            $this->redirect('/academic/syllabus');
            
        } catch (Exception $e) {
            Logger::error('Syllabus creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create syllabus: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show syllabus details
     */
    public function show($id)
    {
        $syllabus = $this->syllabusModel->find($id);
        if (!$syllabus) {
            $this->flash('error', 'Syllabus not found');
            $this->redirect('/academic/syllabus');
        }
        
        $syllabus['course'] = $this->courseModel->find($syllabus['course_id']);
        $syllabus['subject'] = $this->subjectModel->find($syllabus['subject_id']);
        $syllabus['other_units'] = $this->getOtherUnits($syllabus['subject_id'], $syllabus['semester'], $id);
        
        $this->render('academic/syllabus/show', [
            'title' => 'Syllabus Details',
            'syllabus' => $syllabus
        ]);
    }
    
    /**
     * Show edit syllabus form
     */
    public function edit($id)
    {
        $this->requirePermission('syllabus_edit');
        
        $syllabus = $this->syllabusModel->find($id);
        if (!$syllabus) {
            $this->flash('error', 'Syllabus not found');
            $this->redirect('/academic/syllabus');
        }
        
        $courses = $this->courseModel->all('course_name ASC');
        $subjects = $this->subjectModel->all('subject_name ASC');
        
        $this->render('academic/syllabus/edit', [
            'title' => 'Edit Syllabus',
            'syllabus' => $syllabus,
            'courses' => $courses,
            'subjects' => $subjects
        ]);
    }
    
    /**
     * Update syllabus
     */
    public function update($id)
    {
        $this->requirePermission('syllabus_edit');
        
        $syllabus = $this->syllabusModel->find($id);
        if (!$syllabus) {
            $this->flash('error', 'Syllabus not found');
            $this->redirect('/academic/syllabus');
        }
        
        $data = $this->validate([
            'unit_title' => 'required|min:2|max:200',
            'topics' => 'required|min:10',
            'learning_objectives' => 'required|min:10',
            'duration_hours' => 'required|numeric|min:1',
            'teaching_methods' => 'min:10',
            'assessment_methods' => 'min:10',
            'reference_materials' => 'min:10',
            'practical_exercises' => 'min:5',
            'assignments' => 'min:5',
            'status' => 'required|in:draft,published,archived'
        ]);
        
        try {
            $data['updated_by'] = $this->user()['id'];
            $this->syllabusModel->update($id, $data);
            
            $this->logActivity('syllabus_updated', "Updated syllabus unit: {$data['unit_title']}", $data);
            $this->flash('success', 'Syllabus updated successfully');
            
            $this->redirect('/academic/syllabus');
            
        } catch (Exception $e) {
            Logger::error('Syllabus update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update syllabus');
            $this->back();
        }
    }
    
    /**
     * Delete syllabus
     */
    public function destroy($id)
    {
        $this->requirePermission('syllabus_delete');
        
        $syllabus = $this->syllabusModel->find($id);
        if (!$syllabus) {
            return $this->json(['success' => false, 'message' => 'Syllabus not found']);
        }
        
        try {
            $this->syllabusModel->delete($id);
            
            $this->logActivity('syllabus_deleted', "Deleted syllabus unit: {$syllabus['unit_title']}", $syllabus);
            
            return $this->json(['success' => true, 'message' => 'Syllabus deleted successfully']);
            
        } catch (Exception $e) {
            Logger::error('Syllabus deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete syllabus']);
        }
    }
    
    /**
     * Publish syllabus
     */
    public function publish($id)
    {
        $this->requirePermission('syllabus_publish');
        
        $syllabus = $this->syllabusModel->find($id);
        if (!$syllabus) {
            return $this->json(['success' => false, 'message' => 'Syllabus not found']);
        }
        
        try {
            $this->syllabusModel->update($id, [
                'status' => 'published',
                'published_by' => $this->user()['id'],
                'published_date' => now()
            ]);
            
            $this->logActivity('syllabus_published', "Published syllabus unit: {$syllabus['unit_title']}");
            
            return $this->json(['success' => true, 'message' => 'Syllabus published successfully']);
            
        } catch (Exception $e) {
            Logger::error('Syllabus publishing failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to publish syllabus']);
        }
    }
    
    /**
     * Get syllabus by subject
     */
    public function getBySubject($subjectId)
    {
        $syllabi = $this->syllabusModel->where(
            'subject_id = :subject_id AND status = :status',
            ['subject_id' => $subjectId, 'status' => 'published'],
            'unit_number ASC'
        );
        
        return $this->json($syllabi);
    }
    
    /**
     * Get other units for same subject and semester
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