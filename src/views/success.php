<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/tailwind-config.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col">
    <main class="container mx-auto flex-1 px-4 py-12">
        <div class="max-w-lg mx-auto">
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center rounded-full bg-success/20 p-3 mb-4">
                        <i class="fa-solid fa-check text-success text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold tracking-tight">Payment Successful!</h2>
                    <p class="text-muted-foreground mt-2">Thank you for your payment. Your transaction has been completed successfully.</p>
                </div>
                
                <?php if (isset($paymentDetails)): ?>
                <div class="rounded-md border border-border bg-muted p-4 mt-6">
                    <h3 class="text-sm font-medium mb-3">Payment Details</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Reference</span>
                            <span class="text-sm font-medium"><?php echo $paymentDetails['reference']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Amount</span>
                            <span class="text-sm font-medium">$<?php echo $paymentDetails['amount']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Date</span>
                            <span class="text-sm font-medium"><?php echo $paymentDetails['date']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="rounded-md bg-muted/50 px-3 py-2 text-sm text-muted-foreground mt-6">
                    <div class="flex items-center">
                        <i class="fa-solid fa-envelope mr-2"></i>
                        <span>A confirmation email has been sent to you with the transaction details.</span>
                    </div>
                </div>
                
                <div class="flex justify-center mt-8">
                    <?php if (isset($paymentDetails) && isset($paymentDetails['reference'])): ?>
                    <a href="/receipt.php?reference=<?php echo urlencode($paymentDetails['reference']); ?>" class="btn btn-secondary flex items-center">
                        <i class="fa-solid fa-file-lines mr-2"></i>
                        <span>Download Receipt</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6 mt-6">
                <div class="text-sm text-muted-foreground">
                    <p class="mb-2">Need help with your payment? Contact our support team.</p>
                    <div class="flex items-center">
                        <i class="fa-solid fa-headset mr-2"></i>
                        <span>support@example.com</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="border-t border-border py-6 mt-auto">
        <div class="container mx-auto px-4">
            <p class="text-center text-sm text-muted-foreground">&copy; <?php echo date('Y'); ?> Paynow Bridge System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 