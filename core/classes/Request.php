<?php

/**
 * HTTP Request Handler Class
 * 
 * Handles HTTP request data and operations
 */
class Request
{
    private $data = [];
    private $files = [];
    private $headers = [];
    private $method;
    private $uri;
    private $queryParams = [];
    
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->parseHeaders();
        $this->parseData();
        $this->parseFiles();
        $this->parseQueryParams();
    }
    
    /**
     * Parse request headers
     */
    private function parseHeaders()
    {
        if (function_exists('getallheaders')) {
            $this->headers = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace('_', '-', substr($key, 5));
                    $this->headers[$header] = $value;
                }
            }
        }
        
        // Normalize header keys to lowercase
        $this->headers = array_change_key_case($this->headers, CASE_LOWER);
    }
    
    /**
     * Parse request data
     */
    private function parseData()
    {
        switch ($this->method) {
            case 'GET':
                $this->data = $_GET;
                break;
                
            case 'POST':
                $this->data = $_POST;
                
                // Handle JSON data
                if ($this->isJson()) {
                    $json = json_decode(file_get_contents('php://input'), true);
                    if ($json) {
                        $this->data = array_merge($this->data, $json);
                    }
                }
                break;
                
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                // Parse raw input
                $input = file_get_contents('php://input');
                
                if ($this->isJson()) {
                    $this->data = json_decode($input, true) ?: [];
                } else {
                    parse_str($input, $this->data);
                }
                break;
        }
        
        // Sanitize data
        $this->data = $this->sanitizeData($this->data);
    }
    
    /**
     * Parse uploaded files
     */
    private function parseFiles()
    {
        $this->files = $_FILES;
    }
    
    /**
     * Parse query parameters
     */
    private function parseQueryParams()
    {
        $queryString = parse_url($this->uri, PHP_URL_QUERY);
        if ($queryString) {
            parse_str($queryString, $this->queryParams);
        }
    }
    
    /**
     * Get input value
     */
    public function input($key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }
        
        return $this->data[$key] ?? $default;
    }
    
    /**
     * Get all input data
     */
    public function all()
    {
        return $this->data;
    }
    
    /**
     * Get only specified keys
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $result = [];
        
        foreach ($keys as $key) {
            if (isset($this->data[$key])) {
                $result[$key] = $this->data[$key];
            }
        }
        
        return $result;
    }
    
    /**
     * Get all except specified keys
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $result = $this->data;
        
        foreach ($keys as $key) {
            unset($result[$key]);
        }
        
        return $result;
    }
    
    /**
     * Check if input has key
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }
    
    /**
     * Check if input has any of the keys
     */
    public function hasAny($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        
        foreach ($keys as $key) {
            if ($this->has($key)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if input is filled (not empty)
     */
    public function filled($key)
    {
        return $this->has($key) && !empty($this->data[$key]);
    }
    
    /**
     * Get query parameter
     */
    public function query($key = null, $default = null)
    {
        if ($key === null) {
            return $this->queryParams;
        }
        
        return $this->queryParams[$key] ?? $default;
    }
    
    /**
     * Get uploaded file
     */
    public function file($key)
    {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Check if file was uploaded
     */
    public function hasFile($key)
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }
    
    /**
     * Get header value
     */
    public function header($key, $default = null)
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }
    
    /**
     * Get all headers
     */
    public function headers()
    {
        return $this->headers;
    }
    
    /**
     * Get request method
     */
    public function method()
    {
        return $this->method;
    }
    
    /**
     * Check if request method matches
     */
    public function isMethod($method)
    {
        return strtoupper($this->method) === strtoupper($method);
    }
    
    /**
     * Get request URI
     */
    public function uri()
    {
        return $this->uri;
    }
    
    /**
     * Get request path
     */
    public function path()
    {
        return parse_url($this->uri, PHP_URL_PATH);
    }
    
    /**
     * Get full URL
     */
    public function url()
    {
        $protocol = $this->isSecure() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host . $this->uri;
    }
    
    /**
     * Check if request is secure (HTTPS)
     */
    public function isSecure()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Check if request is AJAX
     */
    public function isAjax()
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }
    
    /**
     * Check if request expects JSON
     */
    public function expectsJson()
    {
        return $this->isAjax() || $this->wantsJson();
    }
    
    /**
     * Check if request wants JSON response
     */
    public function wantsJson()
    {
        $acceptable = $this->header('accept', '');
        return strpos($acceptable, 'application/json') !== false;
    }
    
    /**
     * Check if request content type is JSON
     */
    public function isJson()
    {
        $contentType = $this->header('content-type', '');
        return strpos($contentType, 'application/json') !== false;
    }
    
    /**
     * Get client IP address
     */
    public function ip()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get user agent
     */
    public function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Get referer URL
     */
    public function referer()
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }
    
    /**
     * Sanitize input data
     */
    private function sanitizeData($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeData'], $data);
        }
        
        if (is_string($data)) {
            // Remove null bytes
            $data = str_replace("\0", '', $data);
            
            // Trim whitespace
            $data = trim($data);
        }
        
        return $data;
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrfToken()
    {
        $token = $this->input('_token') ?? $this->header('x-csrf-token');
        $session = Session::getInstance();
        
        return $session->verifyCsrfToken($token);
    }
    
    /**
     * Get request signature for rate limiting
     */
    public function signature()
    {
        return md5($this->ip() . '|' . $this->userAgent() . '|' . $this->path());
    }
    
    /**
     * Check if request is from mobile device
     */
    public function isMobile()
    {
        $userAgent = $this->userAgent();
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone',
            'BlackBerry', 'Opera Mini', 'IEMobile'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get preferred language
     */
    public function getPreferredLanguage($available = ['en'])
    {
        $acceptLanguage = $this->header('accept-language', '');
        
        if (empty($acceptLanguage)) {
            return $available[0];
        }
        
        $languages = [];
        
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $code = trim($parts[0]);
            $quality = 1.0;
            
            if (isset($parts[1]) && strpos($parts[1], 'q=') === 0) {
                $quality = floatval(substr($parts[1], 2));
            }
            
            $languages[$code] = $quality;
        }
        
        arsort($languages);
        
        foreach ($languages as $lang => $quality) {
            if (in_array($lang, $available)) {
                return $lang;
            }
            
            // Check for language without country code
            $langCode = substr($lang, 0, 2);
            if (in_array($langCode, $available)) {
                return $langCode;
            }
        }
        
        return $available[0];
    }
}