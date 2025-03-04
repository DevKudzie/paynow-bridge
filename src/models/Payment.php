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
        
        // Initialize Paynow
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
        // In test mode, use the configured auth email instead of the customer email
        if ($this->config['paynow']['test_mode']) {
            $email = $this->config['paynow']['auth_email'];
        }
        
        // Create a new payment
        $payment = $this->paynow->createPayment($reference, $email);
        
        // Add items to the payment
        foreach ($items as $item) {
            $payment->add($item['name'], $item['amount']);
        }
        
        // Determine if it's a mobile payment or regular payment
        if ($paymentMethod !== null && $phone !== null) {
            // This is a mobile payment
            $response = $this->paynow->sendMobile($payment, $phone, $paymentMethod);
        } else {
            // This is a regular web payment
            $response = $this->paynow->send($payment);
        }
        
        if ($response->success()) {
            return [
                'success' => true,
                'redirect_url' => $response->redirectLink(),
                'poll_url' => $response->pollUrl(),
                'instructions' => $response->instructions() ?? null,
                'payment_method' => $paymentMethod,
            ];
        }
        
        return [
            'success' => false,
            'error' => $response->error()
        ];
    }
    
    /**
     * Check the status of a payment
     * 
     * @param string $pollUrl The poll URL to check
     * @return array Status information
     */
    public function checkPaymentStatus($pollUrl)
    {
        $status = $this->paynow->pollTransaction($pollUrl);
        
        return [
            'paid' => $status->paid(),
            'status' => $status->status(),
            'amount' => $status->amount()
        ];
    }
} 