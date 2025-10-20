#!/bin/bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Copy .env file (Railway uses environment variables, but include for local fallback)
if [ -f .env.example ]; then
  cp .env.example .env
fi

# Generate application key
php artisan key:generate --force

# Clear cached configurations
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run database migrations
php artisan migrate --force
