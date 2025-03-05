<?php
/**
 * Main entry point for the Paynow Bridge System
 */

// Debug output for file paths
$vendorPath = __DIR__ . '/../vendor/autoload.php';
$exists = file_exists($vendorPath) ? 'exists' : 'does not exist';
error_log("Autoload path: $vendorPath - $exists");

// Create logs directory if it doesn't exist
$logPath = __DIR__ . '/../logs';
if (!file_exists($logPath)) {
    mkdir($logPath, 0777, true);
    error_log("Created logs directory: $logPath");
}

// Try to load the Composer autoloader
if (file_exists($vendorPath)) {
    require_once $vendorPath;
    error_log("Successfully loaded autoloader");
} else {
    // Log the error
    $errorMessage = "ERROR: Could not find autoloader at $vendorPath. Current directory: " . __DIR__;
    error_log($errorMessage);
    file_put_contents($logPath . '/startup_error.log', $errorMessage . PHP_EOL, FILE_APPEND);
    
    // Extremely simple error page
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>System Setup Error</h1>";
    echo "<p>The application could not find required dependencies. Please contact the administrator.</p>";
    if (getenv('APP_ENV') === 'development') {
        echo "<p><strong>Technical details:</strong> $errorMessage</p>";
    }
    exit(1);
}

// Set up error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define a custom error handler to make errors more user-friendly
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Log the error
    $logPath = __DIR__ . '/../logs';
    if (!file_exists($logPath)) {
        mkdir($logPath, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $errorMessage = "[$timestamp] Error: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
    file_put_contents($logPath . '/error.log', $errorMessage, FILE_APPEND);
    
    // For fatal errors, show a user-friendly message
    if ($errno == E_ERROR || $errno == E_USER_ERROR || $errno == E_PARSE || $errno == E_COMPILE_ERROR) {
        header('HTTP/1.1 500 Internal Server Error');
        include __DIR__ . '/../src/views/error.php';
        exit(1);
    }
    
    // Return false to let PHP handle the error
    return false;
}

// Set the custom error handler
set_error_handler('customErrorHandler', E_ALL);

// Define exception handler
function customExceptionHandler($exception) {
    // Log the exception
    $logPath = __DIR__ . '/../logs';
    if (!file_exists($logPath)) {
        mkdir($logPath, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $errorMessage = "[$timestamp] Exception: " . $exception->getMessage() . 
                    " in " . $exception->getFile() . 
                    " on line " . $exception->getLine() . 
                    PHP_EOL . $exception->getTraceAsString() . PHP_EOL;
    file_put_contents($logPath . '/exception.log', $errorMessage, FILE_APPEND);
    
    // Show a user-friendly message
    header('HTTP/1.1 500 Internal Server Error');
    include __DIR__ . '/../src/views/error.php';
    exit(1);
}

// Set the custom exception handler
set_exception_handler('customExceptionHandler');

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
    try {
        // Check if PaymentController class exists
        if (!class_exists('\App\Controllers\PaymentController')) {
            // Try with lowercase path as fallback
            if (!class_exists('\App\controllers\PaymentController')) {
                throw new \Exception("PaymentController class not found. Check autoloading configuration.");
            } else {
                // Log that we found it with lowercase path
                error_log("Found PaymentController with lowercase path");
            }
        }
        
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
            $successUrl = isset($config['app']) && isset($config['app']['success_url']) ? 
                $config['app']['success_url'] : 'http://localhost:8080/payment/success';
            $errorUrl = isset($config['app']) && isset($config['app']['error_url']) ? 
                $config['app']['error_url'] : 'http://localhost:8080/payment/error';
            
            // For web payments only, redirect directly to Paynow payment page
            // Mobile payments and other types will show the bridge page
            if (isset($redirectUrl) && $redirectUrl && 
                (empty($instructions) || !isset($instructions)) && 
                (empty($paymentMethod) || !isset($paymentMethod)) && 
                !isset($test_mode_message)) {
                // Only redirect for web payments with no special messages
                header("Location: $redirectUrl");
                exit;
            }
            
            // Display the bridge page with appropriate context
            require_once __DIR__ . '/../src/views/bridge.php';
        } else {
            // Handle error case
            $errorMessage = isset($paymentResponse['error']) ? $paymentResponse['error'] : 'An unknown error occurred';
            include __DIR__ . '/../src/views/error.php';
        }
    } catch (\Exception $e) {
        // Log the detailed error
        $logPath = __DIR__ . '/../logs';
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $errorMessage = "[$timestamp] Error in handleBridgePage: " . $e->getMessage() . 
                        " in " . $e->getFile() . " on line " . $e->getLine() . 
                        PHP_EOL . $e->getTraceAsString() . PHP_EOL;
        file_put_contents($logPath . '/bridge_errors.log', $errorMessage, FILE_APPEND);
        
        // Display user-friendly error
        $errorMessage = "We encountered a technical problem processing your payment. Please try again later.";
        include __DIR__ . '/../src/views/error.php';
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
    require_once __DIR__ . '/../src/controllers/PaymentController.php';
    require_once __DIR__ . '/../src/config/config.php';
    
    try {
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
        
        // Get a fallback URL in case of issues
        $config = require_once __DIR__ . '/../src/config/config.php';
        $fallbackUrl = isset($config['app']) && isset($config['app']['error_url']) ? 
            $config['app']['error_url'] : 'http://localhost:8080/payment/error';
        
        // Make sure we have a valid URL to redirect to
        if (empty($redirectUrl)) {
            $redirectUrl = $fallbackUrl;
            $logMessage = date('Y-m-d H:i:s') . " - Empty redirect URL received, using fallback: $fallbackUrl" . PHP_EOL;
            file_put_contents($logPath . '/error.log', $logMessage, FILE_APPEND);
        }
        
        // Redirect the user
        header("Location: $redirectUrl");
        exit;
    } catch (Exception $e) {
        // Log the error
        $logPath = '/var/www/html/logs';
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        $logMessage = date('Y-m-d H:i:s') . " - Error in handlePaymentComplete: " . $e->getMessage() . PHP_EOL;
        file_put_contents($logPath . '/error.log', $logMessage, FILE_APPEND);
        
        // Get fallback error URL
        $config = require_once __DIR__ . '/../src/config/config.php';
        $errorUrl = isset($config['app']) && isset($config['app']['error_url']) ? 
            $config['app']['error_url'] : 'http://localhost:8080/payment/error';
        
        // Redirect to error page
        header("Location: $errorUrl");
        exit;
    }
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
    $homeUrl = isset($config['app']) && isset($config['app']['base_url']) ? 
        $config['app']['base_url'] : 'http://localhost:8080';
    
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
    $homeUrl = isset($config['app']) && isset($config['app']['base_url']) ? 
        $config['app']['base_url'] : 'http://localhost:8080';
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