<?php
/**
 * SMS Configuration
 * 
 * SMS gateway configuration settings
 */

return [
    'default' => env('SMS_DRIVER', 'twilio'),
    
    'drivers' => [
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
        
        'msg91' => [
            'auth_key' => env('MSG91_AUTH_KEY'),
            'sender_id' => env('MSG91_SENDER_ID'),
            'route' => env('MSG91_ROUTE', '4'),
        ],
        
        'textlocal' => [
            'api_key' => env('TEXTLOCAL_API_KEY'),
            'sender' => env('TEXTLOCAL_SENDER'),
        ],
        
        'nexmo' => [
            'key' => env('NEXMO_KEY'),
            'secret' => env('NEXMO_SECRET'),
            'from' => env('NEXMO_FROM'),
        ],
    ],
    
    'templates' => [
        'admission_confirmation' => 'Dear {name}, your admission to {course} has been confirmed. Student ID: {student_id}',
        'fee_reminder' => 'Dear {name}, your fee payment of Rs.{amount} is due on {due_date}. Please pay to avoid late fees.',
        'attendance_alert' => 'Dear Parent, your ward {student_name} was absent today ({date}). Contact school for details.',
        'exam_notification' => 'Dear {name}, your {exam_name} exam is scheduled on {date} at {time}. Venue: {venue}',
        'result_notification' => 'Dear {name}, your {exam_name} results are now available. Login to view your marks.',
        'otp_verification' => 'Your OTP for College ERP is: {otp}. Valid for 10 minutes.',
    ],
    
    'settings' => [
        'max_length' => 160,
        'unicode_support' => true,
        'delivery_reports' => true,
        'rate_limit' => 100, // per hour
    ],
];