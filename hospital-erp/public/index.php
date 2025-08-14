<?php

// Define the root path of the application
define('ROOT_PATH', dirname(__DIR__));

/**
 * Basic Autoloader
 *
 * This simple autoloader will load classes from the 'app' directory.
 * It follows the PSR-4 naming convention.
 * Later, this will be replaced by Composer's autoloader.
 */
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';

    // Base directory for the namespace prefix
    $base_dir = ROOT_PATH . '/app/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});


/**
 * Run the Application
 *
 * Instantiate the core Application class to handle the request.
 */
try {
    $app = new App\Libraries\Core\Application();
} catch (\Exception $e) {
    // Basic error handling for application startup failures
    http_response_code(500);
    echo "<h1>Application Error</h1>";
    echo "<p>An error occurred while starting the application.</p>";
    // For debugging purposes, you might want to show the actual error
    // echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
