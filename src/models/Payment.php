<?php

namespace App\Models;

use Paynow\Payments\Paynow;

class Payment
{
    private $paynow;
    private $config;
    
    /**
     * Initialize the Payment model with configuration
     */
    public function __construct()
    {
        $this->config = require_once __DIR__ . '/../config/config.php';
        
        // Log initialization
        $this->log('Payment model initialized', 'info');
        
        // Initialize Paynow
        try {
            // Check if integration keys are set
            if (empty($this->config['paynow']['integration_id']) || empty($this->config['paynow']['integration_key'])) {
                $this->log('Paynow integration ID or key is missing in configuration', 'error');
                throw new \Exception('Paynow integration credentials are not configured');
            }
            
            $this->log('Initializing Paynow with integration ID: ' . $this->config['paynow']['integration_id'], 'debug');
            
            $this->paynow = new Paynow(
                $this->config['paynow']['integration_id'],
                $this->config['paynow']['integration_key'],
                $this->config['paynow']['result_url'],
                $this->config['paynow']['return_url']
            );
            
            // Enable test mode if configured
            if ($this->config['paynow']['test_mode']) {
                $this->paynow->setResultUrl($this->config['paynow']['result_url']);
                $this->paynow->setReturnUrl($this->config['paynow']['return_url']);
                $this->log('Test mode enabled', 'info');
            }
        } catch (\Exception $e) {
            $this->log('Paynow initialization error: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Create a payment in Paynow
     * 
     * @param string $reference The payment reference/invoice number
     * @param string $email The customer's email address
     * @param array $items Array of items (each with 'name' and 'amount')
     * @param string $paymentMethod Optional payment method (ecocash, onemoney, etc.)
     * @param string $phone Optional phone number for mobile payments
     * @return array Payment response data
     */
    public function createPayment($reference, $email, $items, $paymentMethod = null, $phone = null)
    {
        $this->log("Creating payment: Reference=$reference, Email=$email, PaymentMethod=$paymentMethod, Phone=$phone", 'info');
        
        // Check if Paynow was initialized successfully
        if (!isset($this->paynow) || !$this->paynow) {
            $this->log("Cannot create payment - Paynow SDK not initialized", 'error');
            
            // Get configuration info for debugging
            $debugInfo = "Paynow SDK not initialized\n";
            $debugInfo .= "Integration ID: " . ($this->config['paynow']['integration_id'] ?? 'Not set') . "\n";
            $debugInfo .= "Test Mode: " . ($this->config['paynow']['test_mode'] ? 'Enabled' : 'Disabled') . "\n";
            $debugInfo .= "Auth Email: " . ($this->config['paynow']['auth_email'] ?? 'Not set') . "\n";
            
            return [
                'success' => false,
                'error' => 'Payment system initialization failed. Please check your configuration.',
                'debug_info' => $debugInfo
            ];
        }
        
        // In test mode, use the configured auth email instead of the customer email
        if ($this->config['paynow']['test_mode']) {
            $email = $this->config['paynow']['auth_email'];
            $this->log("Using test mode email: $email", 'info');
        }
        
        try {
            // Create a new payment
            $payment = $this->paynow->createPayment($reference, $email);
            
            // Add items to the payment
            foreach ($items as $item) {
                $payment->add($item['name'], $item['amount']);
                $this->log("Added item: {$item['name']} - {$item['amount']}", 'info');
            }
            
            // Determine if it's a mobile payment or regular payment
            if (!empty($paymentMethod) && !empty($phone)) {
                // This is a mobile payment
                $this->log("Sending mobile payment: $paymentMethod, $phone", 'info');
                $response = $this->paynow->sendMobile($payment, $phone, $paymentMethod);
            } else {
                // This is a regular web payment
                $this->log("Sending web payment", 'info');
                $response = $this->paynow->send($payment);
            }
            
            // Check if the request was successful
            if ($response->success()) {
                $this->log("Payment created successfully. Poll URL: " . $response->pollUrl(), 'info');
                
                // Debug the response object structure to understand what's available
                if (method_exists($response, 'data')) {
                    $responseData = $response->data();
                    $this->log("Response data: " . print_r($responseData, true), 'debug');
                }
                
                // Get the redirect URL from the response data
                $redirectUrl = null;
                if (method_exists($response, 'data') && is_array($response->data())) {
                    if (isset($response->data()['browserurl'])) {
                        $redirectUrl = $response->data()['browserurl'];
                        $this->log("Got redirect URL from data['browserurl']: $redirectUrl", 'info');
                    } elseif (isset($response->data()['authurl'])) {
                        $redirectUrl = $response->data()['authurl'];
                        $this->log("Got redirect URL from data['authurl']: $redirectUrl", 'info');
                    } elseif (isset($response->data()['url'])) {
                        $redirectUrl = $response->data()['url'];
                        $this->log("Got redirect URL from data['url']: $redirectUrl", 'info');
                    }
                }
                
                // Get the instructions if available
                $instructions = null;
                if (method_exists($response, 'instructions')) {
                    $instructions = $response->instructions();
                    if ($instructions) {
                        $this->log("Got payment instructions: $instructions", 'info');
                    }
                }
                
                // Return the payment information
                $returnData = [
                    'success' => true,
                    'poll_url' => $response->pollUrl(),
                    'payment_method' => $paymentMethod,
                    'raw_response' => method_exists($response, 'data') ? $response->data() : null
                ];
                
                // Add redirect URL or instructions if available
                if ($redirectUrl) {
                    $returnData['redirect_url'] = $redirectUrl;
                }
                
                if ($instructions) {
                    $returnData['instructions'] = $instructions;
                }
                
                // Special test mode handling for mobile payments
                if ($this->config['paynow']['test_mode'] && !empty($paymentMethod) && !empty($phone)) {
                    // Get the expected behavior based on the test phone number
                    switch ($phone) {
                        case '0771111111':
                            $this->log("Test mode: Mobile payment will simulate SUCCESS", 'info');
                            break;
                        case '0772222222':
                            $this->log("Test mode: Mobile payment will simulate DELAYED SUCCESS", 'info');
                            break;
                        case '0773333333':
                            $this->log("Test mode: Mobile payment will simulate USER CANCELLED", 'info');
                            break;
                        case '0774444444':
                            $this->log("Test mode: Mobile payment will simulate INSUFFICIENT FUNDS", 'info');
                            break;
                        default:
                            $this->log("Test mode: Using custom phone number, behavior will be SUCCESS", 'info');
                            break;
                    }
                    
                    // In test mode, add a note about the expected behavior
                    $testMessage = "This is a test payment. ";
                    switch ($phone) {
                        case '0772222222':
                            $testMessage .= "This payment will simulate a delayed success (it will take longer to complete).";
                            break;
                        case '0773333333':
                            $testMessage .= "This payment will simulate the user cancelling the payment.";
                            break;
                        case '0774444444':
                            $testMessage .= "This payment will simulate insufficient funds.";
                            break;
                        default:
                            $testMessage .= "This payment will simulate a successful payment.";
                            break;
                    }
                    
                    $returnData['test_mode_message'] = $testMessage;
                }
                
                return $returnData;
            } else {
                // Payment initialization failed
                // Get error details from the response
                $errorMessage = 'Payment initialization failed. Please try again later.';
                
                // Log the full response for debugging
                $responseData = method_exists($response, 'data') ? print_r($response->data(), true) : 'No response data';
                $this->log("Payment creation failed. Response data: " . $responseData, 'error');
                
                // Check if we have error information in the response data
                if (method_exists($response, 'data') && is_array($response->data())) {
                    if (isset($response->data()['error'])) {
                        $errorMessage = 'Payment initialization failed: ' . $response->data()['error'];
                        $this->log("Error from response data: " . $response->data()['error'], 'error');
                    }
                }
                
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'debug_info' => 'Response data: ' . $responseData
                ];
            }
        } catch (\Exception $e) {
            // Exception during payment creation
            $this->log("Exception during payment creation: " . $e->getMessage(), 'error');
            
            return [
                'success' => false,
                'error' => 'Payment initialization failed. Please try again later.',
                'debug_info' => 'Exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString()
            ];
        }
    }
    
    /**
     * Check the status of a payment
     * 
     * @param string $pollUrl The poll URL to check
     * @return array Status information
     */
    public function checkPaymentStatus($pollUrl)
    {
        $this->log("Checking payment status: $pollUrl", 'info');
        
        try {
            // Check if Paynow SDK is properly initialized
            if (!$this->paynow) {
                $this->log("Paynow SDK is not initialized. Reinitializing...", 'warning');
                // Attempt to reinitialize the SDK
                try {
                    $this->paynow = new \Paynow\Payments\Paynow(
                        $this->config['paynow']['integration_id'],
                        $this->config['paynow']['integration_key'],
                        $this->config['paynow']['result_url'],
                        $this->config['paynow']['return_url']
                    );
                    
                    // Enable test mode if configured
                    if ($this->config['paynow']['test_mode']) {
                        $this->paynow->setResultUrl($this->config['paynow']['result_url']);
                        $this->paynow->setReturnUrl($this->config['paynow']['return_url']);
                    }
                } catch (\Exception $e) {
                    $this->log("Failed to reinitialize Paynow SDK: " . $e->getMessage(), 'error');
                    throw new \Exception("Paynow SDK initialization failed: " . $e->getMessage());
                }
            }
            
            // If still null after reinitialization attempt, throw an exception
            if (!$this->paynow) {
                throw new \Exception("Paynow SDK is not available");
            }
            
            $status = $this->paynow->pollTransaction($pollUrl);
            
            // Get the raw status and log it
            $statusString = $status->status();
            $paid = $status->paid();
            $amount = $status->amount();
            
            $this->log("Payment status check result - Status: $statusString, Paid: " . ($paid ? 'true' : 'false') . ", Amount: $amount", 'info');
            
            // Build the response with more detailed information
            $response = [
                'paid' => $paid,
                'status' => $statusString,
                'amount' => $amount,
                'reference' => $status->reference() ?? '',
                'paynow_reference' => $status->paynowReference() ?? '',
                'poll_url' => $pollUrl,
                'checked_at' => date('Y-m-d H:i:s')
            ];
            
            // Log successful payments for reconciliation
            if ($paid) {
                $this->log("PAYMENT SUCCESSFUL - Amount: $amount, Reference: " . ($status->reference() ?? 'N/A'), 'info');
            }
            
            return $response;
        } catch (\Exception $e) {
            $this->log("Error checking payment status: " . $e->getMessage(), 'error');
            return [
                'paid' => false,
                'status' => 'Error',
                'amount' => 0,
                'error_message' => $e->getMessage(),
                'poll_url' => $pollUrl,
                'checked_at' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Log a message, checking for logging configuration first
     */
    private function log($message, $level = 'info')
    {
        // Skip logging if it's disabled
        if (empty($this->config['logging']['enabled'])) {
            return;
        }
        
        // Get log level and path from config
        $configLevel = $this->config['logging']['level'] ?? 'info';
        $logPath = $this->config['logging']['path'] ?? '/var/www/html/logs';
        
        // Define log level priorities (higher number = more severe)
        $levelPriorities = [
            'debug' => 1,
            'info' => 2,
            'warning' => 3,
            'error' => 4
        ];
        
        // Only log if the message level is >= the configured level
        $messagePriority = $levelPriorities[$level] ?? 2; // Default to info priority
        $configPriority = $levelPriorities[$configLevel] ?? 2;
        
        if ($messagePriority < $configPriority) {
            return;
        }
        
        // Create logs directory if it doesn't exist
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        // Format the log message
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        // Determine if we should print to terminal
        $shouldPrintToTerminal = ($this->config['logging']['print_to_terminal'] ?? false) &&
            (php_sapi_name() === 'cli' || getenv('PRINT_LOGS_TO_TERMINAL') === 'true');
        
        // Don't use colorized output when running through the browser to avoid breaking headers
        $isBrowser = php_sapi_name() !== 'cli' && !headers_sent();
        
        if ($shouldPrintToTerminal && !$isBrowser) {
            // Define ANSI color codes for terminal output
            $colors = [
                'debug' => "\033[36m",   // Cyan
                'info' => "\033[32m",    // Green
                'warning' => "\033[33m", // Yellow
                'error' => "\033[31m",   // Red
                'reset' => "\033[0m"     // Reset
            ];
            
            // Print to stdout with color coding based on level
            echo $colors[$level] . "[Payment] " . $formattedMessage . $colors['reset'];
        }
        
        // Write to log file
        file_put_contents($logPath . '/payment.log', "[Payment] " . $formattedMessage, FILE_APPEND);
    }
} 