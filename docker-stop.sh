#!/bin/bash

echo "Stopping Paynow Bridge Docker environment..."
docker-compose down

echo ""
echo "Paynow Bridge has been stopped."
echo "To start the application again, run: ./docker-start.sh" 