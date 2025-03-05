#!/bin/bash
set -e

# Create logs directory if it doesn't exist
mkdir -p /var/www/html/logs
chmod -R 777 /var/www/html/logs

# Create vendor directory if it doesn't exist
if [ ! -d /var/www/html/vendor ] || [ -z "$(ls -A /var/www/html/vendor)" ]; then
  echo "Installing dependencies..."
  cd /var/www/html
  composer install --no-interaction
else
  echo "Dependencies already installed."
fi

# Generate a dump autoload to ensure PSR-4 autoloading works
cd /var/www/html
composer dump-autoload -o

# Run the command
exec "$@" 