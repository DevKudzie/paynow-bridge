# Paynow Payment Bridge

A PHP bridge system for processing payments via Paynow

## Overview

This system provides a bridge for processing payments through Paynow (a Zimbabwean payment gateway). Depending on the payment type, users either stay on the bridge page waiting for a response or are redirected to the Paynow payment page.

## Features

- Support for both web and mobile payments (EcoCash, OneMoney)
- Real-time payment status checking and polling
- Automatic redirection to success or error pages based on payment status
- Responsive and user-friendly dark mode interface
- Modern UI built with Tailwind CSS and Lucide icons
- Customizable success and error pages
- Environment-based configuration for security
- Detailed logging system with terminal output control

## Quick Start Test URLs

### Test Web Payment URL
```
http://localhost:8080/payment/bridge?reference=INV123&email=test@example.com&items[0][name]=Sample+Product&items[0][amount]=10.00
```

### Test Mobile Payment URL
```
http://localhost:8080/payment/bridge?reference=INV123&email=test@example.com&items[0][name]=Sample+Product&items[0][amount]=10.00&payment_method=ecocash&phone=0771111111
```

You can replace `0771111111` with other test phone numbers (see Test Mode section below).

### Accessing on Mobile Devices

To test on a mobile device while running on localhost:

1. **Using your local network IP**:
   - Find your computer's local IP address:
     - Windows: Run `ipconfig` in command prompt
     - macOS/Linux: Run `ifconfig` or `ip addr show` in terminal
   - Replace `localhost` with your IP address in the URL:
     ```
     http://192.168.x.x:8080/payment/bridge?reference=INV123&email=test@example.com&items[0][name]=Sample+Product&items[0][amount]=10.00
     ```
   - Make sure your mobile device is on the same network
   - **Alternatively**: Configure your IP in `.env` file:
     ```
     LOCAL_NETWORK_IP=192.168.0.163  # Replace with your actual IP
     ```
     This will ensure QR codes always use the correct IP address.

2. **Using the QR Code feature**:
   - Click the "QR Code" button next to "Proceed to Payment" 
   - A modal with a QR code will appear containing the current form data
   - Scan this QR code with your mobile device to access the payment page
   - Works with both web and mobile payment methods
   - The QR code will include all your form data including payment method and phone number
   - The system will attempt to detect your local network IP automatically
   - For more reliable results, configure your IP in the `.env` file:
     ```
     LOCAL_NETWORK_IP=192.168.0.163  # Replace with your actual IP
     ```
   - If the QR code shows "localhost" instead of your network IP, it means the environment variable isn't being correctly loaded. Try:
     1. Verify your `.env` file has the correct IP address
     2. Restart the Docker container with `docker-compose restart`
     3. Clear your browser cache and refresh the page

