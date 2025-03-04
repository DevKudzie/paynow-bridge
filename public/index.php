<?php
/**
 * Main entry point for the Paynow Bridge System
 */

// Load the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Define routes and their handlers
$routes = [
    '/payment/bridge' => 'handleBridgePage',
    '/payment/update' => 'handlePaynowCallback',
    '/payment/complete' => 'handlePaymentComplete',
    '/payment/success' => 'showSuccessPage',
    '/payment/error' => 'showErrorPage',
    '/check-status.php' => 'checkPaymentStatus',
    '/' => 'showHomePage'
];

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove trailing slash if present
$path = rtrim($path, '/');

// Default to home if path is empty
if (empty($path)) {
    $path = '/';
}

// Handle the route or show 404
if (isset($routes[$path])) {
    $handlerFunction = $routes[$path];
    call_user_func($handlerFunction);
} else {
    header("HTTP/1.0 404 Not Found");
    echo "404 - Page not found";
    exit;
}

/**
 * Handle the bridge page that processes payments
 */
function handleBridgePage() {
    // Process the payment request
    $paymentController = new \App\Controllers\PaymentController();
    
    // Extract request data
    $requestData = $_GET;
    
    // Process the payment
    $paymentResponse = $paymentController->bridgePayment($requestData);
    
    // Prepare data for the view
    if ($paymentResponse['success']) {
        $redirectUrl = $paymentResponse['redirect_url'] ?? null;
        $instructions = $paymentResponse['instructions'] ?? null;
        $pollUrl = $paymentResponse['poll_url'] ?? null;
        $paymentMethod = $paymentResponse['payment_method'] ?? null;
        
        // Get config
        $config = require_once __DIR__ . '/../src/config/config.php';
        $successUrl = $config['app']['success_url'];
        $errorUrl = $config['app']['error_url'];
        
        // Display the bridge page
        require_once __DIR__ . '/../src/views/bridge.php';
    } else {
        $error = $paymentResponse['error'] ?? 'Unknown error occurred';
        $config = require_once __DIR__ . '/../src/config/config.php';
        $errorUrl = $config['app']['error_url'];
        
        // Display the bridge page with error
        require_once __DIR__ . '/../src/views/bridge.php';
    }
}

/**
 * Handle the callback from Paynow (server-to-server)
 */
function handlePaynowCallback() {
    $paymentController = new \App\Controllers\PaymentController();
    $callbackData = $_POST;
    
    // Process the callback
    $success = $paymentController->processCallback($callbackData);
    
    // Return appropriate response
    if ($success) {
        echo "OK";
    } else {
        echo "Error processing callback";
    }
    
    exit;
}

/**
 * Handle the return from Paynow (redirect)
 */
function handlePaymentComplete() {
    $paymentController = new \App\Controllers\PaymentController();
    
    // Get the poll URL from the session or query parameters
    $pollUrl = $_GET['pollurl'] ?? null;
    
    if ($pollUrl) {
        // Check the payment status
        $status = $paymentController->checkStatus($pollUrl);
        
        // Redirect based on status
        $redirectUrl = $paymentController->getRedirectUrl($status['paid']);
        header("Location: $redirectUrl");
        exit;
    } else {
        // No poll URL, redirect to error
        $config = require_once __DIR__ . '/../src/config/config.php';
        header("Location: " . $config['app']['error_url']);
        exit;
    }
}

/**
 * Show the success page
 */
function showSuccessPage() {
    // You would typically retrieve payment details from your database here
    // For this example, we'll use dummy data
    $paymentDetails = [
        'reference' => $_GET['reference'] ?? 'INV' . time(),
        'amount' => $_GET['amount'] ?? '100.00',
        'date' => date('Y-m-d H:i:s')
    ];
    
    // Get config
    $config = require_once __DIR__ . '/../src/config/config.php';
    $homeUrl = $config['app']['base_url'];
    
    // Display the success page
    require_once __DIR__ . '/../src/views/success.php';
}

/**
 * Show the error page
 */
function showErrorPage() {
    $errorMessage = $_GET['error'] ?? 'There was a problem processing your payment.';
    
    // Get config
    $config = require_once __DIR__ . '/../src/config/config.php';
    $homeUrl = $config['app']['base_url'];
    $retryUrl = $_SERVER['HTTP_REFERER'] ?? $homeUrl;
    
    // Display the error page
    require_once __DIR__ . '/../src/views/error.php';
}

/**
 * Check payment status via AJAX
 */
function checkPaymentStatus() {
    $pollUrl = $_GET['poll_url'] ?? null;
    
    if ($pollUrl) {
        $paymentController = new \App\Controllers\PaymentController();
        $status = $paymentController->checkStatus($pollUrl);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($status);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No poll URL provided']);
    }
    
    exit;
}

/**
 * Show the home page (simple redirection to bridge)
 */
function showHomePage() {
    // Create a simple landing page
    require_once __DIR__ . '/../src/views/home.php';
} 