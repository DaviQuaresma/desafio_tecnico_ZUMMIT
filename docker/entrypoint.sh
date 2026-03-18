#!/bin/bash
set -e

# Aguarda MySQL estar pronto
while ! php -r "new PDO('mysql:host=mysql;port=3306', 'laravel', 'laravel');" 2>/dev/null; do
    echo "Waiting for MySQL..."
    sleep 2
done

# Instala dependências se vendor não existir
if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing dependencies..."
    COMPOSER_PROCESS_TIMEOUT=2000 composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Só continue se vendor existir
if [ ! -f "vendor/autoload.php" ]; then
    echo "ERROR: vendor/autoload.php not found. Please run 'composer install' manually."
    echo "Keeping container running for manual intervention..."
    tail -f /dev/null
fi

# Gera APP_KEY se necessário
APP_KEY_VALUE=$(grep -E "^APP_KEY=" .env 2>/dev/null | cut -d '=' -f2 | tr -d '"' | tr -d "'" || echo "")
if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" = "base64:" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Gera JWT_SECRET se necessário
JWT_SECRET_VALUE=$(grep -E "^JWT_SECRET=" .env 2>/dev/null | cut -d '=' -f2 | tr -d '"' | tr -d "'" || echo "")
if [ -z "$JWT_SECRET_VALUE" ]; then
    echo "Generating JWT_SECRET..."
    php artisan jwt:secret --force
fi

# Limpa caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Executa migrations
php artisan migrate --force

php artisan cache:clear || true

# Ajusta permissões
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

exec apache2-foreground
