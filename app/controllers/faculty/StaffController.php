<?php

/**
 * Staff Management Controller
 * 
 * Handles non-teaching staff operations
 */
class StaffController extends Controller
{
    private $staffModel;
    private $userModel;
    private $departmentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->staffModel = new Staff();
        $this->userModel = new User();
        $this->departmentModel = new Department();
    }
    
    /**
     * Display staff list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $department = $this->input('department', '');
        $type = $this->input('type', '');
        $status = $this->input('status', '');
        
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
        
        if (!empty($type)) {
            $where .= ' AND staff_type = :type';
            $params['type'] = $type;
        }
        
        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $staff = $this->staffModel->paginate($page, 25, $where, $params, 'first_name ASC');
        $departments = $this->departmentModel->all('department_name ASC');
        
        $this->render('faculty/staff/index', [
            'title' => 'Staff Management',
            'staff' => $staff,
            'departments' => $departments,
            'filters' => [
                'search' => $search,
                'department' => $department,
                'type' => $type,
                'status' => $status
            ]
        ]);
    }
    
    /**
     * Show create staff form
     */
    public function create()
    {
        $this->requirePermission('staff_create');
        
        $departments = $this->departmentModel->all('department_name ASC');
        $designations = $this->getDesignations();
        
        $this->render('faculty/staff/create', [
            'title' => 'Add New Staff',
            'departments' => $departments,
            'designations' => $designations
        ]);
    }
    
    /**
     * Store new staff
     */
    public function store()
    {
        $this->requirePermission('staff_create');
        
        $data = $this->validate([
            'employee_id' => 'required|unique:staff',
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:staff',
            'phone' => 'required|phone',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'staff_type' => 'required|in:administrative,technical,support,security,maintenance',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'joining_date' => 'required|date',
            'qualification' => 'required|min:2',
            'experience' => 'numeric',
            'salary' => 'required|numeric',
            'address' => 'required|min:10',
            'emergency_contact' => 'required|phone'
        ]);
        
        try {
            $this->staffModel->beginTransaction();
            
            // Handle photo upload
            if ($this->request->hasFile('photo')) {
                $photo = $this->upload('photo', 'staff/photos', ['jpg', 'jpeg', 'png']);
                $data['photo'] = $photo['filename'];
            }
            
            // Set default status
            $data['status'] = STATUS_ACTIVE;
            
            // Create staff
            $staffId = $this->staffModel->create($data);
            
            // Create user account
            $userId = $this->createStaffUser($staffId, $data);
            
            // Update staff with user_id
            $this->staffModel->update($staffId, ['user_id' => $userId]);
            
            $this->staffModel->commit();
            
            $this->logActivity('staff_created', "Created staff: {$data['first_name']} {$data['last_name']}", $data);
            $this->flash('success', 'Staff added successfully');
            
            $this->redirect('/faculty/staff');
            
        } catch (Exception $e) {
            $this->staffModel->rollback();
            Logger::error('Staff creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to add staff: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show staff details
     */
    public function show($id)
    {
        $staff = $this->staffModel->find($id);
        if (!$staff) {
            $this->flash('error', 'Staff not found');
            $this->redirect('/faculty/staff');
        }
        
        $staff['department'] = $this->departmentModel->find($staff['department_id']);
        $staff['designation'] = $this->getDesignationDetails($staff['designation_id']);
        $staff['attendance'] = $this->getStaffAttendanceStats($id);
        $staff['leave_balance'] = $this->getLeaveBalance($id);
        $staff['performance'] = $this->getPerformanceRating($id);
        
        $this->render('faculty/staff/show', [
            'title' => 'Staff Profile',
            'staff' => $staff
        ]);
    }
    
    /**
     * Show edit staff form
     */
    public function edit($id)
    {
        $this->requirePermission('staff_edit');
        
        $staff = $this->staffModel->find($id);
        if (!$staff) {
            $this->flash('error', 'Staff not found');
            $this->redirect('/faculty/staff');
        }
        
        $departments = $this->departmentModel->all('department_name ASC');
        $designations = $this->getDesignations();
        
        $this->render('faculty/staff/edit', [
            'title' => 'Edit Staff',
            'staff' => $staff,
            'departments' => $departments,
            'designations' => $designations
        ]);
    }
    
    /**
     * Update staff
     */
    public function update($id)
    {
        $this->requirePermission('staff_edit');
        
        $staff = $this->staffModel->find($id);
        if (!$staff) {
            $this->flash('error', 'Staff not found');
            $this->redirect('/faculty/staff');
        }
        
        $data = $this->validate([
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'staff_type' => 'required|in:administrative,technical,support,security,maintenance',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'qualification' => 'required|min:2',
            'experience' => 'numeric',
            'salary' => 'required|numeric',
            'address' => 'required|min:10',
            'emergency_contact' => 'required|phone',
            'status' => 'required|in:active,inactive,suspended,terminated'
        ]);
        
        try {
            // Check email uniqueness
            $existingStaff = $this->staffModel->findBy('email', $data['email']);
            if ($existingStaff && $existingStaff['id'] != $id) {
                throw new Exception('Email already exists');
            }
            
            // Handle photo upload
            if ($this->request->hasFile('photo')) {
                $photo = $this->upload('photo', 'staff/photos', ['jpg', 'jpeg', 'png']);
                $data['photo'] = $photo['filename'];
                
                // Delete old photo
                if (!empty($staff['photo'])) {
                    $oldPhotoPath = public_path('uploads/staff/photos/' . $staff['photo']);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
            }
            
            $this->staffModel->update($id, $data);
            
            $this->logActivity('staff_updated', "Updated staff: {$data['first_name']} {$data['last_name']}", $data);
            $this->flash('success', 'Staff updated successfully');
            
            $this->redirect('/faculty/staff');
            
        } catch (Exception $e) {
            Logger::error('Staff update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update staff: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Create user account for staff
     */
    private function createStaffUser($staffId, $data)
    {
        $userData = [
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['employee_id']), // Default password
            'status' => STATUS_ACTIVE
        ];
        
        $userId = $this->userModel->create($userData);
        
        // Assign staff role
        $auth = new Auth();
        $auth->assignRole($userId, ROLE_STAFF);
        
        return $userId;
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
     * Get staff attendance statistics
     */
    private function getStaffAttendanceStats($staffId)
    {
        $attendanceModel = new StaffAttendance();
        return $attendanceModel->getAttendanceStats($staffId);
    }
    
    /**
     * Get leave balance
     */
    private function getLeaveBalance($staffId)
    {
        $leaveModel = new Leave();
        return $leaveModel->getLeaveBalance($staffId);
    }
    
    /**
     * Get performance rating
     */
    private function getPerformanceRating($staffId)
    {
        $performanceModel = new Performance();
        return $performanceModel->getLatestRating($staffId);
    }
}