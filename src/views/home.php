<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paynow Payment Bridge</title>
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
        
        .lucide {
            height: 1.25rem;
            width: 1.25rem;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col">
    <header class="border-b border-border py-6">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold tracking-tight">Paynow Bridge</h1>
            <nav>
                <a href="#" class="btn btn-secondary">
                    <i class="lucide lucide-info"></i>
                    About
                </a>
            </nav>
        </div>
    </header>
    
    <main class="container mx-auto flex-1 px-4 py-12">
        <div class="max-w-3xl mx-auto">
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6 mb-8">
                <div class="flex flex-col space-y-1.5 mb-6">
                    <h2 class="text-2xl font-semibold leading-none tracking-tight">Paynow Payment Bridge</h2>
                    <p class="text-sm text-muted-foreground">Securely process payments through our bridge system</p>
                </div>
                <div class="space-y-4">
                    <p>Welcome to the Paynow Payment Bridge system. This system processes payments via Paynow, offering a seamless experience for both web and mobile payment methods.</p>
                    
                    <div class="flex flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
                        <div class="flex-1 rounded-md border border-border bg-muted p-4">
                            <div class="flex items-center mb-2">
                                <i class="lucide lucide-credit-card text-primary"></i>
                                <h3 class="text-lg font-medium ml-2">Web Payments</h3>
                            </div>
                            <p class="text-sm text-muted-foreground">Process payments through standard web payment methods supported by Paynow.</p>
                        </div>
                        
                        <div class="flex-1 rounded-md border border-border bg-muted p-4">
                            <div class="flex items-center mb-2">
                                <i class="lucide lucide-smartphone text-primary"></i>
                                <h3 class="text-lg font-medium ml-2">Mobile Payments</h3>
                            </div>
                            <p class="text-sm text-muted-foreground">Support for mobile money payments including EcoCash and OneMoney.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6">
                <div class="flex flex-col space-y-1.5 mb-6">
                    <h3 class="text-xl font-semibold leading-none tracking-tight">Payment Sample</h3>
                    <p class="text-sm text-muted-foreground">Try a sample payment using our bridge system</p>
                </div>
                
                <div class="space-y-4">
                    <div class="rounded-md border border-border p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium">Sample Product</h4>
                                <p class="text-sm text-muted-foreground">Test the payment process with a sample product</p>
                            </div>
                            <span class="text-lg font-medium">$10.00</span>
                        </div>
                    </div>
                    
                    <form action="/payment/bridge" method="get" class="space-y-4">
                        <input type="hidden" name="reference" value="INV<?php echo time(); ?>">
                        <input type="hidden" name="email" value="customer@example.com">
                        <input type="hidden" name="items[0][name]" value="Sample Product">
                        <input type="hidden" name="items[0][amount]" value="10.00">
                        
                        <div>
                            <label for="payment_method" class="block text-sm font-medium mb-2">Payment Method</label>
                            <select id="payment_method" name="payment_method" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="">Web Payment (Default)</option>
                                <option value="ecocash">EcoCash</option>
                                <option value="onemoney">OneMoney</option>
                            </select>
                        </div>
                        
                        <div id="phone-container" class="hidden">
                            <label for="phone" class="block text-sm font-medium mb-2">Mobile Number</label>
                            <input type="text" id="phone" name="phone" placeholder="e.g., 0771234567" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="lucide lucide-wallet"></i>
                            Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="border-t border-border py-6 mt-12">
        <div class="container mx-auto px-4">
            <p class="text-center text-sm text-muted-foreground">&copy; <?php echo date('Y'); ?> Paynow Bridge System. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        // Show/hide phone field based on payment method
        document.getElementById('payment_method').addEventListener('change', function() {
            const phoneContainer = document.getElementById('phone-container');
            if (this.value === 'ecocash' || this.value === 'onemoney') {
                phoneContainer.classList.remove('hidden');
                document.getElementById('phone').setAttribute('required', true);
            } else {
                phoneContainer.classList.add('hidden');
                document.getElementById('phone').removeAttribute('required');
            }
        });
    </script>
</body>
</html> 