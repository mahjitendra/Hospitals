<?php

/**
 * Authentication Controller
 * 
 * Handles user authentication operations
 */
class AuthController extends Controller
{
    private $userModel;
    private $auth;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->userModel = new User();
        $this->auth = new Auth();
    }
    
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // Redirect if already logged in
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }
        
        $this->render('auth/login', [
            'title' => 'Login'
        ], 'auth');
    }
    
    /**
     * Handle login
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }
        
        $data = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'boolean'
        ]);
        
        try {
            $remember = $data['remember'] ?? false;
            
            if ($this->auth->attempt($data, $remember)) {
                $user = $this->auth->user();
                
                // Log successful login
                Logger::security('successful_login', 'User logged in successfully', [
                    'user_id' => $user['id'],
                    'email' => $user['email']
                ]);
                
                $this->logActivity('user_login', 'User logged in');
                
                // Redirect based on role
                $redirectUrl = $this->getRedirectUrl($user);
                $this->redirect($redirectUrl);
                
            } else {
                // Log failed login attempt
                Logger::security('failed_login', 'Failed login attempt', [
                    'email' => $data['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                $this->flash('error', 'Invalid email or password');
                $this->redirect('/login');
            }
            
        } catch (Exception $e) {
            Logger::error('Login error: ' . $e->getMessage());
            $this->flash('error', $e->getMessage());
            $this->redirect('/login');
        }
    }
    
    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        // Check if registration is enabled
        if (!config('app.registration_enabled', true)) {
            $this->flash('error', 'Registration is currently disabled');
            $this->redirect('/login');
        }
        
        $this->render('auth/register', [
            'title' => 'Register'
        ], 'auth');
    }
    
    /**
     * Handle registration
     */
    public function register()
    {
        // Check if registration is enabled
        if (!config('app.registration_enabled', true)) {
            $this->flash('error', 'Registration is currently disabled');
            $this->redirect('/login');
        }
        
        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|strong_password|confirmed',
            'phone' => 'phone',
            'terms' => 'required'
        ]);
        
        try {
            $userId = $this->auth->register($data);
            
            // Send welcome email
            $this->sendWelcomeEmail($data);
            
            $this->logActivity('user_registered', 'New user registered', ['email' => $data['email']]);
            $this->flash('success', 'Registration successful! Please login to continue.');
            
            $this->redirect('/login');
            
        } catch (Exception $e) {
            Logger::error('Registration error: ' . $e->getMessage());
            $this->flash('error', $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        $this->render('auth/forgot-password', [
            'title' => 'Forgot Password'
        ], 'auth');
    }
    
    /**
     * Send password reset link
     */
    public function sendResetLink()
    {
        $data = $this->validate([
            'email' => 'required|email'
        ]);
        
        try {
            $this->auth->sendPasswordResetEmail($data['email']);
            
            $this->flash('success', 'Password reset link sent to your email');
            $this->redirect('/login');
            
        } catch (Exception $e) {
            Logger::error('Password reset error: ' . $e->getMessage());
            $this->flash('error', 'Failed to send reset link');
            $this->back();
        }
    }
    
    /**
     * Show reset password form
     */
    public function showResetPasswordForm($token)
    {
        // Verify token
        $user = $this->userModel->findBy('reset_token', $token);
        
        if (!$user || $user['reset_token_expires'] < now()) {
            $this->flash('error', 'Invalid or expired reset token');
            $this->redirect('/login');
        }
        
        $this->render('auth/reset-password', [
            'title' => 'Reset Password',
            'token' => $token
        ], 'auth');
    }
    
    /**
     * Reset password
     */
    public function resetPassword()
    {
        $data = $this->validate([
            'token' => 'required',
            'password' => 'required|strong_password|confirmed'
        ]);
        
        try {
            $this->auth->resetPassword($data['token'], $data['password']);
            
            $this->flash('success', 'Password reset successfully! Please login with your new password.');
            $this->redirect('/login');
            
        } catch (Exception $e) {
            Logger::error('Password reset error: ' . $e->getMessage());
            $this->flash('error', $e->getMessage());
            $this->back();
        }
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        $user = $this->auth->user();
        
        if ($user) {
            $this->logActivity('user_logout', 'User logged out');
            
            Logger::security('user_logout', 'User logged out', [
                'user_id' => $user['id'],
                'email' => $user['email']
            ]);
        }
        
        $this->auth->logout();
        $this->flash('success', 'You have been logged out successfully');
        $this->redirect('/login');
    }
    
    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl($user)
    {
        $roles = $user['roles'] ?? [];
        
        if (in_array(ROLE_ADMIN, $roles) || in_array(ROLE_SUPER_ADMIN, $roles)) {
            return '/admin/dashboard';
        } elseif (in_array(ROLE_TEACHER, $roles) || in_array(ROLE_HOD, $roles)) {
            return '/faculty/dashboard';
        } elseif (in_array(ROLE_STUDENT, $roles)) {
            return '/student/dashboard';
        } elseif (in_array(ROLE_PARENT, $roles)) {
            return '/parent/dashboard';
        } elseif (in_array(ROLE_LIBRARIAN, $roles)) {
            return '/library/dashboard';
        } elseif (in_array(ROLE_ACCOUNTANT, $roles)) {
            return '/accounts/dashboard';
        } else {
            return '/dashboard';
        }
    }
    
    /**
     * Send welcome email
     */
    private function sendWelcomeEmail($userData)
    {
        try {
            $this->sendMail($userData['email'], 'Welcome to College ERP', 'welcome', [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'login_url' => url('/login')
            ]);
        } catch (Exception $e) {
            Logger::error('Welcome email failed: ' . $e->getMessage());
        }
    }
}