#!/bin/bash

echo "Restarting Paynow Bridge Docker environment and clearing caches..."

# Restart the containers
docker-compose restart

# Clear PHP OpCache (if enabled)
echo "Clearing PHP OpCache..."
docker-compose exec -T app bash -c 'php -r "if (function_exists(\"opcache_reset\")) { opcache_reset(); echo \"OpCache cleared.\n\"; } else { echo \"OpCache not enabled.\n\"; }"'

# Clear Composer cache if needed
echo "Do you want to clear Composer cache? (y/n)"
read clear_composer
if [ "$clear_composer" = "y" ]; then
    docker-compose exec -T app composer clear-cache
    echo "Composer cache cleared."
fi

echo ""
echo "Paynow Bridge Docker environment has been restarted and caches cleared!"
echo "Access the application at: http://localhost:8080" 