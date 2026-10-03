#!/bin/sh
set -e

# 1. Substitute PORT into nginx configuration (Render assigns $PORT dynamically, default 10000)
PORT="${PORT:-10000}"
echo "--> Configuring Nginx to listen on port $PORT..."
sed -i "s/__PORT__/${PORT}/g" /etc/nginx/http.d/default.conf

# 2. SQLite database auto-creation if using SQLite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    DB_DIR=$(dirname "$DB_PATH")
    mkdir -p "$DB_DIR"
    if [ ! -f "$DB_PATH" ]; then
        echo "--> Creating SQLite database file at $DB_PATH..."
        touch "$DB_PATH"
    fi
    chown -R www-data:www-data "$DB_DIR"
    chmod -R 775 "$DB_DIR"
fi

# 3. Ensure permissions on storage and bootstrap/cache
echo "--> Setting storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 4. Create storage symlink
php artisan storage:link --force || true

# 5. Production optimization caches (if APP_KEY is provided)
if [ -n "$APP_KEY" ]; then
    echo "--> Caching Laravel configuration, routes, and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
else
    echo "--> WARNING: APP_KEY is not set. Please set APP_KEY in your Render dashboard environment variables!"
fi

# 6. Database migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "--> Running database migrations..."
    php artisan migrate --force || true
fi

# 7. Database seeding (optional)
if [ "${SEED_DATABASE:-false}" = "true" ]; then
    echo "--> Seeding database..."
    php artisan db:seed --force || true
fi

echo "--> Starting PHP-FPM..."
php-fpm -D

echo "--> Starting Nginx web server on port $PORT..."
exec nginx -g "daemon off;"
