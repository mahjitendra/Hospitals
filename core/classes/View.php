<?php

/**
 * View Renderer Class
 * 
 * Handles template rendering and view management
 */
class View
{
    private $viewPath;
    private $layoutPath;
    private $data = [];
    private $sections = [];
    private $currentSection = null;
    
    public function __construct()
    {
        $this->viewPath = __DIR__ . '/../../app/views/';
        $this->layoutPath = __DIR__ . '/../../app/views/layouts/';
    }
    
    /**
     * Render a view with layout
     */
    public function render($template, $data = [], $layout = 'main')
    {
        $this->data = array_merge($this->data, $data);
        
        // Start output buffering
        ob_start();
        
        // Include the view template
        $this->include($template);
        
        // Get the content
        $content = ob_get_clean();
        
        // If no layout specified, return content directly
        if (!$layout) {
            echo $content;
            return;
        }
        
        // Set content for layout
        $this->data['content'] = $content;
        
        // Render layout
        $this->include("layouts/{$layout}");
    }
    
    /**
     * Include a template file
     */
    private function include($template)
    {
        $templatePath = $this->viewPath . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templatePath)) {
            throw new Exception("View template not found: {$template}");
        }
        
        // Extract data to variables
        extract($this->data);
        
        // Include the template
        include $templatePath;
    }
    
    /**
     * Set global view data
     */
    public function share($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }
    
    /**
     * Start a section
     */
    public function section($name)
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    /**
     * End current section
     */
    public function endSection()
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    /**
     * Yield section content
     */
    public function yield($name, $default = '')
    {
        return $this->sections[$name] ?? $default;
    }
    
    /**
     * Include a partial view
     */
    public function partial($template, $data = [])
    {
        $originalData = $this->data;
        $this->data = array_merge($this->data, $data);
        
        $this->include($template);
        
        $this->data = $originalData;
    }
    
    /**
     * Escape HTML
     */
    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate URL
     */
    public function url($path = '')
    {
        $baseUrl = rtrim(config('app.url', 'http://localhost'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
    
    /**
     * Generate asset URL
     */
    public function asset($path)
    {
        return $this->url('assets/' . ltrim($path, '/'));
    }
    
    /**
     * Get old input value
     */
    public function old($key, $default = '')
    {
        $oldInput = Session::getInstance()->getFlash('old_input', []);
        return $oldInput[$key] ?? $default;
    }
    
    /**
     * Get error message
     */
    public function error($key)
    {
        $errors = Session::getInstance()->getFlash('errors', []);
        return $errors[$key] ?? null;
    }
    
    /**
     * Check if field has error
     */
    public function hasError($key)
    {
        return $this->error($key) !== null;
    }
    
    /**
     * Get flash message
     */
    public function flash($key)
    {
        return Session::getInstance()->getFlash($key);
    }
    
    /**
     * Format date
     */
    public function formatDate($date, $format = 'Y-m-d H:i:s')
    {
        if (!$date) return '';
        
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        return $date->format($format);
    }
    
    /**
     * Format currency
     */
    public function formatCurrency($amount, $currency = '₹')
    {
        return $currency . number_format($amount, 2);
    }
    
    /**
     * Truncate text
     */
    public function truncate($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Generate CSRF token field
     */
    public function csrf()
    {
        $token = Session::getInstance()->get('csrf_token');
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
    
    /**
     * Generate method field for forms
     */
    public function method($method)
    {
        if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE'])) {
            return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
        }
        return '';
    }
    
    /**
     * Check if current route matches
     */
    public function isActive($route, $class = 'active')
    {
        $currentRoute = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($currentRoute, $route) !== false ? $class : '';
    }
    
    /**
     * Include component
     */
    public function component($name, $data = [])
    {
        $this->partial("components/{$name}", $data);
    }
    
    /**
     * Render pagination links
     */
    public function paginate($pagination)
    {
        $this->partial('components.pagination', ['pagination' => $pagination]);
    }
    
    /**
     * Get configuration value
     */
    public function config($key, $default = null)
    {
        return config($key, $default);
    }
    
    /**
     * Get current user
     */
    public function user()
    {
        return Session::getInstance()->get('user');
    }
    
    /**
     * Check if user is authenticated
     */
    public function auth()
    {
        return $this->user() !== null;
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
     * Check if user has permission
     */
    public function hasPermission($permission)
    {
        $user = $this->user();
        return $user && in_array($permission, $user['permissions'] ?? []);
    }
}