<?php
/**
 * Configuration for Paynow Payment Bridge
 */

// Helper function to get environment variables with fallbacks
function env($key, $default = null) {
    // Environment variable already loaded in $_ENV or getenv()
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    
    // For debugging
    error_log("ENV VARIABLE NOT FOUND: $key, using default: $default");
    
    return $default;
}

// Load the .env file if Dotenv exists and it's not already loaded
$dotEnvPath = __DIR__ . '/../../.env';
if (file_exists($dotEnvPath)) {
    $lines = file($dotEnvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Don't overwrite if already set
        if (!isset($_ENV[$key]) && !isset($_SERVER[$key]) && getenv($key) === false) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
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