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
        // Only set these variables if they have actual values
        $redirectUrl = !empty($paymentResponse['redirect_url']) ? $paymentResponse['redirect_url'] : null;
        $instructions = !empty($paymentResponse['instructions']) ? $paymentResponse['instructions'] : null;
        $pollUrl = !empty($paymentResponse['poll_url']) ? $paymentResponse['poll_url'] : null;
        $paymentMethod = !empty($paymentResponse['payment_method']) ? $paymentResponse['payment_method'] : null;
        $test_mode_message = !empty($paymentResponse['test_mode_message']) ? $paymentResponse['test_mode_message'] : null;
        $rawResponse = $paymentResponse['raw_response'] ?? null;
        
        // Log what we're passing to the view - use file logging to avoid output issues
        $logPath = '/var/www/html/logs';
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        $logMessage = date('Y-m-d H:i:s') . " - Passing to view - redirectUrl: " . ($redirectUrl ?? 'null') . 
                     ", instructions: " . ($instructions ?? 'null') . 
                     ", pollUrl: " . ($pollUrl ?? 'null') . 
                     ", paymentMethod: " . ($paymentMethod ?? 'null') . PHP_EOL;
        file_put_contents($logPath . '/bridge_debug.log', $logMessage, FILE_APPEND);
        
        // Get success and error URLs from config
        $config = require_once __DIR__ . '/../src/config/config.php';
        $successUrl = $config['app']['success_url'];
        $errorUrl = $config['app']['error_url'];
        
        // For web payments only, redirect directly to Paynow payment page
        // Mobile payments and other types will show the bridge page
        if ($redirectUrl && !$instructions && empty($paymentMethod) && !isset($test_mode_message)) {
            // Only redirect for web payments with no special messages
            header("Location: $redirectUrl");
            exit;
        }
        
        // Display the bridge page with appropriate context
        require_once __DIR__ . '/../src/views/bridge.php';
    } else {
        // Payment failed
        $error = $paymentResponse['error'];
        
        // In development mode, add debug info
        if (getenv('APP_ENV') === 'development' && isset($paymentResponse['debug_info'])) {
            $debugInfo = "<details class='mt-4'><summary class='cursor-pointer text-sm text-destructive/90 font-medium'>Technical Details</summary>";
            $debugInfo .= "<div class='mt-2 p-3 bg-muted/50 rounded text-xs font-mono text-muted-foreground'>";
            $debugInfo .= nl2br(htmlspecialchars($paymentResponse['debug_info']));
            $debugInfo .= "</div></details>";
            $error .= $debugInfo;
        }
        
        // Get config
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
 * Handle the return from Paynow (user is redirected back)
 */
function handlePaymentComplete() {
    // Create payment controller
    require_once __DIR__ . '/../src/Controllers/PaymentController.php';
    $paymentController = new App\Controllers\PaymentController();
    
    // Get return data from query parameters
    $returnData = $_GET;
    
    // Log the return data
    $logPath = '/var/www/html/logs';
    if (!file_exists($logPath)) {
        mkdir($logPath, 0755, true);
    }
    $logMessage = date('Y-m-d H:i:s') . " - Return Data: " . json_encode($returnData) . PHP_EOL;
    file_put_contents($logPath . '/returns.log', $logMessage, FILE_APPEND);
    
    // Process the return and get the redirect URL
    $redirectUrl = $paymentController->processReturn($returnData);
    
    // Redirect the user
    header("Location: $redirectUrl");
    exit;
}

/**
 * Show the success page
 */
function showSuccessPage() {
    // Start session to store payment details
    session_start();
    
    // You would typically retrieve payment details from your database here
    // For this example, we'll use dummy data with data from the query string if available
    $paymentDetails = [
        'reference' => $_GET['reference'] ?? 'INV' . time(),
        'amount' => $_GET['amount'] ?? '100.00',
        'date' => date('Y-m-d H:i:s'),
        'status' => 'Paid',
        'email' => $_GET['email'] ?? 'customer@example.com',
        'items' => [
            ['name' => 'Payment', 'quantity' => 1, 'price' => $_GET['amount'] ?? '100.00']
        ],
        'merchant' => 'Paynow Bridge System',
        'merchant_address' => 'Your Business Address',
        'paynow_reference' => $_GET['paynow_reference'] ?? 'PN' . rand(100000, 999999)
    ];
    
    // Store payment details in session for receipt generation
    $_SESSION['payment_details'] = $paymentDetails;
    
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
 * Handle status checking for payments
 */
function checkPaymentStatus() {
    // Get the poll URL parameter
    $pollUrl = $_GET['poll_url'] ?? null;
    
    if ($pollUrl) {
        // Check the payment status
        $paymentController = new \App\Controllers\PaymentController();
        $status = $paymentController->checkStatus($pollUrl);
        
        // Log the status for debugging
        $logPath = '/var/www/html/logs';
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        $logMessage = date('Y-m-d H:i:s') . " - Status Check - Poll URL: $pollUrl, Status: " . 
                      print_r($status, true) . PHP_EOL;
        file_put_contents($logPath . '/status_checks.log', $logMessage, FILE_APPEND);
        
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