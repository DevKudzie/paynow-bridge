FROM php:7.4-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install zip pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Install dependencies
RUN composer install --no-interaction --no-plugins --no-scripts

# Make sure Apache can use environment variables
RUN echo "PassEnv PAYNOW_INTEGRATION_ID PAYNOW_INTEGRATION_KEY PAYNOW_RESULT_URL PAYNOW_RETURN_URL APP_BASE_URL APP_SUCCESS_URL APP_ERROR_URL APP_ENV" >> /etc/apache2/conf-available/environment.conf \
    && a2enconf environment

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 