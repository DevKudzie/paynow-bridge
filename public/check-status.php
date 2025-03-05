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
$successUrl = isset($config['app']) && isset($config['app']['success_url']) ? 
    $config['app']['success_url'] : '/payment/success';
$errorUrl = isset($config['app']) && isset($config['app']['error_url']) ? 
    $config['app']['error_url'] : '/payment/error';

if ($pollUrl) {
    // Log the request with a unique identifier for tracing
    $requestId = uniqid();
    $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Status Check Request - Poll URL: $pollUrl" . PHP_EOL;
    file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
    
    try {
        // Use the PaymentController to check status (avoid Dotenv issues)
        // This approach uses the existing application structure which already loads the configuration
        require_once __DIR__ . '/../src/controllers/PaymentController.php';
        require_once __DIR__ . '/../src/models/Payment.php';
        
        // Debug info for troubleshooting
        $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Loading controller for status check" . PHP_EOL;
        file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
        
        try {
            $controller = new App\Controllers\PaymentController();
            
            // Debug - verify controller
            $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Controller loaded, checking status" . PHP_EOL;
            file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
            
            // IMPORTANT: Directly catch any Paynow SDK exceptions for better diagnostics
            try {
                $status = $controller->checkStatus($pollUrl);
                
                // Debug - log raw status response
                $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Raw status response: " . json_encode($status) . PHP_EOL;
                file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
                
                // Add redirect URLs to the response
                if (isset($status['paid']) && $status['paid'] === true) {
                    $status['redirect_url'] = $successUrl;
                    
                    // Log successful redirect
                    $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Payment successful, setting redirect URL: $successUrl" . PHP_EOL;
                    file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
                } else if (isset($status['status']) && ($status['status'] === 'cancelled' || $status['status'] === 'failed')) {
                    $status['redirect_url'] = $errorUrl;
                    
                    // Log error redirect
                    $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Payment failed or cancelled, setting error redirect URL: $errorUrl" . PHP_EOL;
                    file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
                }
            } catch (\Exception $sdkError) {
                // Special handling for SDK errors
                $errorMsg = "Paynow SDK error: " . $sdkError->getMessage();
                $logMessage = date('Y-m-d H:i:s') . " [$requestId] - $errorMsg" . PHP_EOL;
                file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
                
                // Log stack trace for debugging
                $logMessage = date('Y-m-d H:i:s') . " [$requestId] - Stack trace: " . $sdkError->getTraceAsString() . PHP_EOL;
                file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
                
                $status = [
                    'paid' => false,
                    'status' => 'Error',
                    'error_message' => $sdkError->getMessage(),
                    'error_code' => $sdkError->getCode(),
                    'redirect_url' => $errorUrl,
                    'checked_at' => date('Y-m-d H:i:s')
                ];
            }
        } catch (\Exception $controllerError) {
            // Error creating controller
            $errorMsg = "Controller error: " . $controllerError->getMessage();
            $logMessage = date('Y-m-d H:i:s') . " [$requestId] - $errorMsg" . PHP_EOL;
            file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
            
            $status = [
                'paid' => false,
                'status' => 'Error',
                'error_message' => $errorMsg,
                'redirect_url' => $errorUrl,
                'checked_at' => date('Y-m-d H:i:s')
            ];
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