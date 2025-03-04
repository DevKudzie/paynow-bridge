<?php
/**
 * Receipt Generator - Creates a PDF receipt for completed payments
 */

// Load the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Get the payment reference from the query string
$reference = $_GET['reference'] ?? null;

if (!$reference) {
    die('No payment reference provided');
}

// Create a log directory if it doesn't exist
$logPath = '/var/www/html/logs';
if (!file_exists($logPath)) {
    mkdir($logPath, 0755, true);
}

try {
    // Load the payment details (in a real app, you would fetch this from a database)
    // For demonstration, we'll use session data if available, or mock data
    
    session_start();
    
    // Check if we have payment data in the session
    if (isset($_SESSION['payment_details']) && $_SESSION['payment_details']['reference'] === $reference) {
        $paymentDetails = $_SESSION['payment_details'];
    } else {
        // For demonstration purposes, create some mock data
        // In a real application, you'd query your database here
        $paymentDetails = [
            'reference' => $reference,
            'amount' => '10.00',
            'date' => date('Y-m-d H:i:s'),
            'status' => 'Paid',
            'email' => 'customer@example.com',
            'items' => [
                ['name' => 'Product/Service', 'quantity' => 1, 'price' => '10.00']
            ],
            'merchant' => 'Your Company Name',
            'merchant_address' => '123 Business Street, City, Country',
            'paynow_reference' => 'PN' . rand(100000, 999999)
        ];
    }
    
    // Log that we're generating a receipt
    $logMessage = date('Y-m-d H:i:s') . " - Generating receipt for reference: $reference" . PHP_EOL;
    file_put_contents($logPath . '/receipts.log', $logMessage, FILE_APPEND);
    
    // Set the PDF content type header
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="receipt-' . $reference . '.pdf"');
    
    // Create an instance of TCPDF
    require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
    
    // Check if TCPDF is available, otherwise use a basic HTML receipt
    if (class_exists('TCPDF')) {
        // Create a PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Paynow Bridge System');
        $pdf->SetAuthor('Paynow Bridge');
        $pdf->SetTitle('Payment Receipt');
        $pdf->SetSubject('Payment Receipt');
        
        // Set default header and footer data
        $pdf->SetHeaderData('', 0, 'Payment Receipt', 'Generated on ' . date('Y-m-d H:i:s'));
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Generate the receipt content
        $html = '<h1 style="text-align: center; color: #333;">Payment Receipt</h1>';
        $html .= '<hr />';
        $html .= '<table border="0" cellpadding="5">';
        $html .= '<tr><td><strong>Reference:</strong></td><td>' . htmlspecialchars($paymentDetails['reference']) . '</td></tr>';
        $html .= '<tr><td><strong>Paynow Reference:</strong></td><td>' . htmlspecialchars($paymentDetails['paynow_reference'] ?? 'N/A') . '</td></tr>';
        $html .= '<tr><td><strong>Date:</strong></td><td>' . htmlspecialchars($paymentDetails['date']) . '</td></tr>';
        $html .= '<tr><td><strong>Status:</strong></td><td>' . htmlspecialchars($paymentDetails['status']) . '</td></tr>';
        $html .= '<tr><td><strong>Customer Email:</strong></td><td>' . htmlspecialchars($paymentDetails['email']) . '</td></tr>';
        $html .= '</table>';
        
        $html .= '<h2 style="margin-top: 20px;">Order Details</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr style="background-color: #f2f2f2;"><th>Item</th><th>Quantity</th><th>Price</th><th>Total</th></tr>';
        
        $totalAmount = 0;
        if (isset($paymentDetails['items']) && is_array($paymentDetails['items'])) {
            foreach ($paymentDetails['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['price'];
                $totalAmount += $itemTotal;
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($item['name']) . '</td>';
                $html .= '<td style="text-align: center;">' . htmlspecialchars($item['quantity']) . '</td>';
                $html .= '<td style="text-align: right;">$' . htmlspecialchars($item['price']) . '</td>';
                $html .= '<td style="text-align: right;">$' . number_format($itemTotal, 2) . '</td>';
                $html .= '</tr>';
            }
        } else {
            // If no items, just show the total amount
            $totalAmount = $paymentDetails['amount'];
            $html .= '<tr>';
            $html .= '<td colspan="3">Total payment</td>';
            $html .= '<td style="text-align: right;">$' . htmlspecialchars($totalAmount) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '<tr style="background-color: #f2f2f2;">';
        $html .= '<td colspan="3" style="text-align: right;"><strong>Total:</strong></td>';
        $html .= '<td style="text-align: right;"><strong>$' . htmlspecialchars($paymentDetails['amount']) . '</strong></td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        $html .= '<div style="text-align: center; margin-top: 30px; font-size: 9px;">';
        $html .= '<p>This is an electronically generated receipt and does not require a signature.</p>';
        $html .= '<p>Thank you for your business!</p>';
        $html .= '<p>' . htmlspecialchars($paymentDetails['merchant'] ?? 'Merchant Name') . '</p>';
        $html .= '<p>' . htmlspecialchars($paymentDetails['merchant_address'] ?? 'Merchant Address') . '</p>';
        $html .= '</div>';
        
        // Write the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output the PDF
        $pdf->Output('receipt-' . $reference . '.pdf', 'I');
    } else {
        // Fallback to simple HTML if TCPDF is not available
        header('Content-Type: text/html');
        header('Content-Disposition: inline');
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .container { max-width: 800px; margin: 0 auto; }
                .header { text-align: center; margin-bottom: 20px; }
                .details { margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                table, th, td { border: 1px solid #ddd; }
                th, td { padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                .print-button { text-align: center; margin: 20px 0; }
                .print-button button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Payment Receipt</h1>
                    <p>Receipt for payment reference: ' . htmlspecialchars($reference) . '</p>
                </div>
                
                <div class="details">
                    <table>
                        <tr>
                            <td><strong>Reference:</strong></td>
                            <td>' . htmlspecialchars($paymentDetails['reference']) . '</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>' . htmlspecialchars($paymentDetails['date']) . '</td>
                        </tr>
                        <tr>
                            <td><strong>Amount:</strong></td>
                            <td>$' . htmlspecialchars($paymentDetails['amount']) . '</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>' . htmlspecialchars($paymentDetails['status']) . '</td>
                        </tr>
                    </table>
                </div>
                
                <div class="print-button">
                    <button onclick="window.print()">Print Receipt</button>
                </div>
                
                <div class="footer">
                    <p>This is an electronically generated receipt.</p>
                    <p>Thank you for your business!</p>
                </div>
            </div>
        </body>
        </html>';
    }
} catch (Exception $e) {
    // Log the error
    $errorMessage = date('Y-m-d H:i:s') . " - Receipt generation error: " . $e->getMessage() . PHP_EOL;
    file_put_contents($logPath . '/receipts.log', $errorMessage, FILE_APPEND);
    
    // Output error
    header('Content-Type: text/html');
    echo '<h1>Error generating receipt</h1>';
    echo '<p>An error occurred while generating your receipt. Please try again later or contact support.</p>';
    echo '<p><a href="/">Return to Home</a></p>';
} 