3. **Using ngrok for public access**:
   - Install [ngrok](https://ngrok.com/download)
   - Start ngrok tunnel to port 8080:
     ```
     ngrok http 8080
     ```
   - Use the generated URL from ngrok:
     ```
     https://your-ngrok-url.ngrok.io/payment/bridge?reference=INV123&email=test@example.com&items[0][name]=Sample+Product&items[0][amount]=10.00
     ```

## Requirements

### Option 1: Local Installation
- PHP 7.0 or higher
- Composer
- Paynow merchant account and API credentials

### Option 2: Docker Installation (Recommended)
- Docker and Docker Compose
- Paynow merchant account and API credentials

## Installation

### Option 1: Local Installation

1. Clone this repository:
   ```
   git clone https://github.com/yourusername/paynow-bridge.git
   cd paynow-bridge
   ```

2. Install dependencies using Composer:
   ```
   composer install
   ```

3. Configure your environment:
   - Copy the `.env.example` file to `.env`
   ```
   cp .env.example .env
   ```
   - Edit the `.env` file and set your Paynow credentials and other configuration values

4. Configure your web server to point to the `public` directory as the document root.

### Option 2: Docker Installation (Recommended)

1. Clone this repository:
   ```
   git clone https://github.com/yourusername/paynow-bridge.git
   cd paynow-bridge
   ```

2. Configure your environment:
   - Copy the `.env.example` file to `.env` (the start scripts will do this automatically if the file doesn't exist)
   ```
   cp .env.example .env
   ```
   - Edit the `.env` file and set your Paynow credentials and other configuration values:
   ```
   # Paynow Integration Details
   PAYNOW_INTEGRATION_ID=YOUR_INTEGRATION_ID
   PAYNOW_INTEGRATION_KEY=YOUR_INTEGRATION_KEY
   PAYNOW_RESULT_URL=http://localhost:8080/payment/update
   PAYNOW_RETURN_URL=http://localhost:8080/payment/complete
   
   # Application Settings
   APP_BASE_URL=http://localhost:8080
   APP_SUCCESS_URL=http://localhost:8080/payment/success
   APP_ERROR_URL=http://localhost:8080/payment/error
   ```

3. Start the Docker environment:
   
   **For Windows:**
   ```
   docker-start.bat
   ```
   
   **For Linux/Mac:**
   ```
   chmod +x docker-start.sh
   ./docker-start.sh
   ```

4. Access the application at http://localhost:8080

5. To stop the Docker environment:
   
   **For Windows:**
   ```
   docker-stop.bat
   ```
   
   **For Linux/Mac:**
   ```
   chmod +x docker-stop.sh
   ./docker-stop.sh
   ```

## Usage

The payment bridge accepts payment details via GET parameters and processes them through Paynow:

```
/payment/bridge?reference=INV123&email=customer@example.com&items[0][name]=Product&items[0][amount]=10.00&payment_method=ecocash&phone=0771234567
```

Parameters:
- `reference`: Reference/Invoice number
- `email`: Customer email
- `items`: Array of items with name and amount
- `payment_method`: (Optional) Payment method (ecocash, onemoney)
- `phone`: (Optional) Mobile number for mobile payments

## Switching Between Test and Production Mode

The system makes it easy to switch between test and production modes without code changes:

1. **Test Mode** (default):
   - In `.env` file set: `PAYNOW_TEST_MODE=true`
   - Ensure `PAYNOW_AUTH_EMAIL` is set to your merchant email
   - Use test credentials from Paynow

2. **Production Mode**:
   - In `.env` file set: `PAYNOW_TEST_MODE=false` 
   - Use your production integration credentials
   - No need to change any code or URLs

3. **Apply Changes**:
   - After changing the mode, restart the Docker container:
   ```
   docker-compose restart
   ```

## Environment Variables

The application uses the following environment variables:

| Variable | Description | Default |
|----------|-------------|---------|
| PAYNOW_INTEGRATION_ID | Your Paynow integration ID | none |
| PAYNOW_INTEGRATION_KEY | Your Paynow integration key | none |
| PAYNOW_RESULT_URL | URL for server-to-server notifications | http://localhost:8080/payment/update |
| PAYNOW_RETURN_URL | URL for customer redirect after payment | http://localhost:8080/payment/complete |
| PAYNOW_AUTH_EMAIL | Email for authentication in test mode | test@example.com |
| PAYNOW_TEST_MODE | Enable test mode for Paynow | true |
| APP_BASE_URL | Base URL of your application | http://localhost:8080 |
| APP_SUCCESS_URL | URL for successful payments | http://localhost:8080/payment/success |
| APP_ERROR_URL | URL for failed payments | http://localhost:8080/payment/error |
| APP_ENV | Application environment | development |
| LOCAL_NETWORK_IP | Your local network IP address for mobile QR code testing | auto-detected |
| LOGGING_ENABLED | Enable or disable application logging | true |
| LOG_PATH | Directory path for log files | /var/www/html/logs |
| LOG_LEVEL | Minimum log level to record (debug, info, warning, error) | info |
| PRINT_LOGS_TO_TERMINAL | Print logs to terminal/docker logs in real-time | true |
| DEBUG_LOGS | Include detailed data dumps in terminal logs | false |

## Test Mode

This implementation includes support for Paynow's test mode. When test mode is enabled:

1. No real transactions are processed
2. You can use the test credentials provided by Paynow
3. The `auth_email` is required and replaces the customer email when in test mode

According to Paynow documentation:
> After creating a transaction ONLY THE MERCHANT ACCOUNT USED TO CREATE THE INTEGRATION can login and Fake a Payment. Any other users will get a message saying the merchant is in testing and they cannot proceed with payment.

### Test Mode Mobile Payments

To test mobile money payments (EcoCash, OneMoney), you can use these special test phone numbers:

| Phone Number | Result |
|-------------|--------|
| 0771111111 | Success (within 5 seconds) |
| 0772222222 | Delayed Success (30 seconds) |
| 0773333333 | User Cancelled |
| 0774444444 | Insufficient Balance |

For more details, refer to the [Paynow Test Mode Documentation](https://developers.paynow.co.zw/docs/test_mode.html).

## Payment Process Flow

1. **Initiation**: Payment details are sent to the bridge endpoint
2. **Processing**: The bridge processes the payment through Paynow
3. **Payment Method Handling**:
   - **Web Payments**: Users are redirected to Paynow's payment page
   - **Mobile Payments**: Users receive payment instructions on the bridge page
4. **Status Checking**: 
   - Real-time polling for mobile payments
   - Return URL handling for web payments
5. **Completion**: User is redirected to the success or error page based on payment status

## Troubleshooting

### Common Issues

1. **Payment Status Issues**:
   - Check the logs at `/var/www/html/logs/status_checks.log` for detailed information about payment status checks
   - Verify that the poll URL is correctly being passed and processed

2. **Mobile Payment Problems**:
   - Ensure you're using the correct test phone numbers in test mode
   - Check that the `phone` parameter is properly formatted
   - Look for detailed error messages in the logs

3. **QR Code Issues**:
   - If QR codes show "localhost" instead of your network IP, set the `LOCAL_NETWORK_IP` in your `.env` file
   - If scanning the QR code doesn't work, try manually entering the URL on your mobile device
   - Remember that your mobile device must be on the same network as your development computer

4. **Redirect Loops**:
   - Clear browser cache and cookies
   - Verify URL configurations in the `.env` file

5. **Debug Information**:
   - Enable debug logs by setting `DEBUG_LOGS=true` in your `.env` file
   - Check terminal output for detailed API request/response information

### Viewing Logs

Access logs by either:
- Viewing the Docker container logs:
  ```
  docker-compose logs -f app
  ```
- Looking at the log files in the mounted volume:
  ```
  /var/www/html/logs/
  ```

## Docker Development

### When to Rebuild Docker Images

The Docker setup in this project is designed to minimize rebuilds while developing:

1. **Changes to PHP files** in the `src` or `public` directories:
   - No rebuild needed - these directories are mounted as volumes in docker-compose.yml
   - Changes are available immediately

2. **Changes to environment variables** in `.env`:
   - No rebuild needed - simply restart the container:
   ```
   docker-compose restart
   ```
   - Note that some environment variables may require a complete container restart to be recognized properly
   - If changes to environment variables aren't taking effect, try stopping and starting the container instead of just restarting:
   ```
   docker-compose down
   docker-compose up -d
   ```

3. **When to rebuild**:
   - Changes to `Dockerfile`
   - Adding new PHP extensions
   - Changes to `composer.json` (to install new dependencies)
   - Changes to Apache configuration

To rebuild the Docker image:
```
docker-compose build
docker-compose up -d
```

### Cache Considerations

1. **PHP OpCache**: 
   - Disabled in development mode to prevent caching issues
   - In production, you may want to enable it for performance

2. **Browser Cache**:
   - If you make changes to CSS or JavaScript but don't see them, try clearing your browser cache

3. **Composer Cache**:
   - Composer package information is cached
   - If you need to refresh it: `docker-compose exec app composer clear-cache`

## Directory Structure

```
paynow-bridge/
├── docker/               # Docker configuration files
├── public/               # Public web files
│   ├── css/              # Centralized CSS files
│   │   └── styles.css    # Main stylesheet with Tailwind utilities
│   ├── js/               # Centralized JavaScript files
│   │   └── tailwind-config.js # Tailwind configuration
│   ├── index.php         # Entry point
│   └── .htaccess         # URL rewriting rules
├── src/                  # Source code
│   ├── config/           # Configuration files
│   ├── controllers/      # Controllers
│   ├── models/           # Models
│   └── views/            # View templates
├── vendor/               # Composer dependencies
├── .env.example          # Example environment variables
├── .env                  # Environment variables (create from .env.example)
├── docker-compose.yml    # Docker Compose configuration
├── Dockerfile            # Docker configuration
├── composer.json         # Composer configuration
└── README.md             # This file
```

## Customization

You can customize the look and feel of the system by modifying the view files in the `src/views/` directory or by editing the centralized CSS and JavaScript files in the `public` directory. The system uses Tailwind CSS for styling and Lucide icons.

### Styling Architecture

This project uses a centralized approach to styling:

1. **Centralized Tailwind Configuration**:
   - Located at `public/js/tailwind-config.js`
   - Contains all Tailwind theme customizations
   - Consistently applied across all views

2. **Centralized Stylesheet**:
   - Located at `public/css/styles.css`
   - Contains global styles and Tailwind utilities
   - Includes both dark and light mode styles

This approach ensures consistent styling across the application and makes design changes easier to implement.

## Testing

For testing purposes, you can use the Paynow test integration credentials from their documentation. Make sure to switch to your live credentials when deploying to production.

## Paynow Documentation

For more information on the Paynow API, visit their documentation:
[Paynow Developer Documentation](https://developers.paynow.co.zw/docs/quickstart.html)

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

Built with:
- [Paynow PHP SDK](https://github.com/paynow/Paynow-PHP-SDK)
- [Tailwind CSS](https://tailwindcss.com/)
- [Lucide Icons](https://lucide.dev/)

## Receipt Generation

The system includes a receipt generation feature that creates professional PDF receipts for completed payments.

### How Receipting Works

1. **Success Page (`src/views/success.php`)**: 
   - After a successful payment, users are redirected to the success page
   - Displays transaction details and a confirmation message
   - Provides a "Download Receipt" button that links to the receipt generator

2. **Receipt Generator (`public/receipt.php`)**: 
   - Generates a PDF receipt for completed payments
   - Uses TCPDF library for PDF generation
   - Includes payment details, order items, and merchant information
   - Can be accessed directly via URL: `/receipt.php?reference=YOUR_REFERENCE`

### Receipt Features

- **PDF Format**: Professional receipts in portable PDF format
- **Transaction Details**: Includes reference numbers, payment date, status, and amount
- **Order Items**: Lists all purchased items with quantities and prices
- **Merchant Branding**: Includes merchant name and address
- **Fallback HTML**: If PDF generation fails, falls back to HTML receipt with print option

## Future Work

### Planned Improvements

1. **Email Integration**: 
   - Automatically send receipts via email after successful payments
   - Implement email templates for payment notifications

2. **Receipt Customization**:
   - Allow merchants to customize receipt layout and branding
   - Add support for merchant logos on receipts

3. **Enhanced Mobile Experience**:
   - Optimize mobile payment flows further
   - Improve QR code scanning experience

4. **Payment Analytics**:
   - Add dashboard for viewing payment statistics
   - Implement reporting features for merchants

5. **Additional Payment Methods**:
   - Add support for more mobile payment options
   - Implement international payment methods

### Known Issues

- Session data handling could be improved for more reliable receipt generation
- PDF generation requires the TCPDF library to be properly installed
- Receipt design could be enhanced for better visual appeal 