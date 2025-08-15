<?php

namespace App\Middleware;

use App\Libraries\Core\Session;

/**
 * Class AuthMiddleware
 *
 * Checks if a user is authenticated before allowing access to a route.
 */
class AuthMiddleware
{
    /**
     * Handles the middleware check.
     *
     * If the user is not logged in, it redirects them to the login page.
     */
    public function handle()
    {
        $session = new Session();

        if (!$session->has('user')) {
            // User is not logged in, store an error message and redirect.
            $session->flash('error', 'You must be logged in to view that page.');

            // Redirect to the login page
            header('Location: /login');
            exit();
        }
    }
}
