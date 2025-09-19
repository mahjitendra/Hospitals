<?php
/**
 * Backup Configuration
 * 
 * Database and file backup settings
 */

return [
    'backup' => [
        'name' => env('APP_NAME', 'college_erp'),
        
        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('storage/logs'),
                    base_path('storage/cache'),
                    base_path('storage/temp'),
                ],
                'follow_links' => false,
            ],
            
            'databases' => [
                'mysql',
            ],
        ],
        
        'destination' => [
            'filename_prefix' => '',
            'disks' => [
                'local',
            ],
        ],
        
        'temporary_directory' => storage_path('temp'),
    ],
    
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => ['mail'],
        ],
        
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
        
        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', 'admin@college.edu'),
        ],
    ],
    
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'college_erp'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],
    
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
    
    'schedule' => [
        'daily' => '02:00',
        'weekly' => 'sunday 03:00',
        'monthly' => 'first sunday 04:00',
    ],
];