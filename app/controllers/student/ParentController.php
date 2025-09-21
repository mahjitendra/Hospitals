<?php

/**
 * Parent Management Controller
 * 
 * Handles parent/guardian management for students
 */
class ParentController extends Controller
{
    private $parentModel;
    private $studentModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->parentModel = new ParentModel();
        $this->studentModel = new Student();
        $this->userModel = new User();
    }
    
    /**
     * Display student's parents/guardians
     */
    public function index($studentId)
    {
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        $parents = $this->parentModel->where('student_id = :student_id', ['student_id' => $studentId]);
        
        $this->render('student/parents/index', [
            'title' => 'Parent/Guardian Management',
            'student' => $student,
            'parents' => $parents
        ]);
    }
    
    /**
     * Show create parent form
     */
    public function create($studentId)
    {
        $this->requirePermission('student_manage');
        
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        $this->render('student/parents/create', [
            'title' => 'Add Parent/Guardian',
            'student' => $student
        ]);
    }
    
    /**
     * Store new parent
     */
    public function store($studentId)
    {
        $this->requirePermission('student_manage');
        
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/students');
        }
        
        $data = $this->validate([
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:parents',
            'phone' => 'required|phone',
            'relation' => 'required|in:father,mother,guardian,uncle,aunt,grandfather,grandmother,other',
            'occupation' => 'required|min:2',
            'address' => 'required|min:10',
            'city' => 'required|min:2',
            'state' => 'required|min:2',
            'pincode' => 'required|numeric|min:6',
            'is_primary' => 'boolean',
            'can_pickup' => 'boolean',
            'emergency_contact' => 'boolean'
        ]);
        
        try {
            $this->parentModel->beginTransaction();
            
            // Handle photo upload
            if ($this->request->hasFile('photo')) {
                $photo = $this->upload('photo', 'parents/photos', ['jpg', 'jpeg', 'png']);
                $data['photo'] = $photo['filename'];
            }
            
            $data['student_id'] = $studentId;
            
            // Create parent
            $parentId = $this->parentModel->create($data);
            
            // Create user account for parent
            $userId = $this->createParentUser($parentId, $data);
            
            // Update parent with user_id
            $this->parentModel->update($parentId, ['user_id' => $userId]);
            
            $this->parentModel->commit();
            
            // Send welcome email
            $this->sendParentWelcomeEmail($data, $student);
            
            $this->logActivity('parent_added', "Added parent: {$data['first_name']} {$data['last_name']} for student: {$student['first_name']} {$student['last_name']}", $data);
            $this->flash('success', 'Parent/Guardian added successfully');
            
            $this->redirect('/students/' . $studentId . '/parents');
            
        } catch (Exception $e) {
            $this->parentModel->rollback();
            Logger::error('Parent creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to add parent: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show parent details
     */
    public function show($parentId)
    {
        $parent = $this->parentModel->find($parentId);
        if (!$parent) {
            $this->flash('error', 'Parent not found');
            $this->redirect('/students');
        }
        
        $parent['student'] = $this->studentModel->find($parent['student_id']);
        $parent['children'] = $this->getParentChildren($parentId);
        
        $this->render('student/parents/show', [
            'title' => 'Parent Details',
            'parent' => $parent
        ]);
    }
    
    /**
     * Show edit parent form
     */
    public function edit($parentId)
    {
        $this->requirePermission('student_manage');
        
        $parent = $this->parentModel->find($parentId);
        if (!$parent) {
            $this->flash('error', 'Parent not found');
            $this->redirect('/students');
        }
        
        $parent['student'] = $this->studentModel->find($parent['student_id']);
        
        $this->render('student/parents/edit', [
            'title' => 'Edit Parent/Guardian',
            'parent' => $parent
        ]);
    }
    
    /**
     * Update parent
     */
    public function update($parentId)
    {
        $this->requirePermission('student_manage');
        
        $parent = $this->parentModel->find($parentId);
        if (!$parent) {
            $this->flash('error', 'Parent not found');
            $this->redirect('/students');
        }
        
        $data = $this->validate([
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'relation' => 'required|in:father,mother,guardian,uncle,aunt,grandfather,grandmother,other',
            'occupation' => 'required|min:2',
            'address' => 'required|min:10',
            'city' => 'required|min:2',
            'state' => 'required|min:2',
            'pincode' => 'required|numeric|min:6',
            'is_primary' => 'boolean',
            'can_pickup' => 'boolean',
            'emergency_contact' => 'boolean'
        ]);
        
        try {
            // Check email uniqueness (excluding current parent)
            $existingParent = $this->parentModel->findBy('email', $data['email']);
            if ($existingParent && $existingParent['id'] != $parentId) {
                throw new Exception('Email already exists');
            }
            
            // Handle photo upload
            if ($this->request->hasFile('photo')) {
                $photo = $this->upload('photo', 'parents/photos', ['jpg', 'jpeg', 'png']);
                $data['photo'] = $photo['filename'];
                
                // Delete old photo
                if (!empty($parent['photo'])) {
                    $oldPhotoPath = public_path('uploads/parents/photos/' . $parent['photo']);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
            }
            
            $this->parentModel->update($parentId, $data);
            
            $this->logActivity('parent_updated', "Updated parent: {$data['first_name']} {$data['last_name']}", $data);
            $this->flash('success', 'Parent/Guardian updated successfully');
            
            $this->redirect('/students/' . $parent['student_id'] . '/parents');
            
        } catch (Exception $e) {
            Logger::error('Parent update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update parent: ' . $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Delete parent
     */
    public function destroy($parentId)
    {
        $this->requirePermission('student_manage');
        
        $parent = $this->parentModel->find($parentId);
        if (!$parent) {
            return $this->json(['success' => false, 'message' => 'Parent not found']);
        }
        
        try {
            $this->parentModel->beginTransaction();
            
            // Deactivate user account if exists
            if (!empty($parent['user_id'])) {
                $this->userModel->update($parent['user_id'], ['status' => STATUS_INACTIVE]);
            }
            
            // Soft delete parent
            $this->parentModel->softDelete($parentId);
            
            $this->parentModel->commit();
            
            $this->logActivity('parent_deleted', "Deleted parent: {$parent['first_name']} {$parent['last_name']}", $parent);
            
            return $this->json(['success' => true, 'message' => 'Parent/Guardian deleted successfully']);
            
        } catch (Exception $e) {
            $this->parentModel->rollback();
            Logger::error('Parent deletion failed: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to delete parent']);
        }
    }
    
    /**
     * Create user account for parent
     */
    private function createParentUser($parentId, $data)
    {
        $userData = [
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['phone']), // Default password
            'status' => STATUS_ACTIVE
        ];
        
        $userId = $this->userModel->create($userData);
        
        // Assign parent role
        $auth = new Auth();
        $auth->assignRole($userId, ROLE_PARENT);
        
        return $userId;
    }
    
    /**
     * Send welcome email to parent
     */
    private function sendParentWelcomeEmail($parentData, $student)
    {
        $this->sendMail($parentData['email'], 'Welcome to College ERP - Parent Portal', 'parent_welcome', [
            'parent_name' => $parentData['first_name'] . ' ' . $parentData['last_name'],
            'student_name' => $student['first_name'] . ' ' . $student['last_name'],
            'login_url' => url('/login'),
            'default_password' => $parentData['phone']
        ]);
    }
    
    /**
     * Get parent's children
     */
    private function getParentChildren($parentId)
    {
        $db = Database::getInstance();
        $sql = "SELECT s.* FROM students s 
                JOIN parents p ON s.id = p.student_id 
                WHERE p.id = :parent_id";
        
        return $db->fetchAll($sql, ['parent_id' => $parentId]);
    }
}