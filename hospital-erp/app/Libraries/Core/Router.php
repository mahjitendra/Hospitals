<?php

namespace App\Libraries\Core;

/**
 * Class Router
 *
 * A simple router that dispatches requests to controller actions.
 */
class Router
{
    protected array $routes = [];
    protected Request $request;
    protected Response $response;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->loadRoutes();
    }

    /**
     * Loads the routes from the configuration file.
     */
    protected function loadRoutes()
    {
        $routesPath = ROOT_PATH . '/app/Config/Routes.php';
        if (file_exists($routesPath)) {
            $this->routes = require $routesPath;
        } else {
            // This is a critical error, the application can't function without routes.
            throw new \Exception("Route configuration file not found.");
        }
    }

    /**
     * Dispatches the request to the matched route.
     */
    public function dispatch()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();

        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];

            if (is_array($callback) && count($callback) === 2) {
                [$controller, $action] = $callback;

                if (class_exists($controller)) {
                    $controllerInstance = new $controller();

                    if (method_exists($controllerInstance, $action)) {
                        // Call the controller action
                        call_user_func_array([$controllerInstance, $action], []);
                        return;
                    }
                }
            }
        }

        // If no route is matched, handle as a 404 Not Found error.
        $this->handleNotFound();
    }

    /**
     * Handles 404 Not Found errors.
     */
    protected function handleNotFound()
    {
        $this->response->setStatusCode(404);
        // In a real application, you'd show a user-friendly 404 view.
        // For now, a simple message is sufficient.
        require_once ROOT_PATH . '/app/Views/errors/404.php';
    }
}
