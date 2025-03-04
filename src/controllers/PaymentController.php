<?php

namespace App\Controllers;

use App\Models\Payment;

class PaymentController
{
    private $paymentModel;
    private $config;

    public function __construct()
    {
        $this->paymentModel = new Payment();
        $this->config = require_once __DIR__ . '/../config/config.php';
    }

    /**
     * Handle the bridge page functionality
     * 
     * @param array $requestData Request parameters
     * @return array Response data
     */
    public function bridgePayment($requestData)
    {
        // Log the request data
        $this->logPaymentRequest('Received bridge payment request', $requestData);
        
        // Extract payment data from request
        $reference = $requestData['reference'] ?? 'INV' . time();
        
        // For test mode, use auth_email from config instead of the request email
        if ($this->config['paynow']['test_mode']) {
            $email = $this->config['paynow']['auth_email'];
        } else {
            $email = $requestData['email'] ?? 'customer@example.com';
        }
        
        $items = $requestData['items'] ?? [];
        
        // Handle empty parameters properly
        $paymentMethod = (!empty($requestData['payment_method'])) ? $requestData['payment_method'] : null;
        $phone = (!empty($requestData['phone'])) ? $requestData['phone'] : null;
        
        // Check if we should force using the bridge page for all payments
        $useBridgeForAll = isset($requestData['use_bridge']) ? $requestData['use_bridge'] === 'true' : true;
        
        // Validate payment method for mobile payments
        if ($phone && !$paymentMethod) {
            $this->logPaymentRequest('Mobile payment attempted but payment_method is empty', [
                'phone' => $phone,
                'payment_method' => $paymentMethod
            ]);
            
            return [
                'success' => false,
                'error' => 'Payment method is required for mobile payments (ecocash, onemoney, etc.)',
                'debug_info' => "Mobile payment attempted but payment_method is empty\nPhone: $phone"
            ];
        }
        
        // Log the extracted data
        $this->logPaymentRequest('Extracted payment data', [
            'reference' => $reference,
            'email' => $email,
            'items' => $items,
            'paymentMethod' => $paymentMethod,
            'phone' => $phone,
            'test_mode' => $this->config['paynow']['test_mode'],
            'useBridgeForAll' => $useBridgeForAll
        ]);
        
        // Create the payment
        $paymentResponse = $this->paymentModel->createPayment(
            $reference, 
            $email, 
            $items, 
            $paymentMethod, 
            $phone
        );
        
        // Log the response
        $this->logPaymentRequest('Payment creation response', $paymentResponse);
        
        // Always include poll_url in the response for web payments
        if ($paymentResponse['success']) {
            // If we're using bridge for all payments, don't redirect to Paynow immediately
            // Let the bridge page handle the redirection after a delay
            if ($useBridgeForAll && isset($paymentResponse['redirect_url']) && !empty($paymentResponse['redirect_url'])) {
                $this->logPaymentRequest('Using bridge page for web payment', [
                    'redirect_url' => $paymentResponse['redirect_url'],
                    'poll_url' => $paymentResponse['poll_url'] ?? 'Not provided'
                ]);
                
                // Save the redirect URL for later use but mark it as a delayed redirect
                $paymentResponse['paynow_redirect_url'] = $paymentResponse['redirect_url'];
                // Use empty redirect_url to force staying on bridge page
                $paymentResponse['redirect_url'] = null;
            }
        }
        
        // Ensure redirect_url exists for web payments
        if ($paymentResponse['success'] && empty($paymentResponse['redirect_url']) && empty($paymentResponse['instructions'])) {
            $this->logPaymentRequest('Success but no redirect URL or instructions', $paymentResponse);
            
            // If we have a poll URL but no redirect URL, create a local URL to check status
            if (!empty($paymentResponse['poll_url'])) {
                $encodedPollUrl = urlencode($paymentResponse['poll_url']);
                $paymentResponse['redirect_url'] = $this->config['app']['base_url'] . "/check-status.php?poll_url=" . $encodedPollUrl;
                $this->logPaymentRequest('Created local status check URL', ['url' => $paymentResponse['redirect_url']]);
            } else {
                // No valid response data for redirection
                $paymentResponse['success'] = false;
                $paymentResponse['error'] = 'Payment initialized but no redirect URL was provided. Please try again.';
                $paymentResponse['debug_info'] = "Success response from Paynow but missing redirect URL and instructions\n" . 
                                                "Poll URL: " . ($paymentResponse['poll_url'] ?? 'Not provided');
            }
        }
        
        // Store poll URL in session or database for later status checks
        // For this example, we'll return it in the response
        
        return $paymentResponse;
    }
    
    /**
     * Process payment callback from Paynow
     * 
     * @param array $callbackData Data from Paynow callback
     * @return bool Success status
     */
    public function processCallback($callbackData)
    {
        // Validate the callback data
        if (!isset($callbackData['status']) || !isset($callbackData['reference'])) {
            return false;
        }
        
        // Process based on status
        $status = strtolower($callbackData['status']);
        
        if ($status === 'paid' || $status === 'awaiting delivery') {
            // Payment successful - update your database, send confirmation emails, etc.
            return true;
        } else if ($status === 'cancelled') {
            // Payment was cancelled
            return false;
        }
        
        // Other statuses: created, sent, etc.
        return false;
    }
    
