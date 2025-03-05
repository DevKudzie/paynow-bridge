<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/tailwind-config.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col">
    
    <main class="container mx-auto flex-1 px-4 py-12">
        <div class="max-w-lg mx-auto">
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center rounded-full bg-destructive/20 p-3 mb-4">
                        <i class="fa-solid fa-xmark text-destructive text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold tracking-tight">Payment Failed</h2>
                    <p class="text-muted-foreground mt-2">We're sorry, but there was a problem processing your payment.</p>
                </div>
                
                <?php if (isset($errorMessage)): ?>
                <div class="rounded-md border border-destructive/50 bg-destructive/10 p-4 mt-6">
                    <div class="flex">
                        <i class="fa-solid fa-triangle-exclamation text-destructive mt-0.5"></i>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-foreground">Error Details</h3>
                            <p class="text-sm text-foreground/90 mt-2"><?php echo $errorMessage; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="rounded-md bg-muted/50 px-3 py-2 text-sm text-muted-foreground mt-6">
                    <i class="lucide lucide-info inline-block mr-1 align-text-bottom"></i>
                    Please try again or choose a different payment method.
                </div>
                
                <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2 justify-center mt-8">
                    <a href="<?php echo $retryUrl ?? '#'; ?>" class="btn btn-primary flex items-center justify-center">
                        <i class="fa-solid fa-rotate-left mr-2"></i>
                        <span>Try Again</span>
                    </a>
                </div>
            </div>
            
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6 mt-6">
                <div class="text-sm text-muted-foreground">
                    <p class="mb-2">Need help with your payment? Contact our support team.</p>
                    <div class="flex items-center">
                        <i class="lucide lucide-headphones mr-2"></i>
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