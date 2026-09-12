#!/bin/bash
# Fix Laravel Container Error on Production Server
# Run this script on your production server via SSH

echo "=========================================="
echo "Laravel Container Error Fix Script"
echo "=========================================="
echo ""

# Get the project directory (update this path)
PROJECT_DIR="/home/tzpwayfw1x5f/hrms.oceaninfotechcrm.com"

# Navigate to project
cd "$PROJECT_DIR" || exit 1

echo "Step 1: Clearing all caches..."
php artisan clear-compiled
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo ""
echo "Step 2: Removing bootstrap cache files..."
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php

echo ""
echo "Step 3: Fixing permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
# Uncomment and adjust if needed:
# chown -R www-data:www-data storage bootstrap/cache

echo ""
echo "Step 4: Regenerating Composer autoload..."
composer dump-autoload --optimize --no-interaction

echo ""
echo "Step 5: Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "Step 6: Testing artisan command..."
if php artisan --version > /dev/null 2>&1; then
    echo "✅ SUCCESS! Artisan is working."
    php artisan --version
else
    echo "❌ ERROR: Artisan still not working. Check the error above."
    exit 1
fi

echo ""
echo "=========================================="
echo "Fix complete!"
echo "=========================================="
