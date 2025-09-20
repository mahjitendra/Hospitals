<?php

/**
 * User Management Controller
 * 
 * Handles user CRUD operations and management
 */
class UserController extends Controller
{
    private $userModel;
    private $roleModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(ROLE_ADMIN);
        
        $this->userModel = new User();
        $this->roleModel = new Role();
    }
    
    /**
     * Display users list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $role = $this->input('role', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (name LIKE :search OR email LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($role)) {
            $where .= ' AND id IN (SELECT user_id FROM user_roles ur 
                                   JOIN roles r ON ur.role_id = r.id 
                                   WHERE r.name = :role)';
            $params['role'] = $role;
        }
        
        $users = $this->userModel->paginate($page, 25, $where, $params);
        $roles = $this->roleModel->all();
        
        $this->render('admin/users/index', [
            'title' => 'User Management',
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'selectedRole' => $role
        ], 'admin');
    }
    
    /**
     * Show create user form
     */
    public function create()
    {
        $roles = $this->roleModel->all();
        
        $this->render('admin/users/create', [
            'title' => 'Create User',
            'roles' => $roles
        ], 'admin');
    }
    
    /**
     * Store new user
     */
    public function store()
    {
        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|strong_password|confirmed',
            'status' => 'required|in:active,inactive',
            'roles' => 'required'
        ]);
        
        try {
            $this->userModel->beginTransaction();
            
            // Hash password
            $data['password'] = bcrypt($data['password']);
            
            // Create user
            $userId = $this->userModel->create($data);
            
            // Assign roles
            $auth = new Auth();
            foreach ($data['roles'] as $role) {
                $auth->assignRole($userId, $role);
            }
            
            $this->userModel->commit();
            
            $this->logActivity('user_created', "Created user: {$data['name']}", $data);
            $this->flash('success', 'User created successfully');
            
            $this->redirect('/admin/users');
            
        } catch (Exception $e) {
            $this->userModel->rollback();
            Logger::error('User creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create user');
            $this->back();
        }
    }
    
    /**
     * Show user details
     */
    public function show($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->flash('error', 'User not found');
            $this->redirect('/admin/users');
        }
        
        // Get user roles and permissions
        $auth = new Auth();
        $user['roles'] = $auth->getUserRoles($id);
        $user['permissions'] = $auth->getUserPermissions($id);
        
        // Get user activity
        $activities = $this->getUserActivities($id);
        
        $this->render('admin/users/show', [
            'title' => 'User Details',
            'user' => $user,
            'activities' => $activities
        ], 'admin');
    }
    
    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->flash('error', 'User not found');
            $this->redirect('/admin/users');
        }
        
        $roles = $this->roleModel->all();
        $userRoles = $this->getUserRoles($id);
        
        $this->render('admin/users/edit', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles
        ], 'admin');
    }
    
    /**
     * Update user
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->flash('error', 'User not found');
            $this->redirect('/admin/users');
        }
        
        $rules = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'status' => 'required|in:active,inactive,suspended',
            'roles' => 'required'
        ];
        
        // Only validate password if provided
        if (!empty($this->input('password'))) {
            $rules['password'] = 'strong_password|confirmed';
        }
        
        $data = $this->validate($rules);
        
        try {
            $this->userModel->beginTransaction();
            
            // Check email uniqueness (excluding current user)
            $existingUser = $this->userModel->findBy('email', $data['email']);
            if ($existingUser && $existingUser['id'] != $id) {
                throw new Exception('Email already exists');
            }
            
            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }
            
            // Update user
            $this->userModel->update($id, $data);
            
            // Update roles
            $this->updateUserRoles($id, $data['roles']);
            
            $this->userModel->commit();
            
            $this->logActivity('user_updated', "Updated user: {$data['name']}", $data);
            $this->flash('success', 'User updated successfully');
            
            $this->redirect('/admin/users');
            
        } catch (Exception $e) {
            $this->userModel->rollback();
            Logger::error('User update failed: ' . $e->getMessage());
            $this->flash('error', $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->flash('error', 'User not found');
            $this->redirect('/admin/users');
        }
        
        // Prevent deleting super admin
        if ($this->hasRole(ROLE_SUPER_ADMIN, $id)) {
            $this->flash('error', 'Cannot delete super admin user');
            $this->redirect('/admin/users');
        }
        
        try {
            $this->userModel->beginTransaction();
            
            // Delete user roles
            $db = Database::getInstance();
            $db->delete('user_roles', 'user_id = :user_id', ['user_id' => $id]);
            
            // Soft delete user
            $this->userModel->softDelete($id);
            
            $this->userModel->commit();
            
            $this->logActivity('user_deleted', "Deleted user: {$user['name']}", $user);
            $this->flash('success', 'User deleted successfully');
            
        } catch (Exception $e) {
            $this->userModel->rollback();
            Logger::error('User deletion failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to delete user');
        }
        
        $this->redirect('/admin/users');
    }
    
    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User not found']);
        }
        
        $newStatus = $user['status'] === STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE;
        
        if ($this->userModel->update($id, ['status' => $newStatus])) {
            $this->logActivity('user_status_changed', "Changed user status: {$user['name']}", [
                'old_status' => $user['status'],
                'new_status' => $newStatus
            ]);
            
            return $this->json(['success' => true, 'status' => $newStatus]);
        }
        
        return $this->json(['success' => false, 'message' => 'Failed to update status']);
    }
    
    /**
     * Get user roles
     */
    private function getUserRoles($userId)
    {
        $db = Database::getInstance();
        $sql = "SELECT r.name FROM roles r 
                JOIN user_roles ur ON r.id = ur.role_id 
                WHERE ur.user_id = :user_id";
        
        $roles = $db->fetchAll($sql, ['user_id' => $userId]);
        return array_column($roles, 'name');
    }
    
    /**
     * Update user roles
     */
    private function updateUserRoles($userId, $roles)
    {
        $db = Database::getInstance();
        
        // Remove existing roles
        $db->delete('user_roles', 'user_id = :user_id', ['user_id' => $userId]);
        
        // Add new roles
        $auth = new Auth();
        foreach ($roles as $role) {
            $auth->assignRole($userId, $role);
        }
    }
    
    /**
     * Get user activities
     */
    private function getUserActivities($userId)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM activity_logs 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT 20";
        
        return $db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    /**
     * Check if user has role
     */
    private function hasRole($role, $userId)
    {
        $userRoles = $this->getUserRoles($userId);
        return in_array($role, $userRoles);
    }
}