@echo off

echo Restarting Paynow Bridge Docker environment and clearing caches...

REM Restart the containers
docker-compose restart

REM Clear PHP OpCache (if enabled)
echo Clearing PHP OpCache...
docker-compose exec -T app bash -c "php -r \"if (function_exists('opcache_reset')) { opcache_reset(); echo 'OpCache cleared.\n'; } else { echo 'OpCache not enabled.\n'; }\""

REM Clear Composer cache if needed
set /p clear_composer=Do you want to clear Composer cache? (y/n):
if "%clear_composer%"=="y" (
    docker-compose exec -T app composer clear-cache
    echo Composer cache cleared.
)

echo.
echo Paynow Bridge Docker environment has been restarted and caches cleared!
echo Access the application at: http://localhost:8080
pause 