<?php

/**
 * Base Controller Class
 * 
 * Provides common functionality for all controllers
 */
abstract class Controller
{
    protected $view;
    protected $session;
    protected $request;
    protected $validation;
    
    public function __construct()
    {
        $this->view = new View();
        $this->session = Session::getInstance();
        $this->request = new Request();
        $this->validation = new Validation();
        
        // Check authentication for protected routes
        $this->checkAuthentication();
    }
    
    /**
     * Render a view
     */
    protected function render($template, $data = [], $layout = 'main')
    {
        return $this->view->render($template, $data, $layout);
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to another URL
     */
    protected function redirect($url, $statusCode = 302)
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Redirect back to previous page
     */
    protected function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    /**
     * Get input data
     */
    protected function input($key = null, $default = null)
    {
        return $this->request->input($key, $default);
    }
    
    /**
     * Get all input data
     */
    protected function all()
    {
        return $this->request->all();
    }
    
    /**
     * Validate input data
     */
    protected function validate($rules, $messages = [])
    {
        $data = $this->all();
        $validator = $this->validation->make($data, $rules, $messages);
        
        if ($validator->fails()) {
            $this->session->setFlash('errors', $validator->errors());
            $this->session->setFlash('old_input', $data);
            $this->back();
        }
        
        return $validator->validated();
    }
    
    /**
     * Set flash message
     */
    protected function flash($key, $message)
    {
        $this->session->setFlash($key, $message);
    }
    
    /**
     * Get flash message
     */
    protected function getFlash($key)
    {
        return $this->session->getFlash($key);
    }
    
    /**
     * Check if user is authenticated
     */
    protected function checkAuthentication()
    {
        // Override in child controllers if authentication is required
    }
    
    /**
     * Get current user
     */
    protected function user()
    {
        return $this->session->get('user');
    }
    
    /**
     * Check if user has role
     */
    protected function hasRole($role)
    {
        $user = $this->user();
        return $user && in_array($role, $user['roles'] ?? []);
    }
    
    /**
     * Check if user has permission
     */
    protected function hasPermission($permission)
    {
        $user = $this->user();
        return $user && in_array($permission, $user['permissions'] ?? []);
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth()
    {
        if (!$this->user()) {
            $this->flash('error', 'Please login to access this page');
            $this->redirect('/login');
        }
    }
    
    /**
     * Require specific role
     */
    protected function requireRole($role)
    {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            $this->flash('error', 'Access denied');
            $this->redirect('/dashboard');
        }
    }
    
    /**
     * Require specific permission
     */
    protected function requirePermission($permission)
    {
        $this->requireAuth();
        
        if (!$this->hasPermission($permission)) {
            $this->flash('error', 'Access denied');
            $this->redirect('/dashboard');
        }
    }
    
    /**
     * Handle file upload
     */
    protected function upload($field, $destination = 'uploads', $allowedTypes = null)
    {
        $upload = new Upload();
        return $upload->handle($field, $destination, $allowedTypes);
    }
    
    /**
     * Paginate data
     */
    protected function paginate($model, $page = 1, $perPage = null)
    {
        $perPage = $perPage ?? config('app.pagination.per_page', 25);
        $page = max(1, (int) $this->input('page', $page));
        
        return $model->paginate($page, $perPage);
    }
    
    /**
     * Log activity
     */
    protected function logActivity($action, $description = null, $data = null)
    {
        Logger::activity($action, $description, $data, $this->user());
    }
    
    /**
     * Send notification
     */
    protected function notify($users, $message, $type = 'info', $data = null)
    {
        $notification = new Notification();
        return $notification->send($users, $message, $type, $data);
    }
    
    /**
     * Send email
     */
    protected function sendMail($to, $subject, $template, $data = [])
    {
        $mailer = new Mailer();
        return $mailer->send($to, $subject, $template, $data);
    }
    
    /**
     * Send SMS
     */
    protected function sendSMS($to, $message, $template = null, $data = [])
    {
        $sms = new SMS();
        return $sms->send($to, $message, $template, $data);
    }
    
    /**
     * Generate PDF
     */
    protected function generatePDF($template, $data = [], $filename = null)
    {
        $pdf = new PDF();
        return $pdf->generate($template, $data, $filename);
    }
    
    /**
     * Export to Excel
     */
    protected function exportExcel($data, $filename = null, $headers = [])
    {
        $excel = new Excel();
        return $excel->export($data, $filename, $headers);
    }
    
    /**
     * Cache data
     */
    protected function cache($key, $data = null, $ttl = null)
    {
        $cache = Cache::getInstance();
        
        if ($data === null) {
            return $cache->get($key);
        }
        
        return $cache->set($key, $data, $ttl);
    }
    
    /**
     * Clear cache
     */
    protected function clearCache($key = null)
    {
        $cache = Cache::getInstance();
        
        if ($key) {
            return $cache->delete($key);
        }
        
        return $cache->clear();
    }
    
    /**
     * Handle AJAX requests
     */
    protected function isAjax()
    {
        return $this->request->isAjax();
    }
    
    /**
     * Get request method
     */
    protected function method()
    {
        return $this->request->method();
    }
    
    /**
     * Check if request method matches
     */
    protected function isMethod($method)
    {
        return $this->request->isMethod($method);
    }
}