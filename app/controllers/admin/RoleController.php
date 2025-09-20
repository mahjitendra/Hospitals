<?php

/**
 * Role Management Controller
 * 
 * Handles role CRUD operations and permissions
 */
class RoleController extends Controller
{
    private $roleModel;
    private $permissionModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(ROLE_ADMIN);
        
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }
    
    /**
     * Display roles list
     */
    public function index()
    {
        $roles = $this->roleModel->all('name ASC');
        
        // Get user count for each role
        foreach ($roles as &$role) {
            $role['user_count'] = $this->getUserCountForRole($role['id']);
            $role['permissions'] = $this->getRolePermissions($role['id']);
        }
        
        $this->render('admin/roles/index', [
            'title' => 'Role Management',
            'roles' => $roles
        ], 'admin');
    }
    
    /**
     * Show create role form
     */
    public function create()
    {
        $permissions = $this->permissionModel->all('name ASC');
        $groupedPermissions = $this->groupPermissions($permissions);
        
        $this->render('admin/roles/create', [
            'title' => 'Create Role',
            'permissions' => $groupedPermissions
        ], 'admin');
    }
    
    /**
     * Store new role
     */
    public function store()
    {
        $data = $this->validate([
            'name' => 'required|min:2|max:50|unique:roles',
            'description' => 'max:255',
            'permissions' => 'required'
        ]);
        
        try {
            $this->roleModel->beginTransaction();
            
            // Create role
            $roleId = $this->roleModel->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null
            ]);
            
            // Assign permissions
            $this->assignPermissions($roleId, $data['permissions']);
            
            $this->roleModel->commit();
            
            $this->logActivity('role_created', "Created role: {$data['name']}", $data);
            $this->flash('success', 'Role created successfully');
            
            $this->redirect('/admin/roles');
            
        } catch (Exception $e) {
            $this->roleModel->rollback();
            Logger::error('Role creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create role');
            $this->back();
        }
    }
    
    /**
     * Show role details
     */
    public function show($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            $this->flash('error', 'Role not found');
            $this->redirect('/admin/roles');
        }
        
        $role['permissions'] = $this->getRolePermissions($id);
        $role['users'] = $this->getRoleUsers($id);
        
        $this->render('admin/roles/show', [
            'title' => 'Role Details',
            'role' => $role
        ], 'admin');
    }
    
    /**
     * Show edit role form
     */
    public function edit($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            $this->flash('error', 'Role not found');
            $this->redirect('/admin/roles');
        }
        
        $permissions = $this->permissionModel->all('name ASC');
        $groupedPermissions = $this->groupPermissions($permissions);
        $rolePermissions = $this->getRolePermissions($id);
        
        $this->render('admin/roles/edit', [
            'title' => 'Edit Role',
            'role' => $role,
            'permissions' => $groupedPermissions,
            'rolePermissions' => array_column($rolePermissions, 'name')
        ], 'admin');
    }
    
    /**
     * Update role
     */
    public function update($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            $this->flash('error', 'Role not found');
            $this->redirect('/admin/roles');
        }
        
        $rules = [
            'name' => 'required|min:2|max:50',
            'description' => 'max:255',
            'permissions' => 'required'
        ];
        
        // Check name uniqueness (excluding current role)
        $existingRole = $this->roleModel->findBy('name', $this->input('name'));
        if ($existingRole && $existingRole['id'] != $id) {
            $this->flash('error', 'Role name already exists');
            $this->back();
            return;
        }
        
        $data = $this->validate($rules);
        
        try {
            $this->roleModel->beginTransaction();
            
            // Update role
            $this->roleModel->update($id, [
                'name' => $data['name'],
                'description' => $data['description'] ?? null
            ]);
            
            // Update permissions
            $this->updateRolePermissions($id, $data['permissions']);
            
            $this->roleModel->commit();
            
            $this->logActivity('role_updated', "Updated role: {$data['name']}", $data);
            $this->flash('success', 'Role updated successfully');
            
            $this->redirect('/admin/roles');
            
        } catch (Exception $e) {
            $this->roleModel->rollback();
            Logger::error('Role update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update role');
            $this->back();
        }
    }
    
    /**
     * Delete role
     */
    public function destroy($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            $this->flash('error', 'Role not found');
            $this->redirect('/admin/roles');
        }
        
        // Check if role is in use
        $userCount = $this->getUserCountForRole($id);
        if ($userCount > 0) {
            $this->flash('error', 'Cannot delete role that is assigned to users');
            $this->redirect('/admin/roles');
        }
        
        try {
            $this->roleModel->beginTransaction();
            
            // Delete role permissions
            $db = Database::getInstance();
            $db->delete('role_permissions', 'role_id = :role_id', ['role_id' => $id]);
            
            // Delete role
            $this->roleModel->delete($id);
            
            $this->roleModel->commit();
            
            $this->logActivity('role_deleted', "Deleted role: {$role['name']}", $role);
            $this->flash('success', 'Role deleted successfully');
            
        } catch (Exception $e) {
            $this->roleModel->rollback();
            Logger::error('Role deletion failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to delete role');
        }
        
        $this->redirect('/admin/roles');
    }
    
    /**
     * Get user count for role
     */
    private function getUserCountForRole($roleId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM user_roles WHERE role_id = :role_id";
        $result = $db->fetch($sql, ['role_id' => $roleId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get role permissions
     */
    private function getRolePermissions($roleId)
    {
        $db = Database::getInstance();
        $sql = "SELECT p.* FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                WHERE rp.role_id = :role_id 
                ORDER BY p.name";
        
        return $db->fetchAll($sql, ['role_id' => $roleId]);
    }
    
    /**
     * Get role users
     */
    private function getRoleUsers($roleId)
    {
        $db = Database::getInstance();
        $sql = "SELECT u.* FROM users u 
                JOIN user_roles ur ON u.id = ur.user_id 
                WHERE ur.role_id = :role_id 
                ORDER BY u.name";
        
        return $db->fetchAll($sql, ['role_id' => $roleId]);
    }
    
    /**
     * Group permissions by module
     */
    private function groupPermissions($permissions)
    {
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('_', $permission['name']);
            $module = $parts[0] ?? 'general';
            
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
    
    /**
     * Assign permissions to role
     */
    private function assignPermissions($roleId, $permissions)
    {
        $db = Database::getInstance();
        
        foreach ($permissions as $permissionName) {
            $permission = $this->permissionModel->findBy('name', $permissionName);
            if ($permission) {
                $db->insert('role_permissions', [
                    'role_id' => $roleId,
                    'permission_id' => $permission['id']
                ]);
            }
        }
    }
    
    /**
     * Update role permissions
     */
    private function updateRolePermissions($roleId, $permissions)
    {
        $db = Database::getInstance();
        
        // Remove existing permissions
        $db->delete('role_permissions', 'role_id = :role_id', ['role_id' => $roleId]);
        
        // Add new permissions
        $this->assignPermissions($roleId, $permissions);
    }
}