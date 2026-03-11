#!/bin/sh

# Change ownership of storage and bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations if database is available
# php artisan migrate --force
#!/bin/sh

# Clear caches to ensure new Env variables are picked up
php artisan config:clear
php artisan cache:clear

# Run migrations (the --force flag is required for production)
php artisan migrate --force

# Start Apache
apache2-foreground
