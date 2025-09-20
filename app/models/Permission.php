<?php

/**
 * Permission Model
 * 
 * Handles permission data operations
 */
class Permission extends Model
{
    protected $table = 'permissions';
    protected $fillable = ['name', 'description', 'module', 'is_system_permission'];
    
    /**
     * Find permission by name
     */
    public function findByName($name)
    {
        return $this->findBy('name', $name);
    }
    
    /**
     * Get permissions by module
     */
    public function getByModule($module)
    {
        return $this->where('module = :module', ['module' => $module], 'name ASC');
    }
    
    /**
     * Get permission roles
     */
    public function getPermissionRoles($permissionId)
    {
        $sql = "SELECT r.* FROM roles r 
                JOIN role_permissions rp ON r.id = rp.role_id 
                WHERE rp.permission_id = :permission_id 
                ORDER BY r.name";
        
        return $this->db->fetchAll($sql, ['permission_id' => $permissionId]);
    }
    
    /**
     * Get all modules
     */
    public function getModules()
    {
        $sql = "SELECT DISTINCT module FROM {$this->table} WHERE module IS NOT NULL ORDER BY module";
        $result = $this->db->fetchAll($sql);
        return array_column($result, 'module');
    }
    
    /**
     * Get permissions grouped by module
     */
    public function getGroupedByModule()
    {
        $permissions = $this->all('module ASC, name ASC');
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $module = $permission['module'] ?? 'general';
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
    
    /**
     * Get system permissions
     */
    public function getSystemPermissions()
    {
        return $this->where('is_system_permission = :system', ['system' => 1], 'name ASC');
    }
    
    /**
     * Get custom permissions
     */
    public function getCustomPermissions()
    {
        return $this->where('is_system_permission = :system', ['system' => 0], 'name ASC');
    }
    
    /**
     * Check if permission is assigned to any role
     */
    public function isAssigned($permissionId)
    {
        return $this->db->exists('role_permissions', 'permission_id = :permission_id', ['permission_id' => $permissionId]);
    }
    
    /**
     * Get permission statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_permissions,
                    COUNT(DISTINCT module) as total_modules,
                    SUM(CASE WHEN is_system_permission = 1 THEN 1 ELSE 0 END) as system_permissions,
                    SUM(CASE WHEN is_system_permission = 0 THEN 1 ELSE 0 END) as custom_permissions
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Create default permissions for module
     */
    public function createModulePermissions($module, $actions = ['create', 'read', 'update', 'delete'])
    {
        $permissions = [];
        
        foreach ($actions as $action) {
            $permissionName = $module . '_' . $action;
            $description = ucfirst($action) . ' ' . ucfirst($module);
            
            // Check if permission already exists
            if (!$this->findByName($permissionName)) {
                $permissionId = $this->create([
                    'name' => $permissionName,
                    'description' => $description,
                    'module' => $module,
                    'is_system_permission' => 1
                ]);
                
                $permissions[] = $permissionId;
            }
        }
        
        return $permissions;
    }
}