<?php

/**
 * Faculty Management Controller
 * 
 * Handles faculty CRUD operations and management
 */
class FacultyController extends Controller
{
    private $facultyModel;
    private $userModel;
    private $departmentModel;
    private $designationModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->facultyModel = new Faculty();
        $this->userModel = new User();
        $this->departmentModel = new Department();
        $this->designationModel = new Designation();
    }
    
    /**
     * Display faculty list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $department = $this->input('department', '');
        $designation = $this->input('designation', '');
        $status = $this->input('status', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (first_name LIKE :search OR last_name LIKE :search OR employee_id LIKE :search OR email LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($department)) {
            $where .= ' AND department_id = :department';
            $params['department'] = $department;
        }
        
        if (!empty($designation)) {
            $where .= ' AND designation_id = :designation';
            $params['designation'] = $designation;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $faculty = $this->facultyModel->paginate($page, 25, $where, $params, 'first_name ASC');
        $departments = $this->departmentModel->all('name ASC');
        $designations = $this->designationModel->all('name ASC');
        
        $this->render('faculty/index', [
            'title' => 'Faculty Management',
            'faculty' => $faculty,
            'departments' => $departments,
            'designations' => $designations,
            'filters' => [
                'search' => $search,
                'department' => $department,
                'designation' => $designation,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show create faculty form
     */
    public function create()
    {
        $this->requirePermission('faculty_create');
        
        $departments = $this->departmentModel->all('name ASC');
        $designations = $this->designationModel->all('name ASC');
        
        $this->render('faculty/create', [
            'title' => 'Add New Faculty',
            'departments' => $departments,
            'designations' => $designations
        ]);
    }
    
    /**
     * Store new faculty
     */
    public function store()
    {
        $this->requirePermission('faculty_create');
        
        $data = $this->validate([
            'employee_id' => 'required|unique:faculty',
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:faculty',
            'phone' => 'required|phone',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'joining_date' => 'required|date',
            'qualification' => 'required|min:2',
            'experience' => 'numeric',
            'salary' => 'required|numeric',
            'address' => 'required|min:10',
            'city' => 'required|min:2',
            'state' => 'required|min:2',
            'pincode' => 'required|numeric|min:6',
            'emergency_contact' => 'required|phone'
        ]);
        
        try {
            $this->facultyModel->beginTransaction();
            
            // Handle photo upload
            if ($this->request->hasFile('photo')) {
                $photo = $this->upload('photo', 'faculty/photos', ['jpg', 'jpeg', 'png']);
                $data['photo'] = $photo['filename'];
            }
            
            // Handle document uploads
            if ($this->request->hasFile('documents')) {
                $documents = $this->upload('documents', 'faculty/documents', ['pdf', 'doc', 'docx']);
                $data['documents'] = json_encode($documents);
            }
            
            // Set default status
            $data['status'] = STATUS_ACTIVE;
            
            // Create faculty
            $facultyId = $this->facultyModel->create($data);
            
            // Create user account
            $userId = $this->createFacultyUser($facultyId, $data);
            
            // Update faculty with user_id
            $this->facultyModel->update($facultyId, ['user_id' => $userId]);
            
            $this->facultyModel->commit();
            
            $this->logActivity('faculty_created', "Created faculty: {$data['first_name']} {$data['last_name']}", $data);
            $this->flash('success', 'Faculty added successfully');
            
            $this->redirect('/faculty');
            
        } catch (Exception $e) {
            $this->facultyModel->rollback();
            Logger::error('Faculty creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to add faculty: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show faculty details
     */
    public function show($id)
    {
        $faculty = $this->facultyModel->find($id);
        if (!$faculty) {
            $this->flash('error', 'Faculty not found');
            $this->redirect('/faculty');
        }
        
        // Get related data
        $faculty['department'] = $this->departmentModel->find($faculty['department_id']);
        $faculty['designation'] = $this->designationModel->find($faculty['designation_id']);
        $faculty['subjects'] = $this->getFacultySubjects($id);
        $faculty['classes'] = $this->getFacultyClasses($id);
        $faculty['attendance'] = $this->getFacultyAttendanceStats($id);
        $faculty['salary'] = $this->getCurrentSalary($id);
        
        $this->render('faculty/show', [
            'title' => 'Faculty Profile',
            'faculty' => $faculty
        ]);
    }
    
    /**
     * Create user account for faculty
     */
    private function createFacultyUser($facultyId, $data)
    {
        $userData = [
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['employee_id']), // Default password
            'status' => STATUS_ACTIVE
        ];
        
        $userId = $this->userModel->create($userData);
        
        // Assign faculty role
        $auth = new Auth();
        $auth->assignRole($userId, ROLE_TEACHER);
        
        return $userId;
    }
    
    /**
     * Get faculty subjects
     */
    private function getFacultySubjects($facultyId)
    {
        $db = Database::getInstance();
        $sql = "SELECT s.* FROM subjects s 
                JOIN faculty_subjects fs ON s.id = fs.subject_id 
                WHERE fs.faculty_id = :faculty_id";
        
        return $db->fetchAll($sql, ['faculty_id' => $facultyId]);
    }
    
    /**
     * Get faculty classes
     */
    private function getFacultyClasses($facultyId)
    {
        $db = Database::getInstance();
        $sql = "SELECT c.* FROM classes c 
                JOIN class_teachers ct ON c.id = ct.class_id 
                WHERE ct.faculty_id = :faculty_id";
        
        return $db->fetchAll($sql, ['faculty_id' => $facultyId]);
    }
    
    /**
     * Get faculty attendance statistics
     */
    private function getFacultyAttendanceStats($facultyId)
    {
        $db = Database::getInstance();
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
                FROM faculty_attendance 
                WHERE faculty_id = :faculty_id 
                AND attendance_date >= :start_date";
        
        $result = $db->fetch($sql, [
            'faculty_id' => $facultyId,
            'start_date' => date('Y-m-01')
        ]);
        
        if ($result['total_days'] > 0) {
            $result['percentage'] = round(($result['present_days'] / $result['total_days']) * 100, 2);
        } else {
            $result['percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get current salary
     */
    private function getCurrentSalary($facultyId)
    {
        $salaryModel = new Salary();
        return $salaryModel->first('faculty_id = :faculty_id', ['faculty_id' => $facultyId], 'created_at DESC');
    }
}