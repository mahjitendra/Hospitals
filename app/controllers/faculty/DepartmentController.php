<?php

/**
 * Department Management Controller
 * 
 * Handles department operations and management
 */
class DepartmentController extends Controller
{
    private $departmentModel;
    private $facultyModel;
    private $courseModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->departmentModel = new Department();
        $this->facultyModel = new Faculty();
        $this->courseModel = new Course();
    }
    
    /**
     * Display departments list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (department_name LIKE :search OR department_code LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $departments = $this->departmentModel->paginate($page, 25, $where, $params, 'department_name ASC');
        
        // Add statistics for each department
        foreach ($departments['data'] as &$department) {
            $department['stats'] = $this->departmentModel->getDepartmentStats($department['id']);
        }
        
        $this->render('faculty/departments/index', [
            'title' => 'Department Management',
            'departments' => $departments,
            'filters' => [
                'search' => $search,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show create department form
     */
    public function create()
    {
        $this->requirePermission('department_create');
        
        $faculty = $this->facultyModel->all('first_name ASC');
        
        $this->render('faculty/departments/create', [
            'title' => 'Create Department',
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Store new department
     */
    public function store()
    {
        $this->requirePermission('department_create');
        
        $data = $this->validate([
            'department_code' => 'required|min:2|max:10|unique:departments',
            'department_name' => 'required|min:2|max:100|unique:departments',
            'description' => 'min:10',
            'hod_id' => 'exists:faculty,id',
            'established_year' => 'required|numeric|min:1900|max:' . date('Y'),
            'location' => 'max:100',
            'phone' => 'phone',
            'email' => 'email',
            'website' => 'url',
            'vision' => 'min:10',
            'mission' => 'min:10',
            'objectives' => 'min:10',
            'facilities' => 'min:10',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $departmentId = $this->departmentModel->create($data);
            
            $this->logActivity('department_created', "Created department: {$data['department_name']}", $data);
            $this->flash('success', 'Department created successfully');
            
            $this->redirect('/faculty/departments');
            
        } catch (Exception $e) {
            Logger::error('Department creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create department');
            $this->back();
        }
    }
    
    /**
     * Show department details
     */
    public function show($id)
    {
        $department = $this->departmentModel->findWithDetails($id);
        if (!$department) {
            $this->flash('error', 'Department not found');
            $this->redirect('/faculty/departments');
        }
        
        $department['faculty'] = $this->departmentModel->getFaculty($id);
        $department['courses'] = $this->departmentModel->getCourses($id);
        $department['subjects'] = $this->departmentModel->getSubjects($id);
        $department['students'] = $this->departmentModel->getStudents($id);
        $department['performance'] = $this->departmentModel->getPerformance($id);
        
        $this->render('faculty/departments/show', [
            'title' => 'Department Details',
            'department' => $department
        ]);
    }
    
    /**
     * Show edit department form
     */
    public function edit($id)
    {
        $this->requirePermission('department_edit');
        
        $department = $this->departmentModel->find($id);
        if (!$department) {
            $this->flash('error', 'Department not found');
            $this->redirect('/faculty/departments');
        }
        
        $faculty = $this->facultyModel->where('department_id = :dept_id AND status = :status', [
            'dept_id' => $id,
            'status' => STATUS_ACTIVE
        ], 'first_name ASC');
        
        $this->render('faculty/departments/edit', [
            'title' => 'Edit Department',
            'department' => $department,
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Update department
     */
    public function update($id)
    {
        $this->requirePermission('department_edit');
        
        $department = $this->departmentModel->find($id);
        if (!$department) {
            $this->flash('error', 'Department not found');
            $this->redirect('/faculty/departments');
        }
        
        $data = $this->validate([
            'department_name' => 'required|min:2|max:100',
            'description' => 'min:10',
            'hod_id' => 'exists:faculty,id',
            'established_year' => 'required|numeric|min:1900|max:' . date('Y'),
            'location' => 'max:100',
            'phone' => 'phone',
            'email' => 'email',
            'website' => 'url',
            'vision' => 'min:10',
            'mission' => 'min:10',
            'objectives' => 'min:10',
            'facilities' => 'min:10',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            // Check name uniqueness (excluding current department)
            $existingDept = $this->departmentModel->findBy('department_name', $data['department_name']);
            if ($existingDept && $existingDept['id'] != $id) {
                throw new Exception('Department name already exists');
            }
            
            $this->departmentModel->update($id, $data);
            
            $this->logActivity('department_updated', "Updated department: {$data['department_name']}", $data);
            $this->flash('success', 'Department updated successfully');
            
            $this->redirect('/faculty/departments');
            
        } catch (Exception $e) {
            Logger::error('Department update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update department: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Set HOD
     */
    public function setHOD($id)
    {
        $this->requirePermission('department_manage');
        
        $facultyId = $this->input('faculty_id');
        
        try {
            $this->departmentModel->setHOD($id, $facultyId);
            
            $department = $this->departmentModel->find($id);
            $faculty = $this->facultyModel->find($facultyId);
            
            $this->logActivity('hod_assigned', "Assigned HOD: {$faculty['first_name']} {$faculty['last_name']} to department: {$department['department_name']}");
            
            return $this->json(['success' => true, 'message' => 'HOD assigned successfully']);
            
        } catch (Exception $e) {
            Logger::error('HOD assignment failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Remove HOD
     */
    public function removeHOD($id)
    {
        $this->requirePermission('department_manage');
        
        try {
            $department = $this->departmentModel->find($id);
            $this->departmentModel->removeHOD($id);
            
            $this->logActivity('hod_removed', "Removed HOD from department: {$department['department_name']}");
            
            return $this->json(['success' => true, 'message' => 'HOD removed successfully']);
            
        } catch (Exception $e) {
            Logger::error('HOD removal failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to remove HOD']);
        }
    }
    
    /**
     * Get department statistics
     */
    public function statistics($id)
    {
        $department = $this->departmentModel->find($id);
        if (!$department) {
            return $this->json(['success' => false, 'message' => 'Department not found']);
        }
        
        $stats = [
            'basic_stats' => $this->departmentModel->getDepartmentStats($id),
            'performance' => $this->departmentModel->getPerformance($id),
            'faculty_distribution' => $this->getFacultyDistribution($id),
            'course_enrollment' => $this->getCourseEnrollment($id),
            'research_output' => $this->getResearchOutput($id)
        ];
        
        return $this->json($stats);
    }
    
    /**
     * Get designations
     */
    private function getDesignations()
    {
        $designationModel = new Designation();
        return $designationModel->all('designation_name ASC');
    }
    
    /**
     * Get faculty distribution
     */
    private function getFacultyDistribution($departmentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT 
                    d.designation_name,
                    COUNT(f.id) as count
                FROM designations d
                LEFT JOIN faculty f ON d.id = f.designation_id AND f.department_id = :dept_id
                GROUP BY d.id, d.designation_name
                ORDER BY d.designation_name";
        
        return $db->fetchAll($sql, ['dept_id' => $departmentId]);
    }
    
    /**
     * Get course enrollment
     */
    private function getCourseEnrollment($departmentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT 
                    c.course_name,
                    COUNT(s.id) as enrolled_students
                FROM courses c
                LEFT JOIN students s ON c.id = s.course_id AND s.status = 'active'
                WHERE c.department_id = :dept_id
                GROUP BY c.id, c.course_name
                ORDER BY c.course_name";
        
        return $db->fetchAll($sql, ['dept_id' => $departmentId]);
    }
    
    /**
     * Get research output
     */
    private function getResearchOutput($departmentId)
    {
        // This would be implemented when research module is added
        return [
            'publications' => 0,
            'projects' => 0,
            'grants' => 0
        ];
    }
}