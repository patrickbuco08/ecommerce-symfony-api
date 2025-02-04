#!/bin/sh

# Exit script on first error
set -e

# Check if .env exists, if not, copy .env.dist
if [ ! -f .env ]; then
    echo "Copying .env.dist to .env"
    cp .env.dist .env
fi

# Install dependencies if vendor does not exist
if [ ! -d "vendor" ]; then
    echo "Running composer install"
    composer install --no-progress --no-interaction
fi

# Start PHP-FPM
exec "$@"