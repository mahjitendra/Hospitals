<?php

/**
 * Session Management Class
 * 
 * Handles session operations and security
 */
class Session
{
    private static $instance = null;
    private $config;
    private $started = false;
    
    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/session.php';
        $this->start();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Start session
     */
    private function start()
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Configure session settings
        ini_set('session.cookie_lifetime', $this->config['lifetime'] * 60);
        ini_set('session.cookie_httponly', $this->config['http_only']);
        ini_set('session.cookie_secure', $this->config['secure']);
        ini_set('session.cookie_samesite', $this->config['same_site']);
        ini_set('session.use_strict_mode', 1);
        
        // Set session name
        session_name($this->config['cookie']);
        
        // Set session save path if using file driver
        if ($this->config['driver'] === 'file') {
            if (!is_dir($this->config['files'])) {
                mkdir($this->config['files'], 0755, true);
            }
            session_save_path($this->config['files']);
        }
        
        // Start session
        session_start();
        $this->started = true;
        
        // Initialize security measures
        $this->initializeSecurity();
        
        // Handle session regeneration
        $this->handleRegeneration();
    }
    
    /**
     * Initialize security measures
     */
    private function initializeSecurity()
    {
        // Generate CSRF token if not exists
        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', $this->generateToken());
        }
        
        // Validate IP address if enabled
        if ($this->config['security']['ip_validation']) {
            $this->validateIpAddress();
        }
        
        // Validate user agent if enabled
        if ($this->config['security']['user_agent_validation']) {
            $this->validateUserAgent();
        }
    }
    
    /**
     * Handle session regeneration
     */
    private function handleRegeneration()
    {
        $now = time();
        
        // Regenerate session ID on login
        if ($this->config['security']['regenerate_on_login'] && $this->get('just_logged_in')) {
            session_regenerate_id(true);
            $this->remove('just_logged_in');
            $this->set('last_regeneration', $now);
        }
        
        // Regenerate session ID periodically
        $lastRegeneration = $this->get('last_regeneration', 0);
        $interval = $this->config['security']['regenerate_interval'];
        
        if ($now - $lastRegeneration > $interval) {
            session_regenerate_id(true);
            $this->set('last_regeneration', $now);
        }
    }
    
    /**
     * Validate IP address
     */
    private function validateIpAddress()
    {
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
        $sessionIp = $this->get('ip_address');
        
        if ($sessionIp && $sessionIp !== $currentIp) {
            $this->destroy();
            throw new Exception('Session security violation: IP address mismatch');
        }
        
        if (!$sessionIp) {
            $this->set('ip_address', $currentIp);
        }
    }
    
    /**
     * Validate user agent
     */
    private function validateUserAgent()
    {
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $sessionUserAgent = $this->get('user_agent');
        
        if ($sessionUserAgent && $sessionUserAgent !== $currentUserAgent) {
            $this->destroy();
            throw new Exception('Session security violation: User agent mismatch');
        }
        
        if (!$sessionUserAgent) {
            $this->set('user_agent', $currentUserAgent);
        }
    }
    
    /**
     * Set session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session key
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Get all session data
     */
    public function all()
    {
        return $_SESSION;
    }
    
    /**
     * Clear all session data
     */
    public function clear()
    {
        $_SESSION = [];
    }
    
    /**
     * Destroy session
     */
    public function destroy()
    {
        if ($this->started) {
            session_destroy();
            $this->started = false;
        }
        
        // Clear session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }
    
    /**
     * Set flash message
     */
    public function setFlash($key, $value)
    {
        $this->set("flash_{$key}", $value);
    }
    
    /**
     * Get flash message (and remove it)
     */
    public function getFlash($key, $default = null)
    {
        $value = $this->get("flash_{$key}", $default);
        $this->remove("flash_{$key}");
        return $value;
    }
    
    /**
     * Check if flash message exists
     */
    public function hasFlash($key)
    {
        return $this->has("flash_{$key}");
    }
    
    /**
     * Generate secure token
     */
    public function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token)
    {
        return hash_equals($this->get('csrf_token'), $token);
    }
    
    /**
     * Get CSRF token
     */
    public function getCsrfToken()
    {
        return $this->get('csrf_token');
    }
    
    /**
     * Regenerate CSRF token
     */
    public function regenerateCsrfToken()
    {
        $this->set('csrf_token', $this->generateToken());
    }
    
    /**
     * Set user login
     */
    public function login($user)
    {
        $this->set('user', $user);
        $this->set('just_logged_in', true);
        $this->set('login_time', time());
        
        // Log login activity
        Logger::info('User logged in', ['user_id' => $user['id']]);
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        $user = $this->get('user');
        
        if ($user) {
            Logger::info('User logged out', ['user_id' => $user['id']]);
        }
        
        $this->destroy();
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return $this->has('user');
    }
    
    /**
     * Get logged in user
     */
    public function getUser()
    {
        return $this->get('user');
    }
    
    /**
     * Check session timeout
     */
    public function checkTimeout()
    {
        $loginTime = $this->get('login_time');
        $timeout = $this->config['lifetime'] * 60;
        
        if ($loginTime && (time() - $loginTime) > $timeout) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    /**
     * Extend session
     */
    public function extend()
    {
        $this->set('login_time', time());
    }
    
    /**
     * Get session ID
     */
    public function getId()
    {
        return session_id();
    }
    
    /**
     * Get session status
     */
    public function getStatus()
    {
        return session_status();
    }
    
    /**
     * Check if session is active
     */
    public function isActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}