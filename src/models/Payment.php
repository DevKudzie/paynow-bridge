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
            if ($paymentMethod !== null && $phone !== null) {
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
                return [
                    'success' => true,
                    'redirect_url' => $response->redirectLink(),
                    'poll_url' => $response->pollUrl(),
                    'instructions' => $response->instructions() ?? null,
                    'payment_method' => $paymentMethod,
                ];
            } else {
                // Get the response data for debugging
                $data = method_exists($response, 'data') ? print_r($response->data(), true) : 'No data available';
                $this->log("Payment creation failed. Response data: $data", 'error');
                
                return [
                    'success' => false,
                    'error' => 'Payment initialization failed. Please try again later.'
                ];
            }
        } catch (\Exception $e) {
            $this->log("Exception during payment creation: " . $e->getMessage(), 'error');
            return [
                'success' => false,
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
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
            $status = $this->paynow->pollTransaction($pollUrl);
            
            $this->log("Payment status: " . $status->status(), 'info');
            
            return [
                'paid' => $status->paid(),
                'status' => $status->status(),
                'amount' => $status->amount()
            ];
        } catch (\Exception $e) {
            $this->log("Error checking payment status: " . $e->getMessage(), 'error');
            return [
                'paid' => false,
                'status' => 'Error',
                'amount' => 0
            ];
        }
    }
    
    /**
     * Log a message to the payment log file
     * 
     * @param string $message The message to log
     * @param string $level The log level (info, warning, error)
     */
    private function log($message, $level = 'info')
    {
        // Check if logging is enabled in config
        if (!isset($this->config['logging']['enabled']) || !$this->config['logging']['enabled']) {
            return; // Skip logging if disabled
        }
        
        // Get log level from config, default to info
        $configLevel = $this->config['logging']['level'] ?? 'info';
        
        // Only log if the message level is equal to or higher priority than the config level
        $levelPriority = [
            'debug' => 0,
            'info' => 1,
            'warning' => 2,
            'error' => 3
        ];
        
        if ($levelPriority[$level] < $levelPriority[$configLevel]) {
            return; // Skip logging if message level is lower priority than config level
        }
        
        // Get log path from config, default to /var/www/html/logs
        $logPath = $this->config['logging']['path'] ?? '/var/www/html/logs';
        
        // Make sure log directory exists
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $logFile = $logPath . '/payment.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
} 