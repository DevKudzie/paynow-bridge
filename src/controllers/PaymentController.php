<?php

namespace App\Controllers;

use App\models\Payment;

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
        $reference = isset($requestData['reference']) && $requestData['reference'] ? $requestData['reference'] : 'INV' . time();
        
        // For test mode, use auth_email from config instead of the request email
        $email = 'customer@example.com'; // Default email
        
        if (isset($this->config['paynow']) && is_array($this->config['paynow'])) {
            if (!empty($this->config['paynow']['test_mode'])) {
                $email = !empty($this->config['paynow']['auth_email']) ? $this->config['paynow']['auth_email'] : $email;
            } elseif (isset($requestData['email']) && $requestData['email']) {
                $email = $requestData['email'];
            }
        } elseif (isset($requestData['email']) && $requestData['email']) {
            $email = $requestData['email'];
        }
        
        $items = isset($requestData['items']) && is_array($requestData['items']) ? $requestData['items'] : [];
        
        // Handle empty parameters properly
        $paymentMethod = (!empty($requestData['payment_method'])) ? $requestData['payment_method'] : null;
        $phone = (!empty($requestData['phone'])) ? $requestData['phone'] : null;
        
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
        $test_mode = isset($this->config['paynow']) && isset($this->config['paynow']['test_mode']) ? 
            $this->config['paynow']['test_mode'] : false;
            
        $this->logPaymentRequest('Extracted payment data', [
            'reference' => $reference,
            'email' => $email,
            'items' => $items,
            'paymentMethod' => $paymentMethod,
            'phone' => $phone,
            'test_mode' => $test_mode
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
        
        // Ensure redirect_url exists for web payments
        if (isset($paymentResponse['success']) && 
            $paymentResponse['success'] === true && 
            empty($paymentResponse['redirect_url']) && 
            empty($paymentResponse['instructions'])) {
            
            $this->logPaymentRequest('Success but no redirect URL or instructions', $paymentResponse);
            
            // If we have a poll URL but no redirect URL, create a local URL to check status
            if (!empty($paymentResponse['poll_url'])) {
                $encodedPollUrl = urlencode($paymentResponse['poll_url']);
                $base_url = isset($this->config['app']) && isset($this->config['app']['base_url']) ? 
                    $this->config['app']['base_url'] : 'http://localhost:8080';
                $paymentResponse['redirect_url'] = $base_url . "/check-status.php?poll_url=" . $encodedPollUrl;
                $this->logPaymentRequest('Created local status check URL', ['url' => $paymentResponse['redirect_url']]);
            } else {
                // No valid response data for redirection
                $paymentResponse['success'] = false;
                $paymentResponse['error'] = 'Payment initialized but no redirect URL was provided. Please try again.';
                $paymentResponse['debug_info'] = "Success response from Paynow but missing redirect URL and instructions\n" . 
                                               "Poll URL: " . (isset($paymentResponse['poll_url']) ? $paymentResponse['poll_url'] : 'Not provided');
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
            // Validate poll URL format
            if (!$pollUrl || !filter_var($pollUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception("Invalid poll URL format: " . ($pollUrl ?: 'empty'));
            }
            
            // Log that we're about to call the model
            $this->logController("Calling paymentModel->checkPaymentStatus with poll URL: $pollUrl");
            
            // Get payment status from the model
            $status = $this->paymentModel->checkPaymentStatus($pollUrl);
            
            // Check if the status is valid
            if (!is_array($status)) {
                $this->logController("Warning: Payment model returned non-array status", 'warning');
                throw new \Exception("Invalid status response format");
            }
            
            // Log the raw result for debugging
            $this->logController("Raw payment status check result: " . json_encode($status));
            
            // Add more detailed debugging info to the response
            $debugStatus = $status;
            $debugStatus['debug_info'] = [
                'request_time' => date('Y-m-d H:i:s'),
                'poll_url' => $pollUrl,
                'is_test_mode' => isset($this->config['paynow']) && isset($this->config['paynow']['test_mode']) ? 
                    $this->config['paynow']['test_mode'] : false
            ];
            
            // Log the result
            $this->logController("Payment status check result: " . json_encode($debugStatus));
            
            // Return the status information
            return $debugStatus;
        } catch (\Exception $e) {
            // Log the error
            $this->logController("Error checking payment status: " . $e->getMessage(), 'error');
            
            // Log stack trace for debugging
            $this->logController("Stack trace: " . $e->getTraceAsString(), 'error');
            
            // Return error response with more details
            return [
                'paid' => false,
                'status' => 'Error',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'poll_url' => $pollUrl,
                'checked_at' => date('Y-m-d H:i:s'),
                'debug_info' => [
                    'is_test_mode' => isset($this->config['paynow']) && isset($this->config['paynow']['test_mode']) ? 
                        $this->config['paynow']['test_mode'] : false,
                    'error_type' => get_class($e)
                ]
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
            // Check if success_url exists in config
            $url = isset($this->config['app']) && isset($this->config['app']['success_url']) ? 
                $this->config['app']['success_url'] : 'http://localhost:8080/payment/success';
            
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
        
        // Check if error_url exists in config
        return isset($this->config['app']) && isset($this->config['app']['error_url']) ? 
            $this->config['app']['error_url'] : 'http://localhost:8080/payment/error';
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
     * Log payment request details
     */
    private function logPaymentRequest($message, $data)
    {
        // Convert data to string if needed
        $dataString = is_array($data) || is_object($data) ? json_encode($data, JSON_PRETTY_PRINT) : $data;
        
        // Create log message
        $logMessage = date('Y-m-d H:i:s') . " - $message\n$dataString\n\n";
        
        // Log to file
        $logPath = isset($this->config['logging']['path']) ? $this->config['logging']['path'] : '/var/www/html/logs';
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }
        
        // Write to log file
        file_put_contents("$logPath/payment_requests.log", $logMessage, FILE_APPEND);
        
        // Also log to error_log for Docker logs
        error_log("PAYMENT_CONTROLLER: $message");
        
        // Print to terminal if enabled
        if (isset($this->config['logging']['print_to_terminal']) && $this->config['logging']['print_to_terminal']) {
            echo "<!-- LOG: $message -->\n";
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
        if (!isset($this->config['logging']) || !isset($this->config['logging']['enabled']) || !$this->config['logging']['enabled']) {
            return;
        }

        // Create logs directory if it doesn't exist
        $logPath = isset($this->config['logging']['log_path']) ? $this->config['logging']['log_path'] : '/var/www/html/logs';
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