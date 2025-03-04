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
        // Extract payment data from request
        $reference = $requestData['reference'] ?? 'INV' . time();
        
        // For test mode, use auth_email from config instead of the request email
        if ($this->config['paynow']['test_mode']) {
            $email = $this->config['paynow']['auth_email'];
        } else {
            $email = $requestData['email'] ?? 'customer@example.com';
        }
        
        $items = $requestData['items'] ?? [];
        $paymentMethod = $requestData['payment_method'] ?? null;
        $phone = $requestData['phone'] ?? null;
        
        // Create the payment
        $paymentResponse = $this->paymentModel->createPayment(
            $reference, 
            $email, 
            $items, 
            $paymentMethod, 
            $phone
        );
        
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
        return $this->paymentModel->checkPaymentStatus($pollUrl);
    }
    
    /**
     * Redirect to appropriate page based on payment status
     * 
     * @param bool $success Whether payment was successful
     * @return string Redirect URL
     */
    public function getRedirectUrl($success)
    {
        if ($success) {
            return $this->config['app']['success_url'];
        }
        
        return $this->config['app']['error_url'];
    }
    
    /**
     * Process return from Paynow (user redirected back)
     * 
     * @param array $returnData Data from Paynow return
     * @return bool Success status
     */
    public function processReturn($returnData)
    {
        // Process the return data
        // In a real application, you would update your UI here
        
        return true;
    }
} 