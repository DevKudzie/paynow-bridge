<?php
/**
 * Payment Status Check Endpoint
 * 
 * This file checks the status of a payment using the poll URL
 * and returns the status as JSON.
 */

// Load the Composer autoloader (same as in index.php)
require_once __DIR__ . '/../vendor/autoload.php';

// Get the poll URL from the query string
$pollUrl = $_GET['poll_url'] ?? null;

// Create a log directory if it doesn't exist
$logPath = '/var/www/html/logs';
if (!file_exists($logPath)) {
    mkdir($logPath, 0755, true);
}

// Load configuration to get success and error URLs
$config = require_once __DIR__ . '/../src/config/config.php';
$successUrl = $config['app']['success_url'] ?? '/payment/success';
$errorUrl = $config['app']['error_url'] ?? '/payment/error';

if ($pollUrl) {
    // Log the request with a unique identifier for tracing
    $requestId = uniqid();
    $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Status Check Request - Poll URL: $pollUrl" . PHP_EOL;
    file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
    
    try {
        // Use the PaymentController to check status (avoid Dotenv issues)
        // This approach uses the existing application structure which already loads the configuration
        require_once __DIR__ . '/../src/Controllers/PaymentController.php';
        require_once __DIR__ . '/../src/Models/Payment.php';
        
        $controller = new App\Controllers\PaymentController();
        $status = $controller->checkStatus($pollUrl);
        
        // Add redirect URLs to the response
        if (isset($status['paid']) && $status['paid'] === true) {
            $status['redirect_url'] = $successUrl;
        } else if (isset($status['status']) && ($status['status'] === 'cancelled' || $status['status'] === 'failed')) {
            $status['redirect_url'] = $errorUrl;
        }
        
        // Log the response with redirect URL
        $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Status Check Response - " . 
                      "Status: " . ($status['status'] ?? 'Unknown') . ", " .
                      "Paid: " . (($status['paid'] ?? false) ? 'true' : 'false') . ", " .
                      "Redirect URL: " . ($status['redirect_url'] ?? 'None') . ", " .
                      "Full data: " . json_encode($status, JSON_PRETTY_PRINT) . PHP_EOL;
        file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($status);
    } catch (Exception $e) {
        // Log the error
        $errorMessage = date('Y-m-d H:i:s') . " [$requestId] - Status Check Error - " . $e->getMessage() . PHP_EOL;
        file_put_contents($logPath . '/status_checks.log', $errorMessage, FILE_APPEND);
        
        // Return error response
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'An error occurred while checking payment status: ' . $e->getMessage(),
            'paid' => false,
            'status' => 'Error',
            'redirect_url' => $errorUrl
        ]);
    }
} else {
    // No poll URL provided
    $requestId = uniqid();
    $errorMessage = date('Y-m-d H:i:s') . " [$requestId] - Status Check Error - No poll URL provided" . PHP_EOL;
    file_put_contents($logPath . '/status_checks.log', $errorMessage, FILE_APPEND);
    
    // Return error response
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'error' => 'No poll URL provided',
        'paid' => false,
        'status' => 'Error',
        'redirect_url' => $errorUrl
    ]);
} 