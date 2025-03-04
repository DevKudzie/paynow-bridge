#!/bin/bash

# Check if .env file exists, if not create it from .env.example
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo "Please update your .env file with your Paynow credentials before continuing."
    echo "Press Enter to continue or Ctrl+C to cancel..."
    read
fi

echo "Starting Paynow Bridge Docker environment..."
docker-compose up -d

echo ""
echo "Paynow Bridge is now running!"
echo "Access the application at: http://localhost:8080"
echo "To stop the application, run: ./docker-stop.sh" 