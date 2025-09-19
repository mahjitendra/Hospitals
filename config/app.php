<?php
/**
 * Application Configuration
 * 
 * Core application settings and configurations
 */

return [
    'name' => env('APP_NAME', 'College ERP'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => 'en',
    'fallback_locale' => 'en',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    
    'version' => '1.0.0',
    'build' => env('APP_BUILD', '1'),
    
    'maintenance' => [
        'enabled' => env('MAINTENANCE_MODE', false),
        'message' => 'System is under maintenance. Please try again later.',
        'allowed_ips' => explode(',', env('MAINTENANCE_ALLOWED_IPS', '')),
    ],
    
    'security' => [
        'csrf_protection' => true,
        'session_timeout' => 3600, // 1 hour
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'password_min_length' => 8,
        'password_require_special' => true,
        'password_require_numbers' => true,
        'password_require_uppercase' => true,
    ],
    
    'upload' => [
        'max_file_size' => 10485760, // 10MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'image_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'document_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
    ],
    
    'pagination' => [
        'per_page' => 25,
        'max_per_page' => 100,
    ],
    
    'academic' => [
        'current_session' => env('CURRENT_SESSION', '2024-25'),
        'session_start_month' => 4, // April
        'session_end_month' => 3,   // March
        'default_semester_duration' => 6, // months
    ],
];