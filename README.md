# Paynow Payment Bridge

A PHP bridge system for processing payments via Paynow

## Overview

This system provides a bridge for processing payments through Paynow (a Zimbabwean payment gateway). Depending on the payment type, users either stay on the bridge page waiting for a response or are redirected to the Paynow payment page.

## Features

- Support for both web and mobile payments (EcoCash, OneMoney)
- Real-time payment status checking
- Responsive and user-friendly dark mode interface
- Modern UI built with Tailwind CSS and Lucide icons
- Customizable success and error pages
- Environment-based configuration for security

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

## Environment Variables

The application uses the following environment variables:

| Variable | Description | Default |
|----------|-------------|---------|
| PAYNOW_INTEGRATION_ID | Your Paynow integration ID | none |
| PAYNOW_INTEGRATION_KEY | Your Paynow integration key | none |
| PAYNOW_RESULT_URL | URL for server-to-server notifications | http://localhost:8080/payment/update |
| PAYNOW_RETURN_URL | URL for customer redirect after payment | http://localhost:8080/payment/complete |
| APP_BASE_URL | Base URL of your application | http://localhost:8080 |
| APP_SUCCESS_URL | URL for successful payments | http://localhost:8080/payment/success |
| APP_ERROR_URL | URL for failed payments | http://localhost:8080/payment/error |
| APP_ENV | Application environment | development |

## Payment Process

1. Payment details are sent to the bridge endpoint
2. The bridge processes the payment through Paynow
3. For web payments, users are redirected to Paynow's payment page
4. For mobile payments, they receive payment instructions on the bridge page
5. After payment, they are redirected to the success or error page

## Directory Structure

```
paynow-bridge/
├── docker/               # Docker configuration files
├── public/               # Public web files
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

You can customize the look and feel of the system by modifying the view files in the `src/views/` directory. The system uses Tailwind CSS for styling and Lucide icons.

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