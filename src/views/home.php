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
                        light: {
                            background: "hsl(0 0% 100%)",
                            foreground: "hsl(222.2 47.4% 11.2%)",
                            border: "hsl(214.3 31.8% 91.4%)",
                            input: "hsl(214.3 31.8% 91.4%)",
                            card: "hsl(0 0% 100%)",
                            muted: "hsl(210 40% 96.1%)"
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
        
        .icon {
            width: 1.25rem;
            height: 1.25rem;
            display: inline-block;
            margin-right: 0.5rem;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }
        
        .navbar-toggler {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            background-color: hsl(240 3.7% 15.9%);
            border: none;
            cursor: pointer;
        }
        
        .navbar-toggler:hover {
            background-color: hsl(240 5.3% 26.1%);
        }
        
        /* Light mode styles */
        html:not(.dark) body {
            background-color: hsl(0 0% 100%);
            color: hsl(222.2 47.4% 11.2%);
        }
        
        html:not(.dark) .bg-background {
            background-color: hsl(0 0% 100%);
        }
        
        html:not(.dark) .text-foreground {
            color: hsl(222.2 47.4% 11.2%);
        }
        
        html:not(.dark) .border-border {
            border-color: hsl(214.3 31.8% 91.4%);
        }
        
        html:not(.dark) .bg-card {
            background-color: hsl(0 0% 100%);
        }
        
        html:not(.dark) .text-card-foreground {
            color: hsl(222.2 47.4% 11.2%);
        }
        
        html:not(.dark) .bg-muted {
            background-color: hsl(210 40% 96.1%);
        }
    </style>
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col">
    <header class="border-b border-border py-6">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold tracking-tight">Paynow Bridge</h1>
            <nav class="flex items-center gap-4">
                <button id="theme-toggle" type="button" class="navbar-toggler" aria-label="Toggle theme">
                    <!-- Sun icon (for dark mode) -->
                    <svg xmlns="http://www.w3.org/2000/svg" id="theme-toggle-dark-icon" class="icon hidden dark:block" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="5" />
                        <line x1="12" y1="1" x2="12" y2="3" />
                        <line x1="12" y1="21" x2="12" y2="23" />
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                        <line x1="1" y1="12" x2="3" y2="12" />
                        <line x1="21" y1="12" x2="23" y2="12" />
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                    </svg>
                    
                    <!-- Moon icon (for light mode) -->
                    <svg xmlns="http://www.w3.org/2000/svg" id="theme-toggle-light-icon" class="icon block dark:hidden" viewBox="0 0 24 24">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                </button>
                
                <button type="button" class="navbar-toggler" aria-label="Toggle menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24">
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
            </nav>
        </div>
    </header>
    
    <main class="container mx-auto flex-1 px-4 py-12">
        <div class="max-w-3xl mx-auto">
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6 mb-8">
                <div class="space-y-4">
                    <div class="flex flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
                        <div class="flex-1 rounded-md border border-border bg-muted p-4">
                            <div class="flex items-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" viewBox="0 0 24 24">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <line x1="2" y1="10" x2="22" y2="10" />
                                </svg>
                                <h3 class="text-lg font-medium ml-2">Web Payments</h3>
                            </div>
                            <p class="text-sm text-muted-foreground">Process payments through standard web payment methods supported by Paynow.</p>
                        </div>
                        
                        <div class="flex-1 rounded-md border border-border bg-muted p-4">
                            <div class="flex items-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" viewBox="0 0 24 24">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                                    <line x1="12" y1="18" x2="12" y2="18.01" />
                                </svg>
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
                        <input type="hidden" name="email" value="test@example.com">
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
                            <input type="text" id="phone" name="phone" placeholder="e.g., 0771111111" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24">
                                <path d="M20 7.5h-3.9a2 2 0 0 1-2-2V2m4.9 12H5c-1.1 0-2-.9-2-2V7c0-1.1.9-2 2-2h10" />
                                <polyline points="16 12 18 14 22 10" />
                            </svg>
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
        
        // Dark mode toggle
        const themeToggleBtn = document.getElementById('theme-toggle');
        themeToggleBtn.addEventListener('click', function() {
            // Toggle .dark class on the html element
            document.documentElement.classList.toggle('dark');
            
            // Store the preference in localStorage
            if (document.documentElement.classList.contains('dark')) {
                localStorage.setItem('color-theme', 'dark');
            } else {
                localStorage.setItem('color-theme', 'light');
            }
        });
        
        // Check for saved theme preference or respect OS preference
        if (localStorage.getItem('color-theme') === 'light' || 
            (!('color-theme' in localStorage) && 
             window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html> 