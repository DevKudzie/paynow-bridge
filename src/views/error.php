<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
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
                        }
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
        
        .lucide {
            height: 1.25rem;
            width: 1.25rem;
            margin-right: 0.5rem;
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
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center rounded-full bg-destructive/20 p-3 mb-4">
                        <i class="lucide lucide-x text-destructive text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold tracking-tight">Payment Failed</h2>
                    <p class="text-muted-foreground mt-2">We're sorry, but there was a problem processing your payment.</p>
                </div>
                
                <?php if (isset($errorMessage)): ?>
                <div class="rounded-md border border-destructive/50 bg-destructive/20 p-4 mt-6">
                    <div class="flex">
                        <i class="lucide lucide-alert-circle text-destructive mt-0.5"></i>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-destructive">Error Details</h3>
                            <p class="text-sm text-destructive/80 mt-2"><?php echo $errorMessage; ?></p>
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
                        <i class="lucide lucide-refresh-cw mr-2"></i>
                        <span>Try Again</span>
                    </a>
                    <a href="<?php echo $homeUrl ?? '/'; ?>" class="btn btn-secondary flex items-center justify-center">
                        <i class="lucide lucide-home mr-2"></i>
                        <span>Return to Home</span>
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