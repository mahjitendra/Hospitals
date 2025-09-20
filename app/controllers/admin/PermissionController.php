<?php

/**
 * Permission Management Controller
 * 
 * Handles permission CRUD operations and management
 */
class PermissionController extends Controller
{
    private $permissionModel;
    private $roleModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireRole(ROLE_ADMIN);
        
        $this->permissionModel = new Permission();
        $this->roleModel = new Role();
    }
    
    /**
     * Display permissions list
     */
    public function index()
    {
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $module = $this->input('module', '');
        
        $where = '1=1';
        $params = [];
        
        if (!empty($search)) {
            $where .= ' AND (name LIKE :search OR description LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($module)) {
            $where .= ' AND name LIKE :module';
            $params['module'] = "{$module}_%";
        }
        
        $permissions = $this->permissionModel->paginate($page, 25, $where, $params, 'name ASC');
        $groupedPermissions = $this->groupPermissionsByModule($permissions['data']);
        $modules = $this->getPermissionModules();
        
        $this->render('admin/permissions/index', [
            'title' => 'Permission Management',
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
            'modules' => $modules,
            'filters' => [
                'search' => $search,
                'module' => $module
            ]
        ], 'admin');
    }
    
    /**
     * Show create permission form
     */
    public function create()
    {
        $modules = $this->getPermissionModules();
        
        $this->render('admin/permissions/create', [
            'title' => 'Create Permission',
            'modules' => $modules
        ], 'admin');
    }
    
    /**
     * Store new permission
     */
    public function store()
    {
        $data = $this->validate([
            'name' => 'required|min:2|max:100|unique:permissions',
            'description' => 'required|min:5|max:255',
            'module' => 'required|min:2|max:50'
        ]);
        
        try {
            // Format permission name
            $data['name'] = strtolower($data['module']) . '_' . strtolower(str_replace(' ', '_', $data['name']));
            
            $permissionId = $this->permissionModel->create($data);
            
            $this->logActivity('permission_created', "Created permission: {$data['name']}", $data);
            $this->flash('success', 'Permission created successfully');
            
            $this->redirect('/admin/permissions');
            
        } catch (Exception $e) {
            Logger::error('Permission creation failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to create permission');
            $this->back();
        }
    }
    
    /**
     * Show permission details
     */
    public function show($id)
    {
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            $this->flash('error', 'Permission not found');
            $this->redirect('/admin/permissions');
        }
        
        $permission['roles'] = $this->getPermissionRoles($id);
        
        $this->render('admin/permissions/show', [
            'title' => 'Permission Details',
            'permission' => $permission
        ], 'admin');
    }
    
    /**
     * Show edit permission form
     */
    public function edit($id)
    {
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            $this->flash('error', 'Permission not found');
            $this->redirect('/admin/permissions');
        }
        
        $modules = $this->getPermissionModules();
        
        $this->render('admin/permissions/edit', [
            'title' => 'Edit Permission',
            'permission' => $permission,
            'modules' => $modules
        ], 'admin');
    }
    
    /**
     * Update permission
     */
    public function update($id)
    {
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            $this->flash('error', 'Permission not found');
            $this->redirect('/admin/permissions');
        }
        
        $data = $this->validate([
            'description' => 'required|min:5|max:255',
            'module' => 'required|min:2|max:50'
        ]);
        
        try {
            $this->permissionModel->update($id, $data);
            
            $this->logActivity('permission_updated', "Updated permission: {$permission['name']}", $data);
            $this->flash('success', 'Permission updated successfully');
            
            $this->redirect('/admin/permissions');
            
        } catch (Exception $e) {
            Logger::error('Permission update failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to update permission');
            $this->back();
        }
    }
    
    /**
     * Delete permission
     */
    public function destroy($id)
    {
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            $this->flash('error', 'Permission not found');
            $this->redirect('/admin/permissions');
        }
        
        // Check if permission is assigned to roles
        $roleCount = $this->getPermissionRoleCount($id);
        if ($roleCount > 0) {
            $this->flash('error', 'Cannot delete permission that is assigned to roles');
            $this->redirect('/admin/permissions');
        }
        
        try {
            $this->permissionModel->delete($id);
            
            $this->logActivity('permission_deleted', "Deleted permission: {$permission['name']}", $permission);
            $this->flash('success', 'Permission deleted successfully');
            
        } catch (Exception $e) {
            Logger::error('Permission deletion failed: ' . $e->getMessage());
            $this->flash('error', 'Failed to delete permission');
        }
        
        $this->redirect('/admin/permissions');
    }
    
    /**
     * Group permissions by module
     */
    private function groupPermissionsByModule($permissions)
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
     * Get permission modules
     */
    private function getPermissionModules()
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT SUBSTRING_INDEX(name, '_', 1) as module 
                FROM permissions 
                ORDER BY module";
        
        $result = $db->fetchAll($sql);
        return array_column($result, 'module');
    }
    
    /**
     * Get permission roles
     */
    private function getPermissionRoles($permissionId)
    {
        $db = Database::getInstance();
        $sql = "SELECT r.* FROM roles r 
                JOIN role_permissions rp ON r.id = rp.role_id 
                WHERE rp.permission_id = :permission_id 
                ORDER BY r.name";
        
        return $db->fetchAll($sql, ['permission_id' => $permissionId]);
    }
    
    /**
     * Get permission role count
     */
    private function getPermissionRoleCount($permissionId)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM role_permissions WHERE permission_id = :permission_id";
        $result = $db->fetch($sql, ['permission_id' => $permissionId]);
        return $result['count'] ?? 0;
    }
}