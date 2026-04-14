#!/bin/bash
set -e

# Run any pending migrations
php artisan migrate --force

# Start the PHP server
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
