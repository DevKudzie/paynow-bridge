<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Font Awesome for reliable icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/js/tailwind-config.js"></script>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col">
    <!-- <header class="border-b border-border py-6">
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-semibold tracking-tight text-center">Paynow Bridge</h1>
        </div>
    </header>
     -->
    <main class="container mx-auto flex-1 px-4 py-12">
        <div class="max-w-lg mx-auto">
            <div class="card p-8">
                <div class="flex flex-col space-y-1.5 mb-8">
                    <h2 class="text-2xl font-semibold leading-none tracking-tight text-center">Payment Processing</h2>
                    <p class="text-muted-foreground text-center mt-2">Secure transaction via Paynow</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="mb-6 flex justify-center">
                        <div class="rounded-lg bg-destructive/10 border border-destructive/30 p-6 max-w-md">
                            <div class="text-center">
                                <div class="h-12 w-12 rounded-full bg-destructive/20 flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-circle-exclamation text-destructive text-xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-destructive/90 mb-2">Payment Error</h3>
                                <p class="text-sm text-muted-foreground"><?php echo $error; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-center mt-4">
                        <a href="<?php echo $errorUrl; ?>" class="btn btn-primary flex items-center justify-center">
                            <i class="fa-solid fa-rotate-right mr-2"></i>
                            <span>Try Again</span>
                        </a>
                    </div>
                <?php elseif (isset($redirectUrl) && !empty($redirectUrl)): ?>
                    <!-- For web payments -->
                    <div class="text-center my-8">
                        <p class="mb-4 text-lg">You will be redirected to the payment page in <span id="countdown" class="font-semibold text-primary">3</span> seconds...</p>
                        <div class="flex justify-center mb-8">
                            <i class="fa-solid fa-circle-notch animate-spin-slow text-primary text-4xl"></i>
                        </div>
                        <p class="text-sm text-muted-foreground">If you are not redirected automatically, please click the button below:</p>
                        <div class="mt-6">
                            <a href="<?php echo $redirectUrl; ?>" class="btn btn-primary mx-auto">
                                <i class="fa-solid fa-arrow-up-right-from-square mr-2"></i>
                                Proceed to Payment
                            </a>
                        </div>
                    </div>
                    
                    <!-- Auto-redirect script -->
                    <script>
                        // Store the redirect URL
                        const redirectUrl = "<?php echo $redirectUrl; ?>";
                        
                        // Set up countdown
                        let countdown = 3;
                        const countdownEl = document.getElementById('countdown');
                        
                        // Update countdown every second
                        const timer = setInterval(() => {
                            countdown--;
                            countdownEl.textContent = countdown;
                            
                            if (countdown <= 0) {
                                clearInterval(timer);
                                window.location.href = redirectUrl;
                            }
                        }, 1000);
                        
                        // Log for debugging
                        console.log("Will redirect to:", redirectUrl);
                    </script>
                <?php elseif (isset($paymentMethod) && !empty($paymentMethod)): ?>
                    <!-- For mobile payments (e.g., EcoCash, OneMoney) -->
                    <?php if (isset($instructions) && !empty($instructions)): ?>
                    <div class="rounded-md border border-border bg-muted p-6 mb-8">
                        <div class="flex items-start">
                            <i class="fa-solid fa-mobile-screen text-primary text-xl mt-0.5"></i>
                            <div class="ml-4">
                                <h3 class="text-md font-medium">Payment Instructions</h3>
                                <div class="text-sm mt-3 text-muted-foreground">
                                    <?php echo $instructions; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Fallback instructions for mobile payments -->
                    <div class="rounded-md border border-border bg-muted p-6 mb-8">
                        <div class="flex items-start">
                            <i class="fa-solid fa-mobile-screen text-primary text-xl mt-0.5"></i>
                            <div class="ml-4">
                                <h3 class="text-md font-medium">Mobile Payment</h3>
                                <div class="text-sm mt-3 text-muted-foreground">
                                    <p>Please check your mobile phone for a payment request notification.</p>
                                    <p class="mt-2">Follow the instructions on your device to complete the payment.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div id="status-message" class="text-center my-8">
                        <p class="mb-6 text-lg">Waiting for payment confirmation...</p>
                        <div class="flex justify-center">
                            <i class="fa-solid fa-circle-notch animate-spin-slow text-primary text-4xl"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center my-8">
                        <p class="mb-6 text-lg">Initializing payment...</p>
                        <div class="flex justify-center">
                            <i class="fa-solid fa-circle-notch animate-spin-slow text-primary text-4xl"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!isset($error)): ?>
            <div class="card p-6 mt-6">
                <div class="text-sm text-muted-foreground">
                    <div class="flex items-center justify-center mb-3">
                        <i class="fa-solid fa-shield-halved text-success text-xl mr-2"></i>
                        <span class="font-medium">Secure Payment</span>
                    </div>
                    <p class="text-center">This payment is being processed securely through Paynow.</p>
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
        // Status check state
        let isCheckingStatus = false;
        let attemptCount = 0;
        const maxAttempts = 12; // 12 attempts x 5s = 60 seconds maximum
        let timerSeconds = 60; // 60 second timeout timer
        let timerInterval;
        let stopChecking = false;
        let firstCheck = true; // Flag for first status check
        
        // Function to check payment status
        function checkPaymentStatus() {
            // If we've been told to stop checking, don't proceed
            if (stopChecking) return;
            
            // Prevent multiple simultaneous checks
            if (isCheckingStatus) return;
            
            isCheckingStatus = true;
            attemptCount++;
            
            // Debug info
            console.log(`[Attempt ${attemptCount}/${maxAttempts}] Checking payment status...`);
            
            fetch('/check-status.php?poll_url=<?php echo urlencode($pollUrl); ?>')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Network response was not ok: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Payment status check result:", data);
                    
                    // Reset checking flag
                    isCheckingStatus = false;
                    
                    // If status check is successful
                    if (data.paid === true) {
                        // Stop the timer and status checking
                        stopChecking = true;
                        clearInterval(timerInterval);
                        
                        // Show success message before redirect
                        document.getElementById('status-message').innerHTML = `
                            <div class="mb-6 flex justify-center">
                                <div class="rounded-lg bg-success/10 border border-success/30 p-6 max-w-md">
                                    <div class="text-center">
                                        <div class="h-12 w-12 rounded-full bg-success/20 flex items-center justify-center mx-auto mb-4">
                                            <i class="fa-solid fa-check text-success text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-success mb-2">Payment Successful!</h3>
                                        <p class="text-sm text-success/80">Your payment has been processed successfully.</p>
                                        <p class="text-sm text-muted-foreground mt-2">Redirecting you now...</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        console.log("Payment successful! Redirecting to success page...");
                        
                        // For instant success, only wait 1 second before redirecting
                        const redirectDelay = firstCheck ? 1000 : 2000;
                        setTimeout(() => {
                            // Get the redirect URL from the status check response
                            let redirectUrl = data.redirect_url;
                            
                            // If no redirect URL in response, use the configured success URL as fallback
                            if (!redirectUrl) {
                                redirectUrl = "<?php echo $config['app']['success_url'] ?: '/payment/success'; ?>";
                            }
                            
                            console.log("Redirecting to:", redirectUrl);
                            
                            // Log any redirect issues for debugging
                            try {
                                if (redirectUrl.startsWith('http')) {
                                    // If it's a full URL, use it directly
                                    window.location.href = redirectUrl;
                                } else if (redirectUrl.startsWith('/')) {
                                    // If it's a relative URL, construct the full URL
                                    window.location.href = window.location.origin + redirectUrl;
                                } else {
                                    // Fallback - use the relative URL but with a slash
                                    window.location.href = window.location.origin + '/' + redirectUrl;
                                }
                            } catch (error) {
                                console.error("Redirect error:", error);
                                // Fallback redirect using location.replace
                                window.location.replace(redirectUrl);
                            }
                        }, redirectDelay);
                        
                        return;
                    } 
                    
                    // First check completed - no longer first check
                    firstCheck = false;
                    
                    // Check for explicit status values from Paynow
                    if (data.status === 'cancelled' || data.status === 'failed') {
                        // Payment cancelled or failed
                        stopChecking = true;
                        clearInterval(timerInterval);
                        
                        // Get the error redirect URL from the API response or fall back to config
                        const errorRedirectUrl = data.redirect_url || "<?php echo $config['app']['error_url'] ?: '/payment/error'; ?>";
                        
                        console.log(`Payment ${data.status}. Error redirect URL: ${errorRedirectUrl}`);
                        document.getElementById('status-message').innerHTML = `
                            <div class="mb-6 flex justify-center">
                                <div class="rounded-lg bg-destructive/10 border border-destructive/30 p-6 max-w-md">
                                    <div class="text-center">
                                        <div class="h-12 w-12 rounded-full bg-destructive/20 flex items-center justify-center mx-auto mb-4">
                                            <i class="fa-solid fa-xmark text-destructive text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-destructive/90 mb-2">Payment ${data.status === 'cancelled' ? 'Cancelled' : 'Failed'}</h3>
                                        <p class="text-sm text-muted-foreground">Your payment has been ${data.status}.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center mt-4">
                                <a href="${errorRedirectUrl}" class="btn btn-primary flex items-center justify-center">
                                    <i class="fa-solid fa-rotate-right mr-2"></i>
                                    Try Again
                                </a>
                            </div>
                        `;
                        
                        // Auto-redirect after a delay
                        setTimeout(() => {
                            try {
                                if (errorRedirectUrl.startsWith('http')) {
                                    window.location.href = errorRedirectUrl;
                                } else if (errorRedirectUrl.startsWith('/')) {
                                    window.location.href = window.location.origin + errorRedirectUrl;
                                } else {
                                    window.location.href = window.location.origin + '/' + errorRedirectUrl;
                                }
                            } catch (error) {
                                console.error("Error redirect error:", error);
                                window.location.replace(errorRedirectUrl);
                            }
                        }, 3000);
                        
                        return;
                    }
                    
                    // NEW: Check for configuration or system errors
                    if (data.status === 'Error' && data.error_message) {
                        // Configuration or system error
                        stopChecking = true;
                        clearInterval(timerInterval);
                        
                        // Get the error redirect URL from the API response or fall back to config
                        const errorRedirectUrl = data.redirect_url || "<?php echo $config['app']['error_url'] ?: '/payment/error'; ?>";
                        
                        console.log(`System error: ${data.error_message}. Error redirect URL: ${errorRedirectUrl}`);
                        document.getElementById('status-message').innerHTML = `
                            <div class="mb-6 flex justify-center">
                                <div class="rounded-lg bg-destructive/10 border border-destructive/30 p-6 max-w-md">
                                    <div class="text-center">
                                        <div class="h-12 w-12 rounded-full bg-destructive/20 flex items-center justify-center mx-auto mb-4">
                                            <i class="fa-solid fa-exclamation-triangle text-destructive text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-destructive/90 mb-2">Payment System Error</h3>
                                        <p class="text-sm text-muted-foreground">There was a technical issue processing your payment.</p>
                                        <p class="text-xs text-muted-foreground mt-2">${data.error_message}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center mt-4">
                                <a href="${errorRedirectUrl}" class="btn btn-primary flex items-center justify-center">
                                    <i class="fa-solid fa-rotate-right mr-2"></i>
                                    Try Again
                                </a>
                            </div>
                        `;
                        
                        // Auto-redirect after a delay
                        setTimeout(() => {
                            try {
                                if (errorRedirectUrl.startsWith('http')) {
                                    window.location.href = errorRedirectUrl;
                                } else if (errorRedirectUrl.startsWith('/')) {
                                    window.location.href = window.location.origin + errorRedirectUrl;
                                } else {
                                    window.location.href = window.location.origin + '/' + errorRedirectUrl;
                                }
                            } catch (error) {
                                console.error("Error redirect error:", error);
                                window.location.replace(errorRedirectUrl);
                            }
                        }, 5000);
                        
                        return;
                    }
                    
                    // If we've hit the max attempts (timeout)
                    if (attemptCount >= maxAttempts || timerSeconds <= 0) {
                        // Too many attempts, show timeout message
                        stopChecking = true;
                        clearInterval(timerInterval);
                        
                        // Get URLs from config
                        const baseUrl = "<?php echo $config['app']['base_url']; ?>";
                        const errorUrl = "<?php echo $config['app']['error_url']; ?>";
                        
                        console.log("Max attempts reached or timeout. Showing timeout message.");
                        document.getElementById('status-message').innerHTML = `
                            <div class="mb-6 flex justify-center">
                                <div class="rounded-lg bg-muted border border-border p-6 max-w-md">
                                    <div class="text-center">
                                        <div class="h-12 w-12 rounded-full bg-muted/50 flex items-center justify-center mx-auto mb-4">
                                            <i class="fa-solid fa-clock text-primary text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold mb-2">Payment Pending</h3>
                                        <p class="text-sm text-muted-foreground">Your payment is taking longer than expected. It may still be processing or might require additional action.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center mt-4 space-x-4">
                                <a href="${baseUrl}" class="btn btn-secondary">
                                    <i class="fa-solid fa-check mr-2"></i>
                                    Check Status
                                </a>
                                <a href="${errorUrl}" class="btn btn-primary">
                                    <i class="fa-solid fa-rotate-right mr-2"></i>
                                    Try Again
                                </a>
                            </div>
                        `;
                        return;
                    }
                    
                    // Payment still pending, check again after delay
                    // Use shorter delay for first few checks to catch quick payments
                    if (!stopChecking) {
                        const nextCheckDelay = attemptCount < 3 ? 2000 : 5000; // 2s initially, then 5s
                        setTimeout(checkPaymentStatus, nextCheckDelay);
                    }
                })
                .catch(error => {
                    // Reset checking flag
                    isCheckingStatus = false;
                    
                    console.error('Error checking payment status:', error);
                    
                    // Continue checking if we haven't hit the limit or stopped
                    if (!stopChecking && attemptCount < maxAttempts && timerSeconds > 0) {
                        // Try again after a delay even if there was an error
                        setTimeout(checkPaymentStatus, 10000); // Wait longer after an error
                    } else {
                        // Too many attempts or we've stopped checking, show error message
                        stopChecking = true;
                        clearInterval(timerInterval);
                        
                        document.getElementById('status-message').innerHTML = `
                            <div class="mb-6 flex justify-center">
                                <div class="rounded-lg bg-destructive/10 border border-destructive/30 p-6 max-w-md">
                                    <div class="text-center">
                                        <div class="h-12 w-12 rounded-full bg-destructive/20 flex items-center justify-center mx-auto mb-4">
                                            <i class="fa-solid fa-triangle-exclamation text-destructive text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-destructive/90 mb-2">Status Check Failed</h3>
                                        <p class="text-sm text-muted-foreground">We couldn't check your payment status. Please check your connection and try again.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center mt-4">
                                <a href="${errorUrl}" class="btn btn-primary flex items-center justify-center">
                                    <i class="fa-solid fa-rotate-right mr-2"></i>
                                    Try Again
                                </a>
                            </div>
                        `;
                    }
                });
        }

        // Update the UI with the timer
        function updateTimerDisplay() {
            const timerElement = document.getElementById('timeout-timer');
            if (timerElement) {
                timerElement.textContent = timerSeconds;
            }
            
            // Decrement the timer
            timerSeconds--;
            
            // If timer reaches 0, stop the interval and trigger timeout
            if (timerSeconds <= 0) {
                clearInterval(timerInterval);
                // Let the next status check handle the timeout
            }
        }
        
        // Add timer display to status message
        document.getElementById('status-message').innerHTML += `
            <div class="text-sm text-muted-foreground text-center mt-4">
                Time remaining: <span id="timeout-timer" class="font-semibold">${timerSeconds}</span> seconds
            </div>
        `;
        
        // Start the timer
        timerInterval = setInterval(updateTimerDisplay, 1000);

        // Start checking payment status IMMEDIATELY for the first check (don't wait 3 seconds)
        // This helps catch instant success cases
        checkPaymentStatus();
    </script>
    <?php endif; ?>
</body>
</html> 