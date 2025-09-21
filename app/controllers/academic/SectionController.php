<?php

/**
 * Section Management Controller
 * 
 * Handles class sections and divisions
 */
class SectionController extends Controller
{
    private $sectionModel;
    private $classModel;
    private $facultyModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->sectionModel = new Section();
        $this->classModel = new ClassModel();
        $this->facultyModel = new Faculty();
    }
    
    /**
     * Display sections list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $class = $this->input('class', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND section_name LIKE :search';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($class)) {
            $where .= ' AND class_id = :class';
            $params['class'] = $class;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $sections = $this->sectionModel->paginate($page, 25, $where, $params, 'class_id ASC, section_name ASC');
        $classes = $this->classModel->all('class_name ASC');
        
        // Add student count for each section
        foreach ($sections['data'] as &$section) {
            $section['student_count'] = $this->getSectionStudentCount($section['id']);
        }
        
        $this->render('academic/sections/index', [
            'title' => 'Section Management',
            'sections' => $sections,
            'classes' => $classes,
            'filters' => [
                'search' => $search,
                'class' => $class,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show create section form
     */
    public function create()
    {
        $this->requirePermission('section_create');
        
        $classes = $this->classModel->all('class_name ASC');
        $faculty = $this->facultyModel->all('first_name ASC');
        
        $this->render('academic/sections/create', [
            'title' => 'Create Section',
            'classes' => $classes,
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Store new section
     */
    public function store()
    {
        $this->requirePermission('section_create');
        
        $data = $this->validate([
            'section_name' => 'required|min:1|max:10',
            'class_id' => 'required|exists:classes,id',
            'section_teacher_id' => 'exists:faculty,id',
            'room_number' => 'max:50',
            'capacity' => 'required|numeric|min:1|max:100',
            'description' => 'max:500',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            // Check if section already exists for this class
            $existing = $this->sectionModel->first(
                'class_id = :class_id AND section_name = :section_name',
                ['class_id' => $data['class_id'], 'section_name' => $data['section_name']]
            );
            
            if ($existing) {
                throw new Exception('Section already exists for this class');
            }
            
            $sectionId = $this->sectionModel->create($data);
            
            $class = $this->classModel->find($data['class_id']);
            $this->logActivity('section_created', "Created section: {$data['section_name']} for class: {$class['class_name']}", $data);
            $this->flash('success', 'Section created successfully');
            
            $this->redirect('/academic/sections');
            
        } catch (Exception $e) {
            Logger::error('Section creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create section: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show section details
     */
    public function show($id)
    {
        $section = $this->sectionModel->findWithDetails($id);
        if (!$section) {
            $this->flash('error', 'Section not found');
            $this->redirect('/academic/sections');
        }
        
        $section['students'] = $this->sectionModel->getStudents($id);
        $section['timetable'] = $this->sectionModel->getTimetable($id);
        $section['attendance_stats'] = $this->sectionModel->getAttendanceStats($id);
        
        $this->render('academic/sections/show', [
            'title' => 'Section Details',
            'section' => $section
        ]);
    }
    
    /**
     * Show edit section form
     */
    public function edit($id)
    {
        $this->requirePermission('section_edit');
        
        $section = $this->sectionModel->find($id);
        if (!$section) {
            $this->flash('error', 'Section not found');
            $this->redirect('/academic/sections');
        }
        
        $classes = $this->classModel->all('class_name ASC');
        $faculty = $this->facultyModel->all('first_name ASC');
        
        $this->render('academic/sections/edit', [
            'title' => 'Edit Section',
            'section' => $section,
            'classes' => $classes,
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Update section
     */
    public function update($id)
    {
        $this->requirePermission('section_edit');
        
        $section = $this->sectionModel->find($id);
        if (!$section) {
            $this->flash('error', 'Section not found');
            $this->redirect('/academic/sections');
        }
        
        $data = $this->validate([
            'section_name' => 'required|min:1|max:10',
            'class_id' => 'required|exists:classes,id',
            'section_teacher_id' => 'exists:faculty,id',
            'room_number' => 'max:50',
            'capacity' => 'required|numeric|min:1|max:100',
            'description' => 'max:500',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $this->sectionModel->update($id, $data);
            
            $this->logActivity('section_updated', "Updated section: {$data['section_name']}", $data);
            $this->flash('success', 'Section updated successfully');
            
            $this->redirect('/academic/sections');
            
        } catch (Exception $e) {
            Logger::error('Section update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update section');
            $this->back();
        }
    }
    
    /**
     * Delete section
     */
    public function destroy($id)
    {
        $this->requirePermission('section_delete');
        
        $section = $this->sectionModel->find($id);
        if (!$section) {
            return $this->json(['success' => false, 'message' => 'Section not found']);
        }
        
        // Check if section has students
        $studentCount = $this->getSectionStudentCount($id);
        if ($studentCount > 0) {
            return $this->json(['success' => false, 'message' => 'Cannot delete section with enrolled students']);
        }
        
        try {
            $this->sectionModel->delete($id);
            
            $this->logActivity('section_deleted', "Deleted section: {$section['section_name']}", $section);
            
            return $this->json(['success' => true, 'message' => 'Section deleted successfully']);
            
        } catch (Exception $e) {
            Logger::error('Section deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete section']);
        }
    }
    
    /**
     * Get sections by class (AJAX)
     */
    public function getByClass($classId)
    {
        $sections = $this->sectionModel->where('class_id = :class_id AND status = :status', [
            'class_id' => $classId,
            'status' => 'active'
        ], 'section_name ASC');
        
        return $this->json($sections);
    }
    
    /**
     * Assign students to sections
     */
    public function assignStudents($id)
    {
        $this->requirePermission('section_manage');
        
        $section = $this->sectionModel->find($id);
        if (!$section) {
            return $this->json(['success' => false, 'message' => 'Section not found']);
        }
        
        $studentIds = $this->input('student_ids', []);
        
        if (empty($studentIds)) {
            return $this->json(['success' => false, 'message' => 'No students selected']);
        }
        
        try {
            $this->sectionModel->beginTransaction();
            
            $assignedCount = 0;
            
            foreach ($studentIds as $studentId) {
                // Check if student belongs to the same class
                $student = $this->studentModel->find($studentId);
                if ($student && $student['class_id'] == $section['class_id']) {
                    $this->studentModel->update($studentId, ['section_id' => $id]);
                    $assignedCount++;
                }
            }
            
            $this->sectionModel->commit();
            
            $this->logActivity('students_assigned_to_section', "Assigned {$assignedCount} students to section: {$section['section_name']}");
            
            return $this->json(['success' => true, 'message' => "Assigned {$assignedCount} students to section"]);
            
        } catch (Exception $e) {
            $this->sectionModel->rollback();
            Logger::error('Student assignment failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to assign students']);
        }
    }
    
    /**
     * Get section student count
     */
    private function getSectionStudentCount($sectionId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM students WHERE section_id = :section_id AND status = 'active'";
        $result = $db->fetch($sql, ['section_id' => $sectionId]);
        return $result['count'] ?? 0;
    }
}