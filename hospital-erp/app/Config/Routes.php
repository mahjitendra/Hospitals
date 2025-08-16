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

        // Financial Routes
        '/patients/{id}/invoices' => [
            'callback' => ['App\Controllers\Financial\InvoiceController', 'listByPatient'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/invoices/view/{id}' => [
            'callback' => ['App\Controllers\Financial\InvoiceController', 'view'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/patients/{id}/invoices/create' => [
            'callback' => ['App\Controllers\Financial\InvoiceController', 'showCreateForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/invoices/{id}/pay' => [
            'callback' => ['App\Controllers\Financial\PaymentController', 'showPaymentForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // HR Routes
        '/hr/staff' => [
            'callback' => ['App\Controllers\HumanResource\StaffController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/hr/staff/view/{id}' => [
            'callback' => ['App\Controllers\HumanResource\StaffController', 'view'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/hr/staff/create' => [
            'callback' => ['App\Controllers\HumanResource\StaffController', 'showCreateForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/hr/staff/{id}/schedules' => [
            'callback' => ['App\Controllers\HumanResource\ScheduleController', 'listByStaff'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/hr/staff/{id}/schedules/create' => [
            'callback' => ['App\Controllers\HumanResource\ScheduleController', 'showCreateForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Inventory Routes
        '/inventory/items' => [
            'callback' => ['App\Controllers\Inventory\InventoryController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/inventory/items/create' => [
            'callback' => ['App\Controllers\Inventory\InventoryController', 'showCreateForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/inventory/vendors' => [
            'callback' => ['App\Controllers\Inventory\VendorController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/inventory/vendors/create' => [
            'callback' => ['App\Controllers\Inventory\VendorController', 'showCreateForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Quality Routes
        '/quality' => [
            'callback' => ['App\Controllers\Quality\QualityController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/quality/incidents' => [
            'callback' => ['App\Controllers\Quality\IncidentController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/quality/incidents/create' => [
            'callback' => ['App\Controllers\Quality\IncidentController', 'showCreateForm'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],

        // Reports Routes
        '/reports' => [
            'callback' => ['App\Controllers\Reports\ReportController', 'index'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/reports/analytics/patient-demographics' => [
            'callback' => ['App\Controllers\Reports\AnalyticsController', 'viewPatientDemographics'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/reports/analytics/financial-summary' => [
            'callback' => ['App\Controllers\Reports\AnalyticsController', 'viewFinancialSummary'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/reports/analytics/inventory-status' => [
            'callback' => ['App\Controllers\Reports\AnalyticsController', 'viewInventoryStatus'],
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
        '/patients/{id}/invoices/create' => [
            'callback' => ['App\Controllers\Financial\InvoiceController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/invoices/{id}/pay' => [
            'callback' => ['App\Controllers\Financial\PaymentController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/hr/staff/create' => [
            'callback' => ['App\Controllers\HumanResource\StaffController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/hr/staff/{id}/schedules/create' => [
            'callback' => ['App\Controllers\HumanResource\ScheduleController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/inventory/items/create' => [
            'callback' => ['App\Controllers\Inventory\InventoryController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/inventory/vendors/create' => [
            'callback' => ['App\Controllers\Inventory\VendorController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/quality/incidents/create' => [
            'callback' => ['App\Controllers\Quality\IncidentController', 'create'],
            'middleware' => [\App\Middleware\AuthMiddleware::class]
        ],
        '/login' => [
            'callback' => ['App\Controllers\AuthController', 'login']
        ],
    ],
    // You can add other HTTP methods like PUT, DELETE, etc.
];
