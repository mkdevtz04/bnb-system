#!/bin/sh

# Ensure permissions are correct every time the container starts
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear and Cache configuration
# Note: In production, 'config:cache' is better than 'config:clear'
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Run migrations (Force is required for production)
# If this fails, the script stops here and won't start Apache (the White Screen)
php artisan migrate --force || echo "Migration failed, but starting server anyway..."

# Start Apache in foreground
exec apache2-foreground