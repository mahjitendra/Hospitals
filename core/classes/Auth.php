<?php

/**
 * Authentication Class
 * 
 * Handles user authentication and authorization
 */
class Auth
{
    private $session;
    private $userModel;
    private $config;
    
    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->userModel = new User();
        $this->config = config('app.security');
    }
    
    /**
     * Attempt to authenticate user
     */
    public function attempt($credentials, $remember = false)
    {
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            return false;
        }
        
        // Check for too many failed attempts
        if ($this->hasTooManyAttempts($email)) {
            throw new Exception('Too many login attempts. Please try again later.');
        }
        
        // Find user by email
        $user = $this->userModel->findBy('email', $email);
        
        if (!$user) {
            $this->recordFailedAttempt($email);
            return false;
        }
        
        // Check if user is active
        if ($user['status'] !== STATUS_ACTIVE) {
            throw new Exception('Account is not active');
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($email);
            return false;
        }
        
        // Clear failed attempts
        $this->clearFailedAttempts($email);
        
        // Load user roles and permissions
        $user['roles'] = $this->getUserRoles($user['id']);
        $user['permissions'] = $this->getUserPermissions($user['id']);
        
        // Login user
        $this->login($user, $remember);
        
        return true;
    }
    
    /**
     * Login user
     */
    public function login($user, $remember = false)
    {
        // Remove sensitive data
        unset($user['password']);
        
        // Set session
        $this->session->login($user);
        
        // Handle remember me
        if ($remember) {
            $this->setRememberToken($user['id']);
        }
        
        // Update last login
        $this->userModel->update($user['id'], [
            'last_login' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        
        return true;
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        $user = $this->user();
        
        if ($user) {
            // Clear remember token
            $this->clearRememberToken($user['id']);
        }
        
        $this->session->logout();
    }
    
    /**
     * Get current user
     */
    public function user()
    {
        return $this->session->getUser();
    }
    
    /**
     * Check if user is authenticated
     */
    public function check()
    {
        return $this->session->isLoggedIn() && $this->session->checkTimeout();
    }
    
    /**
     * Check if user is guest
     */
    public function guest()
    {
        return !$this->check();
    }
    
    /**
     * Get user ID
     */
    public function id()
    {
        $user = $this->user();
        return $user ? $user['id'] : null;
    }
    
    /**
     * Check if user has role
     */
    public function hasRole($role)
    {
        $user = $this->user();
        return $user && in_array($role, $user['roles'] ?? []);
    }
    
    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($roles)
    {
        $user = $this->user();
        if (!$user) return false;
        
        $userRoles = $user['roles'] ?? [];
        return !empty(array_intersect($roles, $userRoles));
    }
    
    /**
     * Check if user has all of the given roles
     */
    public function hasAllRoles($roles)
    {
        $user = $this->user();
        if (!$user) return false;
        
        $userRoles = $user['roles'] ?? [];
        return empty(array_diff($roles, $userRoles));
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($permission)
    {
        $user = $this->user();
        return $user && in_array($permission, $user['permissions'] ?? []);
    }
    
    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission($permissions)
    {
        $user = $this->user();
        if (!$user) return false;
        
        $userPermissions = $user['permissions'] ?? [];
        return !empty(array_intersect($permissions, $userPermissions));
    }
    
    /**
     * Register new user
     */
    public function register($userData)
    {
        // Validate required fields
        $required = ['name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                throw new Exception("Field {$field} is required");
            }
        }
        
        // Check if email already exists
        if ($this->userModel->findBy('email', $userData['email'])) {
            throw new Exception('Email already exists');
        }
        
        // Validate password strength
        if (!$this->isPasswordStrong($userData['password'])) {
            throw new Exception('Password does not meet security requirements');
        }
        
        // Hash password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userData['status'] = STATUS_ACTIVE;
        $userData['email_verified_at'] = null;
        
        // Create user
        $userId = $this->userModel->create($userData);
        
        // Assign default role
        $this->assignRole($userId, ROLE_STUDENT);
        
        return $userId;
    }
    
    /**
     * Change password
     */
    public function changePassword($currentPassword, $newPassword)
    {
        $user = $this->user();
        if (!$user) {
            throw new Exception('User not authenticated');
        }
        
        // Get current user data
        $userData = $this->userModel->find($user['id']);
        
        // Verify current password
        if (!password_verify($currentPassword, $userData['password'])) {
            throw new Exception('Current password is incorrect');
        }
        
        // Validate new password
        if (!$this->isPasswordStrong($newPassword)) {
            throw new Exception('New password does not meet security requirements');
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userModel->update($user['id'], ['password' => $hashedPassword]);
    }
    
    /**
     * Reset password
     */
    public function resetPassword($token, $newPassword)
    {
        // Find user by reset token
        $user = $this->userModel->findBy('reset_token', $token);
        
        if (!$user || $user['reset_token_expires'] < date('Y-m-d H:i:s')) {
            throw new Exception('Invalid or expired reset token');
        }
        
        // Validate new password
        if (!$this->isPasswordStrong($newPassword)) {
            throw new Exception('Password does not meet security requirements');
        }
        
        // Update password and clear reset token
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userModel->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires' => null
        ]);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email)
    {
        $user = $this->userModel->findBy('email', $email);
        
        if (!$user) {
            // Don't reveal if email exists
            return true;
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save token
        $this->userModel->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expires' => $expires
        ]);
        
        // Send email
        $mailer = new Mailer();
        return $mailer->send($email, 'Password Reset', 'password_reset', [
            'name' => $user['name'],
            'token' => $token,
            'expires' => $expires
        ]);
    }
    
    /**
     * Check if password is strong enough
     */
    private function isPasswordStrong($password)
    {
        $minLength = $this->config['password_min_length'];
        
        if (strlen($password) < $minLength) {
            return false;
        }
        
        if ($this->config['password_require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        if ($this->config['password_require_numbers'] && !preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        if ($this->config['password_require_special'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user has too many failed attempts
     */
    private function hasTooManyAttempts($email)
    {
        $key = "login_attempts_{$email}";
        $attempts = Cache::getInstance()->get($key, 0);
        
        return $attempts >= $this->config['max_login_attempts'];
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt($email)
    {
        $key = "login_attempts_{$email}";
        $attempts = Cache::getInstance()->get($key, 0) + 1;
        
        Cache::getInstance()->set($key, $attempts, $this->config['lockout_duration']);
    }
    
    /**
     * Clear failed login attempts
     */
    private function clearFailedAttempts($email)
    {
        $key = "login_attempts_{$email}";
        Cache::getInstance()->delete($key);
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
     * Get user permissions
     */
    private function getUserPermissions($userId)
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT p.name FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = :user_id";
        
        $permissions = $db->fetchAll($sql, ['user_id' => $userId]);
        return array_column($permissions, 'name');
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($userId, $role)
    {
        $db = Database::getInstance();
        
        // Get role ID
        $roleData = $db->fetch("SELECT id FROM roles WHERE name = :name", ['name' => $role]);
        
        if (!$roleData) {
            throw new Exception("Role not found: {$role}");
        }
        
        // Check if already assigned
        $exists = $db->exists('user_roles', 'user_id = :user_id AND role_id = :role_id', [
            'user_id' => $userId,
            'role_id' => $roleData['id']
        ]);
        
        if (!$exists) {
            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleData['id']
            ]);
        }
    }
    
    /**
     * Remove role from user
     */
    public function removeRole($userId, $role)
    {
        $db = Database::getInstance();
        
        // Get role ID
        $roleData = $db->fetch("SELECT id FROM roles WHERE name = :name", ['name' => $role]);
        
        if ($roleData) {
            $db->delete('user_roles', 'user_id = :user_id AND role_id = :role_id', [
                'user_id' => $userId,
                'role_id' => $roleData['id']
            ]);
        }
    }
    
    /**
     * Set remember token
     */
    private function setRememberToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $this->userModel->update($userId, [
            'remember_token' => $token,
            'remember_token_expires' => $expires
        ]);
        
        // Set cookie
        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
    }
    
    /**
     * Clear remember token
     */
    private function clearRememberToken($userId)
    {
        $this->userModel->update($userId, [
            'remember_token' => null,
            'remember_token_expires' => null
        ]);
        
        // Clear cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    
    /**
     * Check remember token
     */
    public function checkRememberToken()
    {
        $token = $_COOKIE['remember_token'] ?? null;
        
        if (!$token) {
            return false;
        }
        
        $user = $this->userModel->findBy('remember_token', $token);
        
        if (!$user || $user['remember_token_expires'] < date('Y-m-d H:i:s')) {
            return false;
        }
        
        // Login user
        $user['roles'] = $this->getUserRoles($user['id']);
        $user['permissions'] = $this->getUserPermissions($user['id']);
        
        $this->login($user, true);
        
        return true;
    }
}