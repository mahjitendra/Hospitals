<?php
/**
 * Session Configuration
 * 
 * Session management settings
 */

return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('sessions'),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', 'college_erp_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN', null),
    'secure' => env('SESSION_SECURE_COOKIE', false),
    'http_only' => true,
    'same_site' => 'lax',
    
    'security' => [
        'regenerate_on_login' => true,
        'regenerate_interval' => 300, // 5 minutes
        'ip_validation' => true,
        'user_agent_validation' => true,
    ],
];