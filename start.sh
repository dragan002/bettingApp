#!/bin/bash
set -e

# Clear cached config so Railway env vars are always picked up
php artisan config:clear

# Run any pending migrations
php artisan migrate --force

# Start the PHP server
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
