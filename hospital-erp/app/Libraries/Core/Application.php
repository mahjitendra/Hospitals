<?php

namespace App\Libraries\Core;

/**
 * Class Application
 *
 * The main application class that orchestrates the request handling.
 */
class Application
{
    protected Router $router;

    /**
     * Application constructor.
     * Initializes the router and runs the application.
     */
    public function __construct()
    {
        $this->initialize();
        $this->router = new Router();
        $this->run();
    }

    /**
     * Initializes application settings.
     * This can be used for setting up error handling, timezones, etc.
     */
    protected function initialize()
    {
        // Set default timezone
        date_default_timezone_set('UTC');

        // Basic error reporting setup
        // In a production environment, you'd want to log errors instead of displaying them.
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    }

    /**
     * Runs the application by dispatching the router.
     */
    protected function run()
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            // A simple, user-friendly error page for critical failures.
            http_response_code(500);
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Something went wrong on our end. Please try again later.</p>";
            // For development, log or display the detailed error.
            // error_log($e->getMessage());
        }
    }
}
