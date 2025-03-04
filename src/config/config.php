<?php
/**
 * Configuration for Paynow Payment Bridge
 */

return [
    // Paynow Integration Details
    'paynow' => [
        'integration_id' => 'YOUR_INTEGRATION_ID', // Replace with your Paynow Integration ID
        'integration_key' => 'YOUR_INTEGRATION_KEY', // Replace with your Paynow Integration Key
        'result_url' => 'http://yourdomain.com/payment/update', // Server-to-server communication
        'return_url' => 'http://yourdomain.com/payment/complete', // Where to redirect the customer after payment
    ],
    
    // Application Settings
    'app' => [
        'base_url' => 'http://yourdomain.com', // Base URL of your application
        'success_url' => 'http://yourdomain.com/payment/success', // Successful payment page
        'error_url' => 'http://yourdomain.com/payment/error', // Failed payment page
    ]
]; 