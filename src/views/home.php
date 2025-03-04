<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paynow Payment Bridge</title>
    <?php 
    // Debug output - directly access the environment variable
    $networkIP = getenv('LOCAL_NETWORK_IP');
    // For debugging - output to the PHP error log
    error_log('LOCAL_NETWORK_IP from getenv: ' . ($networkIP ?: 'not set'));
    error_log('LOCAL_NETWORK_IP from $_ENV: ' . (isset($_ENV['LOCAL_NETWORK_IP']) ? $_ENV['LOCAL_NETWORK_IP'] : 'not set'));
    error_log('LOCAL_NETWORK_IP from $_SERVER: ' . (isset($_SERVER['LOCAL_NETWORK_IP']) ? $_SERVER['LOCAL_NETWORK_IP'] : 'not set'));
    
    // Try to ensure we get the IP if it exists
    if (empty($networkIP)) {
        $networkIP = isset($_ENV['LOCAL_NETWORK_IP']) ? $_ENV['LOCAL_NETWORK_IP'] : '';
    }
    if (empty($networkIP)) {
        $networkIP = isset($_SERVER['LOCAL_NETWORK_IP']) ? $_SERVER['LOCAL_NETWORK_IP'] : '';
    }
    if (empty($networkIP)) {
        // Hardcode for testing - comment this out in production
        $networkIP = '192.168.0.163';
    }
    ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Pass the LOCAL_NETWORK_IP from PHP to JavaScript -->
    <script>
        // Store the configured IP address from .env
        <?php 
        echo "console.log('PHP detected LOCAL_NETWORK_IP: " . ($networkIP ?: "not set") . "');";
        echo "const configuredNetworkIP = '" . ($networkIP ?: "") . "';";
        ?>
    </script>
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
                            background: "hsl(0 0% 97%)",
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
        
        .navbar-toggler .icon {
            margin: 0;
            width: 1.25rem;
            height: 1.25rem;
            color: white;
        }
        
        /* Light mode styles */
        html:not(.dark) body {
            background-color: hsl(0 0% 97%);
            color: hsl(222.2 47.4% 11.2%);
        }
        
        html:not(.dark) .bg-background {
            background-color: hsl(0 0% 97%);
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
        
        html:not(.dark) .text-primary {
            color: hsl(222.2 47.4% 35.2%);
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
            </nav>
        </div>
    </header>
    
    <main class="container mx-auto flex-1 px-4 py-12">
        <div class="max-w-3xl mx-auto">
            <?php 
            // Get the Paynow configuration
            $config = require_once __DIR__ . '/../config/config.php';
            $isTestMode = $config['paynow']['test_mode'];
            
            // Show different content based on test mode
            if (!$isTestMode):
            ?>
                <!-- Production Mode - Show "Return to Merchant" message -->
                <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm p-6">
                    <div class="flex flex-col space-y-1.5 mb-6">
                        <h3 class="text-xl font-semibold leading-none tracking-tight">Direct Access Not Allowed</h3>
                        <p class="text-sm text-muted-foreground">This page is only accessible from the merchant's website.</p>
                    </div>
                    
                    <div class="rounded-md border border-border bg-muted p-6 mb-6">
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary mt-0.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <div class="ml-4">
                                <h3 class="text-md font-medium">Please Return to Merchant</h3>
                                <p class="text-sm text-muted-foreground mt-2">This payment bridge must be accessed through the merchant's checkout page. Please return to the merchant's website to complete your payment.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Test Mode - Show test payment options -->
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
                        <h3 class="text-xl font-semibold leading-none tracking-tight">Test Payment Sample</h3>
                        <p class="text-sm text-muted-foreground">Try a sample payment using our bridge system</p>
                    </div>
                    
                    <div class="rounded-md border border-border bg-destructive/10 p-4 mb-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-destructive" viewBox="0 0 24 24">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <span class="text-sm font-medium ml-2">Test Mode Active</span>
                        </div>
                        <p class="text-xs text-muted-foreground mt-2">This is a test environment. No real transactions will be processed.</p>
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
                                    <option value="" selected>Web Payment (Default)</option>
                                    <option value="ecocash">EcoCash</option>
                                    <option value="onemoney">OneMoney</option>
                                </select>
                            </div>
                            
                            <div id="phone-container" class="hidden">
                                <label for="phone" class="block text-sm font-medium mb-2">Mobile Number</label>
                                <select id="phone" name="phone" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                    <option value="0771111111">0771111111 - Success</option>
                                    <option value="0772222222">0772222222 - Delayed Success</option>
                                    <option value="0773333333">0773333333 - User Cancelled</option>
                                    <option value="0774444444">0774444444 - Insufficient Funds</option>
                                    <option value="custom">Custom Phone Number</option>
                                </select>
                                <div id="custom-phone-container" class="hidden mt-2">
                                    <input type="text" id="custom-phone" placeholder="Enter a custom phone number" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                </div>
                            </div>
                            
                            <div class="flex space-x-3">
                                <button type="submit" class="btn btn-primary flex-grow">
                                    <i class="fa-solid fa-credit-card mr-2"></i>
                                    Proceed to Payment
                                </button>
                                <button type="button" id="show-qr-code" class="btn btn-secondary" title="Show QR Code for mobile testing">
                                    <i class="fa-solid fa-qrcode"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- QR Code Modal -->
    <div id="qr-modal" class="fixed inset-0 bg-background/80 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-card border border-border rounded-lg p-6 max-w-md w-full mx-4 relative shadow-lg">
            <button id="close-modal" class="absolute top-3 right-3 text-muted-foreground hover:text-foreground">
                <i class="fa-solid fa-times"></i>
            </button>
            <div class="text-center mb-4">
                <h3 class="text-lg font-semibold">Mobile Testing QR Code</h3>
                <p class="text-sm text-muted-foreground mt-1">Scan this code with your mobile device to test the payment page</p>
            </div>
            <div id="qrcode-container" class="bg-white p-4 rounded-md mx-auto max-w-xs flex justify-center"></div>
            <!-- <div class="mt-4 text-center text-sm text-muted-foreground">
                <p>The QR code contains a link to the payment page using your local network address.</p>
                <p class="mt-2">Make sure your mobile device is on the same network as this computer.</p>
            </div> -->
            <div class="mt-4">
                <div class="text-xs text-muted-foreground border-t border-border pt-3 mt-3">
                    <p class="mb-1"><strong>Network IP: </strong><span id="local-ip">detecting...</span> <span id="ip-source" class="text-xs opacity-75"></span></p>
                    <p class="mb-1"><strong>Link Details:</strong></p>
                    <p id="qr-url" class="break-all"></p>
                </div>
            </div>
            <!-- <div class="mt-4 text-sm text-muted-foreground">
                <p><i class="fa-solid fa-info-circle mr-1"></i> If QR code doesn't work, try manually entering the URL on your mobile device.</p>
            </div> -->
        </div>
    </div>
    
    <footer class="border-t border-border py-6 mt-12">
        <div class="container mx-auto px-4">
            <p class="text-center text-sm text-muted-foreground">&copy; <?php echo date('Y'); ?> Paynow Bridge System. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle payment method change
            function handlePaymentMethodChange() {
                const paymentMethod = document.getElementById('payment_method').value;
                const phoneContainer = document.getElementById('phone-container');
                const customPhoneContainer = document.getElementById('custom-phone-container');
                const phoneInput = document.getElementById('phone');
                const customPhoneInput = document.getElementById('custom-phone');
                const qrCodeButton = document.getElementById('show-qr-code');
                
                // Handle visibility of phone fields based on payment method
                if (paymentMethod === 'ecocash' || paymentMethod === 'onemoney') {
                    phoneContainer.classList.remove('hidden');
                    
                    // Make sure the phone field is enabled for mobile payments
                    if (phoneInput) {
                        phoneInput.disabled = false;
                        phoneInput.setAttribute('required', true);
                    }
                    if (customPhoneInput) {
                        customPhoneInput.disabled = false;
                    }
                    
                    // Check if there's a mobile number selection dropdown and hide/show custom field
                    if (phoneInput && phoneInput.value === 'custom' && customPhoneContainer) {
                        customPhoneContainer.classList.remove('hidden');
                    } else if (customPhoneContainer) {
                        customPhoneContainer.classList.add('hidden');
                    }
                    
                    // Hide QR code button for mobile payments
                    if (qrCodeButton) {
                        qrCodeButton.style.display = 'none';
                    }
                } else {
                    // For web payment, hide the phone fields and clear values
                    phoneContainer.classList.add('hidden');
                    if (customPhoneContainer) {
                        customPhoneContainer.classList.add('hidden');
                    }
                    
                    // Clear phone input values when switching to web payment
                    if (phoneInput) {
                        phoneInput.value = '';
                        phoneInput.removeAttribute('required');
                    }
                    if (customPhoneInput) customPhoneInput.value = '';
                    
                    // Show QR code button for web payments
                    if (qrCodeButton) {
                        qrCodeButton.style.display = 'block';
                    }
                }
            }

            // Add event listener for payment method changes
            const paymentMethodSelect = document.getElementById('payment_method');
            if (paymentMethodSelect) {
                paymentMethodSelect.addEventListener('change', handlePaymentMethodChange);
                
                // Trigger the change event on page load to set initial state
                handlePaymentMethodChange();
            }
            
            // Handle mobile number selection changes
            const mobileNumberSelect = document.getElementById('phone');
            if (mobileNumberSelect) {
                mobileNumberSelect.addEventListener('change', function() {
                    const customPhoneContainer = document.getElementById('custom-phone-container');
                    const customPhoneInput = document.getElementById('custom-phone');
                    
                    if (this.value === 'custom' && customPhoneContainer) {
                        customPhoneContainer.classList.remove('hidden');
                        if (customPhoneInput) {
                            customPhoneInput.disabled = false;
                            customPhoneInput.setAttribute('required', true);
                        }
                    } else if (customPhoneContainer) {
                        customPhoneContainer.classList.add('hidden');
                        if (customPhoneInput) {
                            customPhoneInput.removeAttribute('required');
                        }
                    }
                });
            }
            
            // Handle form submission - disable phone field when web payment is selected
            const paymentForm = document.querySelector('form');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function() {
                    const paymentMethod = document.getElementById('payment_method').value;
                    const phoneInput = document.getElementById('phone');
                    const customPhoneInput = document.getElementById('custom-phone');
                    
                    // For web payment, disable phone fields so they don't get submitted
                    // but don't let this persist - this is just for the form submission
                    if (paymentMethod !== 'ecocash' && paymentMethod !== 'onemoney') {
                        if (phoneInput) phoneInput.disabled = true;
                        if (customPhoneInput) customPhoneInput.disabled = true;
                    }
                });
            }
            
            // QR Code Modal Functionality
            const showQrCodeBtn = document.getElementById('show-qr-code');
            const qrModal = document.getElementById('qr-modal');
            const closeModalBtn = document.getElementById('close-modal');
            const qrContainer = document.getElementById('qrcode-container');
            const qrUrlText = document.getElementById('qr-url');
            const localIpText = document.getElementById('local-ip');
            
            if (showQrCodeBtn && qrModal && closeModalBtn && qrContainer) {
                // Function to get local IP address (not always reliable, but works for most cases)
                async function getLocalIPAddress() {
                    try {
                        // Check if we have a configured IP address from .env file
                        console.log('Checking for configured network IP:', configuredNetworkIP);
                        
                        // Make sure the IP is valid and not empty
                        if (configuredNetworkIP && configuredNetworkIP.trim() !== '' && configuredNetworkIP !== 'not set') {
                            console.log('Using configured IP address:', configuredNetworkIP);
                            return configuredNetworkIP;
                        }
                        
                        console.log('No valid IP address configured, attempting auto-detection...');
                        const pc = new RTCPeerConnection({
                            iceServers: []
                        });
                        pc.createDataChannel('');
                        
                        return new Promise((resolve) => {
                            let ipFound = false;
                            
                            pc.onicecandidate = (ice) => {
                                if (!ice || !ice.candidate || !ice.candidate.candidate) return;
                                
                                const ipMatch = /([0-9]{1,3}(\.[0-9]{1,3}){3})/.exec(ice.candidate.candidate);
                                if (ipMatch && !ipFound) {
                                    const ip = ipMatch[1];
                                    if (ip.includes('192.168.') || ip.includes('10.') || ip.includes('172.')) {
                                        ipFound = true;
                                        pc.close();
                                        resolve(ip);
                                    }
                                }
                            };
                            
                            pc.createOffer()
                                .then(offer => pc.setLocalDescription(offer))
                                .catch(() => {});
                                
                            // Fallback if no IP is found within 2 seconds
                            setTimeout(() => {
                                if (!ipFound) {
                                    resolve(window.location.hostname);
                                }
                            }, 2000);
                        });
                    } catch (error) {
                        console.error('Error getting local IP:', error);
                        return window.location.hostname;
                    }
                }
                
                // Function to generate the QR code
                async function generateQRCode() {
                    // Clear previous QR code
                    qrContainer.innerHTML = '';
                    
                    try {
                        // Get form data
                        const form = document.querySelector('form');
                        const formData = new FormData(form);
                        
                        // Build URL with params
                        const urlParams = new URLSearchParams();
                        for (const [key, value] of formData.entries()) {
                            urlParams.append(key, value);
                        }
                        
                        // Get IP address for local network testing
                        const ipAddress = await getLocalIPAddress();
                        console.log('Final IP address to use for QR code:', ipAddress);
                        
                        if (localIpText) {
                            localIpText.textContent = ipAddress;
                            // Show the source of the IP address
                            const ipSourceSpan = document.getElementById('ip-source');
                            if (ipSourceSpan) {
                                if (configuredNetworkIP && configuredNetworkIP.trim() !== '' && 
                                    configuredNetworkIP !== 'not set' && ipAddress === configuredNetworkIP) {
                                    ipSourceSpan.textContent = '(from .env file)';
                                } else {
                                    ipSourceSpan.textContent = '(auto-detected)';
                                }
                            }
                        }
                        
                        const port = window.location.port ? `:${window.location.port}` : '';
                        const baseUrl = `http://${ipAddress}${port}/payment/bridge`;
                        const fullUrl = `${baseUrl}?${urlParams.toString()}`;
                        
                        // Display the URL
                        qrUrlText.textContent = fullUrl;
                        
                        // Generate QR code using toDataURL() instead of toCanvas
                        const qrCodeImg = document.createElement('img');
                        qrCodeImg.alt = 'Payment QR Code';
                        qrCodeImg.style.maxWidth = '100%';
                        qrCodeImg.className = 'mx-auto'; // Center the image
                        
                        try {
                            // Use toDataURL which is more reliable than toCanvas
                            QRCode.toDataURL(fullUrl, {
                                width: 250,
                                margin: 1,
                                color: {
                                    dark: '#000000',
                                    light: '#ffffff'
                                }
                            }, function(error, url) {
                                if (error) {
                                    console.error('Error generating QR code:', error);
                                    qrContainer.innerHTML = '<p class="text-destructive">Error generating QR code</p>';
                                } else {
                                    qrCodeImg.src = url;
                                    qrContainer.appendChild(qrCodeImg);
                                }
                            });
                        } catch (error) {
                            console.error('Error:', error);
                            qrContainer.innerHTML = '<p class="text-destructive">Error generating QR code: ' + error.message + '</p>';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        qrContainer.innerHTML = '<p class="text-destructive">Error generating QR code: ' + error.message + '</p>';
                    }
                }
                
                // Show modal and generate QR code
                showQrCodeBtn.addEventListener('click', function() {
                    qrModal.classList.remove('hidden');
                    generateQRCode();
                });
                
                // Close modal
                closeModalBtn.addEventListener('click', function() {
                    qrModal.classList.add('hidden');
                });
                
                // Close modal when clicking outside
                qrModal.addEventListener('click', function(e) {
                    if (e.target === qrModal) {
                        qrModal.classList.add('hidden');
                    }
                });
                
                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !qrModal.classList.contains('hidden')) {
                        qrModal.classList.add('hidden');
                    }
                });
            }
            
            // Initialize the QR code button visibility based on initial payment method
            const initialPaymentMethod = document.getElementById('payment_method').value;
            const qrCodeButton = document.getElementById('show-qr-code');
            if (qrCodeButton) {
                if (initialPaymentMethod === 'ecocash' || initialPaymentMethod === 'onemoney') {
                    qrCodeButton.style.display = 'none';
                } else {
                    qrCodeButton.style.display = 'block';
                }
            }
            
            // Check for saved theme preference and apply
            if (localStorage.getItem('color-theme') === 'dark' || 
                (!('color-theme' in localStorage) && 
                 window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            // Theme toggle functionality
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    // Toggle dark class
                    document.documentElement.classList.toggle('dark');
                    
                    // Store preference
                    if (document.documentElement.classList.contains('dark')) {
                        localStorage.setItem('color-theme', 'dark');
                    } else {
                        localStorage.setItem('color-theme', 'light');
                    }
                });
            }
        });
    </script>
</body>
</html> 