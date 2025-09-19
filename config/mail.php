<?php
/**
 * Mail Configuration
 * 
 * Email service configuration settings
 */

return [
    'default' => env('MAIL_MAILER', 'smtp'),
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
        ],
        
        'ses' => [
            'transport' => 'ses',
        ],
        
        'mailgun' => [
            'transport' => 'mailgun',
        ],
        
        'postmark' => [
            'transport' => 'postmark',
        ],
        
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => '/usr/sbin/sendmail -bs',
        ],
        
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
        
        'array' => [
            'transport' => 'array',
        ],
    ],
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@college.edu'),
        'name' => env('MAIL_FROM_NAME', 'College ERP'),
    ],
    
    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
    
    'templates' => [
        'welcome' => 'emails.welcome',
        'password_reset' => 'emails.password_reset',
        'admission_confirmation' => 'emails.admission_confirmation',
        'fee_reminder' => 'emails.fee_reminder',
        'exam_notification' => 'emails.exam_notification',
        'attendance_alert' => 'emails.attendance_alert',
    ],
];