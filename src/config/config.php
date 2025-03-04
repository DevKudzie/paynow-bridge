<?php
/**
 * Configuration for Paynow Payment Bridge
 */

// Helper function to get environment variables with fallbacks
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

return [
    // Paynow Integration Details
    'paynow' => [
        'integration_id' => env('PAYNOW_INTEGRATION_ID', ''),
        'integration_key' => env('PAYNOW_INTEGRATION_KEY', ''),
        'result_url' => env('PAYNOW_RESULT_URL', 'http://localhost:8080/payment/update'),
        'return_url' => env('PAYNOW_RETURN_URL', 'http://localhost:8080/payment/complete'),
        'auth_email' => env('PAYNOW_AUTH_EMAIL', 'test@example.com'),
        'test_mode' => env('PAYNOW_TEST_MODE', 'true') === 'true',
    ],
    
    // Application Settings
    'app' => [
        'base_url' => env('APP_BASE_URL', 'http://localhost:8080'),
        'success_url' => env('APP_SUCCESS_URL', 'http://localhost:8080/payment/success'),
        'error_url' => env('APP_ERROR_URL', 'http://localhost:8080/payment/error'),
    ],
    
    // Logging Configuration
    'logging' => [
        'enabled' => env('LOGGING_ENABLED', 'true') === 'true',
        'path' => env('LOG_PATH', '/var/www/html/logs'),
        'level' => env('LOG_LEVEL', 'info'), // possible values: debug, info, warning, error
        'print_to_terminal' => env('PRINT_LOGS_TO_TERMINAL', 'true') === 'true',
        'debug_logs' => env('DEBUG_LOGS', 'false') === 'true'
    ]
]; 