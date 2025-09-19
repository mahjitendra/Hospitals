<?php
/**
 * Payment Gateway Configuration
 * 
 * Payment processing settings
 */

return [
    'default' => env('PAYMENT_GATEWAY', 'razorpay'),
    
    'gateways' => [
        'razorpay' => [
            'key_id' => env('RAZORPAY_KEY_ID'),
            'key_secret' => env('RAZORPAY_KEY_SECRET'),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
            'currency' => 'INR',
            'test_mode' => env('RAZORPAY_TEST_MODE', true),
        ],
        
        'paytm' => [
            'merchant_id' => env('PAYTM_MERCHANT_ID'),
            'merchant_key' => env('PAYTM_MERCHANT_KEY'),
            'website' => env('PAYTM_WEBSITE', 'WEBSTAGING'),
            'industry_type' => env('PAYTM_INDUSTRY_TYPE', 'Retail'),
            'channel_id' => env('PAYTM_CHANNEL_ID', 'WEB'),
            'test_mode' => env('PAYTM_TEST_MODE', true),
        ],
        
        'stripe' => [
            'public_key' => env('STRIPE_PUBLIC_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => 'usd',
            'test_mode' => env('STRIPE_TEST_MODE', true),
        ],
        
        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
            'currency' => 'USD',
        ],
    ],
    
    'fee_types' => [
        'admission' => 'Admission Fee',
        'tuition' => 'Tuition Fee',
        'examination' => 'Examination Fee',
        'library' => 'Library Fee',
        'laboratory' => 'Laboratory Fee',
        'hostel' => 'Hostel Fee',
        'transport' => 'Transport Fee',
        'miscellaneous' => 'Miscellaneous Fee',
        'fine' => 'Fine',
        'caution_deposit' => 'Caution Deposit',
    ],
    
    'settings' => [
        'auto_receipt' => true,
        'email_receipt' => true,
        'sms_receipt' => true,
        'late_fee_percentage' => 2,
        'grace_period_days' => 7,
        'partial_payment' => true,
        'minimum_partial_amount' => 1000,
    ],
];