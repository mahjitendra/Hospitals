<?php

/**
 * College ERP System
 * 
 * Main entry point for the application
 */

// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Load bootstrap
require_once ROOT_PATH . '/core/bootstrap.php';

try {
    // Create router instance
    $router = new Router();
    
    // Load routes
    $router->loadRoutes(ROOT_PATH . '/routes/web.php');
    
    // Dispatch request
    $router->dispatch();
    
} catch (Exception $e) {
    Logger::critical('Application error: ' . $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (config('app.debug', false)) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 4px; font-family: monospace;'>";
        echo "<h2>Application Error</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
        echo "</div>";
    } else {
        http_response_code(500);
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Error</title>";
        echo "<style>body{font-family:Arial,sans-serif;text-align:center;padding:50px;background:#f5f5f5;}";
        echo ".container{max-width:600px;margin:0 auto;background:white;padding:40px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
        echo "h1{color:#e74c3c;margin-bottom:20px;}p{color:#666;font-size:16px;line-height:1.6;}</style>";
        echo "</head><body><div class='container'>";
        echo "<h1>⚠️ Something went wrong</h1>";
        echo "<p>We're sorry, but something went wrong on our end. Please try again later.</p>";
        echo "<p>If the problem persists, please contact the system administrator.</p>";
        echo "</div></body></html>";
    }
}