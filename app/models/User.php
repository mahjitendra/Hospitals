<?php

/**
 * User Model
 * 
 * Handles user data operations
 */
class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'status', 
        'email_verified_at', 'last_login', 'last_login_ip', 
        'remember_token', 'remember_token_expires', 
        'reset_token', 'reset_token_expires'
    ];
    
    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        return $this->findBy('email', $email);
    }
    
    /**
     * Get user with roles
     */
    public function findWithRoles($id)
    {
        $user = $this->find($id);
        if ($user) {
            $user['roles'] = $this->getUserRoles($id);
            $user['permissions'] = $this->getUserPermissions($id);
        }
        return $user;
    }
    
    /**
     * Get user roles
     */
    public function getUserRoles($userId)
    {
        $sql = "SELECT r.name, r.description FROM roles r 
                JOIN user_roles ur ON r.id = ur.role_id 
                WHERE ur.user_id = :user_id";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        $sql = "SELECT DISTINCT p.name, p.description FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = :user_id";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    /**
     * Check if user has role
     */
    public function hasRole($userId, $role)
    {
        $sql = "SELECT COUNT(*) as count FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id AND r.name = :role";
        
        $result = $this->db->fetch($sql, ['user_id' => $userId, 'role' => $role]);
        return $result['count'] > 0;
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $permission)
    {
        $sql = "SELECT COUNT(*) as count FROM user_roles ur
                JOIN role_permissions rp ON ur.role_id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE ur.user_id = :user_id AND p.name = :permission";
        
        $result = $this->db->fetch($sql, ['user_id' => $userId, 'permission' => $permission]);
        return $result['count'] > 0;
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($userId, $ip = null)
    {
        return $this->update($userId, [
            'last_login' => now(),
            'last_login_ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null)
        ]);
    }
    
    /**
     * Set remember token
     */
    public function setRememberToken($userId, $token, $expires)
    {
        return $this->update($userId, [
            'remember_token' => $token,
            'remember_token_expires' => $expires
        ]);
    }
    
    /**
     * Clear remember token
     */
    public function clearRememberToken($userId)
    {
        return $this->update($userId, [
            'remember_token' => null,
            'remember_token_expires' => null
        ]);
    }
    
    /**
     * Set password reset token
     */
    public function setResetToken($userId, $token, $expires)
    {
        return $this->update($userId, [
            'reset_token' => $token,
            'reset_token_expires' => $expires
        ]);
    }
    
    /**
     * Clear password reset token
     */
    public function clearResetToken($userId)
    {
        return $this->update($userId, [
            'reset_token' => null,
            'reset_token_expires' => null
        ]);
    }
    
    /**
     * Verify email
     */
    public function verifyEmail($userId)
    {
        return $this->update($userId, [
            'email_verified_at' => now()
        ]);
    }
    
    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        return $this->where('status = :status', ['status' => STATUS_ACTIVE]);
    }
    
    /**
     * Search users
     */
    public function search($query, $limit = 10)
    {
        $sql = "SELECT id, name, email, avatar FROM {$this->table} 
                WHERE (name LIKE :query OR email LIKE :query) 
                AND status = :status 
                ORDER BY name ASC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, [
            'query' => "%{$query}%",
            'status' => STATUS_ACTIVE
        ]);
    }
    
    /**
     * Get user statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
                    SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        $sql = "SELECT u.* FROM {$this->table} u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                WHERE r.name = :role AND u.status = :status
                ORDER BY u.name ASC";
        
        return $this->db->fetchAll($sql, ['role' => $role, 'status' => STATUS_ACTIVE]);
    }
}