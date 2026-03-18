#!/bin/bash
set -e

while ! php -r "new PDO('mysql:host=mysql;port=3306', 'laravel', 'laravel');" 2>/dev/null; do
    sleep 2
done

if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

APP_KEY_VALUE=$(grep -E "^APP_KEY=" .env 2>/dev/null | cut -d '=' -f2 | tr -d '"' | tr -d "'")
if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" = "base64:" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

JWT_SECRET_VALUE=$(grep -E "^JWT_SECRET=" .env 2>/dev/null | cut -d '=' -f2 | tr -d '"' | tr -d "'")
if [ -z "$JWT_SECRET_VALUE" ]; then
    echo "Generating JWT_SECRET..."
    php artisan jwt:secret --force
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force

php artisan cache:clear || true

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

exec apache2-foreground
