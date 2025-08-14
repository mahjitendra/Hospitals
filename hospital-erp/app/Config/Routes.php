<?php

/**
 * --------------------------------------------------------------------
 * URI Routing
 * --------------------------------------------------------------------
 * This file contains the route definitions for the application.
 * Routes are defined as an array where the key is the HTTP method (GET, POST, etc.)
 * and the value is another array of URI patterns and their corresponding
 * controller and method.
 *
 * Example:
 * 'GET' => [
 *     '/users' => ['App\Controllers\UserController', 'index'],
 *     '/user/{id}' => ['App\Controllers\UserController', 'show'],
 * ],
 *
 */

return [
    'GET' => [
        // The default route for the application, pointing to the Dashboard.
        '/' => ['App\Controllers\DashboardController', 'index'],

        // Authentication Routes
        '/register' => ['App\Controllers\AuthController', 'showRegistrationForm'],
        '/login'    => ['App\Controllers\AuthController', 'showLoginForm'],
        '/logout'   => ['App\Controllers\AuthController', 'logout'],
    ],
    'POST' => [
        // Authentication Routes
        '/register' => ['App\Controllers\AuthController', 'register'],
        '/login'    => ['App\Controllers\AuthController', 'login'],
    ],
    // You can add other HTTP methods like PUT, DELETE, etc.
];