    /**
     * Check payment status using poll URL
     * 
     * @param string $pollUrl URL for polling payment status
     * @return array Status information
     */
    public function checkStatus($pollUrl)
    {
        // Log the request for debugging purposes
        $this->logController("Checking payment status with poll URL: $pollUrl");
        
        try {
            // Get payment status from the model
            $status = $this->paymentModel->checkPaymentStatus($pollUrl);
            
            // Log the result
            $this->logController("Payment status check result: " . json_encode($status));
            
            // Return the status information
            return $status;
        } catch (\Exception $e) {
            // Log the error
            $this->logController("Error checking payment status: " . $e->getMessage(), 'error');
            
            // Return error response
            return [
                'paid' => false,
                'status' => 'Error',
                'error_message' => $e->getMessage(),
                'checked_at' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Redirect to appropriate page based on payment status
     * 
     * @param bool $success Whether payment was successful
     * @param array $paymentData Optional payment data to include in the URL
     * @return string Redirect URL
     */
    public function getRedirectUrl($success, $paymentData = [])
    {
        if ($success) {
            $url = $this->config['app']['success_url'];
            
            // If payment data is provided, add it as query parameters
            if (!empty($paymentData)) {
                $queryParams = http_build_query([
                    'reference' => $paymentData['reference'] ?? '',
                    'amount' => $paymentData['amount'] ?? '',
                    'email' => $paymentData['email'] ?? '',
                    'paynow_reference' => $paymentData['paynow_reference'] ?? ''
                ]);
                
                $url .= (strpos($url, '?') !== false) ? '&' : '?';
                $url .= $queryParams;
            }
            
            return $url;
        }
        
        return $this->config['app']['error_url'];
    }
    
    /**
     * Process return from Paynow (user redirected back)
     * 
     * @param array $returnData Return data from Paynow
     * @return string The URL to redirect the user to
     */
    public function processReturn($returnData)
    {
        $this->logController("Processing return: " . json_encode($returnData));
        
        // Check if the transaction was successful
        if (isset($returnData['status']) && $returnData['status'] === 'Ok') {
            $this->logController("Return status is OK, redirecting to success page");
            
            // Build payment data for receipt generation
            $paymentData = [
                'reference' => $returnData['reference'] ?? '',
                'amount' => $returnData['amount'] ?? '',
                'email' => $returnData['email'] ?? '',
                'paynow_reference' => $returnData['paynowreference'] ?? ''
            ];
            
            return $this->getRedirectUrl(true, $paymentData);
        }
        
        $this->logController("Return status not OK, redirecting to error page");
        return $this->getRedirectUrl(false);
    }

    /**
     * Logs payment request data to a file
     * 
     * @param string $message Message about the request
     * @param array $data Data to log
     */
    private function logPaymentRequest($message, $data)
    {
        // Check if logging is enabled in config
        if (!isset($this->config['logging']['enabled']) || !$this->config['logging']['enabled']) {
            return; // Skip logging if disabled
        }
        
        // Get log path from config, default to /var/www/html/logs
        $logPath = $this->config['logging']['path'] ?? '/var/www/html/logs';
        
        // Make sure log directory exists
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $logFile = $logPath . '/payment_requests.log';
        $timestamp = date('Y-m-d H:i:s');
        
        // Format data as JSON for better readability
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        
        // Create log message
        $logMessage = "[$timestamp] $message\n$jsonData\n\n";
        
        // Append to log file
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Also print to terminal if in CLI mode AND explicitly enabled
        $isCli = php_sapi_name() === 'cli';
        $printToTerminal = getenv('PRINT_LOGS_TO_TERMINAL') === 'true';
        
        if ($isCli && $printToTerminal) {
            // Color the output
            $color = "\033[34m"; // Blue for controller logs
            $resetColor = "\033[0m";
            
            // Print to stdout
            echo $color . "[Controller] [$timestamp] $message" . $resetColor . PHP_EOL;
            
            // Only print data for debug level messages or if explicitly requested
            if (getenv('DEBUG_LOGS') === 'true') {
                echo $color . $jsonData . $resetColor . PHP_EOL;
            }
        }
    }

    /**
     * Log controller-level messages
     * 
     * @param string $message Message to log
     * @param string $level Log level (debug, info, warning, error)
     * @return void
     */
    private function logController($message, $level = 'info')
    {
        // Check if logging is enabled
        if (!$this->config['logging']['enabled']) {
            return;
        }

        // Create logs directory if it doesn't exist
        $logPath = $this->config['logging']['log_path'];
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        // Format log message
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] [$level] [PaymentController] $message" . PHP_EOL;
        
        // Write to controller log file
        file_put_contents($logPath . '/controller.log', $formattedMessage, FILE_APPEND);
        
        // If running in CLI or debug logs enabled, print to output
        if (php_sapi_name() === 'cli' || (isset($_ENV['PRINT_LOGS_TO_TERMINAL']) && $_ENV['PRINT_LOGS_TO_TERMINAL'] === 'true')) {
            // For controller logs, use blue text
            $color = "\033[34m"; // Blue
            $reset = "\033[0m";  // Reset
            
            if ($level === 'error') {
                $color = "\033[31m"; // Red for errors
            } elseif ($level === 'warning') {
                $color = "\033[33m"; // Yellow for warnings
            }
            
            echo $color . $formattedMessage . $reset;
        }
    }
} 