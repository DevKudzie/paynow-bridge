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

# Create logs directory
RUN mkdir -p /var/www/html/logs && \
    chmod -R 777 /var/www/html/logs

# Copy composer files first
COPY composer.json /var/www/html/

# Run composer install to generate vendor directory
RUN composer install --no-interaction

# Copy application files after dependencies are installed
COPY ./src /var/www/html/src
COPY ./public /var/www/html/public
COPY ./docker /var/www/html/docker

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Make sure Apache can use environment variables
RUN echo "PassEnv PAYNOW_INTEGRATION_ID PAYNOW_INTEGRATION_KEY PAYNOW_RESULT_URL PAYNOW_RETURN_URL PAYNOW_AUTH_EMAIL PAYNOW_TEST_MODE APP_BASE_URL APP_SUCCESS_URL APP_ERROR_URL APP_ENV PRINT_LOGS_TO_TERMINAL" >> /etc/apache2/conf-available/environment.conf \
    && a2enconf environment

# Expose port 80
EXPOSE 80

# Create an entrypoint script
RUN echo '#!/bin/bash\n\
# Make sure vendor directory has correct permissions\n\
chmod -R 755 /var/www/html/vendor\n\
chown -R www-data:www-data /var/www/html/vendor\n\
\n\
# Ensure autoload is up to date\n\
composer dump-autoload -o\n\
\n\
# Start Apache\n\
exec "$@"' > /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"] 