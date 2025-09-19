<?php

/**
 * Global Helper Functions
 * 
 * Common utility functions used throughout the application
 */

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config($key, $default = null)
    {
        static $config = [];
        
        $keys = explode('.', $key);
        $file = array_shift($keys);
        
        if (!isset($config[$file])) {
            $configFile = __DIR__ . '/../../config/' . $file . '.php';
            if (file_exists($configFile)) {
                $config[$file] = require $configFile;
            } else {
                return $default;
            }
        }
        
        $value = $config[$file];
        
        foreach ($keys as $segment) {
            if (is_array($value) && isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convert string representations to actual types
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        // Remove quotes if present
        if (strlen($value) > 1 && $value[0] === '"' && $value[-1] === '"') {
            return substr($value, 1, -1);
        }
        
        return $value;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable
     */
    function dump(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset($path)
    {
        $baseUrl = config('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url($path = '', $parameters = [])
    {
        $baseUrl = config('app.url', 'http://localhost');
        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }
        
        return $url;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL
     */
    function redirect($url, $statusCode = 302)
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back
     */
    function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old($key, $default = '')
    {
        $session = Session::getInstance();
        $oldInput = $session->getFlash('old_input', []);
        return $oldInput[$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token
     */
    function csrf_token()
    {
        $session = Session::getInstance();
        return $session->getCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF token field
     */
    function csrf_field()
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate method field for forms
     */
    function method_field($method)
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('auth')) {
    /**
     * Get Auth instance
     */
    function auth()
    {
        return new Auth();
    }
}

if (!function_exists('user')) {
    /**
     * Get current user
     */
    function user()
    {
        return auth()->user();
    }
}

if (!function_exists('cache')) {
    /**
     * Get Cache instance
     */
    function cache($key = null, $value = null, $ttl = null)
    {
        $cache = Cache::getInstance();
        
        if ($key === null) {
            return $cache;
        }
        
        if ($value === null) {
            return $cache->get($key);
        }
        
        return $cache->set($key, $value, $ttl);
    }
}

if (!function_exists('session')) {
    /**
     * Get Session instance
     */
    function session($key = null, $default = null)
    {
        $session = Session::getInstance();
        
        if ($key === null) {
            return $session;
        }
        
        return $session->get($key, $default);
    }
}

if (!function_exists('flash')) {
    /**
     * Set flash message
     */
    function flash($key, $message)
    {
        $session = Session::getInstance();
        $session->setFlash($key, $message);
    }
}

if (!function_exists('request')) {
    /**
     * Get Request instance
     */
    function request()
    {
        return new Request();
    }
}

if (!function_exists('response')) {
    /**
     * Create response
     */
    function response($content = '', $status = 200, $headers = [])
    {
        http_response_code($status);
        
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }
        
        echo $content;
        exit;
    }
}

if (!function_exists('json_response')) {
    /**
     * Create JSON response
     */
    function json_response($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('view')) {
    /**
     * Render view
     */
    function view($template, $data = [], $layout = 'main')
    {
        $view = new View();
        return $view->render($template, $data, $layout);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML
     */
    function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limit string length
     */
    function str_limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }
        
        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate URL slug
     */
    function str_slug($title, $separator = '-')
    {
        $title = strtolower($title);
        $title = preg_replace('/[^a-z0-9\s-]/', '', $title);
        $title = preg_replace('/[\s-]+/', $separator, $title);
        return trim($title, $separator);
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate random string
     */
    function str_random($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}

if (!function_exists('array_get')) {
    /**
     * Get array value using dot notation
     */
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }
        
        if (isset($array[$key])) {
            return $array[$key];
        }
        
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        
        return $array;
    }
}

if (!function_exists('array_set')) {
    /**
     * Set array value using dot notation
     */
    function array_set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        
        $keys = explode('.', $key);
        
        while (count($keys) > 1) {
            $key = array_shift($keys);
            
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            
            $array = &$array[$key];
        }
        
        $array[array_shift($keys)] = $value;
        
        return $array;
    }
}

if (!function_exists('collect')) {
    /**
     * Create collection from array
     */
    function collect($items = [])
    {
        return new Collection($items);
    }
}

if (!function_exists('now')) {
    /**
     * Get current timestamp
     */
    function now($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }
}

if (!function_exists('today')) {
    /**
     * Get today's date
     */
    function today($format = 'Y-m-d')
    {
        return date($format);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get storage path
     */
    function storage_path($path = '')
    {
        return __DIR__ . '/../../storage/' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    /**
     * Get public path
     */
    function public_path($path = '')
    {
        return __DIR__ . '/../../public/' . ltrim($path, '/');
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path
     */
    function base_path($path = '')
    {
        return __DIR__ . '/../../' . ltrim($path, '/');
    }
}

if (!function_exists('app_path')) {
    /**
     * Get app path
     */
    function app_path($path = '')
    {
        return __DIR__ . '/../../app/' . ltrim($path, '/');
    }
}

if (!function_exists('format_bytes')) {
    /**
     * Format bytes to human readable
     */
    function format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency
     */
    function format_currency($amount, $currency = '₹')
    {
        return $currency . number_format($amount, 2);
    }
}

if (!function_exists('is_active')) {
    /**
     * Check if current route is active
     */
    function is_active($route, $class = 'active')
    {
        $currentRoute = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($currentRoute, $route) !== false ? $class : '';
    }
}

if (!function_exists('gravatar')) {
    /**
     * Generate Gravatar URL
     */
    function gravatar($email, $size = 80, $default = 'mp')
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$default}";
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with HTTP status code
     */
    function abort($code, $message = '')
    {
        http_response_code($code);
        
        switch ($code) {
            case 404:
                echo '<h1>404 - Page Not Found</h1>';
                break;
            case 403:
                echo '<h1>403 - Forbidden</h1>';
                break;
            case 500:
                echo '<h1>500 - Internal Server Error</h1>';
                break;
            default:
                echo "<h1>{$code} - Error</h1>";
        }
        
        if ($message) {
            echo '<p>' . e($message) . '</p>';
        }
        
        exit;
    }
}

if (!function_exists('validate')) {
    /**
     * Validate data
     */
    function validate($data, $rules, $messages = [])
    {
        $validation = new Validation();
        return $validation->make($data, $rules, $messages);
    }
}

if (!function_exists('bcrypt')) {
    /**
     * Hash password
     */
    function bcrypt($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

if (!function_exists('password_verify_hash')) {
    /**
     * Verify password hash
     */
    function password_verify_hash($password, $hash)
    {
        return password_verify($password, $hash);
    }
}