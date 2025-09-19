<?php

/**
 * URL Router Class
 * 
 * Handles URL routing and dispatching to controllers
 */
class Router
{
    private $routes = [];
    private $middlewares = [];
    private $currentRoute = null;
    private $parameters = [];
    
    /**
     * Add GET route
     */
    public function get($pattern, $handler, $middlewares = [])
    {
        return $this->addRoute('GET', $pattern, $handler, $middlewares);
    }
    
    /**
     * Add POST route
     */
    public function post($pattern, $handler, $middlewares = [])
    {
        return $this->addRoute('POST', $pattern, $handler, $middlewares);
    }
    
    /**
     * Add PUT route
     */
    public function put($pattern, $handler, $middlewares = [])
    {
        return $this->addRoute('PUT', $pattern, $handler, $middlewares);
    }
    
    /**
     * Add PATCH route
     */
    public function patch($pattern, $handler, $middlewares = [])
    {
        return $this->addRoute('PATCH', $pattern, $handler, $middlewares);
    }
    
    /**
     * Add DELETE route
     */
    public function delete($pattern, $handler, $middlewares = [])
    {
        return $this->addRoute('DELETE', $pattern, $handler, $middlewares);
    }
    
    /**
     * Add route for any method
     */
    public function any($pattern, $handler, $middlewares = [])
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        foreach ($methods as $method) {
            $this->addRoute($method, $pattern, $handler, $middlewares);
        }
    }
    
    /**
     * Add route group with common middleware
     */
    public function group($middlewares, $callback)
    {
        $originalMiddlewares = $this->middlewares;
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        
        $callback($this);
        
        $this->middlewares = $originalMiddlewares;
    }
    
    /**
     * Add route with prefix
     */
    public function prefix($prefix, $callback)
    {
        $originalPrefix = $this->currentPrefix ?? '';
        $this->currentPrefix = $originalPrefix . '/' . trim($prefix, '/');
        
        $callback($this);
        
        $this->currentPrefix = $originalPrefix;
    }
    
    /**
     * Add a route
     */
    private function addRoute($method, $pattern, $handler, $middlewares = [])
    {
        // Add prefix if set
        if (isset($this->currentPrefix)) {
            $pattern = $this->currentPrefix . $pattern;
        }
        
        // Combine middlewares
        $middlewares = array_merge($this->middlewares, $middlewares);
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
        
        return $this;
    }
    
    /**
     * Dispatch the current request
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Handle method override
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = $this->convertPatternToRegex($route['pattern']);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                $this->parameters = $matches;
                $this->currentRoute = $route;
                
                // Run middlewares
                $this->runMiddlewares($route['middlewares']);
                
                // Execute handler
                return $this->executeHandler($route['handler'], $matches);
            }
        }
        
        // No route found
        $this->handleNotFound();
    }
    
    /**
     * Convert route pattern to regex
     */
    private function convertPatternToRegex($pattern)
    {
        // Replace {param} with regex group
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        
        // Replace {param?} with optional regex group
        $pattern = preg_replace('/\{([^}]+)\?\}/', '([^/]*)', $pattern);
        
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Run middlewares
     */
    private function runMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            if (is_string($middleware)) {
                if (class_exists($middleware)) {
                    $middlewareInstance = new $middleware();
                    $middlewareInstance->handle();
                } else {
                    throw new Exception("Middleware class not found: {$middleware}");
                }
            } elseif (is_callable($middleware)) {
                $middleware();
            }
        }
    }
    
    /**
     * Execute route handler
     */
    private function executeHandler($handler, $parameters = [])
    {
        if (is_string($handler)) {
            // Handle "Controller@method" format
            if (strpos($handler, '@') !== false) {
                list($controller, $method) = explode('@', $handler);
                
                if (!class_exists($controller)) {
                    throw new Exception("Controller class not found: {$controller}");
                }
                
                $controllerInstance = new $controller();
                
                if (!method_exists($controllerInstance, $method)) {
                    throw new Exception("Method not found: {$controller}::{$method}");
                }
                
                return call_user_func_array([$controllerInstance, $method], $parameters);
            }
            
            // Handle function name
            if (function_exists($handler)) {
                return call_user_func_array($handler, $parameters);
            }
            
            throw new Exception("Handler not found: {$handler}");
        }
        
        if (is_array($handler)) {
            // Handle [Controller::class, 'method'] format
            if (count($handler) === 2) {
                list($controller, $method) = $handler;
                
                if (!class_exists($controller)) {
                    throw new Exception("Controller class not found: {$controller}");
                }
                
                $controllerInstance = new $controller();
                
                if (!method_exists($controllerInstance, $method)) {
                    throw new Exception("Method not found: {$controller}::{$method}");
                }
                
                return call_user_func_array([$controllerInstance, $method], $parameters);
            }
        }
        
        if (is_callable($handler)) {
            return call_user_func_array($handler, $parameters);
        }
        
        throw new Exception("Invalid route handler");
    }
    
    /**
     * Handle 404 Not Found
     */
    private function handleNotFound()
    {
        http_response_code(404);
        
        // Try to load 404 view
        try {
            $view = new View();
            $view->render('errors.404', ['title' => '404 - Page Not Found']);
        } catch (Exception $e) {
            // Fallback to simple 404 message
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The requested page could not be found.</p>';
        }
    }
    
    /**
     * Generate URL for named route
     */
    public function url($name, $parameters = [])
    {
        // This would require implementing named routes
        // For now, return a simple URL
        return '/' . ltrim($name, '/');
    }
    
    /**
     * Get current route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }
    
    /**
     * Get route parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }
    
    /**
     * Get specific parameter
     */
    public function getParameter($index, $default = null)
    {
        return $this->parameters[$index] ?? $default;
    }
    
    /**
     * Load routes from file
     */
    public function loadRoutes($file)
    {
        if (!file_exists($file)) {
            throw new Exception("Routes file not found: {$file}");
        }
        
        $router = $this;
        require $file;
    }
}