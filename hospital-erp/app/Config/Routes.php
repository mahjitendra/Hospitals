<?php

/**
 * --------------------------------------------------------------------
 * URI Routing
 * --------------------------------------------------------------------
 * This file contains the route definitions for the application.
 * Routes are defined with a 'callback' and optional 'middleware'.
 *
 * Example:
 * '/users' => [
 *     'callback' => ['App\Controllers\UserController', 'index'],
 *     'middleware' => ['App\Middleware\AuthMiddleware']
 * ],
 *
 */

return [
    'GET' => [
        // The default route for the application, pointing to the Dashboard.
        '/' => [
            'callback' => ['App\Controllers\DashboardController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class] // Example of protecting the dashboard
        ],

        // Authentication Routes
        '/register' => [
            'callback' => ['App\Controllers\AuthController', 'showRegistrationForm']
        ],
        '/login' => [
            'callback' => ['App\Controllers\AuthController', 'showLoginForm']
        ],
        '/logout' => [
            'callback' => ['App\Controllers\AuthController', 'logout'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Patient Registration Routes
        '/patients/register' => [
            'callback' => ['App\Controllers\Patient\RegistrationController', 'showForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Patient Listing and Viewing Routes
        '/patients' => [
            'callback' => ['App\Controllers\Patient\PatientController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/patients/view/{id}' => [
            'callback' => ['App\Controllers\Patient\PatientController', 'view'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Medical Record Routes
        '/patients/{patient_id}/records/create' => [
            'callback' => ['App\Controllers\Patient\MedicalRecordController', 'showCreateForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Insurance Routes
        '/patients/{patient_id}/insurance/create' => [
            'callback' => ['App\Controllers\Patient\InsuranceController', 'showCreateForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Discharge Routes
        '/patients/{patient_id}/discharge' => [
            'callback' => ['App\Controllers\Patient\DischargeController', 'showDischargeForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Laboratory Routes
        '/lab' => [
            'callback' => ['App\Controllers\Laboratory\LaboratoryController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/patients/{id}/lab/order' => [
            'callback' => ['App\Controllers\Laboratory\TestOrderController', 'showOrderForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
    ],
    'POST' => [
        // Authentication Routes
        '/register' => [
            'callback' => ['App\Controllers\AuthController', 'register']
        ],
        '/patients/register' => [
            'callback' => ['App\Controllers\Patient\RegistrationController', 'register'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/patients/{patient_id}/records/create' => [
            'callback' => ['App\Controllers\Patient\MedicalRecordController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/patients/{patient_id}/insurance/create' => [
            'callback' => ['App\Controllers\Patient\InsuranceController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/patients/{patient_id}/discharge' => [
            'callback' => ['App\Controllers\Patient\DischargeController', 'discharge'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/patients/{id}/lab/order' => [
            'callback' => ['App\Controllers\Laboratory\TestOrderController', 'createOrder'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/login' => [
            'callback' => ['App\Controllers\AuthController', 'login']
        ],
    ],
    // You can add other HTTP methods like PUT, DELETE, etc.
];
