<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        border: "hsl(240 3.7% 15.9%)",
                        input: "hsl(240 3.7% 15.9%)",
                        ring: "hsl(240 4.9% 83.9%)",
                        background: "hsl(240 10% 3.9%)",
                        foreground: "hsl(0 0% 98%)",
                        primary: {
                            DEFAULT: "hsl(240 5.9% 90%)",
                            foreground: "hsl(240 5.9% 10%)",
                        },
                        secondary: {
                            DEFAULT: "hsl(240 3.7% 15.9%)",
                            foreground: "hsl(0 0% 98%)",
                        },
                        destructive: {
                            DEFAULT: "hsl(0 62.8% 30.6%)",
                            foreground: "hsl(0 85.7% 97.3%)",
                        },
                        muted: {
                            DEFAULT: "hsl(240 3.7% 15.9%)",
                            foreground: "hsl(240 5% 64.9%)",
                        },
                        accent: {
                            DEFAULT: "hsl(240 3.7% 15.9%)",
                            foreground: "hsl(0 0% 98%)",
                        },
                        card: {
                            DEFAULT: "hsl(240 10% 3.9%)",
                            foreground: "hsl(0 0% 98%)",
                        },
                    },
                    borderRadius: {
                        lg: "0.5rem",
                        md: "calc(0.5rem - 2px)",
                        sm: "calc(0.5rem - 4px)",
                    }
                }
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css" rel="stylesheet">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
            border: 1px solid transparent;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .btn-primary {
            background-color: hsl(240 5.9% 90%);
            color: hsl(240 5.9% 10%);
        }
        
        .btn-primary:hover {
            background-color: hsl(240 4.8% 82.9%);
        }
        
        .btn-secondary {
            background-color: hsl(240 3.7% 15.9%);
            color: hsl(0 0% 98%);
        }
        
        .btn-secondary:hover {
            background-color: hsl(240 5.3% 26.1%);
        }
        
        .btn-destructive {
            background-color: hsl(0 62.8% 30.6%);
            color: hsl(0 85.7% 97.3%);
        }
        
        .btn-destructive:hover {
            background-color: hsl(0 63% 35%);
        }
        
        .lucide {
            height: 1.25rem;
            width: 1.25rem;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        
        .animate-spin-slow {
            animation: spin 1.5s linear infinite;
        }
    </style>
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col">
    <header class="border-b border-border py-6">
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-semibold tracking-tight text-center">Paynow Bridge</h1>
        </div>
    </header>
    
    <main class="container mx-auto flex-1 px-4 py-12">
        <div class="max-w-lg mx-auto">
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6">
                <div class="flex flex-col space-y-1.5 mb-6">
                    <h2 class="text-2xl font-semibold leading-none tracking-tight text-center">Payment Processing</h2>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="rounded-md border border-destructive p-4 mb-6">
                        <div class="flex items-start">
                            <i class="lucide lucide-alert-circle text-destructive mt-0.5"></i>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-destructive">Payment Error</h3>
                                <p class="text-sm mt-2"><?php echo $error; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-center mt-6">
                        <a href="<?php echo $errorUrl; ?>" class="btn btn-secondary">
                            <i class="lucide lucide-refresh-cw"></i>
                            Try Again
                        </a>
                    </div>
                <?php elseif (isset($instructions)): ?>
                    <!-- For mobile payments (e.g., EcoCash) -->
                    <div class="rounded-md border border-border bg-muted p-4 mb-6">
                        <div class="flex items-start">
                            <i class="lucide lucide-smartphone text-primary mt-0.5"></i>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium">Payment Instructions</h3>
                                <div class="text-sm mt-2 text-muted-foreground">
                                    <?php echo $instructions; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="status-message" class="text-center my-8">
                        <p class="mb-4">Waiting for payment confirmation...</p>
                        <div class="flex justify-center">
                            <i class="lucide lucide-loader-2 animate-spin-slow text-primary text-3xl"></i>
                        </div>
                    </div>
                <?php elseif (isset($redirectUrl)): ?>
                    <!-- For web payments -->
                    <div class="text-center my-8">
                        <p class="mb-4">You will be redirected to the payment page in <span id="countdown" class="font-semibold">5</span> seconds...</p>
                        <div class="flex justify-center mb-6">
                            <i class="lucide lucide-loader-2 animate-spin-slow text-primary text-3xl"></i>
                        </div>
                        <p class="text-sm text-muted-foreground">If you are not redirected automatically, please click the button below:</p>
                        <div class="mt-4">
                            <a href="<?php echo $redirectUrl; ?>" class="btn btn-primary">
                                <i class="lucide lucide-external-link"></i>
                                Proceed to Payment
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center my-8">
                        <p class="mb-4">Initializing payment...</p>
                        <div class="flex justify-center">
                            <i class="lucide lucide-loader-2 animate-spin-slow text-primary text-3xl"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!isset($error)): ?>
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6 mt-6">
                <div class="text-sm text-muted-foreground">
                    <p class="mb-2">This payment is being processed securely through Paynow.</p>
                    <div class="flex items-center">
                        <i class="lucide lucide-shield-check mr-2"></i>
                        <span>Your payment information is protected</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer class="border-t border-border py-6 mt-auto">
        <div class="container mx-auto px-4">
            <p class="text-center text-sm text-muted-foreground">&copy; <?php echo date('Y'); ?> Paynow Bridge System. All rights reserved.</p>
        </div>
    </footer>

    <?php if (isset($pollUrl)): ?>
    <script>
        // Function to check payment status
        function checkPaymentStatus() {
            fetch('/check-status.php?poll_url=<?php echo urlencode($pollUrl); ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.paid) {
                        // Payment successful, redirect to success page
                        window.location.href = '<?php echo $successUrl; ?>';
                    } else if (data.status === 'cancelled') {
                        // Payment cancelled
                        document.getElementById('status-message').innerHTML = `
                            <div class="rounded-md border border-destructive p-4 mb-6">
                                <div class="flex items-start">
                                    <i class="lucide lucide-x-circle text-destructive mt-0.5"></i>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-destructive">Payment Cancelled</h3>
                                        <p class="text-sm mt-2">Your payment has been cancelled.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center mt-6">
                                <a href="<?php echo $errorUrl; ?>" class="btn btn-secondary">
                                    <i class="lucide lucide-refresh-cw"></i>
                                    Try Again
                                </a>
                            </div>
                        `;
                    } else {
                        // Payment still pending, check again after 5 seconds
                        setTimeout(checkPaymentStatus, 5000);
                    }
                })
                .catch(error => {
                    console.error('Error checking payment status:', error);
                });
        }

        // Start checking payment status
        setTimeout(checkPaymentStatus, 5000);
    </script>
    <?php endif; ?>

    <?php if (isset($redirectUrl)): ?>
    <script>
        // Countdown for redirect
        let seconds = 5;
        const countdownEl = document.getElementById('countdown');
        
        function updateCountdown() {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                window.location.href = '<?php echo $redirectUrl; ?>';
            } else {
                setTimeout(updateCountdown, 1000);
            }
        }
        
        // Start countdown
        setTimeout(updateCountdown, 1000);
    </script>
    <?php endif; ?>
</body>
</html> 