<?php

/**
 * Application Bootstrap
 * 
 * Initialize the application and load necessary components
 */

// Define application constants
define('APP_START_TIME', microtime(true));
define('APP_START_MEMORY', memory_get_usage());

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('UTC');

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load helper functions
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/date_helper.php';
require_once __DIR__ . '/helpers/string_helper.php';
require_once __DIR__ . '/helpers/security_helper.php';
require_once __DIR__ . '/helpers/file_helper.php';
require_once __DIR__ . '/helpers/url_helper.php';

// Load core classes
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Model.php';
require_once __DIR__ . '/classes/Controller.php';
require_once __DIR__ . '/classes/View.php';
require_once __DIR__ . '/classes/Router.php';
require_once __DIR__ . '/classes/Session.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Request.php';
require_once __DIR__ . '/classes/Validation.php';
require_once __DIR__ . '/classes/Upload.php';
require_once __DIR__ . '/classes/Pagination.php';
require_once __DIR__ . '/classes/Cache.php';
require_once __DIR__ . '/classes/Logger.php';

// Load middleware
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RoleMiddleware.php';
require_once __DIR__ . '/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/middleware/ThrottleMiddleware.php';

// Set up error and exception handlers
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    Logger::error("PHP Error: {$message}", [
        'severity' => $severity,
        'file' => $file,
        'line' => $line,
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
    ]);
    
    if (config('app.debug', false)) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<strong>Error:</strong> {$message}<br>";
        echo "<strong>File:</strong> {$file}<br>";
        echo "<strong>Line:</strong> {$line}";
        echo "</div>";
    }
    
    return true;
});

set_exception_handler(function($exception) {
    Logger::critical("Uncaught Exception: " . $exception->getMessage(), [
        'exception' => get_class($exception),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (config('app.debug', false)) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<h3>Uncaught Exception</h3>";
        echo "<strong>Message:</strong> " . $exception->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        http_response_code(500);
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
    }
});

// Register shutdown function
register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        Logger::critical("Fatal Error: " . $error['message'], [
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => $error['type']
        ]);
        
        if (!config('app.debug', false)) {
            http_response_code(500);
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Something went wrong. Please try again later.</p>";
        }
    }
    
    // Log performance metrics
    $executionTime = microtime(true) - APP_START_TIME;
    $memoryUsage = memory_get_peak_usage() - APP_START_MEMORY;
    
    Logger::debug('Request completed', [
        'execution_time' => round($executionTime * 1000, 2) . 'ms',
        'memory_usage' => format_bytes($memoryUsage),
        'peak_memory' => format_bytes(memory_get_peak_usage()),
        'url' => $_SERVER['REQUEST_URI'] ?? '',
        'method' => $_SERVER['REQUEST_METHOD'] ?? ''
    ]);
});

// Initialize session
Session::getInstance();

// Set up CSRF protection
$session = Session::getInstance();
if (!$session->has('csrf_token')) {
    $session->set('csrf_token', bin2hex(random_bytes(32)));
}

// Load application constants
require_once __DIR__ . '/../config/constants.php';

// Initialize cache
Cache::getInstance();

// Log application start
Logger::info('Application started', [
    'php_version' => PHP_VERSION,
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'environment' => config('app.env', 'production')
]);

// Check for maintenance mode
if (config('app.maintenance.enabled', false)) {
    $allowedIps = config('app.maintenance.allowed_ips', []);
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if (!in_array($clientIp, $allowedIps)) {
        http_response_code(503);
        $message = config('app.maintenance.message', 'System is under maintenance. Please try again later.');
        
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Maintenance Mode</title>";
        echo "<style>body{font-family:Arial,sans-serif;text-align:center;padding:50px;background:#f5f5f5;}";
        echo ".container{max-width:600px;margin:0 auto;background:white;padding:40px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
        echo "h1{color:#e74c3c;margin-bottom:20px;}p{color:#666;font-size:16px;line-height:1.6;}</style>";
        echo "</head><body><div class='container'>";
        echo "<h1>🔧 Maintenance Mode</h1>";
        echo "<p>{$message}</p>";
        echo "</div></body></html>";
        exit;
    }
}

// Auto-load models
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace('\\', '/', $class) . '.php';
    
    // Check in app directory
    $appFile = __DIR__ . '/../app/' . $file;
    if (file_exists($appFile)) {
        require_once $appFile;
        return;
    }
    
    // Check in core directory
    $coreFile = __DIR__ . '/' . $file;
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }
});

// Initialize database connection (lazy loading)
try {
    Database::getInstance();
} catch (Exception $e) {
    Logger::critical('Database connection failed: ' . $e->getMessage());
    
    if (config('app.debug', false)) {
        die('Database connection failed: ' . $e->getMessage());
    } else {
        die('Database connection failed. Please check your configuration.');
    }
}

// Set up view globals
$view = new View();
$view->share([
    'app_name' => config('app.name', 'College ERP'),
    'app_version' => config('app.version', '1.0.0'),
    'current_year' => date('Y'),
    'user' => auth()->user(),
    'csrf_token' => csrf_token()
]);

Logger::debug('Bootstrap completed successfully');