<?php
/**
 * Cache Configuration
 * 
 * Caching system configuration
 */

return [
    'default' => env('CACHE_DRIVER', 'file'),
    
    'stores' => [
        'apc' => [
            'driver' => 'apc',
        ],
        
        'array' => [
            'driver' => 'array',
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],
        
        'file' => [
            'driver' => 'file',
            'path' => storage_path('cache/data'),
        ],
        
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
        
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
    ],
    
    'prefix' => env('CACHE_PREFIX', 'college_erp_cache'),
    
    'ttl' => [
        'default' => 3600, // 1 hour
        'students' => 7200, // 2 hours
        'faculty' => 7200, // 2 hours
        'courses' => 86400, // 24 hours
        'settings' => 86400, // 24 hours
        'reports' => 1800, // 30 minutes
    ],
];