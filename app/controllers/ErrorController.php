<?php

/**
 * Error Controller
 * 
 * Handles error pages and responses
 */
class ErrorController extends Controller
{
    /**
     * Show 404 Not Found page
     */
    public function notFound()
    {
        http_response_code(404);
        
        $this->render('errors/404', [
            'title' => '404 - Page Not Found'
        ], 'error');
    }
    
    /**
     * Show 403 Forbidden page
     */
    public function forbidden()
    {
        http_response_code(403);
        
        $this->render('errors/403', [
            'title' => '403 - Access Forbidden'
        ], 'error');
    }
    
    /**
     * Show 500 Internal Server Error page
     */
    public function serverError()
    {
        http_response_code(500);
        
        $this->render('errors/500', [
            'title' => '500 - Internal Server Error'
        ], 'error');
    }
    
    /**
     * Show maintenance page
     */
    public function maintenance()
    {
        http_response_code(503);
        
        $this->render('errors/maintenance', [
            'title' => 'System Maintenance'
        ], 'error');
    }
    
    /**
     * Handle AJAX errors
     */
    public function ajaxError($code = 500, $message = 'An error occurred')
    {
        http_response_code($code);
        
        return $this->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ]);
    }
    
    /**
     * Log error and show appropriate page
     */
    public function handleError($exception)
    {
        Logger::error('Application error: ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        if ($this->request->isAjax()) {
            return $this->ajaxError(500, 'An unexpected error occurred');
        }
        
        if (config('app.debug', false)) {
            // Show detailed error in debug mode
            $this->render('errors/debug', [
                'title' => 'Application Error',
                'exception' => $exception
            ], 'error');
        } else {
            // Show generic error page
            $this->serverError();
        }
    }
}