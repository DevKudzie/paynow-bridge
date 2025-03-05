<?php

namespace App\models;

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
            // Ensure config has proper structure
            if (!is_array($this->config)) {
                $this->log('Config is not an array, initializing it', 'warning');
                $this->config = [];
            }
            
            if (!isset($this->config['paynow']) || !is_array($this->config['paynow'])) {
                $this->log('Paynow config section missing or not an array, initializing it', 'warning');
                $this->config['paynow'] = [];
            }
            
            // Check if integration keys are set
            if (empty($this->config['paynow']['integration_id']) || empty($this->config['paynow']['integration_key'])) {
                $this->log('Paynow credentials missing in config, attempting to load directly from .env file', 'warning');
                
                // Attempt to load directly from .env file as a fallback
                $dotEnvPath = __DIR__ . '/../../.env';
                if (file_exists($dotEnvPath)) {
                    $this->log("Found .env file at: $dotEnvPath", 'info');
                    $envVars = $this->parseEnvFile($dotEnvPath);
                    
                    // Set the credentials directly if found in .env
                    if (!empty($envVars['PAYNOW_INTEGRATION_ID']) && !empty($envVars['PAYNOW_INTEGRATION_KEY'])) {
                        $this->log("Loaded credentials directly from .env file", 'info');
                        $this->config['paynow']['integration_id'] = $envVars['PAYNOW_INTEGRATION_ID'];
                        $this->config['paynow']['integration_key'] = $envVars['PAYNOW_INTEGRATION_KEY'];
                        $this->config['paynow']['result_url'] = $envVars['PAYNOW_RESULT_URL'] ?? 'http://localhost:8080/payment/update';
                        $this->config['paynow']['return_url'] = $envVars['PAYNOW_RETURN_URL'] ?? 'http://localhost:8080/payment/complete';
                        $this->config['paynow']['auth_email'] = $envVars['PAYNOW_AUTH_EMAIL'] ?? 'test@example.com';
                        $this->config['paynow']['test_mode'] = ($envVars['PAYNOW_TEST_MODE'] ?? 'true') === 'true';
                    } else {
                        $this->log("Credentials not found in .env file", 'error');
                    }
                } else {
                    $this->log(".env file not found at: $dotEnvPath", 'error');
                }
            }
            
            // Final check if credentials are available
            if (empty($this->config['paynow']['integration_id']) || empty($this->config['paynow']['integration_key'])) {
                // Hardcoded credentials as absolute last resort (for testing only)
                $this->log("Using hardcoded credentials as last resort", 'warning');
                $this->config['paynow']['integration_id'] = '20072';
                $this->config['paynow']['integration_key'] = 'e38f4124-5ae7-4c86-ad27-cf989818d195';
                $this->config['paynow']['result_url'] = 'http://localhost:8080/payment/update';
                $this->config['paynow']['return_url'] = 'http://localhost:8080/payment/complete';
                $this->config['paynow']['auth_email'] = 'nyanza.v@gmail.com';
                $this->config['paynow']['test_mode'] = true;
            }
            
            $this->log('Initializing Paynow with integration ID: ' . $this->config['paynow']['integration_id'], 'debug');
            
            $this->paynow = new Paynow(
                $this->config['paynow']['integration_id'],
                $this->config['paynow']['integration_key'],
                $this->config['paynow']['result_url'],
                $this->config['paynow']['return_url']
            );
            
            // Enable test mode if configured
            if (!empty($this->config['paynow']['test_mode'])) {
                $this->log('Enabling test mode', 'info');
                $this->paynow->setResultUrl($this->config['paynow']['result_url']);
                $this->paynow->setReturnUrl($this->config['paynow']['return_url']);
            }
            
            // Verify the Paynow object was successfully created
            if (!$this->paynow) {
                $this->log('Paynow SDK initialization failed - object not created', 'error');
                throw new \Exception('Failed to create Paynow SDK instance');
            }
            
            $this->log('Paynow SDK successfully initialized', 'info');
        } catch (\Exception $e) {
            // Log the error to help with debugging
            $this->log('Exception during Paynow initialization: ' . $e->getMessage(), 'error');
            $this->log('Stack trace: ' . $e->getTraceAsString(), 'error');
            
            // Re-throw to allow handling by caller
            throw $e;
        }
    }
    
    /**
     * Parse an .env file into an array of key-value pairs
     * 
     * @param string $filePath Path to the .env file
     * @return array Array of environment variables
     */
    private function parseEnvFile($filePath)
    {
        $vars = [];
        
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return $vars;
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=value format
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }
                
                $vars[$name] = $value;
            }
        }
        
        return $vars;
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
            // Ensure config has proper structure
            if (!is_array($this->config)) {
                $this->log('Config is not an array during status check, initializing it', 'warning');
                $this->config = [];
            }
            
            if (!isset($this->config['paynow']) || !is_array($this->config['paynow'])) {
                $this->log('Paynow config section missing during status check, initializing it', 'warning');
                $this->config['paynow'] = [];
            }
            
            // Check if Paynow SDK is properly initialized
            if (!$this->paynow) {
                $this->log("Paynow SDK is not initialized. Reinitializing...", 'warning');
                // Attempt to reinitialize the SDK
                try {
                    // Ensure we have valid credentials before reinitializing
                    if (empty($this->config['paynow']['integration_id']) || empty($this->config['paynow']['integration_key'])) {
                        $this->log('Credentials missing for reinitialization, attempting to load from .env', 'warning');
                        
                        // Try loading directly from .env file
                        $dotEnvPath = __DIR__ . '/../../.env';
                        if (file_exists($dotEnvPath)) {
                            $this->log("Found .env file at: $dotEnvPath", 'info');
                            $envVars = $this->parseEnvFile($dotEnvPath);
                            
                            // Set credentials if found
                            if (!empty($envVars['PAYNOW_INTEGRATION_ID']) && !empty($envVars['PAYNOW_INTEGRATION_KEY'])) {
                                $this->log("Loaded credentials from .env for reinitialization", 'info');
                                $this->config['paynow']['integration_id'] = $envVars['PAYNOW_INTEGRATION_ID'];
                                $this->config['paynow']['integration_key'] = $envVars['PAYNOW_INTEGRATION_KEY'];
                                $this->config['paynow']['result_url'] = $envVars['PAYNOW_RESULT_URL'] ?? 'http://localhost:8080/payment/update';
                                $this->config['paynow']['return_url'] = $envVars['PAYNOW_RETURN_URL'] ?? 'http://localhost:8080/payment/complete';
                                $this->config['paynow']['auth_email'] = $envVars['PAYNOW_AUTH_EMAIL'] ?? 'test@example.com';
                                $this->config['paynow']['test_mode'] = ($envVars['PAYNOW_TEST_MODE'] ?? 'true') === 'true';
                            } else {
                                $this->log("Credentials not found in .env file", 'error');
                            }
                        }
                        
                        // Last resort - use hardcoded credentials if still missing
                        if (empty($this->config['paynow']['integration_id']) || empty($this->config['paynow']['integration_key'])) {
                            $this->log("Using hardcoded credentials for reinitialization", 'warning');
                            $this->config['paynow']['integration_id'] = '20072';
                            $this->config['paynow']['integration_key'] = 'e38f4124-5ae7-4c86-ad27-cf989818d195';
                            $this->config['paynow']['result_url'] = 'http://localhost:8080/payment/update';
                            $this->config['paynow']['return_url'] = 'http://localhost:8080/payment/complete';
                            $this->config['paynow']['auth_email'] = 'nyanza.v@gmail.com';
                            $this->config['paynow']['test_mode'] = true;
                        }
                    }
                    
                    // Log configuration values for debugging (with sensitive info masked)
                    $configDebug = [
                        'integration_id' => substr($this->config['paynow']['integration_id'] ?? 'missing', 0, 4) . '...',
                        'integration_key' => substr($this->config['paynow']['integration_key'] ?? 'missing', 0, 4) . '...',
                        'result_url' => $this->config['paynow']['result_url'] ?? 'missing',
                        'return_url' => $this->config['paynow']['return_url'] ?? 'missing',
                        'test_mode' => isset($this->config['paynow']['test_mode']) ? 'true' : 'false'
                    ];
                    $this->log("Reinitializing with config: " . json_encode($configDebug), 'debug');
                    
                    // Create new Paynow instance
                    $this->paynow = new Paynow(
                        $this->config['paynow']['integration_id'],
                        $this->config['paynow']['integration_key'],
                        $this->config['paynow']['result_url'],
                        $this->config['paynow']['return_url']
                    );
                    
                    // Enable test mode if configured
                    if (!empty($this->config['paynow']['test_mode'])) {
                        $this->log('Enabling test mode for reinitialized SDK', 'info');
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
            
            // Log raw poll URL for debugging
            $this->log("Raw poll URL before sending: $pollUrl", 'debug');
            
            // Poll transaction status
            $status = $this->paynow->pollTransaction($pollUrl);
            
            // Check if status is a valid object
            if (!$status) {
                $this->log("Received null status from Paynow", 'error');
                throw new \Exception("Invalid status response from Paynow");
            }
            
            // Dump the entire status object for debugging
            if ($this->config['logging']['debug_logs']) {
                ob_start();
                var_dump($status);
                $statusDump = ob_get_clean();
                $this->log("Full status object: $statusDump", 'debug');
            }
            
            // Get the raw status and log it
            $statusString = $status->status() ?? 'unknown';
            $paid = $status->paid() ?? false;
            $amount = $status->amount() ?? 0;
            
            $this->log("Payment status check result - Status: $statusString, Paid: " . ($paid ? 'true' : 'false') . ", Amount: $amount", 'info');
            
            // For debugging - add more detailed status check
            if ($paid) {
                $this->log("SUCCESS: Payment marked as PAID with status '$statusString'", 'info');
            } else {
                $this->log("NOTICE: Payment not marked as paid. Status: '$statusString'", 'info');
                
                // Special handling for edge cases where status indicates success but paid flag isn't set
                if (strtolower($statusString) === 'paid' || strtolower($statusString) === 'awaiting delivery') {
                    $this->log("OVERRIDE: Status indicates success ('$statusString') but paid flag is false. Forcing paid=true", 'info');
                    $paid = true;
                }
            }
            
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
        // Ensure config has proper structure for logging
        if (!is_array($this->config)) {
            // Can't use log function here to avoid recursion
            error_log('Config is not an array during logging, initializing it');
            $this->config = [];
        }
        
        if (!isset($this->config['logging']) || !is_array($this->config['logging'])) {
            error_log('Logging config section missing, initializing it');
            $this->config['logging'] = [
                'enabled' => true,
                'path' => '/var/www/html/logs',
                'level' => 'debug',
                'print_to_terminal' => true,
                'debug_logs' => true
            ];
        }
        
        // Skip logging if it's disabled
        if (empty($this->config['logging']['enabled'])) {
            return;
        }
        
        // Only log if the level is appropriate
        $logLevels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];
        $configLevel = $this->config['logging']['level'] ?? 'info';
        
        if (!isset($logLevels[$level]) || !isset($logLevels[$configLevel])) {
            return; // Invalid log level
        }
        
        if ($logLevels[$level] < $logLevels[$configLevel]) {
            return; // Skip if level is lower than configured
        }
        
        // Format the message
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        // Determine log path
        $logPath = $this->config['logging']['path'] ?? '/var/www/html/logs';
        
        // Create the directory if it doesn't exist
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }
        
        // Write to the appropriate log file
        $logFile = "$logPath/paynow.log";
        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
        
        // Print to terminal if configured
        if (!empty($this->config['logging']['print_to_terminal'])) {
            error_log($formattedMessage);
        }
    }
} 