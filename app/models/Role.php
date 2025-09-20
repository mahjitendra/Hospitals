<?php

/**
 * Role Model
 * 
 * Handles role data operations
 */
class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['name', 'description', 'is_system_role'];
    
    /**
     * Find role by name
     */
    public function findByName($name)
    {
        return $this->findBy('name', $name);
    }
    
    /**
     * Get role with permissions
     */
    public function findWithPermissions($id)
    {
        $role = $this->find($id);
        if ($role) {
            $role['permissions'] = $this->getRolePermissions($id);
        }
        return $role;
    }
    
    /**
     * Get role permissions
     */
    public function getRolePermissions($roleId)
    {
        $sql = "SELECT p.* FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                WHERE rp.role_id = :role_id 
                ORDER BY p.name";
        
        return $this->db->fetchAll($sql, ['role_id' => $roleId]);
    }
    
    /**
     * Get role users
     */
    public function getRoleUsers($roleId)
    {
        $sql = "SELECT u.* FROM users u 
                JOIN user_roles ur ON u.id = ur.user_id 
                WHERE ur.role_id = :role_id 
                ORDER BY u.name";
        
        return $this->db->fetchAll($sql, ['role_id' => $roleId]);
    }
    
    /**
     * Check if role has permission
     */
    public function hasPermission($roleId, $permission)
    {
        $sql = "SELECT COUNT(*) as count FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id AND p.name = :permission";
        
        $result = $this->db->fetch($sql, ['role_id' => $roleId, 'permission' => $permission]);
        return $result['count'] > 0;
    }
    
    /**
     * Assign permission to role
     */
    public function assignPermission($roleId, $permissionId)
    {
        // Check if already assigned
        $exists = $this->db->exists('role_permissions', 
            'role_id = :role_id AND permission_id = :permission_id',
            ['role_id' => $roleId, 'permission_id' => $permissionId]
        );
        
        if (!$exists) {
            return $this->db->insert('role_permissions', [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ]);
        }
        
        return true;
    }
    
    /**
     * Remove permission from role
     */
    public function removePermission($roleId, $permissionId)
    {
        return $this->db->delete('role_permissions', 
            'role_id = :role_id AND permission_id = :permission_id',
            ['role_id' => $roleId, 'permission_id' => $permissionId]
        );
    }
    
    /**
     * Get system roles
     */
    public function getSystemRoles()
    {
        return $this->where('is_system_role = :system', ['system' => 1], 'name ASC');
    }
    
    /**
     * Get custom roles
     */
    public function getCustomRoles()
    {
        return $this->where('is_system_role = :system', ['system' => 0], 'name ASC');
    }
    
    /**
     * Get role statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_roles,
                    SUM(CASE WHEN is_system_role = 1 THEN 1 ELSE 0 END) as system_roles,
                    SUM(CASE WHEN is_system_role = 0 THEN 1 ELSE 0 END) as custom_roles
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get roles with user count
     */
    public function getRolesWithUserCount()
    {
        $sql = "SELECT r.*, COUNT(ur.user_id) as user_count
                FROM {$this->table} r
                LEFT JOIN user_roles ur ON r.id = ur.role_id
                GROUP BY r.id
                ORDER BY r.name";
        
        return $this->db->fetchAll($sql);
    }
}